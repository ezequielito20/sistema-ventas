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
            // Calcular deuda restante por venta con FIFO hasta la fecha de cierre (o ahora)
            $endDate = $this->closing_date ?: now();
            $remainingForSale = $this->calculateRemainingForSaleFIFO($sale, $endDate);
            $isPaid = $remainingForSale <= 0.00001;
            
            return [
               'invoice_number' => $sale->getFormattedInvoiceNumber(),
               'sale_date' => $sale->sale_date,
               'customer_name' => $sale->customer->name ?? 'Cliente sin nombre',
               'total_amount' => $sale->total_price,
               'payment_status' => $isPaid ? 'Pagado' : 'Pendiente',
               'remaining_amount' => max(0, round($remainingForSale, 2)),
               'products_count' => $sale->saleDetails->count()
            ];
         });
   }

   /**
    * Determina si una venta está pagada
    */
   private function isSalePaid($sale)
   {
      // Calcular usando FIFO de pagos por cliente hasta la fecha fin del arqueo
      $endDate = $this->closing_date ?: now();
      $remaining = $this->calculateRemainingForSaleFIFO($sale, $endDate);
      return $remaining <= 0.00001;
   }

   /**
    * Calcula la deuda restante de una venta aplicando pagos FIFO del cliente hasta $endDate
    */
   private function calculateRemainingForSaleFIFO(Sale $sale, $endDate)
   {
      // Traer todas las ventas del cliente (histórico) hasta la fecha fin
      $customerSales = Sale::where('company_id', $this->company_id)
         ->where('customer_id', $sale->customer_id)
         ->where('sale_date', '<=', $endDate)
         ->orderBy('sale_date', 'asc')
         ->orderBy('id', 'asc')
         ->get(['id', 'total_price', 'sale_date']);

      // Total de pagos del cliente hasta la fecha fin
      $totalPayments = DebtPayment::where('company_id', $this->company_id)
         ->where('customer_id', $sale->customer_id)
         ->where('created_at', '<=', $endDate)
         ->sum('payment_amount');

      $remainingPayments = (float) $totalPayments;
      foreach ($customerSales as $cs) {
         $saleOutstanding = (float) $cs->total_price;
         if ($remainingPayments > 0) {
            $applied = min($saleOutstanding, $remainingPayments);
            $saleOutstanding -= $applied;
            $remainingPayments -= $applied;
         }

         if ($cs->id === $sale->id) {
            return max(0.0, $saleOutstanding);
         }
      }

      // Si no se encontró la venta (caso atípico), retornar total
      return (float) $sale->total_price;
   }

   /**
    * Obtiene estadísticas de pagos para el modal
    */
   public function getPaymentsStats()
   {
      $current = $this->getCurrentPaymentsStats();
      $previous = $this->getPreviousPaymentsStats();
      return [
         'current' => $current,
         'previous' => $previous,
         'comparison' => $this->calculatePaymentsComparison($current, $previous)
      ];
   }

   /**
    * Estadísticas de pagos del arqueo actual (fuente: debt_payments)
    */
   private function getCurrentPaymentsStats()
   {
      $payments = DebtPayment::where('company_id', $this->company_id)
         ->where('created_at', '>=', $this->opening_date)
         ->when($this->closing_date, function($q) { return $q->where('created_at', '<=', $this->closing_date); })
         ->with('customer')
         ->orderBy('created_at', 'asc')
         ->get();

      $totalPayments = (float) $payments->sum('payment_amount');
      $paymentsCount = (int) $payments->count();
      $averagePerPayment = $paymentsCount > 0 ? $totalPayments / $paymentsCount : 0.0;

      // Deuda restante del arqueo = Ventas del periodo - Pagos del periodo
      $periodSales = Sale::where('company_id', $this->company_id)
         ->where('sale_date', '>=', $this->opening_date)
         ->when($this->closing_date, function($q) { return $q->where('sale_date', '<=', $this->closing_date); })
         ->sum('total_price');
      $remainingDebt = max(0, (float) $periodSales - $totalPayments);

      return [
         'total_payments' => $totalPayments,
         'payments_count' => $paymentsCount,
         'average_per_payment' => $averagePerPayment,
         'remaining_debt' => $remainingDebt,
         'payments_data' => $this->getPaymentsDetailedData($payments)
      ];
   }

   /**
    * Estadísticas de pagos del arqueo anterior
    */
   private function getPreviousPaymentsStats()
   {
      $previous = $this->getPreviousCashCount();
      if (!$previous) {
         return [
            'total_payments' => 0.0,
            'payments_count' => 0,
            'average_per_payment' => 0.0,
            'remaining_debt' => 0.0,
         ];
      }

      $payments = DebtPayment::where('company_id', $this->company_id)
         ->where('created_at', '>=', $previous->opening_date)
         ->when($previous->closing_date, function($q) use ($previous) { return $q->where('created_at', '<=', $previous->closing_date); })
         ->get();

      $totalPayments = (float) $payments->sum('payment_amount');
      $paymentsCount = (int) $payments->count();
      $averagePerPayment = $paymentsCount > 0 ? $totalPayments / $paymentsCount : 0.0;

      $periodSales = Sale::where('company_id', $this->company_id)
         ->where('sale_date', '>=', $previous->opening_date)
         ->when($previous->closing_date, function($q) use ($previous) { return $q->where('sale_date', '<=', $previous->closing_date); })
         ->sum('total_price');
      $remainingDebt = max(0, (float) $periodSales - $totalPayments);

      return [
         'total_payments' => $totalPayments,
         'payments_count' => $paymentsCount,
         'average_per_payment' => $averagePerPayment,
         'remaining_debt' => $remainingDebt,
      ];
   }

   /**
    * Calcula la comparación de pagos entre arqueos
    */
   private function calculatePaymentsComparison(array $current, array $previous)
   {
      $keys = ['total_payments', 'payments_count', 'average_per_payment', 'remaining_debt'];
      $out = [];
      foreach ($keys as $key) {
         $prev = $previous[$key] ?? 0;
         $cur = $current[$key] ?? 0;
         if ($prev > 0) {
            $pct = (($cur - $prev) / $prev) * 100;
            $out[$key] = ['percentage' => round($pct, 1), 'is_positive' => $key === 'remaining_debt' ? $pct <= 0 : $pct >= 0];
         } else {
            $out[$key] = ['percentage' => $cur > 0 ? 100 : 0, 'is_positive' => $key === 'remaining_debt' ? $cur <= 0 : $cur > 0];
         }
      }
      return $out;
   }

   /**
    * Detalle de pagos del periodo
    */
   private function getPaymentsDetailedData($preloadedPayments = null)
   {
      $payments = $preloadedPayments ?: DebtPayment::where('company_id', $this->company_id)
         ->where('created_at', '>=', $this->opening_date)
         ->when($this->closing_date, function($q) { return $q->where('created_at', '<=', $this->closing_date); })
         ->with('customer')
         ->orderBy('created_at', 'asc')
         ->get();

      return $payments->map(function($p) {
         return [
            'id' => (int) $p->id,
            'customer_id' => (int) $p->customer_id,
            'payment_date' => $p->created_at ? $p->created_at->toISOString() : null,
            'customer_name' => optional($p->customer)->name ?? 'Cliente',
            'payment_amount' => (float) $p->payment_amount,
            'remaining_debt' => (float) $p->remaining_debt,
            'notes' => (string) ($p->notes ?? '')
         ];
      });
   }

   /**
    * Obtiene estadísticas de compras para el modal
    */
   public function getPurchasesStats()
   {
      $current = $this->getCurrentPurchasesStats();
      $previous = $this->getPreviousPurchasesStats();
      return [
         'current' => $current,
         'previous' => $previous,
         'comparison' => $this->calculatePurchasesComparison($current, $previous)
      ];
   }

   /**
    * Compras del arqueo actual
    */
   private function getCurrentPurchasesStats()
   {
      // Compras del periodo
      $purchases = Purchase::where('company_id', $this->company_id)
         ->whereBetween('created_at', [$this->opening_date, $this->closing_date ?: now()])
         ->get(['id', 'total_price', 'purchase_date', 'created_at']);

      $totalPurchases = (float) $purchases->sum('total_price');
      $purchasesCount = (int) $purchases->count();
      $averagePerPurchase = $purchasesCount > 0 ? $totalPurchases / $purchasesCount : 0.0;

      // Calcular suma de valores de venta y compra de productos adquiridos en el periodo
      $details = DB::table('purchase_details as pd')
         ->join('purchases as p', 'pd.purchase_id', '=', 'p.id')
         ->join('products as pr', 'pd.product_id', '=', 'pr.id')
         ->where('p.company_id', $this->company_id)
         ->whereBetween('p.created_at', [$this->opening_date, $this->closing_date ?: now()])
         ->select(
            DB::raw('COALESCE(SUM(pd.quantity * pr.sale_price), 0) as total_sale_value'),
            DB::raw('COALESCE(SUM(pd.quantity * pr.purchase_price), 0) as total_purchase_cost')
         )
         ->first();

      $sumSale = (float) ($details->total_sale_value ?? 0);
      $sumPurchase = (float) ($details->total_purchase_cost ?? 0);
      $marginPercentage = $sumSale > 0 ? (($sumSale - $sumPurchase) / $sumSale) * 100.0 : 0.0;

      return [
         'total_purchases' => $totalPurchases,
         'purchases_count' => $purchasesCount,
         'average_per_purchase' => $averagePerPurchase,
         'margin_percentage' => round($marginPercentage, 1),
         'purchases_data' => $this->getPurchasesDetailedData()
      ];
   }

   /**
    * Compras del arqueo anterior
    */
   private function getPreviousPurchasesStats()
   {
      $previous = $this->getPreviousCashCount();
      if (!$previous) {
         return [
            'total_purchases' => 0.0,
            'purchases_count' => 0,
            'average_per_purchase' => 0.0,
            'margin_percentage' => 0.0,
         ];
      }

      $purchases = Purchase::where('company_id', $this->company_id)
         ->whereBetween('created_at', [$previous->opening_date, $previous->closing_date ?: now()])
         ->get(['id', 'total_price', 'purchase_date', 'created_at']);

      $totalPurchases = (float) $purchases->sum('total_price');
      $purchasesCount = (int) $purchases->count();
      $averagePerPurchase = $purchasesCount > 0 ? $totalPurchases / $purchasesCount : 0.0;

      $details = DB::table('purchase_details as pd')
         ->join('purchases as p', 'pd.purchase_id', '=', 'p.id')
         ->join('products as pr', 'pd.product_id', '=', 'pr.id')
         ->where('p.company_id', $this->company_id)
         ->whereBetween('p.created_at', [$previous->opening_date, $previous->closing_date ?: now()])
         ->select(
            DB::raw('COALESCE(SUM(pd.quantity * pr.sale_price), 0) as total_sale_value'),
            DB::raw('COALESCE(SUM(pd.quantity * pr.purchase_price), 0) as total_purchase_cost')
         )
         ->first();

      $sumSale = (float) ($details->total_sale_value ?? 0);
      $sumPurchase = (float) ($details->total_purchase_cost ?? 0);
      $marginPercentage = $sumSale > 0 ? (($sumSale - $sumPurchase) / $sumSale) * 100.0 : 0.0;

      return [
         'total_purchases' => $totalPurchases,
         'purchases_count' => $purchasesCount,
         'average_per_purchase' => $averagePerPurchase,
         'margin_percentage' => round($marginPercentage, 1),
      ];
   }

   /**
    * Comparación de compras
    */
   private function calculatePurchasesComparison(array $current, array $previous)
   {
      $keys = ['total_purchases', 'purchases_count', 'average_per_purchase', 'margin_percentage'];
      $out = [];
      foreach ($keys as $key) {
         $prev = $previous[$key] ?? 0;
         $cur = $current[$key] ?? 0;
         if ($prev > 0) {
            $pct = (($cur - $prev) / $prev) * 100;
            $out[$key] = ['percentage' => round($pct, 1), 'is_positive' => $cur >= $prev];
         } else {
            $out[$key] = ['percentage' => $cur > 0 ? 100 : 0, 'is_positive' => $cur > 0];
         }
      }
      return $out;
   }

   /**
    * Detalle de compras por compra
    */
    private function getPurchasesDetailedData()
    {
       // Datos por compra: fecha, productos únicos, productos totales, total compra
       $rows = DB::table('purchases as p')
          ->leftJoin('purchase_details as pd', 'pd.purchase_id', '=', 'p.id')
          ->where('p.company_id', $this->company_id)
          ->whereBetween('p.created_at', [$this->opening_date, $this->closing_date ?: now()])
          ->groupBy('p.id', 'p.purchase_date', 'p.created_at', 'p.total_price')
          ->select(
             'p.id',
             'p.purchase_date',
             'p.created_at',
             'p.total_price',
             DB::raw('COUNT(DISTINCT pd.product_id) as unique_products'),
             DB::raw('COALESCE(SUM(pd.quantity), 0) as total_products')
          )
          ->orderBy('p.created_at', 'desc')
          ->get();

       return $rows->map(function($r) {
          $date = $r->purchase_date ?: $r->created_at;
          return [
             'id' => (int) $r->id,
             'purchase_date' => $date ? (new \Carbon\Carbon($date))->toISOString() : null,
             'unique_products' => (int) $r->unique_products,
             'total_products' => (int) $r->total_products,
             'total_amount' => (float) ($r->total_price ?? 0),
          ];
       });
    }

    /**
     * Estadísticas de productos vendidos en el periodo
     */
    public function getProductsStats()
    {
       $current = $this->getCurrentProductsStats();
       $previous = $this->getPreviousProductsStats();
       return [
          'current' => $current,
          'previous' => $previous,
       ];
    }

    /**
     * Productos vendidos durante este arqueo
     */
    private function getCurrentProductsStats()
    {
       $start = $this->opening_date;
       $end = $this->closing_date ?: now();

       // Productos vendidos en el periodo: cantidades, ingresos y costos
       $rows = DB::table('sale_details as sd')
          ->join('sales as s', 'sd.sale_id', '=', 's.id')
          ->join('products as p', 'sd.product_id', '=', 'p.id')
          ->where('s.company_id', $this->company_id)
          ->whereBetween('s.sale_date', [$start, $end])
          ->groupBy('p.id', 'p.name', 'p.stock', 'p.purchase_price', 'p.sale_price')
          ->select(
             'p.id', 'p.name', 'p.stock', 'p.purchase_price', 'p.sale_price',
             DB::raw('COALESCE(SUM(sd.quantity),0) as quantity_sold'),
             DB::raw('COALESCE(SUM(sd.quantity * p.sale_price),0) as income'),
             DB::raw('COALESCE(SUM(sd.quantity * p.purchase_price),0) as cost')
          )
          ->orderByDesc(DB::raw('COALESCE(SUM(sd.quantity),0)'))
          ->get();

       $totalQty = (int) ($rows->sum('quantity_sold') ?? 0);
       $uniqueProducts = (int) $rows->count();

       // Valor de inventario (stock * purchase_price) para toda la empresa
       $inventoryRow = DB::table('products')
          ->where('company_id', $this->company_id)
          ->select(DB::raw('COALESCE(SUM(stock * purchase_price),0) as inventory_value_cost'))
          ->first();

       $productsData = $rows->map(function ($r) {
          $income = (float) $r->income;
          $cost = (float) $r->cost;
          $marginPct = $income > 0 ? (($income - $cost) / $income) * 100.0 : 0.0;
          return [
             'id' => (int) $r->id,
             'product_name' => (string) $r->name,
             'stock' => (int) $r->stock,
             'quantity_sold' => (int) $r->quantity_sold,
             'income' => $income,
             'cost' => $cost,
             'purchase_price' => (float) $r->purchase_price,
             'sale_price' => (float) $r->sale_price,
             'margin_percentage' => round($marginPct, 1),
          ];
       });

       return [
          'total_quantity_sold' => $totalQty,
          'unique_products_sold' => $uniqueProducts,
          'inventory_value_cost' => (float) ($inventoryRow->inventory_value_cost ?? 0),
          'products_data' => $productsData,
       ];
    }

    /**
     * Productos vendidos en el arqueo anterior (para comparación básica)
     */
    private function getPreviousProductsStats()
    {
       $previous = $this->getPreviousCashCount();
       if (!$previous) {
          return [
             'total_quantity_sold' => 0,
             'unique_products_sold' => 0,
             'inventory_value_cost' => 0.0,
             'products_data' => collect([]),
          ];
       }

       $start = $previous->opening_date;
       $end = $previous->closing_date ?: now();

       $rows = DB::table('sale_details as sd')
          ->join('sales as s', 'sd.sale_id', '=', 's.id')
          ->where('s.company_id', $this->company_id)
          ->whereBetween('s.sale_date', [$start, $end])
          ->select(DB::raw('COALESCE(SUM(sd.quantity),0) as quantity_sold'))
          ->get();

       $totalQty = (int) ($rows->sum('quantity_sold') ?? 0);
       $uniqueProducts = (int) $rows->count();

       $inventoryRow = DB::table('products')
          ->where('company_id', $this->company_id)
          ->select(DB::raw('COALESCE(SUM(stock * purchase_price),0) as inventory_value_cost'))
          ->first();

       return [
          'total_quantity_sold' => $totalQty,
          'unique_products_sold' => $uniqueProducts,
          'inventory_value_cost' => (float) ($inventoryRow->inventory_value_cost ?? 0),
       ];
    }
}
