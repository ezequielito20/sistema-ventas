<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
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
         


         // Obtener todos los clientes con sus ventas (paginados)
         $perPage = $request->get('per_page', 25);
         $customers = Customer::with('sales')
            ->where('company_id', $this->company->id)
            ->orderBy('name')
            ->paginate($perPage);
         $currency = $this->currencies;
         $company = $this->company;

         // Calcular estadísticas básicas (usando consultas separadas para totales)
         $totalCustomers = Customer::where('company_id', $this->company->id)->count();
         $activeCustomers = Customer::where('company_id', $this->company->id)
            ->whereHas('sales')
            ->count();
         $newCustomers = Customer::where('company_id', $this->company->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
         $customerGrowth = $totalCustomers > 0 ? round(($newCustomers / $totalCustomers) * 100) : 0;

         // Calcular la deuda total de todos los clientes
         $totalDebt = Customer::where('company_id', $this->company->id)->sum('total_debt');

         // Calcular ingresos totales
         $totalRevenue = \App\Models\Sale::where('company_id', $this->company->id)->sum('total_price');

         // Optimizar cálculo de estadísticas de deudas usando consultas consolidadas
         $customerIds = $customers->pluck('id')->toArray();
         
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

         // Calcular estadísticas optimizadas (solo para la página actual)
         $defaultersCount = 0;
         $currentDebtorsCount = 0;
         $previousCashCountDebtTotal = 0;
         $currentCashCountDebtTotal = 0;

         // Crear un array para almacenar los datos calculados de cada cliente
         $customersData = [];
         
         foreach ($customers as $customer) {
            $customerSales = $salesInfo->get($customer->id, collect());
            $customerPayments = $paymentsInfo->get($customer->id, collect());
            
            // Calcular ventas antes y después del arqueo actual
            $salesBeforeCashCount = $customerSales->where('sale_date', '<', $openingDate);
            $salesAfterCashCount = $customerSales->where('sale_date', '>=', $openingDate);
            
            $totalSalesBefore = $salesBeforeCashCount->sum('total_price');
            $totalSalesAfter = $salesAfterCashCount->sum('total_price');
            
            // Calcular pagos totales
            $totalPayments = $customerPayments->sum('payment_amount');
            
            // CORRECCIÓN: Calcular deuda anterior considerando TODOS los pagos
            // La deuda anterior = Ventas anteriores - Pagos totales (si hay ventas anteriores)
            $previousDebt = 0;
            if ($totalSalesBefore > 0) {
               // Si tiene ventas anteriores, calcular cuánto debe de esas ventas
               $previousDebt = max(0, $totalSalesBefore - $totalPayments);
            }
            
            // Calcular deuda actual (solo ventas del arqueo actual)
            $currentDebt = max(0, $totalSalesAfter);
            
            // Determinar si es moroso (SOLO si tiene deuda pendiente de arqueos anteriores)
            $hasOldSales = $salesBeforeCashCount->count() > 0;
            $isDefaulter = $previousDebt > 0;
            
            // Almacenar datos calculados
            $customersData[$customer->id] = [
               'isDefaulter' => $isDefaulter,
               'previousDebt' => $previousDebt,
               'currentDebt' => $currentDebt,
               'hasOldSales' => $hasOldSales
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

         // Calcular estadísticas totales de morosos (para toda la base de datos)
         $totalDefaultersCount = Customer::where('company_id', $this->company->id)
            ->whereHas('sales', function($query) use ($openingDate) {
               $query->where('sale_date', '<', $openingDate);
            })
            ->count();

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
            'company'
         ));
      } catch (\Exception $e) {
         Log::error('Error en CustomerController@index: ' . $e->getMessage());
         return redirect()->route('admin.dashboard')
            ->with('message', 'Error al cargar la lista de clientes: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      try {
         $company = $this->company;
         return view('admin.customers.create', compact('company'));
      } catch (\Exception $e) {
         Log::error('Error en CustomerController@create: ' . $e->getMessage());
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

         // Registrar la acción en el log
         Log::info('Cliente creado exitosamente', [
            'user_id' => Auth::id(),
            'customer_id' => $customer->id,
            'customer_name' => $customer->name
         ]);

         DB::commit();

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
         Log::error('Error en CustomerController@store: ' . $e->getMessage());

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
         $customer = Customer::with(['sales' => function ($query) {
            $query->orderBy('sale_date', 'desc');
         }, 'sales.saleDetails'])->findOrFail($id);

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
               Log::error('Error al obtener datos de pago de deuda: ' . $e->getMessage(), [
                  'user_id' => Auth::id(),
                  'customer_id' => $customer->id
               ]);

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
               Log::error('Error al obtener detalles del cliente: ' . $e->getMessage(), [
                  'customer_id' => $customer->id,
                  'error' => $e->getMessage()
               ]);
               
               return response()->json([
                  'success' => false,
                  'message' => 'Error al obtener detalles del cliente'
               ], 500);
            }
         }

         // Obtener estadísticas del cliente
         $stats = [
            'total_purchases' => $customer->sales->count(),
            'total_spent' => $customer->sales->sum('total_price'),
            'purchase_history' => $this->getPurchaseHistory($customer->sales)
         ];

         // Obtener las ventas individuales con detalles
         $salesData = $customer->sales->map(function ($sale) {
            return [
               'id' => $sale->id,
               'date' => $sale->sale_date->format('d/m/Y'),
               'total_products' => $sale->saleDetails->sum('quantity'),
               'total_amount' => $sale->total_price,
               'invoice_number' => $sale->getFormattedInvoiceNumber()
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
         Log::error('Error al mostrar cliente: ' . $e->getMessage(), [
            'user_id' => Auth::id(),
            'customer_id' => $id
         ]);

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
         return $sale->sale_date->format('Y-m');
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
         $company = $this->company;
         // Buscar el cliente
         $customer = Customer::findOrFail($id);

         // Retornar vista con datos del cliente
         return view('admin.customers.edit', compact('customer', 'company'));
      } catch (\Exception $e) {
         Log::error('Error en CustomerController@edit: ' . $e->getMessage(), [
            'user_id' => Auth::id(),
            'customer_id' => $id
         ]);

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
         // Buscar el cliente
         $customer = Customer::findOrFail($id);

         // Validación personalizada
         $validated = $request->validate([
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
               'regex:/^\(\d{3}\)\s\d{3}-\d{4}$/',
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
            'phone.regex' => 'El formato del teléfono debe ser: (XXX) XXX-XXXX',
            'phone.unique' => 'Este teléfono ya está registrado',

            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'Debe ingresar un correo electrónico válido',
            'email.max' => 'El correo no debe exceder los 255 caracteres',
            'email.unique' => 'Este correo ya está registrado',
            'total_debt.numeric' => 'La deuda debe ser un valor numérico',
            'total_debt.min' => 'La deuda no puede ser un valor negativo',
         ]);

         // Actualizar el cliente
         $customer->update($validated);

         // Log de la actualización
         Log::info('Cliente actualizado exitosamente', [
            'user_id' => Auth::id(),
            'customer_id' => $customer->id,
            'customer_name' => $customer->name
         ]);

         // Redireccionar con mensaje de éxito
         return redirect()->route('admin.customers.index')
            ->with('message', '¡Cliente actualizado exitosamente!')
            ->with('icons', 'success');
      } catch (\Illuminate\Validation\ValidationException $e) {
         return redirect()->back()
            ->withErrors($e->validator)
            ->withInput()
            ->with('message', 'Por favor, corrija los errores en el formulario.')
            ->with('icons', 'error');
      } catch (\Exception $e) {
         Log::error('Error al actualizar cliente: ' . $e->getMessage(), [
            'user_id' => Auth::id(),
            'customer_id' => $id,
            'data' => $request->all()
         ]);

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
            ], 422);
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
            ], 422);
         }

         // Guardar información para el log antes de eliminar
         $customerInfo = [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email
         ];

         // Eliminar el cliente
         $customer->delete();

         // Log de la eliminación
         Log::info('Cliente eliminado exitosamente', [
            'user_id' => Auth::id(),
            'customer_info' => $customerInfo
         ]);

         // Retornar respuesta exitosa
         return response()->json([
            'success' => true,
            'message' => '¡Cliente eliminado exitosamente!',
            'icons' => 'success'
         ]);
      } catch (\Illuminate\Database\QueryException $e) {
         // Capturar errores específicos de base de datos
         Log::error('Error de base de datos al eliminar cliente: ' . $e->getMessage(), [
            'user_id' => Auth::id(),
            'customer_id' => $id,
            'error_code' => $e->getCode()
         ]);

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
            ], 422);
         }

         return response()->json([
            'success' => false,
            'message' => 'Error de base de datos al eliminar el cliente. Por favor, inténtelo de nuevo.',
            'icons' => 'error'
         ], 500);
      } catch (\Exception $e) {
         Log::error('Error al eliminar cliente: ' . $e->getMessage(), [
            'user_id' => Auth::id(),
            'customer_id' => $id
         ]);

         return response()->json([
            'success' => false,
            'message' => 'Hubo un problema al eliminar el cliente. Por favor, inténtelo de nuevo.',
            'icons' => 'error'
         ], 500);
      }
   }

   public function report(Request $request)
   {
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
         // Validar la solicitud
         $validated = $request->validate([
            'total_debt' => 'required|numeric|min:0',
         ]);

         // Buscar el cliente
         $customer = Customer::findOrFail($id);

         // Guardar el valor anterior para el log
         $previousDebt = $customer->total_debt;

         // Actualizar la deuda
         $customer->total_debt = $validated['total_debt'];
         $customer->save();

         // Log de la actualización
         Log::info('Deuda de cliente actualizada', [
            'user_id' => Auth::id(),
            'customer_id' => $customer->id,
            'previous_debt' => $previousDebt,
            'new_debt' => $customer->total_debt
         ]);

         return response()->json([
            'success' => true,
            'message' => 'Deuda actualizada correctamente',
         ]);
      } catch (\Exception $e) {
         Log::error('Error al actualizar deuda: ' . $e->getMessage(), [
            'user_id' => Auth::id(),
            'customer_id' => $id
         ]);

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

         // Log de la generación del reporte
         Log::info('Reporte de deudas generado', [
            'user_id' => Auth::id(),
            'total_customers' => $customers->count(),
            'total_debt' => $totalDebt,
            'filters' => $request->only(['search', 'debt_range', 'debt_min', 'debt_max', 'order', 'exchange_rate'])
         ]);

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
         Log::error('Error al generar reporte de deudas: ' . $e->getMessage());
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
         Log::error('Error al generar reporte de deudas modal: ' . $e->getMessage());
         return response()->json([
            'success' => false,
            'message' => 'Error al generar el reporte de deudas: ' . $e->getMessage()
         ], 500);
      }
   }



   public function registerDebtPayment(Request $request, Customer $customer)
   {
      $request->validate([
         'payment_amount' => 'required|numeric|min:0.01|max:' . $customer->total_debt,
         'payment_date' => 'required|date|before_or_equal:today',
         'payment_time' => 'required|date_format:H:i',
         'notes' => 'nullable|string|max:500',
      ]);

      $previousDebt = $customer->total_debt;
      $paymentAmount = $request->payment_amount;
      $paymentDate = $request->payment_date;
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
      $paymentDateTime = Carbon::parse($paymentDate . ' ' . $paymentTime);
      $debtPayment->created_at = $paymentDateTime;
      $debtPayment->updated_at = $paymentDateTime;
      $debtPayment->save();

      // Actualizar la deuda total del cliente
      $customer->update([
         'total_debt' => $remainingDebt
      ]);

      // Verificar que la actualización se haya realizado correctamente
      $customer->refresh();
      
      // Registrar en el log para depuración
      Log::info('Pago de deuda registrado', [
         'customer_id' => $customer->id,
         'previous_debt' => $previousDebt,
         'payment_amount' => $paymentAmount,
         'payment_date' => $paymentDate,
         'payment_time' => $paymentTime,
         'payment_datetime' => $paymentDateTime->toDateTimeString(),
         'remaining_debt' => $remainingDebt,
         'new_total_debt' => $customer->total_debt
      ]);

      return response()->json([
         'success' => true,
         'message' => 'Pago registrado correctamente',
         'new_debt' => $customer->total_debt,
         'formatted_new_debt' => number_format($customer->total_debt, 2)
      ]);
   }

   public function paymentHistory(Request $request)
   {
      $query = DebtPayment::where('company_id', $this->company->id)
         ->with(['customer', 'user']);

      // Aplicar filtros
      if ($request->has('customer_id') && $request->customer_id) {
         $query->where('customer_id', $request->customer_id);
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

      // Datos mensuales
      $monthlyData = DebtPayment::where('company_id', $this->company->id)
         ->whereYear('created_at', date('Y'))
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

      $customers = Customer::where('company_id', $this->company->id)->orderBy('name')->get();
      $currency = $this->currencies;

      return view('admin.customers.payment-history', [
         'payments' => $payments,
         'customers' => $customers,
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
         $query = DebtPayment::where('company_id', $this->company->id)
            ->with(['customer', 'user']);
         
         // Aplicar filtros
         if ($request->has('customer_id') && $request->customer_id) {
            $query->where('customer_id', $request->customer_id);
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
         Log::error('Error al exportar historial de pagos: ' . $e->getMessage());
         return redirect()->route('admin.customers.payment-history')
            ->with('message', 'Error al exportar el historial de pagos: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   public function deletePayment(DebtPayment $payment)
   {
      try {
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
         Log::error('Error al eliminar pago de deuda: ' . $e->getMessage());
         
         return response()->json([
            'success' => false,
            'message' => 'Error al eliminar el pago: ' . $e->getMessage()
         ], 500);
      }
   }
}
