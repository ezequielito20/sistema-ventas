<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Barryvdh\DomPDF\Facade\Pdf;

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
   public function index()
   {
      try {
         // Obtener todos los clientes con sus ventas
         $customers = Customer::with('sales')
            ->where('company_id', $this->company->id)
            ->orderBy('name') 
            ->get();
         $currency = $this->currencies;
         $company = $this->company;

         // Calcular estadísticas
         $totalCustomers = $customers->count();
         $activeCustomers = $customers->filter(function ($customer) {
            return $customer->sales->count() > 0;
         })->count();
         $newCustomers = $customers->filter(function ($customer) {
            return $customer->created_at->isCurrentMonth();
         })->count();
         $customerGrowth = $totalCustomers > 0 ? round(($newCustomers / $totalCustomers) * 100) : 0;
         
         // Calcular la deuda total de todos los clientes
         $totalDebt = $customers->sum('total_debt');
         
         // Calcular ingresos totales
         $totalRevenue = $customers->sum(function ($customer) {
            return $customer->sales->sum('total_price');
         });

         return view('admin.customers.index', compact(
            'customers',
            'totalCustomers',
            'activeCustomers',
            'newCustomers',
            'customerGrowth',
            'totalRevenue',
            'totalDebt',
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

         // Determinar la redirección basada en el botón presionado
         if ($request->input('action') === 'save_and_new') {
            return redirect()->route('admin.customers.create')
               ->with('message', '¡Cliente creado exitosamente! Puedes crear otro cliente.')
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
         $customer = Customer::with(['sales' => function($query) {
            $query->orderBy('sale_date', 'asc');
         }])->findOrFail($id);

         // Obtener estadísticas del cliente
         $stats = [
            'total_purchases' => $customer->sales->count(),
            'total_spent' => $customer->sales->sum('total_price'),
            'purchase_history' => $this->getPurchaseHistory($customer->sales)
         ];

         return response()->json([
            'success' => true,
            'customer' => [
               'name' => $customer->name,
               'email' => $customer->email,
               'phone' => $customer->phone,
               'nit_number' => $customer->nit_number,
               'created_at' => $customer->created_at->format('d/m/Y H:i'),
               'updated_at' => $customer->updated_at->format('d/m/Y H:i'),
               'stats' => $stats
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
      $salesByMonth = $sales->groupBy(function($sale) {
         return $sale->sale_date->format('Y-m');
      })->map(function($monthSales) {
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

   public function report()
   {
      $company = $this->company;
      $currency = $this->currencies;
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
   public function debtReport()
   {
       try {
           // Obtener clientes con deudas pendientes
           $customers = Customer::where('company_id', $this->company->id)
               ->where('total_debt', '>', 0)
               ->orderBy('total_debt', 'desc')
               ->get();
               
           $company = $this->company;
           $currency = $this->currencies;
           $totalDebt = $customers->sum('total_debt');
           
           // Generar PDF
           $pdf = PDF::loadView('admin.customers.reports.debt-report', compact(
               'customers', 
               'company', 
               'currency',
               'totalDebt'
           ));
           
           // Configurar PDF
           $pdf->setPaper('a4', 'portrait');
           
           // Log de la generación del reporte
           Log::info('Reporte de deudas generado', [
               'user_id' => Auth::id(),
               'total_customers' => $customers->count(),
               'total_debt' => $totalDebt
           ]);
           
           // Descargar PDF
           return $pdf->download('reporte-deudas-clientes-' . date('Y-m-d') . '.pdf');
           
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
   public function debtReportModal()
   {
       try {
           // Obtener clientes con deudas pendientes
           $customers = Customer::where('company_id', $this->company->id)
               ->where('total_debt', '>', 0)
               ->with('lastSale')
               ->orderBy('total_debt', 'desc')
               ->get();
               
           $company = $this->company;
           $currency = $this->currencies;
           $totalDebt = $customers->sum('total_debt');
           
           // Devolver la vista parcial para el modal
           return view('admin.customers.reports.debt-report-modal', compact(
               'customers', 
               'company', 
               'currency',
               'totalDebt'
           ));
           
       } catch (\Exception $e) {
           Log::error('Error al generar reporte de deudas modal: ' . $e->getMessage());
           return response()->json([
               'success' => false,
               'message' => 'Error al generar el reporte de deudas: ' . $e->getMessage()
           ], 500);
       }
   }
}
