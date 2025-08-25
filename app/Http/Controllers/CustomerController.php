<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\DebtPayment;

class CustomerController extends Controller
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
   public function index(Request $request)
   {
      try {
         // Obtener el arqueo de caja actual una sola vez para evitar N+1 queries
         $currentCashCount = \App\Models\CashCount::where('company_id', $this->company->id)
            ->whereNull('closing_date')
            ->first();
         
         $openingDate = $currentCashCount ? $currentCashCount->opening_date : now();

         // Consulta básica de clientes con paginación
         $query = Customer::where('company_id', $this->company->id);
         
         // Aplicar búsqueda si se proporciona
         if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
               $q->where('name', 'ILIKE', "%{$searchTerm}%")
                 ->orWhere('email', 'ILIKE', "%{$searchTerm}%")
                 ->orWhere('phone', 'ILIKE', "%{$searchTerm}%")
                 ->orWhere('nit_number', 'ILIKE', "%{$searchTerm}%");
            });
         }
         
         $customers = $query->orderBy('name')->paginate(20); // 20 clientes por página

         $currency = $this->currencies;
         $company = $this->company;

         // Estadísticas básicas optimizadas - Compatible con PostgreSQL
         $stats = DB::table('customers')
            ->where('company_id', $this->company->id)
            ->selectRaw('
               COUNT(*) as total_customers,
               SUM(total_debt) as total_debt,
               COUNT(CASE WHEN EXTRACT(MONTH FROM created_at) = ? AND EXTRACT(YEAR FROM created_at) = ? THEN 1 END) as new_customers
            ', [now()->month, now()->year])
            ->first();

         $totalCustomers = $stats->total_customers ?? 0;
         $totalDebt = $stats->total_debt ?? 0;
         $newCustomers = $stats->new_customers ?? 0;
         $customerGrowth = $totalCustomers > 0 ? round(($newCustomers / $totalCustomers) * 100) : 0;

         // Calcular clientes activos con una sola consulta
         $activeCustomers = DB::table('customers')
            ->join('sales', 'customers.id', '=', 'sales.customer_id')
            ->where('customers.company_id', $this->company->id)
            ->distinct()
            ->count('customers.id');

         // Calcular ingresos totales con una sola consulta
         $totalRevenue = DB::table('sales')
            ->where('company_id', $this->company->id)
            ->sum('total_price');

         // Calcular estadísticas de deudas con consultas SQL directas
         $customerIds = $customers->pluck('id')->toArray();
         
         if (empty($customerIds)) {
            $customersData = [];
            $defaultersCount = 0;
            $currentDebtorsCount = 0;
            $previousCashCountDebtTotal = 0;
            $currentCashCountDebtTotal = 0;
         } else {
            // Consulta de ventas optimizada
            $salesData = DB::table('sales')
               ->whereIn('customer_id', $customerIds)
               ->where('company_id', $this->company->id)
               ->selectRaw('
                  customer_id,
                  SUM(CASE WHEN sale_date < ? THEN total_price ELSE 0 END) as sales_before,
                  SUM(CASE WHEN sale_date >= ? THEN total_price ELSE 0 END) as sales_after,
                  COUNT(CASE WHEN sale_date < ? THEN 1 END) as sales_before_count
               ', [$openingDate, $openingDate, $openingDate])
               ->groupBy('customer_id')
               ->get()
               ->keyBy('customer_id');

            // Consulta de pagos optimizada
            $paymentsData = [];
            if (Schema::hasTable('debt_payments')) {
               $paymentsData = DB::table('debt_payments')
                  ->whereIn('customer_id', $customerIds)
                  ->where('company_id', $this->company->id)
                  ->selectRaw('customer_id, SUM(payment_amount) as total_payments')
                  ->groupBy('customer_id')
                  ->get()
                  ->keyBy('customer_id');
            }

            // Calcular estadísticas optimizadas
            $defaultersCount = 0;
            $currentDebtorsCount = 0;
            $previousCashCountDebtTotal = 0;
            $currentCashCountDebtTotal = 0;
            $customersData = [];

            foreach ($customers as $customer) {
               $sales = $salesData->get($customer->id);
               $payments = $paymentsData->get($customer->id);
               
               $salesBefore = $sales ? $sales->sales_before : 0;
               $salesAfter = $sales ? $sales->sales_after : 0;
               $totalPayments = $payments ? $payments->total_payments : 0;
               
               $previousDebt = max(0, $salesBefore - $totalPayments);
               $currentDebt = max(0, $salesAfter);
               $isDefaulter = $previousDebt > 0;
               
               $customersData[$customer->id] = [
                  'isDefaulter' => $isDefaulter,
                  'previousDebt' => $previousDebt,
                  'currentDebt' => $currentDebt,
                  'hasOldSales' => $sales ? $sales->sales_before_count > 0 : false
               ];
               
               if ($isDefaulter) {
                  $defaultersCount++;
                  $previousCashCountDebtTotal += $previousDebt;
               }
               
               if ($customer->total_debt > 0 && !$isDefaulter) {
                  $currentDebtorsCount++;
                  $currentCashCountDebtTotal += $currentDebt;
               }
            }
         }

         // Obtener el tipo de cambio desde localStorage o usar valor por defecto
         $exchangeRate = 134.0; // Valor por defecto

         // Optimizar: NO cargar relaciones innecesarias - ya tenemos los datos calculados
         // $customers->load(['sales' => function($query) {
         //    $query->select('id', 'customer_id', 'sale_date', 'total_price');
         // }]);

         // Optimizar: Verificar permisos una sola vez para evitar múltiples verificaciones
         $permissions = [
            'can_report' => Gate::allows('customers.report'),
            'can_create' => Gate::allows('customers.create'),
            'can_edit' => Gate::allows('customers.edit'),
            'can_show' => Gate::allows('customers.show'),
            'can_destroy' => Gate::allows('customers.destroy'),
            'can_create_sales' => Gate::allows('sales.create'),
         ];

         // Si es una petición AJAX, devolver solo el contenido necesario
         if ($request->ajax() || $request->wantsJson()) {
            return view('admin.customers.index', compact(
               'customers',
               'customersData',
               'totalCustomers',
               'activeCustomers',
               'newCustomers',
               'customerGrowth',
               'totalRevenue',
               'totalDebt',
               'defaultersCount',
               'currentDebtorsCount',
               'previousCashCountDebtTotal',
               'currentCashCountDebtTotal',
               'currency',
               'company',
               'exchangeRate',
               'permissions'
            ));
         }

         return view('admin.customers.index', compact(
            'customers',
            'customersData',
            'totalCustomers',
            'activeCustomers',
            'newCustomers',
            'customerGrowth',
            'totalRevenue',
            'totalDebt',
            'defaultersCount',
            'currentDebtorsCount',
            'previousCashCountDebtTotal',
            'currentCashCountDebtTotal',
            'currency',
            'company',
            'exchangeRate',
            'permissions'
         ));
      } catch (\Exception $e) {


         // Determinar el tipo de error para mostrar un mensaje más específico
         $errorMessage = 'Error al cargar la lista de clientes';
         $errorDetails = '';
         
         if (str_contains($e->getMessage(), 'Undefined function')) {
            $errorMessage = 'Error de compatibilidad con la base de datos';
            $errorDetails = 'El sistema detectó un problema de compatibilidad con las funciones de la base de datos. Esto puede ocurrir cuando se usan funciones específicas de MySQL en PostgreSQL.';
         } elseif (str_contains($e->getMessage(), 'SQLSTATE')) {
            $errorMessage = 'Error de consulta en la base de datos';
            $errorDetails = 'Ocurrió un error al ejecutar las consultas en la base de datos. Verifique que todas las tablas existan y tengan la estructura correcta.';
         } elseif (str_contains($e->getMessage(), 'Connection')) {
            $errorMessage = 'Error de conexión a la base de datos';
            $errorDetails = 'No se pudo establecer conexión con la base de datos. Verifique la configuración de la base de datos.';
         } else {
            $errorDetails = $e->getMessage();
         }

         return redirect()->route('admin.index')
            ->with('message', $errorMessage)
            ->with('error_details', $errorDetails)
            ->with('icons', 'error');
      }
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      try {
         // Verificar autorización
         if (!Gate::allows('customers.create')) {
            return redirect()->route('admin.customers.index')
               ->with('message', 'No tienes permisos para crear clientes')
               ->with('icons', 'error');
         }

         $company = $this->company;
         return view('admin.customers.create', compact('company'));
      } catch (\Exception $e) {

         return redirect()->route('admin.customers.index')
            ->with('message', 'Error al cargar el formulario de creación')
            ->with('icons', 'error');
      }
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(Request $request)
   {
      try {
         // Verificar autorización
         if (!Gate::allows('customers.create')) {
            if ($request->ajax() || $request->wantsJson()) {
               return response()->json([
                  'success' => false,
                  'message' => 'No tienes permisos para crear clientes'
               ], 403);
            }
            
            return redirect()->route('admin.customers.index')
               ->with('message', 'No tienes permisos para crear clientes')
               ->with('icons', 'error');
         }

         DB::beginTransaction();

         // Validación personalizada
         $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'],
            'nit_number' => [
               'nullable',
               'string',
               'max:20',
               // 'regex:/^\d{3}-\d{6}-\d{3}-\d{1}$/',
               Rule::unique('customers', 'nit_number'),
            ],
            'phone' => [
               'nullable',
               'string',
               'max:20',
            ],
            'email' => [
               'nullable',
               'string',
               'email',
               'max:255',
               Rule::unique('customers', 'email'),
            ],
            'total_debt' => [
               'nullable',
               'numeric',
               'min:0',
            ],
         ], [
            'name.required' => 'El nombre del cliente es obligatorio',
            'name.regex' => 'El nombre solo debe contener letras, espacios y guiones',
            'nit_number.unique' => 'Este NIT ya está registrado para otro cliente',
            'nit_number.regex' => 'El formato del NIT no es válido (ej: 123-456789-123-1)',
            'email.email' => 'El formato del correo electrónico no es válido',
            'email.unique' => 'Este correo electrónico ya está registrado para otro cliente',
            'total_debt.min' => 'La deuda no puede ser un valor negativo',
         ]);

         if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
               return response()->json([
                  'success' => false,
                  'message' => 'Error de validación',
                  'errors' => $validator->errors()
               ], 422);
            }
            
            return redirect()->back()
               ->withErrors($validator)
               ->withInput();
         }

         // Formatear datos
         $customerData = [
            'name' => ucwords(strtolower($request->name)),
            'nit_number' => $request->filled('nit_number') ? $request->nit_number : null,
            'phone' => $request->filled('phone') ? $request->phone : null,
            'email' => $request->filled('email') ? strtolower($request->email) : null,
            'total_debt' => $request->filled('total_debt') ? $request->total_debt : 0,
            'company_id' => Auth::user()->company_id,
            'created_at' => now(),
            'updated_at' => now()
         ];

         // Crear el cliente
         $customer = Customer::create($customerData);

         DB::commit();

         // Respuesta para peticiones AJAX
         if ($request->ajax() || $request->wantsJson()) {
            $message = '¡Cliente creado exitosamente!';
            
            if ($request->input('action') === 'save_and_new') {
               $message = '¡Cliente creado exitosamente! Puedes crear otro cliente.';
            }
            
            return response()->json([
               'success' => true,
               'message' => $message,
               'customer' => $customer
            ]);
         }

         // Determinar la redirección basada en el botón presionado o el parámetro return_to
         $returnTo = $request->input('return_to');
         
         if ($request->input('action') === 'save_and_new') {
            $redirectRoute = $returnTo ? route('admin.customers.create') . "?return_to={$returnTo}" : route('admin.customers.create');
            return redirect($redirectRoute)
               ->with('message', '¡Cliente creado exitosamente! Puedes crear otro cliente.')
               ->with('icons', 'success');
         }

         // Si viene de la vista de ventas, redirigir allí con el cliente seleccionado
         if ($returnTo === 'sales.create') {
            return redirect()->route('admin.sales.create', ['customer_id' => $customer->id])
               ->with('message', '¡Cliente creado exitosamente! Ya está seleccionado en la venta.')
               ->with('icons', 'success');
         }

         return redirect()->route('admin.customers.index')
            ->with('message', '¡Cliente creado exitosamente!')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();

         if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
               'success' => false,
               'message' => 'Error al crear el cliente: ' . $e->getMessage()
            ], 500);
         }

         return redirect()->back()
            ->withInput()
            ->with('message', 'Error al crear el cliente: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Display the specified resource.
    */
   public function show($id)
   {
      try {
         // Verificar autorización
         if (!Gate::allows('customers.show')) {
            return response()->json([
               'success' => false,
               'message' => 'No tienes permisos para ver detalles de clientes'
            ], 403);
         }

         $customer = Customer::findOrFail($id);

         // Verificar si es una petición para datos de pago de deuda
         if (request()->has('debt_payment_data')) {
            try {
               // Obtener el arqueo de caja actual para determinar si es moroso
               $currentCashCount = \App\Models\CashCount::where('company_id', $this->company->id)
                  ->whereNull('closing_date')
                  ->first();
               
               $openingDate = $currentCashCount ? $currentCashCount->opening_date : now();

               // Obtener información de ventas y pagos del cliente
               $customerSales = \App\Models\Sale::where('customer_id', $customer->id)
                  ->where('company_id', $this->company->id)
                  ->select('sale_date', 'total_price')
                  ->get();

               $customerPayments = [];
               if (Schema::hasTable('debt_payments')) {
                  $customerPayments = \App\Models\DebtPayment::where('customer_id', $customer->id)
                     ->where('company_id', $this->company->id)
                     ->select('payment_amount')
                     ->get();
               }

               // Calcular ventas antes del arqueo actual
               $salesBeforeCashCount = $customerSales->where('sale_date', '<', $openingDate);
               $totalSalesBefore = $salesBeforeCashCount->sum('total_price');
               $totalPayments = $customerPayments->sum('payment_amount');
               
               // Calcular deuda anterior
               $previousDebt = 0;
               if ($totalSalesBefore > 0) {
                  $previousDebt = max(0, $totalSalesBefore - $totalPayments);
               }
               
               // Determinar si es moroso
               $isDefaulter = $previousDebt > 0;

               return response()->json([
                  'success' => true,
                  'customer_id' => $customer->id,
                  'customer_name' => $customer->name,
                  'customer_phone' => $customer->phone,
                  'current_debt' => number_format($customer->total_debt, 2),
                  'remaining_debt' => number_format($customer->total_debt, 2), // Inicialmente igual a la deuda actual
                  'is_defaulter' => $isDefaulter,
                  'customer_status' => $isDefaulter ? 'Moroso' : 'Actual'
               ]);
            } catch (\Exception $e) {
               return response()->json([
                  'success' => false,
                  'message' => 'Error al cargar los datos del cliente'
               ], 500);
            }
         }
         
         // Verificar si es una petición para detalles del cliente
         if (request()->has('customer_details')) {
            try {
               // Obtener el arqueo de caja actual para determinar si es moroso
               $currentCashCount = \App\Models\CashCount::where('company_id', $this->company->id)
                  ->whereNull('closing_date')
                  ->first();
               
               $openingDate = $currentCashCount ? $currentCashCount->opening_date : now();

               // Obtener información de ventas y pagos del cliente
               $customerSales = \App\Models\Sale::where('customer_id', $customer->id)
                  ->where('company_id', $this->company->id)
                  ->select('sale_date', 'total_price')
                  ->get();

               $customerPayments = [];
               if (Schema::hasTable('debt_payments')) {
                  $customerPayments = \App\Models\DebtPayment::where('customer_id', $customer->id)
                     ->where('company_id', $this->company->id)
                     ->select('payment_amount')
                     ->get();
               }

               // Calcular ventas antes del arqueo actual
               $salesBeforeCashCount = $customerSales->where('sale_date', '<', $openingDate);
               $totalSalesBefore = $salesBeforeCashCount->sum('total_price');
               $totalPayments = $customerPayments->sum('payment_amount');
               
               // Calcular deuda anterior
               $previousDebt = 0;
               if ($totalSalesBefore > 0) {
                  $previousDebt = max(0, $totalSalesBefore - $totalPayments);
               }
               
               // Determinar si es moroso
               $isDefaulter = $previousDebt > 0;
               
               // Obtener historial de ventas con información de productos
               $sales = $customer->sales()
                  ->with(['saleDetails.product'])
                  ->orderBy('sale_date', 'desc')
                  ->get()
                  ->map(function ($sale) {
                     $uniqueProducts = $sale->saleDetails->count();
                     $totalProducts = $sale->saleDetails->sum('quantity');
                     
                     return [
                        'id' => $sale->id,
                        'created_at' => $sale->sale_date,
                        'total' => $sale->total_price,
                        'unique_products' => $uniqueProducts,
                        'total_products' => $totalProducts
                     ];
                  });
               
               return response()->json([
                  'success' => true,
                  'customer' => [
                     'id' => $customer->id,
                     'name' => $customer->name,
                     'phone' => $customer->phone,
                     'is_defaulter' => $isDefaulter
                  ],
                  'sales' => $sales
               ]);
            } catch (\Exception $e) {
               return response()->json([
                  'success' => false,
                  'message' => 'Error al obtener detalles del cliente'
               ], 500);
            }
         }

         // Obtener estadísticas del cliente usando consultas optimizadas
         $customerSales = DB::table('sales')
            ->where('customer_id', $customer->id)
            ->where('company_id', $this->company->id)
            ->select('sale_date', 'total_price')
            ->get();

         $stats = [
            'total_purchases' => $customerSales->count(),
            'total_spent' => $customerSales->sum('total_price'),
            'purchase_history' => $this->getPurchaseHistory($customerSales)
         ];

         // Obtener las ventas individuales con detalles usando consultas optimizadas
         $salesData = DB::table('sales')
            ->leftJoin('sale_details', 'sales.id', '=', 'sale_details.sale_id')
            ->where('sales.customer_id', $customer->id)
            ->where('sales.company_id', $this->company->id)
            ->select(
               'sales.id',
               'sales.sale_date',
               'sales.total_price',
               DB::raw('SUM(sale_details.quantity) as total_products')
            )
            ->groupBy('sales.id', 'sales.sale_date', 'sales.total_price')
            ->get()
            ->map(function ($sale) {
               return [
                  'id' => $sale->id,
                  'date' => \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y'),
                  'total_products' => $sale->total_products ?? 0,
                  'total_amount' => $sale->total_price,
                  'invoice_number' => 'V-' . str_pad($sale->id, 6, '0', STR_PAD_LEFT)
               ];
            });

         return response()->json([
            'success' => true,
            'customer' => [
               'name' => $customer->name,
               'email' => $customer->email,
               'phone' => $customer->phone,
               'nit_number' => $customer->nit_number,
               'created_at' => $customer->created_at->format('d/m/Y H:i'),
               'updated_at' => $customer->updated_at->format('d/m/Y H:i'),
               'stats' => $stats,
               'sales' => $salesData
            ]
         ]);
               } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Error al cargar los datos del cliente'
         ], 500);
      }
   }

   /**
    * Obtiene el historial de compras por mes
    */
   private function getPurchaseHistory($sales)
   {
      $months = collect([]);
      $values = collect([]);

      // Agrupar ventas por mes
      $salesByMonth = $sales->groupBy(function ($sale) {
         return \Carbon\Carbon::parse($sale->sale_date)->format('Y-m');
      })->map(function ($monthSales) {
         return $monthSales->sum('total_price');
      });

      // Obtener los últimos 6 meses
      for ($i = 5; $i >= 0; $i--) {
         $date = now()->subMonths($i);
         $monthKey = $date->format('Y-m');

         $months->push($date->format('M'));
         $values->push($salesByMonth[$monthKey] ?? 0);
      }

      return [
         'labels' => $months->toArray(),
         'values' => $values->toArray()
      ];
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit($id)
   {
      try {
         // Verificar autorización
         if (!Gate::allows('customers.edit')) {
            return redirect()->route('admin.customers.index')
               ->with('message', 'No tienes permisos para editar clientes')
               ->with('icons', 'error');
         }

         $company = $this->company;
         // Buscar el cliente
         $customer = Customer::findOrFail($id);

         // Retornar vista con datos del cliente
         return view('admin.customers.edit', compact('customer', 'company'));
      } catch (\Exception $e) {


         return redirect()->route('admin.customers.index')
            ->with('message', 'No se pudo cargar el formulario de edición')
            ->with('icons', 'error');
      }
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(Request $request, $id)
   {
      try {
         // Verificar autorización
         if (!Gate::allows('customers.edit')) {
            if ($request->ajax() || $request->wantsJson()) {
               return response()->json([
                  'success' => false,
                  'message' => 'No tienes permisos para editar clientes'
               ], 403);
            }
            
            return redirect()->route('admin.customers.index')
               ->with('message', 'No tienes permisos para editar clientes')
               ->with('icons', 'error');
         }

         // Buscar el cliente
         $customer = Customer::findOrFail($id);

         // Validación personalizada
         $validator = Validator::make($request->all(), [
            'name' => [
               'required',
               'string',
               'max:255',
               'regex:/^[\pL\s\-]+$/u'
            ],
            'nit_number' => [
               'nullable',
               'string',
               'max:20',
               // 'regex:/^\d{3}-\d{6}-\d{3}-\d{1}$/',
               'unique:customers,nit_number,' . $id,
            ],
            'phone' => [
               'nullable',
               'string',
               'max:20',
               'unique:customers,phone,' . $id,
            ],
            'email' => [
               'nullable',
               'email',
               'max:255',
               'unique:customers,email,' . $id,
            ],
            'total_debt' => [
               'nullable',
               'numeric',
               'min:0',
            ],
         ], [
            'name.required' => 'El nombre es obligatorio',
            'name.regex' => 'El nombre solo debe contener letras y espacios',
            'name.string' => 'El nombre debe ser texto',
            'name.max' => 'El nombre no debe exceder los 255 caracteres',

            'nit_number.required' => 'El NIT es obligatorio',
            'nit_number.string' => 'El NIT debe ser texto',
            'nit_number.max' => 'El NIT no debe exceder los 20 caracteres',
            'nit_number.regex' => 'El formato del NIT debe ser: XXX-XXXXXX-XXX-X',
            'nit_number.unique' => 'Este NIT ya está registrado',

            'phone.required' => 'El teléfono es obligatorio',
            'phone.string' => 'El teléfono debe ser texto',
            'phone.unique' => 'Este teléfono ya está registrado',

            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'Debe ingresar un correo electrónico válido',
            'email.max' => 'El correo no debe exceder los 255 caracteres',
            'email.unique' => 'Este correo ya está registrado',
            'total_debt.numeric' => 'La deuda debe ser un valor numérico',
            'total_debt.min' => 'La deuda no puede ser un valor negativo',
         ]);

         if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
               return response()->json([
                  'success' => false,
                  'message' => 'Error de validación',
                  'errors' => $validator->errors()
               ], 422);
            }
            
            return redirect()->back()
               ->withErrors($validator)
               ->withInput();
         }

         // Formatear datos
         $customerData = [
            'name' => ucwords(strtolower($request->name)),
            'nit_number' => $request->filled('nit_number') ? $request->nit_number : null,
            'phone' => $request->filled('phone') ? $request->phone : null,
            'email' => $request->filled('email') ? strtolower($request->email) : null,
            'total_debt' => $request->filled('total_debt') ? $request->total_debt : 0,
         ];

         // Actualizar el cliente
         $customer->update($customerData);

         // Respuesta para peticiones AJAX
         if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
               'success' => true,
               'message' => '¡Cliente actualizado exitosamente!',
               'customer' => $customer
            ]);
         }

         // Redireccionar con mensaje de éxito
         return redirect()->route('admin.customers.index')
            ->with('message', '¡Cliente actualizado exitosamente!')
            ->with('icons', 'success');
      } catch (\Illuminate\Validation\ValidationException $e) {
         if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
               'success' => false,
               'message' => 'Error de validación',
               'errors' => $e->validator->errors()
            ], 422);
         }
         
         return redirect()->back()
            ->withErrors($e->validator)
            ->withInput()
            ->with('message', 'Por favor, corrija los errores en el formulario.')
            ->with('icons', 'error');
      } catch (\Exception $e) {
         if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
               'success' => false,
               'message' => 'Hubo un problema al actualizar el cliente. Por favor, inténtelo de nuevo.'
            ], 500);
         }

         return redirect()->back()
            ->withInput()
            ->with('message', 'Hubo un problema al actualizar el cliente. Por favor, inténtelo de nuevo.')
            ->with('icons', 'error');
      }
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy($id)
   {
      try {
         // Verificar autorización
         if (!Gate::allows('customers.destroy')) {
            return response()->json([
               'success' => false,
               'message' => 'No tienes permisos para eliminar clientes'
            ], 403);
         }

         // Buscar el cliente
         $customer = Customer::findOrFail($id);

         // Verificar si el cliente tiene ventas asociadas
         $salesCount = $customer->sales()->count();
         if ($salesCount > 0) {
            $totalSalesAmount = $customer->sales()->sum('total_price');
            $firstSaleDate = $customer->sales()->orderBy('sale_date', 'asc')->first()->sale_date->format('d/m/Y');
            $lastSaleDate = $customer->sales()->orderBy('sale_date', 'desc')->first()->sale_date->format('d/m/Y');
            
            return response()->json([
               'success' => false,
               'message' => "⚠️ No se puede eliminar el cliente '{$customer->name}' porque tiene ventas asociadas.\n\n" .
                           "📊 Detalles:\n" .
                           "• Cliente: {$customer->name}\n" .
                           "• Ventas asociadas: {$salesCount}\n" .
                           "• Total de ventas: $" . number_format($totalSalesAmount, 2) . "\n" .
                           "• Primera venta: {$firstSaleDate}\n" .
                           "• Última venta: {$lastSaleDate}\n\n" .
                           "🔧 Acción requerida:\n" .
                           "Primero debes eliminar todas las ventas asociadas a este cliente antes de poder eliminarlo.\n\n" .
                           "💡 Sugerencia:\n" .
                           "Ve a la sección de Ventas y busca las ventas de este cliente para eliminarlas.",
               'icons' => 'warning',
               'has_sales' => true,
               'sales_count' => $salesCount,
               'total_sales_amount' => $totalSalesAmount,
               'first_sale_date' => $firstSaleDate,
               'last_sale_date' => $lastSaleDate,
               'customer_name' => $customer->name
            ], 200);
         }

         // Verificar si el cliente tiene pagos de deuda asociados
         $paymentsCount = \App\Models\DebtPayment::where('customer_id', $customer->id)->count();
         if ($paymentsCount > 0) {
            $totalPaymentsAmount = \App\Models\DebtPayment::where('customer_id', $customer->id)->sum('payment_amount');
            $firstPaymentDate = \App\Models\DebtPayment::where('customer_id', $customer->id)->orderBy('created_at', 'asc')->first()->created_at->format('d/m/Y');
            $lastPaymentDate = \App\Models\DebtPayment::where('customer_id', $customer->id)->orderBy('created_at', 'desc')->first()->created_at->format('d/m/Y');
            
            return response()->json([
               'success' => false,
               'message' => "⚠️ No se puede eliminar el cliente '{$customer->name}' porque tiene pagos de deuda asociados.\n\n" .
                           "📊 Detalles:\n" .
                           "• Cliente: {$customer->name}\n" .
                           "• Pagos asociados: {$paymentsCount}\n" .
                           "• Total de pagos: $" . number_format($totalPaymentsAmount, 2) . "\n" .
                           "• Primer pago: {$firstPaymentDate}\n" .
                           "• Último pago: {$lastPaymentDate}\n\n" .
                           "🔧 Acción requerida:\n" .
                           "Primero debes eliminar todos los pagos de deuda asociados a este cliente antes de poder eliminarlo.\n\n" .
                           "💡 Sugerencia:\n" .
                           "Ve al historial de pagos del cliente y elimina los registros de pago.",
               'icons' => 'warning',
               'has_payments' => true,
               'payments_count' => $paymentsCount,
               'total_payments_amount' => $totalPaymentsAmount,
               'first_payment_date' => $firstPaymentDate,
               'last_payment_date' => $lastPaymentDate,
               'customer_name' => $customer->name
            ], 200);
         }

         // Eliminar el cliente
         $customer->delete();


         // Retornar respuesta exitosa
         return response()->json([
            'success' => true,
            'message' => '¡Cliente eliminado exitosamente!',
            'icons' => 'success'
         ]);
      } catch (\Illuminate\Database\QueryException $e) {
         // Verificar si es un error de restricción de clave foránea
         if ($e->getCode() == 23000) { // Código de error de MySQL para restricción de clave foránea
            return response()->json([
               'success' => false,
               'message' => "⚠️ No se puede eliminar el cliente '{$customer->name}' porque tiene registros asociados en el sistema.\n\n" .
                           "🔧 Acción requerida:\n" .
                           "Primero debes eliminar todas las ventas y pagos asociados a este cliente antes de poder eliminarlo.\n\n" .
                           "💡 Sugerencia:\n" .
                           "Ve a la sección de Ventas y busca las ventas de este cliente para eliminarlas.",
               'icons' => 'warning',
               'customer_name' => $customer->name
            ], 200);
         }

         return response()->json([
            'success' => false,
            'message' => 'Error de base de datos al eliminar el cliente. Por favor, inténtelo de nuevo.',
            'icons' => 'error'
         ], 500);
      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Hubo un problema al eliminar el cliente. Por favor, inténtelo de nuevo.',
            'icons' => 'error'
         ], 500);
      }
   }

   public function report(Request $request)
   {
      // Verificar autorización
      if (!Gate::allows('customers.report')) {
         return redirect()->route('admin.customers.index')
            ->with('message', 'No tienes permisos para generar reportes de clientes')
            ->with('icons', 'error');
      }

      $company = $this->company;
      $currency = $this->currencies;
      
      // Si hay filtros, generar reporte de deudas filtrado
      if ($request->has('search') || $request->has('debt_range') || $request->has('exchange_rate')) {
         return $this->debtReport($request);
      }
      
      // Reporte normal de todos los clientes
      $customers = Customer::withCount('sales')->where('company_id', $company->id)->get();
      $pdf = PDF::loadView('admin.customers.report', compact('customers', 'company', 'currency'));
      return $pdf->stream('reporte-clientes.pdf');
   }

   /**
    * Actualiza la deuda de un cliente directamente
    */
   public function updateDebt(Request $request, $id)
   {
      try {
         // Verificar autorización
         if (!Gate::allows('customers.edit')) {
            return response()->json([
               'success' => false,
               'message' => 'No tienes permisos para editar clientes'
            ], 403);
         }

         // Validar la solicitud
         $validated = $request->validate([
            'total_debt' => 'required|numeric|min:0',
         ]);

         // Buscar el cliente
         $customer = Customer::findOrFail($id);
;
         // Guardar el valor anterior para el log
         $previousDebt = $customer->total_debt;

         // Actualizar la deuda
         $customer->total_debt = $validated['total_debt'];
         $customer->save();



         return response()->json([
            'success' => true,
            'message' => 'Deuda actualizada correctamente',
         ]);
      } catch (\Exception $e) {


         return response()->json([
            'success' => false,
            'message' => 'Error al actualizar la deuda: ' . $e->getMessage(),
         ], 500);
      }
   }

   /**
    * Genera un reporte PDF de clientes con deudas pendientes
    */
   public function debtReport(Request $request)
   {
      try {
         // Verificar autorización
         if (!Gate::allows('customers.report')) {
            return redirect()->route('admin.customers.index')
               ->with('message', 'No tienes permisos para generar reportes de deudas')
               ->with('icons', 'error');
         }

         // Obtener el arqueo de caja actual una sola vez
         $currentCashCount = \App\Models\CashCount::where('company_id', $this->company->id)
            ->whereNull('closing_date')
            ->first();
         
         $openingDate = $currentCashCount ? $currentCashCount->opening_date : now();

         // Obtener clientes con deudas pendientes
         $query = Customer::where('company_id', $this->company->id)
            ->where('total_debt', '>', 0)
            ->select('id', 'name', 'phone', 'email', 'nit_number', 'total_debt', 'created_at', 'updated_at');

         // Aplicar filtros si existen
         if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
               $q->where('name', 'ILIKE', "%{$searchTerm}%")
                 ->orWhere('phone', 'ILIKE', "%{$searchTerm}%")
                 ->orWhere('email', 'ILIKE', "%{$searchTerm}%")
                 ->orWhere('nit_number', 'ILIKE', "%{$searchTerm}%");
            });
         }

         if ($request->has('debt_range') && $request->debt_range) {
            $debtRange = $request->debt_range;
            switch($debtRange) {
               case '0-50':
                  $query->whereBetween('total_debt', [0, 50]);
                  break;
               case '50-100':
                  $query->whereBetween('total_debt', [50.01, 100]);
                  break;
               case '100-500':
                  $query->whereBetween('total_debt', [100.01, 500]);
                  break;
               case '500+':
                  $query->where('total_debt', '>', 500);
                  break;
            }
         }

         if ($request->filled('debt_min')) {
            $query->where('total_debt', '>=', floatval($request->debt_min));
         }
         if ($request->filled('debt_max')) {
            $query->where('total_debt', '<=', floatval($request->debt_max));
         }

         // Filtrar por tipo de deuda (morosos vs deuda actual) - optimizado
         if ($request->filled('debt_type')) {
            $debtType = $request->debt_type;
            $customerIds = $query->pluck('id')->toArray();
            
            if (!empty($customerIds)) {
               // Obtener información de ventas y pagos en consultas consolidadas
               $salesInfo = \App\Models\Sale::whereIn('customer_id', $customerIds)
                  ->where('company_id', $this->company->id)
                  ->select('customer_id', 'sale_date', 'total_price')
                  ->get()
                  ->groupBy('customer_id');

               $paymentsInfo = [];
               if (Schema::hasTable('debt_payments')) {
                  $paymentsInfo = \App\Models\DebtPayment::whereIn('customer_id', $customerIds)
                     ->where('company_id', $this->company->id)
                     ->select('customer_id', 'payment_amount', 'created_at')
                     ->get()
                     ->groupBy('customer_id');
               }

               $defaultersIds = [];
               $currentDebtorsIds = [];

               foreach ($customerIds as $customerId) {
                  $customerSales = $salesInfo->get($customerId, collect());
                  $customerPayments = $paymentsInfo->get($customerId, collect());
                  
                  // Calcular ventas antes del arqueo actual
                  $salesBeforeCashCount = $customerSales->where('sale_date', '<', $openingDate);
                  
                  $totalSalesBefore = $salesBeforeCashCount->sum('total_price');
                  $totalPayments = $customerPayments->sum('payment_amount');
                  
                  // CORRECCIÓN: Calcular deuda anterior considerando TODOS los pagos
                  $previousDebt = 0;
                  if ($totalSalesBefore > 0) {
                     $previousDebt = max(0, $totalSalesBefore - $totalPayments);
                  }
                  
                  // Determinar si es moroso
                  $hasOldSales = $salesBeforeCashCount->count() > 0;
                  
                  if ($previousDebt > 0) {
                     $defaultersIds[] = $customerId;
                  } else {
                     $currentDebtorsIds[] = $customerId;
                  }
               }
               
               if ($debtType === 'defaulters') {
                  // Solo clientes morosos (con deudas de arqueos anteriores)
                  $query->whereIn('id', $defaultersIds);
               } elseif ($debtType === 'current') {
                  // Solo clientes con deuda del arqueo actual
                  $query->whereIn('id', $currentDebtorsIds);
               }
            }
         }

         // Aplicar ordenamiento según el parámetro order
         $order = $request->get('order', 'debt_desc'); // Por defecto ordenar por deuda descendente
         switch($order) {
            case 'name_asc':
               $query->orderBy('name', 'asc');
               break;
            case 'name_desc':
               $query->orderBy('name', 'desc');
               break;
            case 'debt_asc':
               $query->orderBy('total_debt', 'asc');
               break;
            case 'debt_desc':
            default:
               $query->orderBy('total_debt', 'desc');
               break;
         }

         $customers = $query->get();

         $company = $this->company;
         $currency = $this->currencies;
         $totalDebt = $customers->sum('total_debt');
         $exchangeRate = $request->get('exchange_rate', 1);

         // Generar PDF
         $pdf = PDF::loadView('admin.customers.reports.debt-report', compact(
            'customers',
            'company',
            'currency',
            'totalDebt',
            'exchangeRate'
         ));

         // Configurar PDF
         $pdf->setPaper('a4', 'portrait');



         // Nombre del archivo con filtros aplicados
         $fileName = 'reporte-deudas-clientes-' . date('Y-m-d');
         if ($request->search) {
            $fileName .= '-busqueda';
         }
         if ($request->filled('debt_min') || $request->filled('debt_max')) {
            $fileName .= '-filtrado-deuda';
         }
         if ($request->order && $request->order !== 'debt_desc') {
            $fileName .= '-ordenado';
         }
         $fileName .= '.pdf';

         // Mostrar PDF en el navegador
         return $pdf->stream($fileName);
      } catch (\Exception $e) {
         return redirect()->route('admin.customers.index')
            ->with('message', 'Error al generar el reporte de deudas: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Muestra un modal con el reporte de deudas de clientes
    */
   public function debtReportModal(Request $request)
   {
      try {
         // Verificar autorización
         if (!Gate::allows('customers.report')) {
            return response()->json([
               'success' => false,
               'message' => 'No tienes permisos para generar reportes de deudas'
            ], 403);
         }

         // Obtener el arqueo de caja actual una sola vez
         $currentCashCount = \App\Models\CashCount::where('company_id', $this->company->id)
            ->whereNull('closing_date')
            ->first();
         
         $openingDate = $currentCashCount ? $currentCashCount->opening_date : now();

         // Obtener clientes con deudas pendientes en una sola consulta optimizada
         $query = Customer::where('company_id', $this->company->id)
            ->where('total_debt', '>', 0)
            ->select('id', 'name', 'phone', 'email', 'nit_number', 'total_debt', 'created_at', 'updated_at');

         // Aplicar filtros si existen
         if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
               $q->where('name', 'ILIKE', "%{$searchTerm}%")
                 ->orWhere('phone', 'ILIKE', "%{$searchTerm}%")
                 ->orWhere('email', 'ILIKE', "%{$searchTerm}%")
                 ->orWhere('nit_number', 'ILIKE', "%{$searchTerm}%");
            });
         }

         // Aplicar ordenamiento según el parámetro order
         $order = $request->get('order', 'debt_desc'); // Por defecto ordenar por deuda descendente
         switch($order) {
            case 'name_asc':
               $query->orderBy('name', 'asc');
               break;
            case 'name_desc':
               $query->orderBy('name', 'desc');
               break;
            case 'debt_asc':
               $query->orderBy('total_debt', 'asc');
               break;
            case 'debt_desc':
            default:
               $query->orderBy('total_debt', 'desc');
               break;
         }

         $customers = $query->get();

         // Obtener información de ventas y pagos en consultas consolidadas
         $customerIds = $customers->pluck('id')->toArray();
         
         $salesInfo = \App\Models\Sale::whereIn('customer_id', $customerIds)
            ->where('company_id', $this->company->id)
            ->select('customer_id', 'sale_date', 'total_price')
            ->get()
            ->groupBy('customer_id');

         $paymentsInfo = [];
         if (Schema::hasTable('debt_payments')) {
            $paymentsInfo = \App\Models\DebtPayment::whereIn('customer_id', $customerIds)
               ->where('company_id', $this->company->id)
               ->select('customer_id', 'payment_amount', 'created_at')
               ->get()
               ->groupBy('customer_id');
         }

         // Crear un array para almacenar los datos calculados de cada cliente
         $customersData = [];
         $filteredCustomers = collect();
         
         // Procesar estadísticas optimizadas y aplicar filtro de tipo de deuda
         foreach ($customers as $customer) {
            $customerSales = $salesInfo->get($customer->id, collect());
            $customerPayments = $paymentsInfo->get($customer->id, collect());
            
            // Calcular ventas antes del arqueo actual
            $salesBeforeCashCount = $customerSales->where('sale_date', '<', $openingDate);
            
            $totalSalesBefore = $salesBeforeCashCount->sum('total_price');
            $totalPayments = $customerPayments->sum('payment_amount');
            
            // CORRECCIÓN: Calcular deuda anterior considerando TODOS los pagos
            // La deuda anterior = Ventas anteriores - Pagos totales (si hay ventas anteriores)
            $previousDebt = 0;
            if ($totalSalesBefore > 0) {
               // Si tiene ventas anteriores, calcular cuánto debe de esas ventas
               $previousDebt = max(0, $totalSalesBefore - $totalPayments);
            }
            
            // Determinar si es moroso (SOLO si tiene deuda pendiente de arqueos anteriores)
            $hasOldSales = $salesBeforeCashCount->count() > 0;
            $isDefaulter = $previousDebt > 0;
            
            // Almacenar datos calculados
            $customersData[$customer->id] = [
               'isDefaulter' => $isDefaulter,
               'previousDebt' => $previousDebt,
               'hasOldSales' => $hasOldSales
            ];
            
            // Aplicar filtro por tipo de deuda
            $debtType = $request->get('debt_type', '');
            $shouldInclude = true;
            
            if ($debtType === 'defaulters' && !$isDefaulter) {
               $shouldInclude = false;
            } elseif ($debtType === 'current' && $isDefaulter) {
               $shouldInclude = false;
            }
            
            if ($shouldInclude) {
               $filteredCustomers->push($customer);
            }
         }

         // Usar los clientes filtrados para las estadísticas
         $customers = $filteredCustomers;
         
         // Inicializar variables de estadísticas
         $defaultersCount = 0;
         $currentDebtorsCount = 0;
         $defaultersDebt = 0;
         $currentDebt = 0;
         
         // Calcular estadísticas finales con los clientes filtrados
         foreach ($customers as $customer) {
            $customerData = $customersData[$customer->id];
            
            if ($customerData['isDefaulter']) {
               $defaultersCount++;
               $defaultersDebt += $customer->total_debt;
            } else {
               $currentDebtorsCount++;
               $currentDebt += $customer->total_debt;
            }
         }

         $company = $this->company;
         $currency = $this->currencies;
         $totalDebt = $customers->sum('total_debt');
         $exchangeRate = request('exchange_rate', 1);

         // Verificar si es una petición AJAX
         if (request()->ajax() || request()->has('ajax')) {
            // Devolver solo el contenido del modal para AJAX
            return view('admin.customers.reports.debt-report-modal', compact(
               'customers',
               'customersData',
               'company',
               'currency',
               'totalDebt',
               'exchangeRate',
               'defaultersCount',
               'currentDebtorsCount',
               'defaultersDebt',
               'currentDebt'
            ));
         } else {
            // Devolver la vista completa para peticiones normales
            return view('admin.customers.reports.debt-report-modal', compact(
               'customers',
               'customersData',
               'company',
               'currency',
               'totalDebt',
               'exchangeRate',
               'defaultersCount',
               'currentDebtorsCount',
               'defaultersDebt',
               'currentDebt'
            ));
         }
      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Error al generar el reporte de deudas: ' . $e->getMessage()
         ], 500);
      }
   }



   public function registerDebtPayment(Request $request, Customer $customer)
   {
      // Verificar autorización
      if (!Gate::allows('customers.edit')) {
         return response()->json([
            'success' => false,
            'message' => 'No tienes permisos para registrar pagos de deuda'
         ], 403);
      }

      $request->validate([
         'payment_amount' => 'required|numeric|min:0.01|max:' . $customer->total_debt,
         'payment_date' => 'required|date',
         'payment_time' => 'required|date_format:H:i',
         'notes' => 'nullable|string|max:500',
      ]);

      // Validación manual de fecha usando la zona horaria de Venezuela
      $paymentDate = $request->payment_date;
      $today = now()->setTimezone('America/Caracas')->format('Y-m-d');
      
      if ($paymentDate > $today) {
         return response()->json([
            'success' => false,
            'message' => 'La fecha del pago no puede ser mayor a hoy',
            'errors' => [
               'payment_date' => ['La fecha del pago no puede ser mayor a hoy']
            ]
         ], 422);
      }

      $previousDebt = $customer->total_debt;
      $paymentAmount = $request->payment_amount;
      $paymentTime = $request->payment_time;
      $remainingDebt = $previousDebt - $paymentAmount;

      // Registrar el pago con la fecha especificada
      $debtPayment = DebtPayment::create([
         'company_id' => $this->company->id,
         'customer_id' => $customer->id,
         'previous_debt' => $previousDebt,
         'payment_amount' => $paymentAmount,
         'remaining_debt' => $remainingDebt,
         'notes' => $request->notes,
         'user_id' => Auth::id(),
      ]);

      // Actualizar las fechas created_at y updated_at con la fecha y hora proporcionadas
      $paymentDateTime = Carbon::parse($paymentDate . ' ' . $paymentTime, 'America/Caracas');
      $debtPayment->created_at = $paymentDateTime;
      $debtPayment->updated_at = $paymentDateTime;
      $debtPayment->save();

      // Actualizar la deuda total del cliente
      $customer->update([
         'total_debt' => $remainingDebt
      ]);

      // Verificar que la actualización se haya realizado correctamente
      $customer->refresh();
      


      return response()->json([
         'success' => true,
         'message' => 'Pago registrado correctamente',
         'new_debt' => $customer->total_debt,
         'formatted_new_debt' => number_format($customer->total_debt, 2)
      ]);
   }

   /**
    * Registrar pago de deuda via AJAX para SPA
    */
   public function registerDebtPaymentAjax(Request $request, Customer $customer)
   {
      try {
         // Verificar autorización
         if (!Gate::allows('customers.edit')) {
            return response()->json([
               'success' => false,
               'message' => 'No tienes permisos para registrar pagos de deuda'
            ], 403);
         }

         $request->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:' . $customer->total_debt,
            'payment_date' => 'required|date',
            'payment_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:500',
         ]);

         // Validación manual de fecha usando la zona horaria de Venezuela
         $paymentDate = $request->payment_date;
         $today = now()->setTimezone('America/Caracas')->format('Y-m-d');
         
         if ($paymentDate > $today) {
            return response()->json([
               'success' => false,
               'message' => 'La fecha del pago no puede ser mayor a hoy',
               'errors' => [
                  'payment_date' => ['La fecha del pago no puede ser mayor a hoy']
               ]
            ], 422);
         }

         $previousDebt = $customer->total_debt;
         $paymentAmount = $request->payment_amount;
         $paymentTime = $request->payment_time;
         $remainingDebt = $previousDebt - $paymentAmount;

         // Registrar el pago con la fecha especificada
         $debtPayment = DebtPayment::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'previous_debt' => $previousDebt,
            'payment_amount' => $paymentAmount,
            'remaining_debt' => $remainingDebt,
            'notes' => $request->notes,
            'user_id' => Auth::id(),
         ]);

         // Actualizar las fechas created_at y updated_at con la fecha y hora proporcionadas
         $paymentDateTime = Carbon::parse($paymentDate . ' ' . $paymentTime, 'America/Caracas');
         $debtPayment->created_at = $paymentDateTime;
         $debtPayment->updated_at = $paymentDateTime;
         $debtPayment->save();

         // Actualizar la deuda total del cliente
         $customer->update([
            'total_debt' => $remainingDebt
         ]);

         // Verificar que la actualización se haya realizado correctamente
         $customer->refresh();

         // Obtener estadísticas actualizadas para el dashboard
         $stats = $this->getUpdatedStats();

         return response()->json([
            'success' => true,
            'message' => 'Pago registrado correctamente',
            'customer' => [
               'id' => $customer->id,
               'name' => $customer->name,
               'total_debt' => $customer->total_debt,
               'formatted_total_debt' => number_format($customer->total_debt, 2),
               'has_debt' => $customer->total_debt > 0,
               'is_defaulter' => $this->isCustomerDefaulter($customer->id)
            ],
            'stats' => $stats,
            'payment' => [
               'id' => $debtPayment->id,
               'amount' => $paymentAmount,
               'date' => $paymentDateTime->format('d/m/Y H:i'),
               'remaining_debt' => $remainingDebt
            ]
         ]);

      } catch (\Illuminate\Validation\ValidationException $e) {
         return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $e->errors()
         ], 422);
      } catch (\Exception $e) {

         return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor'
         ], 500);
      }
   }

   /**
    * Obtener estadísticas actualizadas para el dashboard
    */
   private function getUpdatedStats()
   {
      // Obtener el arqueo de caja actual
      $currentCashCount = \App\Models\CashCount::where('company_id', $this->company->id)
         ->whereNull('closing_date')
         ->first();
      
      $openingDate = $currentCashCount ? $currentCashCount->opening_date : now();

      // Estadísticas básicas
      $stats = DB::table('customers')
         ->where('company_id', $this->company->id)
         ->selectRaw('
            COUNT(*) as total_customers,
            SUM(total_debt) as total_debt,
            COUNT(CASE WHEN EXTRACT(MONTH FROM created_at) = ? AND EXTRACT(YEAR FROM created_at) = ? THEN 1 END) as new_customers
         ', [now()->month, now()->year])
         ->first();

      $totalCustomers = $stats->total_customers ?? 0;
      $totalDebt = $stats->total_debt ?? 0;
      $newCustomers = $stats->new_customers ?? 0;
      $customerGrowth = $totalCustomers > 0 ? round(($newCustomers / $totalCustomers) * 100) : 0;

      // Calcular clientes activos
      $activeCustomers = DB::table('customers')
         ->join('sales', 'customers.id', '=', 'sales.customer_id')
         ->where('customers.company_id', $this->company->id)
         ->distinct()
         ->count('customers.id');

      // Calcular ingresos totales
      $totalRevenue = DB::table('sales')
         ->where('company_id', $this->company->id)
         ->sum('total_price');

      // Calcular clientes morosos
      $defaultersCount = $this->getDefaultersCount();

      return [
         'total_customers' => $totalCustomers,
         'active_customers' => $activeCustomers,
         'new_customers' => $newCustomers,
         'customer_growth' => $customerGrowth,
         'total_revenue' => $totalRevenue,
         'defaulters_count' => $defaultersCount,
         'total_debt' => $totalDebt
      ];
   }

   /**
    * Verificar si un cliente es moroso
    */
   private function isCustomerDefaulter($customerId)
   {
      $currentCashCount = \App\Models\CashCount::where('company_id', $this->company->id)
         ->whereNull('closing_date')
         ->first();
      
      $openingDate = $currentCashCount ? $currentCashCount->opening_date : now();

      // Verificar si tiene ventas antes del arqueo actual
      $hasOldSales = DB::table('sales')
         ->where('customer_id', $customerId)
         ->where('company_id', $this->company->id)
         ->where('sale_date', '<', $openingDate)
         ->exists();

      return $hasOldSales;
   }

   /**
    * Obtener el conteo de clientes morosos
    */
   private function getDefaultersCount()
   {
      $currentCashCount = \App\Models\CashCount::where('company_id', $this->company->id)
         ->whereNull('closing_date')
         ->first();
      
      $openingDate = $currentCashCount ? $currentCashCount->opening_date : now();

      return DB::table('customers')
         ->where('customers.company_id', $this->company->id)
         ->where('customers.total_debt', '>', 0)
         ->whereExists(function ($query) use ($openingDate) {
            $query->select(DB::raw(1))
               ->from('sales')
               ->whereColumn('sales.customer_id', 'customers.id')
               ->where('sales.company_id', $this->company->id)
               ->where('sales.sale_date', '<', $openingDate);
         })
         ->count();
   }

   /**
    * Obtener datos del cliente para el modal de pago
    */
   public function getCustomerPaymentData(Customer $customer)
   {
      try {
         // Verificar autorización
         if (!Gate::allows('customers.show')) {
            return response()->json([
               'success' => false,
               'message' => 'No tienes permisos para ver datos de clientes'
            ], 403);
         }

         // Verificar que el cliente pertenezca a la empresa
         if ($customer->company_id !== $this->company->id) {
            return response()->json([
               'success' => false,
               'message' => 'Cliente no encontrado'
            ], 404);
         }

         // Verificar si el cliente tiene ventas
         $hasSales = DB::table('sales')
            ->where('customer_id', $customer->id)
            ->where('company_id', $this->company->id)
            ->exists();

         // Verificar si es moroso
         $isDefaulter = $this->isCustomerDefaulter($customer->id);

         return response()->json([
            'success' => true,
            'customer' => [
               'id' => $customer->id,
               'name' => $customer->name,
               'phone' => $customer->phone,
               'email' => $customer->email,
               'total_debt' => $customer->total_debt,
               'formatted_total_debt' => number_format($customer->total_debt, 2),
               'has_sales' => $hasSales,
               'is_defaulter' => $isDefaulter,
               'has_debt' => $customer->total_debt > 0
            ]
         ]);

      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor'
         ], 500);
      }
   }

   /**
    * Obtener historial de ventas del cliente
    */
   public function getCustomerSalesHistory(Customer $customer)
   {
      try {
         // Verificar autorización
         if (!Gate::allows('customers.show')) {
            return response()->json([
               'success' => false,
               'message' => 'No tienes permisos para ver historial de ventas'
            ], 403);
         }

         // Verificar que el cliente pertenezca a la empresa
         if ($customer->company_id !== $this->company->id) {
            return response()->json([
               'success' => false,
               'message' => 'Cliente no encontrado'
            ], 404);
         }

         // Obtener ventas del cliente con detalles de productos
         $sales = DB::table('sales')
            ->where('customer_id', $customer->id)
            ->where('company_id', $this->company->id)
            ->select('sale_date', 'total_price', 'id')
            ->orderBy('sale_date', 'desc')
            ->get();

         Log::info('Customer sales found:', ['customer_id' => $customer->id, 'sales_count' => $sales->count()]);

         // Formatear datos para la tabla con información de productos
         $formattedSales = $sales->map(function ($sale) {
            // Obtener detalles de productos para esta venta
            $saleDetails = DB::table('sale_details')
               ->where('sale_id', $sale->id)
               ->selectRaw('COUNT(DISTINCT product_id) as unique_products, SUM(quantity) as total_units')
               ->first();

            $uniqueProducts = $saleDetails->unique_products ?? 0;
            $totalUnits = $saleDetails->total_units ?? 0;

            Log::info('Sale details:', [
               'sale_id' => $sale->id,
               'unique_products' => $uniqueProducts,
               'total_units' => $totalUnits
            ]);

            return [
               'date' => Carbon::parse($sale->sale_date)->format('d/m/Y'),
               'products' => $uniqueProducts . ' productos únicos<br><small class="text-gray-500">' . $totalUnits . ' unidades totales</small>',
               'total' => $sale->total_price
            ];
         });

         return response()->json([
            'success' => true,
            'sales' => $formattedSales
         ]);

      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor'
         ], 500);
      }
   }

   public function paymentHistory(Request $request)
   {
      // Verificar autorización
      if (!Gate::allows('customers.report')) {
         return redirect()->route('admin.customers.index')
            ->with('message', 'No tienes permisos para ver el historial de pagos')
            ->with('icons', 'error');
      }

      $query = DebtPayment::where('company_id', $this->company->id)
         ->with(['customer', 'user']);

      // Aplicar filtros
      if ($request->has('customer_search') && $request->customer_search) {
         $query->whereHas('customer', function ($q) use ($request) {
            $q->where('name', 'ilike', '%' . $request->customer_search . '%');
         });
      }

      if ($request->has('date_from') && $request->date_from) {
         $query->whereDate('created_at', '>=', $request->date_from);
      }

      if ($request->has('date_to') && $request->date_to) {
         $query->whereDate('created_at', '<=', $request->date_to);
      }

      $payments = $query->orderBy('created_at', 'desc')->paginate(15);

      // Estadísticas
      $totalPayments = $query->sum('payment_amount');
      $paymentsCount = $query->count();
      $averagePayment = $paymentsCount > 0 ? $totalPayments / $paymentsCount : 0;
      $totalRemainingDebt = Customer::where('company_id', $this->company->id)->sum('total_debt');

      // Datos para gráficos
      $weekdayData = DebtPayment::where('company_id', $this->company->id)
         ->selectRaw('EXTRACT(DOW FROM created_at) as day_of_week, SUM(payment_amount) as total')
         ->groupBy('day_of_week')
         ->orderBy('day_of_week')
         ->get()
         ->pluck('total', 'day_of_week')
         ->toArray();

      $weekdayLabels = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
      $weekdayDataArray = [];

      // PostgreSQL DOW: 0=Domingo, 1=Lunes, ..., 6=Sábado
      for ($i = 0; $i <= 6; $i++) {
         $weekdayDataArray[] = $weekdayData[$i] ?? 0;
      }

      // Datos mensuales - Compatible con PostgreSQL
      $monthlyData = DebtPayment::where('company_id', $this->company->id)
         ->whereRaw('EXTRACT(YEAR FROM created_at) = ?', [date('Y')])
         ->selectRaw('EXTRACT(MONTH FROM created_at) as month, SUM(payment_amount) as total')
         ->groupBy('month')
         ->orderBy('month')
         ->get()
         ->pluck('total', 'month')
         ->toArray();

      $monthlyLabels = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
      $monthlyDataArray = [];

      for ($i = 1; $i <= 12; $i++) {
         $monthlyDataArray[] = $monthlyData[$i] ?? 0;
      }

      $currency = $this->currencies;

      return view('admin.customers.payment-history', [
         'payments' => $payments,
         'totalPayments' => $totalPayments,
         'paymentsCount' => $paymentsCount,
         'averagePayment' => $averagePayment,
         'totalRemainingDebt' => $totalRemainingDebt,
         'weekdayLabels' => $weekdayLabels,
         'weekdayData' => $weekdayDataArray,
         'monthlyLabels' => $monthlyLabels,
         'monthlyData' => $monthlyDataArray,
         'currency' => $currency,
      ]);
   }

   /**
    * Exporta el historial de pagos a Excel
    */
   public function exportPaymentHistory(Request $request)
   {
      try {
         // Verificar autorización
         if (!Gate::allows('customers.report')) {
            return redirect()->route('admin.customers.index')
               ->with('message', 'No tienes permisos para exportar el historial de pagos')
               ->with('icons', 'error');
         }

         $query = DebtPayment::where('company_id', $this->company->id)
            ->with(['customer', 'user']);
         
         // Aplicar filtros
         if ($request->has('customer_search') && $request->customer_search) {
            $query->whereHas('customer', function ($q) use ($request) {
               $q->where('name', 'ilike', '%' . $request->customer_search . '%');
            });
         }
         
         if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
         }
         
         if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
         }
         
         $payments = $query->orderBy('created_at', 'desc')->get();
         
         // Crear un archivo Excel
         $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
         $sheet = $spreadsheet->getActiveSheet();
         
         // Establecer el título
         $sheet->setCellValue('A1', 'HISTORIAL DE PAGOS DE DEUDAS');
         $sheet->mergeCells('A1:G1');
         $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
         $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
         
         // Establecer encabezados
         $sheet->setCellValue('A3', 'FECHA');
         $sheet->setCellValue('B3', 'CLIENTE');
         $sheet->setCellValue('C3', 'DEUDA ANTERIOR');
         $sheet->setCellValue('D3', 'MONTO PAGADO');
         $sheet->setCellValue('E3', 'DEUDA RESTANTE');
         $sheet->setCellValue('F3', 'REGISTRADO POR');
         $sheet->setCellValue('G3', 'NOTAS');
         
         $sheet->getStyle('A3:G3')->getFont()->setBold(true);
         $sheet->getStyle('A3:G3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');
         
         // Llenar datos
         $row = 4;
         foreach ($payments as $payment) {
            $sheet->setCellValue('A' . $row, $payment->created_at->format('d/m/Y H:i:s'));
            $sheet->setCellValue('B' . $row, $payment->customer->name);
            $sheet->setCellValue('C' . $row, $payment->previous_debt);
            $sheet->setCellValue('D' . $row, $payment->payment_amount);
            $sheet->setCellValue('E' . $row, $payment->remaining_debt);
            $sheet->setCellValue('F' . $row, $payment->user->name);
            $sheet->setCellValue('G' . $row, $payment->notes);
            $row++;
         }
         
         // Autoajustar columnas
         foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
         }
         
         // Crear el archivo Excel
         $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
         $fileName = 'historial_pagos_' . date('Y-m-d') . '.xlsx';
         $tempFile = tempnam(sys_get_temp_dir(), $fileName);
         $writer->save($tempFile);
         
         return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
      } catch (\Exception $e) {
         return redirect()->route('admin.customers.payment-history')
            ->with('message', 'Error al exportar el historial de pagos: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   public function deletePayment(DebtPayment $payment)
   {
      try {
         // Verificar autorización
         if (!Gate::allows('customers.edit')) {
            return response()->json([
               'success' => false,
               'message' => 'No tienes permisos para eliminar pagos de deuda'
            ], 403);
         }

         DB::beginTransaction();

         // Obtener el cliente y el monto del pago
         $customer = $payment->customer;
         $paymentAmount = $payment->payment_amount;

         // Restaurar la deuda al cliente
         $customer->total_debt += $paymentAmount;
         $customer->save();

         // Eliminar el registro del pago
         $payment->delete();

         DB::commit();

         // Obtener estadísticas actualizadas
         $totalPayments = DebtPayment::where('company_id', $this->company->id)->sum('payment_amount');
         $paymentsCount = DebtPayment::where('company_id', $this->company->id)->count();
         $averagePayment = $paymentsCount > 0 ? $totalPayments / $paymentsCount : 0;

         return response()->json([
            'success' => true,
            'message' => 'Pago eliminado correctamente',
            'statistics' => [
                'totalPayments' => number_format($totalPayments, 2),
                'paymentsCount' => $paymentsCount,
                'averagePayment' => number_format($averagePayment, 2)
            ]
         ]);

               } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
               'success' => false,
               'message' => 'Error al eliminar el pago: ' . $e->getMessage()
            ], 500);
         }
   }
}
