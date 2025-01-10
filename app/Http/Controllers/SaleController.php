<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Company;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CashCount;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
      try {
         $companyId = Auth::user()->company_id;
         $currency = $this->currencies;
         $cashCount = CashCount::where('company_id', $this->company->id)
         ->whereNull('closing_date')
         ->first();

         // Obtener ventas con sus relaciones
         $sales = Sale::with(['saleDetails.product', 'customer', 'company'])
            ->where('company_id', $companyId)
            ->get();

         // Calcular productos únicos vendidos
         $totalSales = $sales->flatMap(function ($sale) {
            return $sale->saleDetails->pluck('product_id');
         })->unique()->count();
         $totalAmount = $sales->sum('total_price');
         $monthlySales = $sales->filter(function ($sale) {
            return $sale->sale_date->isCurrentMonth();
         })->count();

         // Calcular ticket promedio
         $averageTicket = $sales->count() > 0
            ? $totalAmount / $sales->count()
            : 0;

         return view('admin.sales.index', compact(
            'sales',
            'totalSales',
            'totalAmount',
            'monthlySales',
            'averageTicket',
            'currency',
            'cashCount'
         ));
      } catch (\Exception $e) {
         Log::error('Error en index de ventas: ' . $e->getMessage());
         return redirect()->back()
            ->with('message', 'Error al cargar las ventas')
            ->with('icons', 'error');
      }
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      try {
         $companyId = Auth::user()->company_id;
         $currency = $this->currencies;

         // Obtener productos y clientes de la compañía actual
         $products = Product::where('company_id', $companyId)
            // ->where('status', true)
            ->get();
         $customers = Customer::where('company_id', $companyId)
            // ->where('status', true)
            ->get();

         return view('admin.sales.create', compact('products', 'customers', 'currency'));
      } catch (\Exception $e) {
         Log::error('Error en SaleController@create: ' . $e->getMessage());
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
      // dd($request->all());
      try {
         // Validación de los datos
         $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sale_date' => 'required|date',
            // 'payment_type' => 'required|in:cash,card,transfer',
            'items' => 'required|array|min:1',
            'total_price' => 'required|numeric|min:0',
         ]);

         DB::beginTransaction();

         // Crear la venta principal
         $sale = Sale::create([
            'sale_date' => $validated['sale_date'],
            'total_price' => $validated['total_price'],
            'company_id' => Auth::user()->company_id,
            'customer_id' => $validated['customer_id'],
         ]);

         // Procesar cada producto en la venta
         foreach ($request->items as $key => $item) {
            // Obtener el producto
            $product = Product::where('id', $key)
               ->where('company_id', Auth::user()->company_id)
               ->firstOrFail();

            // Verificar stock disponible
            if ($product->stock < $item['quantity']) {
               throw new \Exception("Stock insuficiente para el producto: {$product->name}");
            }

            // Crear el detalle de la venta
            SaleDetail::create([
               'quantity' => $item['quantity'],
               'sale_id' => $sale->id,
               'product_id' => $product->id,
            ]);

            // Actualizar el stock del producto
            $product->stock -= $item['quantity'];
            $product->save();
         }

         // Log de la acción
         Log::info('Venta creada exitosamente', [
            'user_id' => Auth::user()->id,
            'sale_id' => $sale->id,
            'company_id' => Auth::user()->company_id,
            'total' => $validated['total_price'],
         ]);

         DB::commit();

         return redirect()->route('admin.sales.index')
            ->with('message', '¡Venta registrada exitosamente!')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();
         Log::error('Error al crear venta: ' . $e->getMessage(), [
            'user_id' => Auth::user()->id,
            'company_id' => Auth::user()->company_id,
            'data' => $request->all()
         ]);

         return redirect()->back()
            ->with('message', 'Error al registrar la venta: ' . $e->getMessage())
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
         $companyId = Auth::user()->company_id;
         $currency = $this->currencies;

         // Obtener la venta con sus detalles y productos
         $sale = Sale::with(['saleDetails.product'])
            ->where('company_id', $companyId)
            ->findOrFail($id);

         // dd($sale);

         // Obtener los detalles de la venta una sola vez
         $saleDetails = $sale->saleDetails->map(function ($detail) {
            return [
               'id' => $detail->product_id,
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

         return view('admin.sales.edit', compact('sale', 'products', 'customers', 'saleDetails', 'currency'));
      } catch (\Exception $e) {
         Log::error('Error en SaleController@edit: ' . $e->getMessage());
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
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|numeric|min:1',
            'total_price' => 'required|numeric|min:0',
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

         // Actualizar datos principales de la venta
         $sale->sale_date = $validated['sale_date'];
         $sale->customer_id = $validated['customer_id'];
         $sale->total_price = $validated['total_price'];
         $sale->save();

         // Obtener IDs de detalles actuales
         $currentDetailIds = $sale->saleDetails->pluck('id')->toArray();
         $newDetailIds = [];

         // Procesar cada producto en la venta
         foreach ($request->items as $productId => $item) {
            $product = Product::where('id', $productId)
               ->where('company_id', Auth::user()->company_id)
               ->firstOrFail();

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

         // Log de la actualización
         Log::info('Venta actualizada exitosamente', [
            'user_id' => Auth::user()->id,
            'sale_id' => $sale->id,
            'previous_state' => $previousState,
            'new_state' => [
               'sale_date' => $sale->sale_date,
               'customer_id' => $sale->customer_id,
               'total_price' => $sale->total_price,
               'items' => $request->items
            ]
         ]);

         DB::commit();

         return redirect()->route('admin.sales.index')
            ->with('message', '¡Venta actualizada exitosamente!')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();
         Log::error('Error al actualizar venta: ' . $e->getMessage(), [
            'user_id' => Auth::user()->id,
            'sale_id' => $id,
            'request_data' => $request->all()
         ]);

         return redirect()->back()
            ->withInput()
            ->with('message', 'Error al actualizar la venta: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy($id)
   {
      try {
         // Buscar la venta
         $sale = Sale::findOrFail($id);

         // Verificar que la venta pertenece a la compañía del usuario
         if ($sale->company_id !== Auth::user()->company_id) {
            Log::warning('Intento de eliminación no autorizada de venta', [
               'user_id' => Auth::user()->id,
               'sale_id' => $id
            ]);

            return redirect()->back()
               ->with('message', 'No tienes permisos para eliminar esta venta')
               ->with('icons', 'error');
         }

         // Iniciar transacción
         DB::beginTransaction();

         // Guardar información para el log antes de eliminar
         $saleInfo = [
            'id' => $sale->id,
            'sale_date' => $sale->sale_date,
            'total_price' => $sale->total_price,
            'company_id' => $sale->company_id,
            'customer_id' => $sale->customer_id
         ];

         // Revertir el stock de los productos
         foreach ($sale->saleDetails as $detail) {
            $product = $detail->product;
            $product->stock += $detail->quantity; // Sumamos al stock porque es una venta
            $product->save();
         }

         // Eliminar la venta (los detalles se eliminarán en cascada)
         $sale->delete();

         DB::commit();

         // Registrar la eliminación en el log
         Log::info('Venta eliminada exitosamente', [
            'user_id' => Auth::user()->id,
            'company_id' => Auth::user()->company_id,
            'sale_info' => $saleInfo
         ]);

         // Retornar respuesta exitosa
         return response()->json([
            'success' => true,
            'message' => '¡Venta eliminada exitosamente!',
            'icons' => 'success'
         ]);
      } catch (\Exception $e) {
         // Revertir transacción en caso de error
         DB::rollBack();

         Log::error('Error al eliminar venta: ' . $e->getMessage(), [
            'user_id' => Auth::user()->id,
            'sale_id' => $id
         ]);

         return redirect()->back()
            ->with('message', 'Hubo un problema al eliminar la venta. Por favor, inténtelo de nuevo.')
            ->with('icons', 'error');
      }
   }

   /**
    * Obtiene los detalles de un producto por su código para el modal
    */
   public function getProductDetails($code)
   {
      try {
         $product = Product::with('category')
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
            'image_url' => $product->image_url
         ];

         return response()->json([
            'success' => true,
            'product' => $productData
         ]);
      } catch (\Exception $e) {
         Log::error('Error al obtener detalles del producto: ' . $e->getMessage());
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
         $product = Product::where('code', $code)
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
            'stock_status_class' => $product->stock > 10 ? 'success' : 'warning'
         ];

         return response()->json([
            'success' => true,
            'product' => $productData
         ]);
      } catch (\Exception $e) {
         Log::error('Error al buscar producto por código: ' . $e->getMessage());
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
         $saleDetails = SaleDetail::with(['product.category', 'sale.customer'])
            ->where('sale_id', $id)
            ->get();

         $sale = Sale::with('customer')->find($id);

         $details = $saleDetails->map(function ($detail) {
            return [
               'quantity' => $detail->quantity,
               'product_price' => $detail->product->sale_price,
               'product' => [
                  'code' => $detail->product->code,
                  'name' => $detail->product->name,
                  'category' => $detail->product->category->name ?? 'N/A',
                  'image_url' => $detail->product->image,
               ],
               'subtotal' => $detail->quantity * $detail->product_price
            ];
         });

         return response()->json([
            'success' => true,
            'sale' => [
               'id' => $sale->id,
               'date' => $sale->sale_date->format('d/m/Y'),
               'customer_name' => $sale->customer->name,
               'customer_email' => $sale->customer->email,
               'total_price' => $sale->total_price
            ],
            'details' => $details
         ]);
      } catch (\Exception $e) {
         Log::error('Error en getDetails de ventas: ' . $e->getMessage());
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
         Log::error('Error al generar PDF de venta: ' . $e->getMessage(), [
            'user_id' => Auth::user()->id,
            'sale_id' => $id
         ]);

         return redirect()->back()
            ->with('message', 'Error al generar el PDF de la venta. Por favor, inténtelo de nuevo.')
            ->with('icons', 'error');
      }
   }
}
