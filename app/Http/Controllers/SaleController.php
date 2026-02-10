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
use Illuminate\Support\Facades\Gate;

use Carbon\Carbon;

class SaleController extends Controller
{

   public $currencies;
   protected $company;

   public function __construct()
   {
      $this->middleware(function ($request, $next) {
         $this->company = Auth::user()->company;

         // Obtener la moneda de la empresa configurada
         if ($this->company && $this->company->currency) {
            // Buscar la moneda por código en lugar de por país
            $this->currencies = DB::table('currencies')
               ->select('id', 'name', 'code', 'symbol', 'country_id')
               ->where('code', $this->company->currency)
               ->first();
         }

         // Fallback si no se encuentra la moneda configurada
         if (!$this->currencies) {
            $this->currencies = DB::table('currencies')
               ->select('id', 'name', 'code', 'symbol', 'country_id')
               ->where('country_id', $this->company->country)
               ->first();
         }

         return $next($request);
      });
   }

   /**
    * Genera paginación inteligente con ventana dinámica
    */
   private function generateSmartPagination($paginator, $windowSize = 2)
   {
      $currentPage = $paginator->currentPage();
      $lastPage = $paginator->lastPage();

      if ($lastPage <= 1) {
         // No hay paginación
         $paginator->smartLinks = [];
         $paginator->hasPrevious = false;
         $paginator->hasNext = false;
         $paginator->previousPageUrl = null;
         $paginator->nextPageUrl = null;
         $paginator->firstPageUrl = null;
         $paginator->lastPageUrl = null;
         return $paginator;
      }

      $smartLinks = [];

      // Siempre mostrar primera página
      $smartLinks[] = [
         'page' => 1,
         'url' => $paginator->url(1),
         'label' => 1,
         'active' => $currentPage == 1,
         'isSeparator' => false,
      ];

      // Calcular rango de ventana centrado en la página actual
      $start = max(2, $currentPage - $windowSize);
      $end = min($lastPage - 1, $currentPage + $windowSize);

      // Separador izquierdo si hay hueco entre 1 y el inicio de la ventana
      if ($start > 2) {
         $smartLinks[] = [
            'page' => '...',
            'url' => null,
            'label' => '...',
            'active' => false,
            'isSeparator' => true,
         ];
      }

      // Páginas de la ventana
      for ($i = $start; $i <= $end; $i++) {
         $smartLinks[] = [
            'page' => $i,
            'url' => $paginator->url($i),
            'label' => $i,
            'active' => $i == $currentPage,
            'isSeparator' => false,
         ];
      }

      // Separador derecho si hay hueco entre el final de la ventana y la última página
      if ($end < $lastPage - 1) {
         $smartLinks[] = [
            'page' => '...',
            'url' => null,
            'label' => '...',
            'active' => false,
            'isSeparator' => true,
         ];
      }

      // Siempre mostrar última página (si hay más de una)
      if ($lastPage > 1) {
         $smartLinks[] = [
            'page' => $lastPage,
            'url' => $paginator->url($lastPage),
            'label' => $lastPage,
            'active' => $currentPage == $lastPage,
            'isSeparator' => false,
         ];
      }

      // Info adicional de navegación
      $paginator->smartLinks = $smartLinks;
      $paginator->hasPrevious = $paginator->previousPageUrl() !== null;
      $paginator->hasNext = $paginator->nextPageUrl() !== null;
      $paginator->previousPageUrl = $paginator->previousPageUrl();
      $paginator->nextPageUrl = $paginator->nextPageUrl();
      $paginator->firstPageUrl = $paginator->url(1);
      $paginator->lastPageUrl = $paginator->url($lastPage);

      return $paginator;
   }

   public function index(Request $request)
   {
      // Optimización de permisos - verificar una sola vez
      $permissions = [
         'can_report' => Gate::allows('sales.report'),
         'can_create' => Gate::allows('sales.create'),
         'can_edit' => Gate::allows('sales.edit'),
         'can_show' => Gate::allows('sales.show'),
         'can_destroy' => Gate::allows('sales.destroy'),
         'can_print' => Gate::allows('sales.print'),
      ];

      // Obtener la fecha de inicio y fin de la semana actual
      $startOfWeek = Carbon::now()->startOfWeek();
      $endOfWeek = Carbon::now()->endOfWeek();

      // Consulta básica de ventas con paginación - OPTIMIZADA para evitar N+1
      $query = Sale::select('id', 'sale_date', 'total_price', 'customer_id', 'company_id', 'note')
         ->where('company_id', $this->company->id)
         ->with([
            'customer:id,name,email,phone',
            'saleDetails:id,sale_id,product_id,quantity,unit_price,subtotal',
            'saleDetails.product:id,code,name,image,category_id',
            'saleDetails.product.category:id,name'
         ]);

      // Aplicar búsqueda si se proporciona
      if ($request->has('search') && $request->search) {
         $searchTerm = $request->search;
         $query->where(function ($q) use ($searchTerm) {
            // Buscar por ID de venta
            $q->where('id', 'LIKE', "%{$searchTerm}%")
               // Buscar por monto total (convertir a texto para búsqueda)
               ->orWhereRaw("CAST(total_price AS TEXT) ILIKE ?", ["%{$searchTerm}%"])
               // Buscar por fecha en múltiples formatos
               ->orWhereRaw("CAST(sale_date AS TEXT) ILIKE ?", ["%{$searchTerm}%"])
               ->orWhereRaw("TO_CHAR(sale_date, 'DD/MM/YY') ILIKE ?", ["%{$searchTerm}%"])
               ->orWhereRaw("TO_CHAR(sale_date, 'DD/MM/YYYY') ILIKE ?", ["%{$searchTerm}%"])
               ->orWhereRaw("TO_CHAR(sale_date, 'DD-MM-YY') ILIKE ?", ["%{$searchTerm}%"])
               ->orWhereRaw("TO_CHAR(sale_date, 'DD-MM-YYYY') ILIKE ?", ["%{$searchTerm}%"])
               ->orWhereRaw("TO_CHAR(sale_date, 'DD.MM.YY') ILIKE ?", ["%{$searchTerm}%"])
               ->orWhereRaw("TO_CHAR(sale_date, 'DD.MM.YYYY') ILIKE ?", ["%{$searchTerm}%"])
               // Buscar por día y mes (formato dd/mm)
               ->orWhereRaw("TO_CHAR(sale_date, 'DD/MM') ILIKE ?", ["%{$searchTerm}%"])
               ->orWhereRaw("TO_CHAR(sale_date, 'DD-MM') ILIKE ?", ["%{$searchTerm}%"])
               ->orWhereRaw("TO_CHAR(sale_date, 'DD.MM') ILIKE ?", ["%{$searchTerm}%"])
               // Buscar en información del cliente
               ->orWhereHas('customer', function ($customerQuery) use ($searchTerm) {
                  $customerQuery->whereRaw('name ILIKE ?', ["%{$searchTerm}%"])
                     ->orWhereRaw('email ILIKE ?', ["%{$searchTerm}%"])
                     ->orWhereRaw('phone ILIKE ?', ["%{$searchTerm}%"]);
               })
               // Buscar en productos de la venta
               ->orWhereHas('saleDetails.product', function ($productQuery) use ($searchTerm) {
                  $productQuery->whereRaw('code ILIKE ?', ["%{$searchTerm}%"])
                     ->orWhereRaw('name ILIKE ?', ["%{$searchTerm}%"])
                     ->orWhereHas('category', function ($categoryQuery) use ($searchTerm) {
                        $categoryQuery->whereRaw('name ILIKE ?', ["%{$searchTerm}%"]);
                     });
               });
         });
      }

      // Aplicar filtro por fecha desde
      if ($request->has('dateFrom') && $request->dateFrom) {
         $query->whereDate('sale_date', '>=', $request->dateFrom);
      }

      // Aplicar filtro por fecha hasta
      if ($request->has('dateTo') && $request->dateTo) {
         $query->whereDate('sale_date', '<=', $request->dateTo);
      }

      // Aplicar filtro por monto mínimo
      if ($request->has('amountMin') && $request->amountMin) {
         $query->where('total_price', '>=', $request->amountMin);
      }

      // Aplicar filtro por monto máximo
      if ($request->has('amountMax') && $request->amountMax) {
         $query->where('total_price', '<=', $request->amountMax);
      }

      // Aplicar paginación manteniendo los parámetros de búsqueda
      $sales = $query->orderBy('sale_date', 'desc')->paginate(15)->withQueryString();

      // OPTIMIZACIÓN: Agregar atributos calculados para evitar N+1 en la vista
      $sales->getCollection()->transform(function ($sale) {
         $sale->products_count = $sale->sale_details ? $sale->sale_details->count() : 0;
         $sale->total_quantity = $sale->sale_details ? $sale->sale_details->sum('quantity') : 0;
         return $sale;
      });

      // Generar paginación inteligente
      $sales = $this->generateSmartPagination($sales, 2);

      // Calcular ventas de esta semana - OPTIMIZADO con DB::table
      $salesThisWeekData = DB::table('sales')
         ->where('company_id', $this->company->id)
         ->whereBetween('sale_date', [$startOfWeek, $endOfWeek])
         ->select('total_price')
         ->get();

      // 1. Total de ventas en dinero esta semana
      $totalSalesAmountThisWeek = $salesThisWeekData->sum('total_price');

      // 2. Ingresos netos (ganancias) esta semana - asumiendo un margen promedio del 35%
      $profitMargin = 0.35; // 35% de margen de ganancia
      $totalProfitThisWeek = $totalSalesAmountThisWeek * $profitMargin;

      // 3. Cantidad de ventas esta semana
      $salesCountThisWeek = $salesThisWeekData->count();

      // Otros cálculos existentes - OPTIMIZADOS
      $totalSales = $sales->sum(function ($sale) {
         return $sale->saleDetails->count();
      });

      $totalAmount = $sales->sum('total_price');

      // OPTIMIZADO: Usar DB::table para contar ventas mensuales
      $monthlySales = DB::table('sales')
         ->where('company_id', $this->company->id)
         ->whereMonth('sale_date', Carbon::now()->month)
         ->count();

      $averageTicket = $sales->count() > 0 ? $totalAmount / $sales->count() : 0;

      // OPTIMIZADO: Obtener solo los campos necesarios de la caja actual
      $currentCashCount = CashCount::select('id', 'opening_date')
         ->where('company_id', $this->company->id)
         ->whereNull('closing_date')
         ->first();

      // Calcular porcentajes dinámicos basados en ventas desde la apertura de la caja
      $salesPercentageThisWeek = 0;
      $profitPercentageThisWeek = 0;
      $salesCountPercentageThisWeek = 0;
      $averageTicketPercentage = 0;

      $salesCountSinceCashOpen = 0;
      $salesCountToday = 0;
      // Cantidades totales de productos vendidos (unidades)
      $productsQtySinceCashOpen = 0;
      $productsQtyThisWeek = 0;
      $productsQtyToday = 0;

      if ($currentCashCount) {
         $salesSinceCashOpenStats = DB::table('sales')
            ->where('company_id', $this->company->id)
            ->where('sale_date', '>=', $currentCashCount->opening_date)
            ->selectRaw('COUNT(*) as count, SUM(total_price) as total')
            ->first();

         $totalSalesSinceCashOpen = $salesSinceCashOpenStats->total ?? 0;
         // Exponer monto total de ventas desde apertura de arqueo
         $totalSalesAmountSinceCashOpen = $totalSalesSinceCashOpen;
         $totalProfitSinceCashOpen = $totalSalesSinceCashOpen * $profitMargin;
         $totalSalesCountSinceCashOpen = $salesSinceCashOpenStats->count ?? 0;
         $averageTicketSinceCashOpen = $totalSalesCountSinceCashOpen > 0 ? $totalSalesSinceCashOpen / $totalSalesCountSinceCashOpen : 0;
         $salesCountSinceCashOpen = $totalSalesCountSinceCashOpen;

         // Cantidad total de productos vendidos desde apertura de arqueo
         $productsQtySinceCashOpen = DB::table('sale_details as sd')
            ->join('sales as s', 'sd.sale_id', '=', 's.id')
            ->where('s.company_id', $this->company->id)
            ->where('s.sale_date', '>=', $currentCashCount->opening_date)
            ->sum('sd.quantity');

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

      // Calcular ventas de hoy desde las 00:00
      $startOfToday = Carbon::today();
      $salesCountToday = DB::table('sales')
         ->where('company_id', $this->company->id)
         ->where('sale_date', '>=', $startOfToday)
         ->count();

      // Monto total de ventas de hoy desde las 00:00
      $totalSalesAmountToday = DB::table('sales')
         ->where('company_id', $this->company->id)
         ->where('sale_date', '>=', $startOfToday)
         ->sum('total_price');
      // Ganancia estimada de hoy
      $totalProfitToday = $totalSalesAmountToday * $profitMargin;

      // Cantidad total de productos vendidos esta semana
      $productsQtyThisWeek = DB::table('sale_details as sd')
         ->join('sales as s', 'sd.sale_id', '=', 's.id')
         ->where('s.company_id', $this->company->id)
         ->whereBetween('s.sale_date', [$startOfWeek, $endOfWeek])
         ->sum('sd.quantity');

      // Cantidad total de productos vendidos hoy
      $productsQtyToday = DB::table('sale_details as sd')
         ->join('sales as s', 'sd.sale_id', '=', 's.id')
         ->where('s.company_id', $this->company->id)
         ->where('s.sale_date', '>=', $startOfToday)
         ->sum('sd.quantity');

      $currency = $this->currencies;
      // OPTIMIZADO: Usar exists() directamente para verificar si hay caja abierta
      $cashCount = DB::table('cash_counts')
         ->where('company_id', $this->company->id)
         ->whereNull('closing_date')
         ->exists();

      // Si es una petición AJAX, devolver solo la vista parcial con los datos
      if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
         // Preparar datos para JavaScript
         $salesData = $sales->map(function ($sale) {
            return [
               'id' => $sale->id,
               'sale_date' => $sale->sale_date,
               'total_price' => $sale->total_price,
               'customer' => $sale->customer ? [
                  'id' => $sale->customer->id,
                  'name' => $sale->customer->name,
                  'email' => $sale->customer->email,
                  'phone' => $sale->customer->phone
               ] : null,
               'sale_details' => $sale->saleDetails->map(function ($detail) {
                  return [
                     'id' => $detail->id,
                     'quantity' => $detail->quantity,
                     'unit_price' => $detail->unit_price,
                     'subtotal' => $detail->subtotal,
                     'product' => $detail->product ? [
                        'id' => $detail->product->id,
                        'code' => $detail->product->code,
                        'name' => $detail->product->name,
                        'image' => $detail->product->image,
                        'category' => $detail->product->category ? [
                           'id' => $detail->product->category->id,
                           'name' => $detail->product->category->name
                        ] : null
                     ] : null
                  ];
               })
            ];
         });

         return response()->json([
            'success' => true,
            'sales' => $salesData,
            'pagination' => [
               'current_page' => $sales->currentPage(),
               'last_page' => $sales->lastPage(),
               'per_page' => $sales->perPage(),
               'total' => $sales->total(),
               'smart_links' => $sales->smartLinks ?? []
            ]
         ]);
      }

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
         'averageTicketPercentage',
         'totalSalesAmountSinceCashOpen',
         'totalProfitSinceCashOpen',
         'totalSalesAmountToday',
         'totalProfitToday',
         'salesCountSinceCashOpen',
         'salesCountToday',
         'productsQtySinceCashOpen',
         'productsQtyThisWeek',
         'productsQtyToday',
         'permissions'
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
            ->select('id', 'code', 'name', 'image', 'stock', 'min_stock', 'max_stock', 'sale_price', 'category_id')
            ->with(['category:id,name']) // Solo cargar la categoría con campos necesarios
            ->get()
            ->map(function ($product) {
               // Asegurar que el accessor se incluya en la serialización
               $product->append('stock_status_label');
               return $product;
            });

         // Obtener clientes con solo los campos necesarios para el select
         $customers = Customer::where('company_id', $companyId)
            ->select('id', 'name', 'total_debt', 'phone')
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
            'general_discount_value' => $request->input('general_discount_value', 0),
            'general_discount_type' => $request->input('general_discount_type', 'fixed'),
            'subtotal_before_discount' => $request->input('subtotal_before_discount', $validated['total_price']),
            'total_with_discount' => $request->input('total_with_discount', $validated['total_price']),
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
               'discount_value' => $item['discount_value'] ?? 0,
               'discount_type' => $item['discount_type'] ?? 'fixed',
               'original_price' => $item['original_price'] ?? $item['unit_price'],
               'final_price' => $item['final_price'] ?? $item['unit_price'],
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
            $action = $request->input('action') ?? $request->query('action');

            $response = [
               'success' => true,
               'message' => '¡Venta procesada exitosamente!',
               'sale_id' => $sale->id,
               'received_action' => $action,
               'all_data' => $request->all()
            ];

            // Determinar la URL de redirección basada en la acción
            if ($action == 'save_and_new') {
               $response['redirect_url'] = route('admin.sales.create');
               $response['action'] = 'save_and_new';
            } else {
               $response['redirect_url'] = route('admin.sales.index');
               $response['action'] = 'save';
            }

            return response()->json($response);
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

   public function bulkStore(Request $request)
   {
      try {
         $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'sale_date' => 'required|date',
            'sale_time' => 'required|date_format:H:i',
            'sales' => 'required|array|min:1',
            'sales.*.customer_id' => 'required|exists:customers,id',
            'sales.*.quantity' => 'required|numeric|min:0.01',
            'sales.*.price' => 'required|numeric|min:0',
            'sales.*.is_paid' => 'nullable|boolean',
            'sales.*.payment_amount' => 'nullable|numeric|min:0',
         ]);

         DB::beginTransaction();

         // Verificar si hay una caja abierta
         $currentCashCount = CashCount::where('company_id', $this->company->id)
            ->whereNull('closing_date')
            ->first();

         if (!$currentCashCount) {
            return response()->json([
               'success' => false,
               'message' => 'No hay una caja abierta. Debe abrir una caja antes de realizar ventas.'
            ], 400);
         }

         $product = Product::where('id', $validated['product_id'])
            ->where('company_id', $this->company->id)
            ->firstOrFail();

         $totalSalesCount = 0;
         $totalQuantitySold = 0;
         $automaticPaymentsCount = 0;

         foreach ($validated['sales'] as $saleData) {
            $customer = Customer::where('id', $saleData['customer_id'])
               ->where('company_id', $this->company->id)
               ->firstOrFail();

            $totalPrice = $saleData['quantity'] * $saleData['price'];
            $isPaid = $saleData['is_paid'] ?? false;
            $paymentAmount = $saleData['payment_amount'] ?? 0;

            // Crear la venta principal
            $sale = Sale::create([
               'sale_date' => $validated['sale_date'] . ' ' . $validated['sale_time'],
               'total_price' => $totalPrice,
               'company_id' => $this->company->id,
               'customer_id' => $customer->id,
               'cash_count_id' => $currentCashCount->id,
               'note' => 'Venta masiva procesada' . ($isPaid ? ' (Con abono automático)' : ''),
               'general_discount_value' => 0,
               'general_discount_type' => 'fixed',
               'subtotal_before_discount' => $totalPrice,
               'total_with_discount' => $totalPrice,
            ]);

            // Crear detalle sale_details
            SaleDetail::create([
               'sale_id' => $sale->id,
               'product_id' => $product->id,
               'quantity' => $saleData['quantity'],
               'unit_price' => $saleData['price'],
               'subtotal' => $totalPrice,
               'discount_value' => 0,
               'discount_type' => 'fixed',
               'original_price' => $saleData['price'],
               'final_price' => $saleData['price'],
            ]);

            // Lógica de Deuda y Pagos
            if ($isPaid && $paymentAmount > 0) {
               // Registrar el pago automático
               $previousDebt = $customer->total_debt;

               // Si el pago es TOTAL (o mayor/igual al precio de venta)
               if ($paymentAmount >= $totalPrice) {
                  // No aumentamos la deuda, solo registramos el pago simbólico por el monto de la venta

                  DB::table('debt_payments')->insert([
                     'company_id' => Auth::user()->company_id,
                     'customer_id' => $customer->id,
                     'previous_debt' => $previousDebt,
                     'payment_amount' => $totalPrice, // El pago cubre la venta
                     'remaining_debt' => $previousDebt, // La deuda global no cambia (se anula el efecto de la venta)
                     'notes' => 'Pago automático (Venta Masiva #' . $sale->id . ')',
                     'user_id' => Auth::user()->id,
                     'created_at' => now(),
                     'updated_at' => now(),
                  ]);

                  // NO sumamos al total_debt del cliente
                  $automaticPaymentsCount++;
               } else {
                  // Pago PARCIAL
                  // 1. Sumamos la venta completa a la deuda
                  $oldDebt = $customer->total_debt;
                  $customer->total_debt += $totalPrice;
                  $customer->save();

                  // 2. Registramos el abono parcial
                  $newDebt = $customer->total_debt;

                  DB::table('debt_payments')->insert([
                     'company_id' => Auth::user()->company_id,
                     'customer_id' => $customer->id,
                     'previous_debt' => $newDebt,
                     'payment_amount' => $paymentAmount,
                     'remaining_debt' => $newDebt - $paymentAmount,
                     'notes' => 'Abono parcial automático (Venta Masiva #' . $sale->id . ')',
                     'user_id' => Auth::user()->id,
                     'created_at' => now(),
                     'updated_at' => now(),
                  ]);

                  // 3. Restamos el abono a la deuda
                  $customer->total_debt -= $paymentAmount;
                  $customer->save();

                  $automaticPaymentsCount++;
               }
            } else {
               // No hay pago, solo deuda pura
               $customer->total_debt += $totalPrice;
               $customer->save();
            }

            // Registrar movimiento de caja
            CashMovement::create([
               'cash_count_id' => $currentCashCount->id,
               'amount' => $totalPrice,
               'type' => CashMovement::TYPE_INCOME,
               'description' => 'Venta Masiva #' . $sale->id . ' - ' . $customer->name,
            ]);

            $totalQuantitySold += $saleData['quantity'];
            $totalSalesCount++;
         }

         // Actualizar stock del producto UNA SOLA VEZ al final pa optimizar
         $product->stock -= $totalQuantitySold;
         $product->save();

         DB::commit();

         $msg = "¡Se han procesado {$totalSalesCount} ventas exitosamente!";
         if ($automaticPaymentsCount > 0) {
            $msg .= " Se registraron {$automaticPaymentsCount} pagos automáticos.";
         }

         return response()->json([
            'success' => true,
            'message' => $msg,
         ]);
      } catch (\Exception $e) {
         DB::rollBack();
         return response()->json([
            'success' => false,
            'message' => 'Error al procesar ventas masivas: ' . $e->getMessage()
         ], 500);
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
         $sale = Sale::with(['saleDetails.product.category'])
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
               'stock' => $detail->product->stock + $detail->quantity, // Stock real + cantidad en venta
               'real_stock' => $detail->product->stock, // Stock real del producto
               'category' => $detail->product->category->name ?? 'Sin categoría',
               'stock_status_class' => $detail->product->stock > 10 ? 'success' : ($detail->product->stock > 0 ? 'warning' : 'danger'),
               'discount_value' => $detail->discount_value ?? 0,
               'discount_type' => $detail->discount_type ?? 'fixed',
               'original_price' => $detail->original_price ?? $detail->product->sale_price,
               'final_price' => $detail->final_price ?? $detail->product->sale_price,
            ];
         });

         // Calcular el total inicial
         $totalAmount = $saleDetails->sum('subtotal');

         // Obtener productos y clientes para los selectores
         // Incluir todos los productos, incluso los que están en la venta actual
         $products = Product::where('company_id', $companyId)
            ->with(['category'])
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

         // Actualizar descuentos generales
         $sale->general_discount_value = $request->input('general_discount_value', 0);
         $sale->general_discount_type = $request->input('general_discount_type', 'fixed');
         $sale->subtotal_before_discount = $request->input('subtotal_before_discount', $validated['total_price']);
         $sale->total_with_discount = $request->input('total_with_discount', $validated['total_price']);

         $sale->save();

         // PASO 1: Restaurar todo el stock de la venta actual al inicio
         foreach ($sale->saleDetails as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
               $product->stock += $detail->quantity;
               $product->save();
            }
         }

         // Obtener IDs de detalles actuales
         $currentDetailIds = $sale->saleDetails->pluck('id')->toArray();
         $newDetailIds = [];

         // PASO 2: Procesar cada producto en la venta con nueva lógica de validación
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

            // PASO 3: Aplicar fórmula universal de validación
            $stockDisponible = $product->stock;
            $cantidadActualEnVenta = $detail ? $detail->quantity : 0;
            $nuevaCantidad = $item['quantity'];

            // Validación: stock_disponible + cantidad_actual_en_venta >= nueva_cantidad
            if (($stockDisponible + $cantidadActualEnVenta) < $nuevaCantidad) {
               if ($detail) {
                  // Producto existente - aumentar cantidad
                  throw new \Exception("Stock insuficiente para aumentar la cantidad de {$product->name}. Stock disponible: {$stockDisponible}, cantidad actual en venta: {$cantidadActualEnVenta}, cantidad solicitada: {$nuevaCantidad}");
               } else {
                  // Producto nuevo
                  throw new \Exception("No hay stock disponible para agregar {$product->name}. Stock disponible: {$stockDisponible}, cantidad solicitada: {$nuevaCantidad}");
               }
            }

            if ($detail) {
               // Producto existente - actualizar detalle con descuentos
               $detail->quantity = $item['quantity'];
               $detail->discount_value = $item['discount_value'] ?? 0;
               $detail->discount_type = $item['discount_type'] ?? 'fixed';
               $detail->original_price = $item['original_price'] ?? $item['unit_price'];
               $detail->final_price = $item['final_price'] ?? $item['unit_price'];
               $detail->save();
               $newDetailIds[] = $detail->id;
            } else {
               // Producto nuevo - crear detalle con descuentos
               $detail = SaleDetail::create([
                  'sale_id' => $sale->id,
                  'product_id' => $productId,
                  'quantity' => $item['quantity'],
                  'unit_price' => $item['unit_price'] ?? $product->sale_price,
                  'subtotal' => $item['subtotal'] ?? ($item['quantity'] * $product->sale_price),
                  'discount_value' => $item['discount_value'] ?? 0,
                  'discount_type' => $item['discount_type'] ?? 'fixed',
                  'original_price' => $item['original_price'] ?? $product->sale_price,
                  'final_price' => $item['final_price'] ?? $product->sale_price,
               ]);
               $newDetailIds[] = $detail->id;
            }

            // PASO 4: Descontar stock según la nueva cantidad
            $product->stock -= $item['quantity'];
            $product->save();
         }

         // PASO 5: Eliminar detalles que ya no están en la venta
         $detailsToDelete = array_diff($currentDetailIds, $newDetailIds);
         foreach ($detailsToDelete as $detailId) {
            $detail = SaleDetail::find($detailId);
            if ($detail) {
               // El stock ya fue restaurado en el PASO 1, solo eliminar el detalle
               $detail->delete();
            }
         }

         DB::commit();

         // Si es una petición AJAX, devolver JSON
         if ($request->expectsJson()) {
            return response()->json([
               'success' => true,
               'message' => '¡Venta actualizada exitosamente!',
               'sale_id' => $sale->id
            ]);
         }

         return redirect()->route('admin.sales.index')
            ->with('message', '¡Venta actualizada exitosamente!')
            ->with('icons', 'success')
            ->with('update_success', true);
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

         // Si es una petición AJAX, devolver JSON
         if ($request->expectsJson()) {
            return response()->json([
               'success' => false,
               'message' => 'Error al actualizar la venta: ' . $e->getMessage()
            ], 500);
         }

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
                  "• Total de la venta: $" . number_format((float)$sale->total_price, 2) . "\n" .
                  "• Pagos posteriores: $" . number_format((float)$totalPaid, 2) . "\n" .
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

         // Restaurar el stock de los productos antes de eliminar los detalles
         $saleDetails = SaleDetail::where('sale_id', $sale->id)->get();

         foreach ($saleDetails as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
               $product->stock += $detail->quantity;
               $product->save();
            }
         }

         // Eliminar movimientos de caja asociados a esta venta
         CashMovement::where('description', 'Venta #' . $sale->id)->delete();

         // Eliminar la venta (esto también eliminará los detalles por la relación)
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

         // Obtener la moneda de la empresa configurada
         if ($company && $company->currency) {
            $currency = DB::table('currencies')
               ->select('id', 'name', 'code', 'symbol', 'country_id')
               ->where('code', $company->currency)
               ->first();
         }

         // Fallback si no se encuentra la moneda configurada
         if (!$currency) {
            $currency = DB::table('currencies')
               ->select('id', 'name', 'code', 'symbol', 'country_id')
               ->where('code', 'USD')
               ->first();
         }

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
