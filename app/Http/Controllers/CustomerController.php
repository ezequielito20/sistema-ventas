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

class CustomerController extends Controller
{
   /**
    * Display a listing of the resource.
    */
   public function index()
   {
      try {
         // Obtener todos los clientes
         $customers = Customer::all();

         // Estadísticas básicas
         $totalCustomers = $customers->count();
         
         // Calcular crecimiento de clientes
         $lastMonthCustomers = Customer::whereMonth('created_at', '=', Carbon::now()->subMonth()->month)->count();
         $customerGrowth = $lastMonthCustomers > 0 
            ? round((($totalCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100, 1)
            : 0;

         // Nuevos clientes este mes
         $newCustomers = $customers->filter(function($customer) {
            return $customer->created_at->isCurrentMonth();
         })->count();

         // Por ahora, establecemos estos valores en 0 hasta implementar las ventas
         $activeCustomers = 0;
         $totalRevenue = 0;

         return view('admin.customers.index', compact(
            'customers',
            'totalCustomers',
            'customerGrowth',
            'activeCustomers',
            'newCustomers',
            'totalRevenue'
         ));

      } catch (\Exception $e) {
         Log::error('Error en CustomerController@index: ' . $e->getMessage());
         
         return redirect()->back()
            ->with('message', 'Hubo un problema al cargar los clientes')
            ->with('icon', 'error');
      }
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      try {
         return view('admin.customers.create');
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
               'required',
               'string',
               'max:20',
               // 'regex:/^\d{3}-\d{6}-\d{3}-\d{1}$/',
               Rule::unique('customers', 'nit_number'),
            ],
            'phone' => [
               'required',
               'string',
               'regex:/^\(\d{3}\)\s\d{3}-\d{4}$/',
               Rule::unique('customers', 'phone'),
            ],
            'email' => [
               'required',
               'string',
               'email',
               'max:255',
               Rule::unique('customers', 'email'),
            ]
         ], [
            'name.regex' => 'El nombre solo debe contener letras y espacios',
            'nit_number.regex' => 'El formato del NIT debe ser: XXX-XXXXXX-XXX-X',
            'phone.regex' => 'El formato del teléfono debe ser: (XXX) XXX-XXXX',
            'nit_number.unique' => 'Este NIT ya está registrado',
            'phone.unique' => 'Este teléfono ya está registrado',
            'email.unique' => 'Este correo ya está registrado'
         ]);

         if ($validator->fails()) {
            return redirect()->back()
               ->withErrors($validator)
               ->withInput()
               ->with('message', 'Error de validación')
               ->with('icons', 'error');
         }

         // Formatear datos
         $customerData = [
            'name' => ucwords(strtolower($request->name)),
            'nit_number' => $request->nit_number,
            'phone' => $request->phone,
            'email' => strtolower($request->email),
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
   public function show(Customer $customer)
   {
      //
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit(Customer $customer)
   {
      //
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(UpdateCustomerRequest $request, Customer $customer)
   {
      //
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy(Customer $customer)
   {
      //
   }
}
