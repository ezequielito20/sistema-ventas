<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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

         // Estadísticas básicas que no dependen de la relación con purchases
         $totalCustomers = $customers->count();
         
         // Clientes nuevos este mes
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
            ->with('message', 'Hubo un problema al cargar los clientes: ' . $e->getMessage())
            ->with('icon', 'error');
      }
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      //
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(StoreCustomerRequest $request)
   {
      //
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
