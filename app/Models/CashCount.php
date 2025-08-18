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
      $sales = Sale::where('company_id', $this->company_id)
         ->where('sale_date', '>=', $this->opening_date)
         ->when($this->closing_date, function($query) {
            return $query->where('sale_date', '<=', $this->closing_date);
         })
         ->with('customer')
         ->get();
      
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
      
      $sales = Sale::where('company_id', $this->company_id)
         ->where('sale_date', '>=', $previousCashCount->opening_date)
         ->when($previousCashCount->closing_date, function($query) use ($previousCashCount) {
            return $query->where('sale_date', '<=', $previousCashCount->closing_date);
         })
         ->with('customer')
         ->get();
      
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
      return Sale::where('company_id', $this->company_id)
         ->where('sale_date', '>=', $this->opening_date)
         ->when($this->closing_date, function($query) {
            return $query->where('sale_date', '<=', $this->closing_date);
         })
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

   /**
    * Obtiene estadísticas de ventas para el modal
    */
   public function getSalesStats()
   {
      // Obtener datos del arqueo actual
      $currentStats = $this->getCurrentSalesStats();
      
      // Obtener datos del arqueo anterior para comparación
      $previousStats = $this->getPreviousSalesStats();
      
      return [
         'current' => $currentStats,
         'previous' => $previousStats,
         'comparison' => $this->calculateSalesComparison($currentStats, $previousStats)
      ];
   }

   /**
    * Obtiene estadísticas de ventas del arqueo actual
    */
   private function getCurrentSalesStats()
   {
      // Obtener ventas basándose en las fechas del arqueo
      $sales = Sale::where('company_id', $this->company_id)
         ->where('sale_date', '>=', $this->opening_date)
         ->when($this->closing_date, function($query) {
            return $query->where('sale_date', '<=', $this->closing_date);
         })
         ->with(['saleDetails.product', 'customer'])
         ->get();
      
      // Calcular totales de ventas
      $totalSales = $sales->sum('total_price');
      $salesCount = $sales->count();
      $averagePerSale = $salesCount > 0 ? $totalSales / $salesCount : 0;
      
      // Calcular totales de precios de compra y venta desde sale_details
      $totalPurchaseCost = 0;
      $totalSaleValue = 0;
      
      foreach ($sales as $sale) {
         foreach ($sale->saleDetails as $detail) {
            if ($detail->product) {
               $totalPurchaseCost += $detail->quantity * $detail->product->purchase_price;
               $totalSaleValue += $detail->quantity * $detail->product->sale_price;
            }
         }
      }
      
      // Si no hay sale_details, usar el total_price como aproximación
      if ($totalSaleValue == 0 && $totalSales > 0) {
         $totalSaleValue = $totalSales;
         // Asumir un margen de ganancia del 30% como aproximación
         $totalPurchaseCost = $totalSales * 0.7;
      }
      
      // Calcular balances
      $theoreticalBalance = $totalSaleValue - $totalPurchaseCost;
      
      // Calcular balance real (considerando pagos)
      $totalPayments = $this->getTotalPaymentsInCashCount();
      // Balance Real = Balance Teórico - Deuda Restante
      // Deuda Restante = Total Ventas - Total Pagos
      $remainingDebt = $totalSaleValue - $totalPayments;
      $realBalance = $theoreticalBalance - $remainingDebt;
      
      return [
         'total_sales' => $totalSales,
         'sales_count' => $salesCount,
         'average_per_sale' => $averagePerSale,
         'theoretical_balance' => $theoreticalBalance,
         'real_balance' => $realBalance,
         'total_purchase_cost' => $totalPurchaseCost,
         'total_sale_value' => $totalSaleValue,
         'total_payments' => $totalPayments,
         'sales_data' => $this->getSalesDetailedData()
      ];
   }

   /**
    * Obtiene estadísticas de ventas del arqueo anterior
    */
   private function getPreviousSalesStats()
   {
      $previousCashCount = $this->getPreviousCashCount();
      
      if (!$previousCashCount) {
         return [
            'total_sales' => 0,
            'sales_count' => 0,
            'average_per_sale' => 0,
            'theoretical_balance' => 0,
            'real_balance' => 0
         ];
      }
      
      $sales = Sale::where('company_id', $this->company_id)
         ->where('sale_date', '>=', $previousCashCount->opening_date)
         ->when($previousCashCount->closing_date, function($query) use ($previousCashCount) {
            return $query->where('sale_date', '<=', $previousCashCount->closing_date);
         })
         ->with(['saleDetails.product'])
         ->get();
      
      $totalSales = $sales->sum('total_price');
      $salesCount = $sales->count();
      $averagePerSale = $salesCount > 0 ? $totalSales / $salesCount : 0;
      
      // Calcular totales de precios de compra y venta
      $totalPurchaseCost = 0;
      $totalSaleValue = 0;
      
      foreach ($sales as $sale) {
         foreach ($sale->saleDetails as $detail) {
            if ($detail->product) {
               $totalPurchaseCost += $detail->quantity * $detail->product->purchase_price;
               $totalSaleValue += $detail->quantity * $detail->product->sale_price;
            }
         }
      }
      
      // Si no hay sale_details, usar el total_price como aproximación
      if ($totalSaleValue == 0 && $totalSales > 0) {
         $totalSaleValue = $totalSales;
         $totalPurchaseCost = $totalSales * 0.7;
      }
      
      $theoreticalBalance = $totalSaleValue - $totalPurchaseCost;
      $totalPayments = $previousCashCount->getTotalPaymentsInCashCount();
      $remainingDebt = $totalSaleValue - $totalPayments;
      $realBalance = $theoreticalBalance - $remainingDebt;
      
      return [
         'total_sales' => $totalSales,
         'sales_count' => $salesCount,
         'average_per_sale' => $averagePerSale,
         'theoretical_balance' => $theoreticalBalance,
         'real_balance' => $realBalance
      ];
   }

   /**
    * Calcula la comparación de ventas entre arqueos
    */
   private function calculateSalesComparison($current, $previous)
   {
      $comparison = [];
      
      foreach ($current as $key => $value) {
         if (in_array($key, ['sales_data', 'total_purchase_cost', 'total_sale_value', 'total_payments'])) continue;
         
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
    * Obtiene el total de pagos realizados en este arqueo
    */
   private function getTotalPaymentsInCashCount()
   {
      return $this->movements()
         ->where('type', 'income')
         ->sum('amount');
   }

   /**
    * Obtiene datos detallados de ventas para la tabla
    */
   private function getSalesDetailedData()
   {
      return Sale::where('company_id', $this->company_id)
         ->where('sale_date', '>=', $this->opening_date)
         ->when($this->closing_date, function($query) {
            return $query->where('sale_date', '<=', $this->closing_date);
         })
         ->with(['customer', 'saleDetails.product'])
         ->get()
         ->map(function ($sale) {
            // Calcular si la venta está pagada
            $isPaid = $this->isSalePaid($sale);
            
            return [
               'invoice_number' => $sale->getFormattedInvoiceNumber(),
               'sale_date' => $sale->sale_date,
               'customer_name' => $sale->customer->name ?? 'Cliente sin nombre',
               'total_amount' => $sale->total_price,
               'payment_status' => $isPaid ? 'Pagado' : 'Pendiente',
               'products_count' => $sale->saleDetails->count()
            ];
         });
   }

   /**
    * Determina si una venta está pagada
    */
   private function isSalePaid($sale)
   {
      // Buscar pagos relacionados con esta venta en los movimientos
      $paymentsForSale = $this->movements()
         ->where('type', 'income')
         ->where('description', 'like', '%' . $sale->customer->name . '%')
         ->sum('amount');
      
      return $paymentsForSale >= $sale->total_price;
   }
}
