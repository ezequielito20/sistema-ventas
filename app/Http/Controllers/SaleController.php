<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Company;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CashCount;
use App\Models\SaleDetail;
use App\Models\CashMovement;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SaleController extends Controller
{

   public $currencies;
   protected $company;

   public function __construct()
   {
      $this->middleware(function ($request, $next) {
         $this->company = Auth::user()->company;
         $this->currencies = DB::table('currencies')
            ->where('country_id', $this->company->country)
            ->first();

         return $next($request);
      });
   }

   public function index()
   {
      // Obtener la fecha de inicio y fin de la semana actual
      $startOfWeek = Carbon::now()->startOfWeek();
      $endOfWeek = Carbon::now()->endOfWeek();
      
      // Obtener todas las ventas con paginación
      $sales = Sale::where('company_id', $this->company->id)
                  ->with(['customer', 'saleDetails', 'saleDetails.product'])
                  ->orderBy('sale_date', 'desc')
                  ->paginate(15);
      
      // Calcular ventas de esta semana
      $salesThisWeek = Sale::where('company_id', $this->company->id)
                          ->whereBetween('sale_date', [$startOfWeek, $endOfWeek])
                          ->get();
      
      // 1. Total de ventas en dinero esta semana
      $totalSalesAmountThisWeek = $salesThisWeek->sum('total_price');
      
      // 2. Ingresos netos (ganancias) esta semana - asumiendo un margen promedio del 35%
      $profitMargin = 0.35; // 35% de margen de ganancia
      $totalProfitThisWeek = $totalSalesAmountThisWeek * $profitMargin;
      
      // 3. Cantidad de ventas esta semana
      $salesCountThisWeek = $salesThisWeek->count();
      
      // Otros cálculos existentes
      $totalSales = $sales->sum(function ($sale) {
          return $sale->saleDetails->count();
      });
      
      $totalAmount = $sales->sum('total_price');
      
      $monthlySales = Sale::where('company_id', $this->company->id)
                          ->whereMonth('sale_date', Carbon::now()->month)
                          ->count();
      
      $averageTicket = $sales->count() > 0 ? $totalAmount / $sales->count() : 0;
      
      // Obtener la caja actual (abierta)
      $currentCashCount = CashCount::where('company_id', $this->company->id)
                                  ->whereNull('closing_date')
                                  ->first();
      
      // Calcular porcentajes dinámicos basados en ventas desde la apertura de la caja
      $salesPercentageThisWeek = 0;
      $profitPercentageThisWeek = 0;
      $salesCountPercentageThisWeek = 0;
      $averageTicketPercentage = 0;
      
      if ($currentCashCount) {
         // Obtener todas las ventas desde que se abrió la caja actual
         $salesSinceCashOpen = Sale::where('company_id', $this->company->id)
                                  ->where('sale_date', '>=', $currentCashCount->opening_date)
                                  ->get();
         
         $totalSalesSinceCashOpen = $salesSinceCashOpen->sum('total_price');
         $totalProfitSinceCashOpen = $totalSalesSinceCashOpen * $profitMargin;
         $totalSalesCountSinceCashOpen = $salesSinceCashOpen->count();
         $averageTicketSinceCashOpen = $totalSalesCountSinceCashOpen > 0 ? $totalSalesSinceCashOpen / $totalSalesCountSinceCashOpen : 0;
         
         // Calcular porcentajes
         if ($totalSalesSinceCashOpen > 0) {
            $salesPercentageThisWeek = round(($totalSalesAmountThisWeek / $totalSalesSinceCashOpen) * 100, 1);
         }
         
         if ($totalProfitSinceCashOpen > 0) {
            $profitPercentageThisWeek = round(($totalProfitThisWeek / $totalProfitSinceCashOpen) * 100, 1);
         }
         
         if ($totalSalesCountSinceCashOpen > 0) {
            $salesCountPercentageThisWeek = round(($salesCountThisWeek / $totalSalesCountSinceCashOpen) * 100, 1);
         }
         
         if ($averageTicketSinceCashOpen > 0) {
            $averageTicketPercentage = round((($averageTicket - $averageTicketSinceCashOpen) / $averageTicketSinceCashOpen) * 100, 1);
         }
      }
      
      $currency = $this->currencies;
      $cashCount = CashCount::where('company_id', $this->company->id)
                          ->whereNull('closing_date')
                          ->exists();
      
      return view('admin.sales.index', compact(
          'sales', 
          'totalSales', 
          'totalAmount', 
          'monthlySales', 
          'averageTicket', 
          'currency', 
          'cashCount',
          'totalSalesAmountThisWeek',
          'totalProfitThisWeek',
          'salesCountThisWeek',
          'salesPercentageThisWeek',
          'profitPercentageThisWeek',
          'salesCountPercentageThisWeek',
          'averageTicketPercentage'
      ));
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create(Request $request)
   {
      try {
         $company = $this->company;
         $companyId = $company->id;
         $currency = $this->currencies;

         // Obtener productos con solo los campos necesarios
         $products = Product::where('company_id', $companyId)
            ->where('stock', '>', 0)
            ->select('id', 'code', 'name', 'image', 'stock', 'sale_price', 'category_id')
            ->with(['category:id,name']) // Solo cargar la categoría con campos necesarios
            ->get()
            ->map(function ($product) {
                // Agregar el image_url al producto
                $product->image_url = $product->image_url;
                return $product;
            });

         // Obtener clientes con solo los campos necesarios para el select
         $customers = Customer::where('company_id', $companyId)
            ->select('id', 'name', 'total_debt')
            ->orderBy('name', 'asc')
            ->get();

         // Obtener el customer_id de la URL si existe
         $selectedCustomerId = $request->input('customer_id');

         // Capturar la URL de referencia para redirección posterior
         $referrerUrl = $request->header('referer');
         if ($referrerUrl && !str_contains($referrerUrl, 'sales/create')) {
            session(['sales_referrer' => $referrerUrl]);
         }

         return view('admin.sales.create', compact('products', 'customers', 'currency', 'selectedCustomerId'));
      } catch (\Exception $e) {
         return redirect()->back()
            ->with('message', 'Error al cargar el formulario de venta')
            ->with('icons', 'error');
      }
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(Request $request)
   {
      try {
         // Validación de los datos
         $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sale_date' => 'required|date',
            'sale_time' => 'required|date_format:H:i',
            'sale_details' => 'required|array|min:1',
            'sale_details.*.product_id' => 'required|exists:products,id',
            'sale_details.*.quantity' => 'required|numeric|min:1',
            'sale_details.*.unit_price' => 'required|numeric|min:0',
            'sale_details.*.subtotal' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:1000',
            'already_paid' => 'required|boolean',
         ]);

         DB::beginTransaction();

         // Verificar si hay una caja abierta
         $currentCashCount = CashCount::where('company_id', $this->company->id)
            ->whereNull('closing_date')
            ->first();

         if (!$currentCashCount) {
            if ($request->expectsJson()) {
               return response()->json([
                  'success' => false,
                  'message' => 'No hay una caja abierta. Debe abrir una caja antes de realizar ventas.'
               ], 400);
            }
            
            return redirect()->back()
               ->with('message', 'No hay una caja abierta. Debe abrir una caja antes de realizar ventas.')
               ->with('icons', 'error');
         }

         // Obtener el cliente
         $customer = Customer::findOrFail($validated['customer_id']);

         // Crear la venta principal
         $sale = Sale::create([
            'sale_date' => $validated['sale_date'] . ' ' . $validated['sale_time'], // Combinar fecha y hora del formulario
            'total_price' => $validated['total_price'],
            'company_id' => Auth::user()->company_id,
            'customer_id' => $validated['customer_id'],
            'cash_count_id' => $currentCashCount->id,
            'note' => $validated['note'] ?? null,
         ]);

         // Manejar el pago automático si ya pagó
         if ($validated['already_paid']) {
            // Obtener la deuda anterior del cliente
            $previousDebt = $customer->total_debt;
            
            // Registrar el pago automático en la tabla debt_payments
            DB::table('debt_payments')->insert([
               'company_id' => Auth::user()->company_id,
               'customer_id' => $validated['customer_id'],
               'previous_debt' => $previousDebt,
               'payment_amount' => $validated['total_price'],
               'remaining_debt' => $previousDebt, // La deuda restante es igual a la anterior porque ya pagó esta venta
               'notes' => 'Pago automático registrado al crear la venta #' . $sale->id,
               'user_id' => Auth::user()->id,
               'created_at' => now(),
               'updated_at' => now(),
            ]);

            // No actualizar la deuda del cliente porque ya pagó
            // La deuda se mantiene igual
         } else {
            // Actualizar la deuda del cliente solo si no pagó
            $customer->total_debt = $customer->total_debt + $validated['total_price'];
            $customer->save();
         }

         // Obtener todos los productos necesarios en una sola consulta
         $productIds = collect($request->sale_details)->pluck('product_id')->unique();
         $products = Product::whereIn('id', $productIds)
            ->select('id', 'stock')
            ->get()
            ->keyBy('id');

         // Procesar cada producto en la venta
         foreach ($request->sale_details as $item) {
            // Crear el detalle de venta
            SaleDetail::create([
               'sale_id' => $sale->id,
               'product_id' => $item['product_id'],
               'quantity' => $item['quantity'],
               'unit_price' => $item['unit_price'],
               'subtotal' => $item['subtotal'],
            ]);

            // Actualizar el stock del producto usando el modelo ya cargado
            $product = $products->get($item['product_id']);
            if ($product) {
               $product->stock -= $item['quantity'];
               $product->save();
            }
         }

         // Registrar la transacción en la caja usando CashMovement en lugar de CashTransaction
         CashMovement::create([
            'cash_count_id' => $currentCashCount->id,
            'amount' => $validated['total_price'],
            'type' => CashMovement::TYPE_INCOME,
            'description' => 'Venta #' . $sale->id,
         ]);

         DB::commit();

         // Si es una petición AJAX, devolver JSON
         if ($request->expectsJson()) {
            return response()->json([
               'success' => true,
               'message' => '¡Venta procesada exitosamente!',
               'sale_id' => $sale->id,
               'redirect_url' => route('admin.sales.index')
            ]);
         }

         // Determinar la redirección basada en el botón presionado
         if ($request->input('action') == 'save_and_new') {
            return redirect()->route('admin.sales.create')
                ->with('message', '¡Venta procesada exitosamente! Puedes crear otra venta.')
                ->with('icons', 'success');
         }

         // Verificar si hay una URL de referencia guardada
         $referrerUrl = session('sales_referrer');
         if ($referrerUrl) {
            // Limpiar la session
            session()->forget('sales_referrer');
            
            return redirect($referrerUrl)
                ->with('message', '¡Venta registrada exitosamente!')
                ->with('icons', 'success');
         }

         // Fallback: redirigir a la lista de ventas
         return redirect()->route('admin.sales.index')
            ->with('message', '¡Venta registrada exitosamente!')
            ->with('icons', 'success');

      } catch (\Illuminate\Validation\ValidationException $e) {
         if ($request->expectsJson()) {
            return response()->json([
               'success' => false,
               'message' => 'Error de validación en los datos de la venta',
               'errors' => $e->errors()
            ], 422);
         }
         
         return redirect()->back()
            ->withErrors($e->validator)
            ->withInput()
            ->with('message', 'Error de validación en los datos de la venta')
            ->with('icons', 'error');
      } catch (\Exception $e) {
         DB::rollBack();

         if ($request->expectsJson()) {
            return response()->json([
               'success' => false,
               'message' => 'Hubo un problema al registrar la venta: ' . $e->getMessage()
            ], 500);
         }

         return redirect()->back()
            ->withInput()
            ->with('message', 'Hubo un problema al registrar la venta: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Display the specified resource.
    */
   public function show(Sale $sale)
   {
      //
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit($id)
   {
      try {
         $company = $this->company;
         $companyId = $company->id;
         $currency = $this->currencies;

         // Obtener la venta con sus detalles y productos
         $sale = Sale::with(['saleDetails.product'])
            ->where('company_id', $companyId)
            ->findOrFail($id);

         // dd($sale);

         // Obtener los detalles de la venta una sola vez
         $saleDetails = $sale->saleDetails->map(function ($detail) {
            return [
               'product_id' => $detail->product_id,
               'code' => $detail->product->code,
               'name' => $detail->product->name,
               'quantity' => $detail->quantity,
               'sale_price' => $detail->product->sale_price,
               'subtotal' => $detail->quantity * $detail->product->sale_price,
               'stock' => $detail->product->stock + $detail->quantity,
               'stock_status_class' => $detail->product->stock > 10 ? 'success' : ($detail->product->stock > 0 ? 'warning' : 'danger'),
            ];
         });

         // Calcular el total inicial
         $totalAmount = $saleDetails->sum('subtotal');

         // Obtener productos y clientes para los selectores
         $products = Product::where('company_id', $companyId)
            ->where('stock', '>', 0)
            ->get();
         $customers = Customer::where('company_id', $companyId)->get();

         return view('admin.sales.edit', compact('sale', 'products', 'customers', 'saleDetails', 'currency', 'company'));
      } catch (\Exception $e) {
         return redirect()->route('admin.sales.index')
            ->with('message', 'Error al cargar el formulario de edición')
            ->with('icons', 'error');
      }
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(Request $request, $id)
   {
      try {
         // Validación de datos
         $validated = $request->validate([
            'sale_date' => 'required|date',
            'sale_time' => 'nullable|date_format:H:i',
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|min:1',
            'items.*.quantity' => 'required|numeric|min:1',
            'total_price' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:1000',
         ]);

         DB::beginTransaction();

         // Obtener la venta
         $sale = Sale::where('company_id', Auth::user()->company_id)
            ->findOrFail($id);

         // Guardar estado anterior para el log
         $previousState = [
            'sale_date' => $sale->sale_date,
            'customer_id' => $sale->customer_id,
            'total_price' => $sale->total_price,
            'details' => $sale->saleDetails->map(function ($detail) {
               return [
                  'product_id' => $detail->product_id,
                  'quantity' => $detail->quantity
               ];
            })->toArray()
         ];

         // Si el cliente cambió o el precio cambió, actualizar las deudas
         if ($sale->customer_id != $validated['customer_id'] || $sale->total_price != $validated['total_price']) {
            // Restar la deuda del cliente anterior
            $oldCustomer = Customer::findOrFail($sale->customer_id);
            $oldCustomer->total_debt = max(0, $oldCustomer->total_debt - $sale->total_price);
            $oldCustomer->save();

            // Sumar la deuda al nuevo cliente
            $newCustomer = Customer::findOrFail($validated['customer_id']);
            $newCustomer->total_debt = $newCustomer->total_debt + $validated['total_price'];
            $newCustomer->save();
         } else if ($sale->total_price != $validated['total_price']) {
            // Solo cambió el precio, actualizar la deuda del mismo cliente
            $customer = Customer::findOrFail($sale->customer_id);
            $customer->total_debt = $customer->total_debt - $sale->total_price + $validated['total_price'];
            $customer->save();
         }

         // Actualizar datos principales de la venta
         // Combinar fecha y hora si se proporciona
         if (isset($validated['sale_time'])) {
            $sale->sale_date = $validated['sale_date'] . ' ' . $validated['sale_time'];
         } else {
         $sale->sale_date = $validated['sale_date'];
         }
         $sale->customer_id = $validated['customer_id'];
         $sale->total_price = $validated['total_price'];
         $sale->note = $validated['note'] ?? null;
         $sale->save();

         // Obtener IDs de detalles actuales
         $currentDetailIds = $sale->saleDetails->pluck('id')->toArray();
         $newDetailIds = [];

         // Procesar cada producto en la venta
         foreach ($request->items as $productId => $item) {
            // Validar que el productId sea un entero válido
            if (!is_numeric($productId) || $productId <= 0) {
               throw new \Exception("ID de producto inválido: {$productId}. Debe ser un número mayor a 0.");
            }

            $product = Product::where('id', $productId)
               ->where('company_id', Auth::user()->company_id)
               ->first();

            // Verificar si el producto existe
            if (!$product) {
               // Intentar obtener información del producto para un mensaje más descriptivo
               $productInfo = Product::find($productId);
               if ($productInfo) {
                  throw new \Exception("El producto '{$productInfo->name}' (ID: {$productId}) no pertenece a esta empresa.");
               } else {
                  throw new \Exception("El producto con ID {$productId} no existe en la base de datos.");
               }
            }

            // Buscar si ya existe el detalle
            $detail = SaleDetail::where('sale_id', $sale->id)
               ->where('product_id', $productId)
               ->first();

            if ($detail) {
               // Actualizar stock: devolver la cantidad anterior y restar la nueva
               $stockDifference = $detail->quantity - $item['quantity'];
               $product->stock += $stockDifference;

               // Verificar stock suficiente
               if ($product->stock < 0) {
                  throw new \Exception("Stock insuficiente para el producto: {$product->name}");
               }

               // Actualizar detalle
               $detail->quantity = $item['quantity'];
               $detail->save();
               $newDetailIds[] = $detail->id;
            } else {
               // Verificar stock suficiente para nuevo detalle
               if ($product->stock < $item['quantity']) {
                  throw new \Exception("Stock insuficiente para el producto: {$product->name}");
               }

               // Crear nuevo detalle
               $detail = SaleDetail::create([
                  'sale_id' => $sale->id,
                  'product_id' => $productId,
                  'quantity' => $item['quantity']
               ]);

               // Actualizar stock
               $product->stock -= $item['quantity'];
               $newDetailIds[] = $detail->id;
            }

            $product->save();
         }

         // Eliminar detalles que ya no están en la venta
         $detailsToDelete = array_diff($currentDetailIds, $newDetailIds);
         foreach ($detailsToDelete as $detailId) {
            $detail = SaleDetail::find($detailId);
            if ($detail) {
               // Devolver al stock la cantidad que se elimina
               $product = Product::find($detail->product_id);
               if ($product) {
                  $product->stock += $detail->quantity;
                  $product->save();
               }
               $detail->delete();
            }
         }

         DB::commit();

         return redirect()->route('admin.sales.index')
            ->with('message', '¡Venta actualizada exitosamente!')
            ->with('icons', 'success')
            ->with('update_success', true);
      } catch (\Exception $e) {
         DB::rollBack();
         return redirect()->back()
            ->withInput()
            ->with('message', 'Hubo un problema al actualizar la venta: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy($id)
   {
      try {
         DB::beginTransaction();

         // Buscar la venta
         $sale = Sale::where('company_id', Auth::user()->company_id)
            ->findOrFail($id);

         // Verificar si hay pagos de deuda del cliente después de la fecha de esta venta
         $debtPayments = DB::table('debt_payments')
            ->where('customer_id', $sale->customer_id)
            ->where('company_id', Auth::user()->company_id)
            ->where('created_at', '>', $sale->sale_date)
            ->get();

         if ($debtPayments->count() > 0) {
            $totalPaid = $debtPayments->sum('payment_amount');
            $customerName = $sale->customer->name ?? 'Cliente';
            
            return response()->json([
               'error' => true,
               'message' => "⚠️ No se puede eliminar esta venta porque el cliente tiene pagos de deuda posteriores.\n\n" .
                           "📊 Detalles:\n" .
                           "• Cliente: {$customerName}\n" .
                           "• Venta #{$sale->id} del " . $sale->sale_date->format('d/m/Y') . "\n" .
                           "• Total de la venta: $" . number_format($sale->total_price, 2) . "\n" .
                           "• Pagos posteriores: $" . number_format($totalPaid, 2) . "\n" .
                           "• Cantidad de pagos posteriores: {$debtPayments->count()}\n\n" .
                           "🔧 Acción requerida:\n" .
                           "Primero debes eliminar todos los pagos de deuda posteriores a esta venta antes de poder eliminarla.",
               'icons' => 'warning',
               'has_payments' => true,
               'payments_count' => $debtPayments->count(),
               'total_paid' => $totalPaid
            ], 200);
         }

         // Restar la deuda del cliente
         $customer = Customer::findOrFail($sale->customer_id);
         $customer->total_debt = max(0, $customer->total_debt - $sale->total_price);
         $customer->save();

         // Eliminar movimientos de caja asociados a esta venta
         CashMovement::where('description', 'Venta #' . $sale->id)->delete();

         // Eliminar la venta
         $sale->delete();

         DB::commit();

         return response()->json([
            'success' => true,
            'message' => '¡Venta eliminada exitosamente!',
            'icons' => 'success'
         ]);

      } catch (\Exception $e) {
         DB::rollBack();

         return response()->json([
            'success' => false,
            'message' => 'Hubo un problema al eliminar la venta: ' . $e->getMessage(),
            'icons' => 'error'
         ], 500);
      }
   }

   /**
    * Obtiene los detalles de un producto por su código para el modal
    */
   public function getProductDetails($code)
   {
      try {
         $product = Product::select('id', 'code', 'name', 'stock', 'sale_price', 'image', 'category_id')
            ->with(['category:id,name'])
            ->where('code', $code)
            ->where('company_id', Auth::user()->company_id)
            ->first();

         if (!$product) {
            return response()->json([
               'success' => false,
               'message' => 'Producto no encontrado'
            ], 404);
         }

         // Preparar la respuesta con los datos necesarios para la vista
         $productData = [
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'stock' => $product->stock,
            'sale_price' => $product->sale_price,
            'category' => $product->category->name,
            'stock_status_class' => $product->stock > 10 ? 'success' : ($product->stock > 0 ? 'warning' : 'danger'),
            'image' => $product->image_url
         ];

         return response()->json([
            'success' => true,
            'product' => $productData
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Error al cargar los datos del producto'
         ], 500);
      }
   }

   /**
    * Busca un producto por código para la entrada rápida
    */
   public function getProductByCode($code)
   {
      try {
         $product = Product::select('id', 'code', 'name', 'stock', 'sale_price', 'image')
            ->where('code', $code)
            ->where('company_id', Auth::user()->company_id)
            ->first();

         if (!$product) {
            return response()->json([
               'success' => false,
               'message' => 'Producto no encontrado'
            ], 404);
         }

         // Verificar stock
         if ($product->stock <= 0) {
            return response()->json([
               'success' => false,
               'message' => 'El producto no tiene stock disponible'
            ], 400);
         }

         // Preparar la respuesta con los datos necesarios
         $productData = [
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'stock' => $product->stock,
            'sale_price' => $product->sale_price,
            'stock_status_class' => $product->stock > 10 ? 'success' : 'warning',
            'image' => $product->image_url
         ];

         return response()->json([
            'success' => true,
            'product' => $productData
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Error al buscar el producto'
         ], 500);
      }
   }

   /**
    * Obtiene los detalles de una venta por su ID para el modal
    */
   public function getDetails($id)
   {
      try {
         // Verificar que la venta existe primero
         $sale = Sale::with('customer')->find($id);
         
         if (!$sale) {
            return response()->json([
               'success' => false,
               'message' => 'Venta no encontrada'
            ], 404);
         }
         
         $saleDetails = SaleDetail::with(['product.category', 'sale.customer'])
            ->where('sale_id', $id)
            ->get();

         $details = $saleDetails->map(function ($detail) {
            return [
               'quantity' => $detail->quantity,
               'unit_price' => $detail->product->sale_price,
               'subtotal' => $detail->quantity * $detail->product->sale_price,
               'product' => [
                  'code' => $detail->product->code,
                  'name' => $detail->product->name,
                  'category' => [
                     'name' => $detail->product->category->name ?? 'Sin categoría'
                  ]
               ]
            ];
         });

         $response = [
            'customer' => [
               'name' => $sale->customer->name,
               'email' => $sale->customer->email,
               'phone' => $sale->customer->phone
            ],
            'sale_date' => $sale->sale_date->format('d/m/Y'),
            'sale_time' => $sale->sale_date->format('H:i'),
            'total_price' => $sale->total_price,
            'note' => $sale->note,
            'details' => $details
         ];

         return response()->json($response);
      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Error al cargar los detalles de la venta'
         ], 500);
      }
   }

   /**
    * Imprimir una venta
    */
   public function printSale($id)
   {
      try {
         // Obtener la venta con sus relaciones
         $sale = Sale::with(['customer', 'company'])->findOrFail($id);

         // Verificar que el usuario tenga acceso a esta venta (misma compañía)
         if ($sale->company_id !== Auth::user()->company_id) {
            return redirect()->back()
               ->with('message', 'No tiene permiso para acceder a esta venta.')
               ->with('icons', 'error');
         }

         // Obtener los detalles de la venta
         $saleDetails = SaleDetail::with(['product'])
            ->where('sale_id', $id)
            ->get();

         // Obtener la compañía
         $company = Company::find($sale->company_id);

         // Obtener el cliente
         $customer = Customer::find($sale->customer_id);

         $currency = DB::table('currencies')->where('country_id', $company->country)->first();

         // Generar el PDF
         $pdf = PDF::loadView('admin.sales.print', compact(
            'sale',
            'saleDetails',
            'company',
            'customer',
            'currency'
         ));

         // Configurar el PDF
         $pdf->setPaper('a4');

         // Nombre del archivo
         $fileName = 'factura-' . str_pad($sale->id, 8, '0', STR_PAD_LEFT) . '.pdf';

         // Retornar el PDF para descarga o visualización
         return $pdf->stream($fileName);
      } catch (\Exception $e) {
         return redirect()->back()
            ->with('message', 'Error al generar el PDF de la venta. Por favor, inténtelo de nuevo.')
            ->with('icons', 'error');
      }
   }

   public function report()
   {
      $company = $this->company;
      $currency = $this->currencies;
      $sales = Sale::with(['saleDetails.product', 'customer', 'company'])->where('company_id', $company->id)->orderBy('created_at', 'desc')->get();
      $pdf = PDF::loadView('admin.sales.report', compact('sales', 'company', 'currency'));
      return $pdf->stream('reporte-ventas.pdf');
   }
}
