<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
   /**
    * Display a listing of the resource.
    */
   public function index()
   {
      try {
         $companyId = Auth::user()->company_id;

         // Obtener ventas con sus relaciones
         $sales = Sale::with(['saleDetails.product', 'customer', 'company'])
            ->where('company_id', $companyId)
            ->get();

         // Calcular estadísticas
         $totalSales = $sales->sum(function($sale) {
            return $sale->saleDetails->count();
         });
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
            'averageTicket'
         ));
      } catch (\Exception $e) {
         Log::error('Error en index de ventas: ' . $e->getMessage());
         return redirect()->back()
            ->with('message', 'Error al cargar las ventas')
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
   public function store(StoreSaleRequest $request)
   {
      //
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
   public function edit(Sale $sale)
   {
      //
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(UpdateSaleRequest $request, Sale $sale)
   {
      //
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy(Sale $sale)
   {
      //
   }
}
