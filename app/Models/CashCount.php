<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CashCount extends Model
{
   use HasFactory;

   /**
    * Los atributos que son asignables masivamente.
    *
    * @var array<string>
    */
   protected $fillable = [
      'opening_date',
      'closing_date',
      'initial_amount',
      'final_amount',
      'observations',
      'company_id'
   ];

   /**
    * Los atributos que deben ser convertidos a tipos nativos.
    *
    * @var array<string, string>
    */
   protected $casts = [
      'opening_date' => 'datetime',
      'closing_date' => 'datetime',
      'initial_amount' => 'decimal:2',
      'final_amount' => 'decimal:2',
   ];

   /**
    * Obtiene la compañía asociada al arqueo de caja.
    */
   public function company()
   {
      return $this->belongsTo(Company::class);
   }

   /**
    * Obtiene los movimientos de caja asociados al arqueo.
    */
   public function movements()
   {
      return $this->hasMany(CashMovement::class);
   }

   /**
    * Obtiene las ventas asociadas al arqueo.
    */
   public function sales()
   {
      return $this->hasMany(Sale::class);
   }

   /**
    * Obtiene el arqueo anterior
    */
   public function getPreviousCashCount()
   {
      return static::where('company_id', $this->company_id)
         ->where('id', '<', $this->id)
         ->orderBy('id', 'desc')
         ->first();
   }

   /**
    * Obtiene estadísticas de clientes para el modal
    */
   public function getCustomerStats()
   {
      // Obtener datos del arqueo actual
      $currentStats = $this->getCurrentCashCountStats();
      
      // Obtener datos del arqueo anterior para comparación
      $previousStats = $this->getPreviousCashCountStats();
      
      return [
         'current' => $currentStats,
         'previous' => $previousStats,
         'comparison' => $this->calculateComparison($currentStats, $previousStats)
      ];
   }

   /**
    * Obtiene estadísticas del arqueo actual
    */
   private function getCurrentCashCountStats()
   {
      $sales = $this->sales()->with('customer')->get();
      
      $uniqueCustomers = $sales->pluck('customer_id')->unique()->count();
      $totalSales = $sales->sum('total_price');
      $totalDebt = $sales->sum('total_price'); // En este sistema, todas las ventas son deudas
      $averagePerCustomer = $uniqueCustomers > 0 ? $totalSales / $uniqueCustomers : 0;
      
      return [
         'unique_customers' => $uniqueCustomers,
         'total_sales' => $totalSales,
         'total_debt' => $totalDebt,
         'average_per_customer' => $averagePerCustomer,
         'customers_data' => $this->getCustomersDetailedData()
      ];
   }

   /**
    * Obtiene estadísticas del arqueo anterior
    */
   private function getPreviousCashCountStats()
   {
      $previousCashCount = $this->getPreviousCashCount();
      
      if (!$previousCashCount) {
         return [
            'unique_customers' => 0,
            'total_sales' => 0,
            'total_debt' => 0,
            'average_per_customer' => 0
         ];
      }
      
      $sales = $previousCashCount->sales;
      
      $uniqueCustomers = $sales->pluck('customer_id')->unique()->count();
      $totalSales = $sales->sum('total_price');
      $totalDebt = $sales->sum('total_price');
      $averagePerCustomer = $uniqueCustomers > 0 ? $totalSales / $uniqueCustomers : 0;
      
      return [
         'unique_customers' => $uniqueCustomers,
         'total_sales' => $totalSales,
         'total_debt' => $totalDebt,
         'average_per_customer' => $averagePerCustomer
      ];
   }

   /**
    * Calcula la comparación entre arqueos
    */
   private function calculateComparison($current, $previous)
   {
      $comparison = [];
      
      foreach ($current as $key => $value) {
         if ($key === 'customers_data') continue; // Saltar datos detallados
         
         if ($previous[$key] > 0) {
            $percentage = (($value - $previous[$key]) / $previous[$key]) * 100;
            $comparison[$key] = [
               'percentage' => round($percentage, 1),
               'is_positive' => $percentage >= 0
            ];
         } else {
            $comparison[$key] = [
               'percentage' => $value > 0 ? 100 : 0,
               'is_positive' => $value > 0
            ];
         }
      }
      
      return $comparison;
   }

   /**
    * Obtiene datos detallados de clientes para la tabla
    */
   private function getCustomersDetailedData()
   {
      return $this->sales()
         ->with('customer')
         ->select('customer_id', DB::raw('SUM(total_price) as total_purchases'), DB::raw('SUM(total_price) as total_debt'))
         ->groupBy('customer_id')
         ->get()
         ->map(function ($sale) {
            return [
               'name' => $sale->customer->name ?? 'Cliente sin nombre',
               'phone' => $sale->customer->phone ?? 'No registrado',
               'total_purchases' => $sale->total_purchases,
               'total_debt' => $sale->total_debt
            ];
         });
   }
}
