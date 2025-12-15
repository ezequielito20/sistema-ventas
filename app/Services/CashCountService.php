<?php

namespace App\Services;

use App\Models\CashCount;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\DebtPayment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashCountService
{
    /**
     * Obtiene todas las estadísticas para el dashboard de arqueos (Controller::index)
     */
    public function getDashboardStats($companyId)
    {
        $today = Carbon::today();

        // Consulta consolidada para obtener todas las estadísticas necesarias
        // Refactorizado de raw query a query builder donde sea posible o manteniendo el raw optimizado
        $allStats = DB::select("
             WITH current_cash_count AS (
                SELECT id, initial_amount, opening_date, closing_date
                FROM cash_counts 
                WHERE company_id = ? AND closing_date IS NULL
                LIMIT 1
             ),
             today_movements AS (
                SELECT 
                   COALESCE(SUM(CASE WHEN cm.type = 'income' THEN cm.amount ELSE 0 END), 0) as today_income,
                   COALESCE(SUM(CASE WHEN cm.type = 'expense' THEN cm.amount ELSE 0 END), 0) as today_expenses,
                   COUNT(*) as total_movements
                FROM cash_movements cm
                INNER JOIN cash_counts cc ON cm.cash_count_id = cc.id
                WHERE cc.company_id = ? 
                AND cc.closing_date IS NULL
                AND DATE(cm.created_at) = ?
             ),
             current_balance_stats AS (
                SELECT 
                   COALESCE(SUM(CASE WHEN cm.type = 'income' THEN cm.amount ELSE 0 END), 0) as total_income,
                   COALESCE(SUM(CASE WHEN cm.type = 'expense' THEN cm.amount ELSE 0 END), 0) as total_expenses
                FROM cash_movements cm
                INNER JOIN current_cash_count ccc ON cm.cash_count_id = ccc.id
             )
             SELECT 
                COALESCE(tm.today_income, 0) as today_income,
                COALESCE(tm.today_expenses, 0) as today_expenses,
                COALESCE(tm.total_movements, 0) as total_movements,
                COALESCE(ccc.initial_amount, 0) as initial_amount,
                COALESCE(cbs.total_income, 0) as total_income,
                COALESCE(cbs.total_expenses, 0) as total_expenses,
                COALESCE((ccc.initial_amount + cbs.total_income - cbs.total_expenses), 0) as current_balance
             FROM current_cash_count ccc
             LEFT JOIN today_movements tm ON true
             LEFT JOIN current_balance_stats cbs ON true
          ", [$companyId, $companyId, $today->format('Y-m-d')]);

        return $allStats[0] ?? (object)[
            'today_income' => 0,
            'today_expenses' => 0,
            'total_movements' => 0,
            'initial_amount' => 0,
            'total_income' => 0,
            'total_expenses' => 0,
            'current_balance' => 0
        ];
    }

    /**
     * Obtiene datos para el gráfico de ingresos/egresos
     */
    public function getChartData($companyId)
    {
        $lastDays = collect(range(6, 0))->map(function ($days) {
            return Carbon::today()->subDays($days);
        });

        $dateRange = [
            $lastDays->first()->format('Y-m-d'),
            $lastDays->last()->format('Y-m-d')
        ];

        $chartDataRaw = DB::select("
             SELECT 
                date_series.date,
                COALESCE(income_data.total_amount, 0) as income,
                COALESCE(expense_data.total_amount, 0) as expenses
             FROM (
                SELECT generate_series(?, ?, '1 day'::interval)::date as date
             ) date_series
             LEFT JOIN (
                SELECT 
                   DATE(cm.created_at) as date,
                   SUM(cm.amount) as total_amount
                FROM cash_movements cm
                INNER JOIN cash_counts cc ON cm.cash_count_id = cc.id
                WHERE cc.company_id = ? 
                AND cm.type = 'income'
                AND DATE(cm.created_at) BETWEEN ? AND ?
                GROUP BY DATE(cm.created_at)
             ) income_data ON date_series.date = income_data.date
             LEFT JOIN (
                SELECT 
                   DATE(cm.created_at) as date,
                   SUM(cm.amount) as total_amount
                FROM cash_movements cm
                INNER JOIN cash_counts cc ON cm.cash_count_id = cc.id
                WHERE cc.company_id = ? 
                AND cm.type = 'expense'
                AND DATE(cm.created_at) BETWEEN ? AND ?
                GROUP BY DATE(cm.created_at)
             ) expense_data ON date_series.date = expense_data.date
             ORDER BY date_series.date
          ", [
            $dateRange[0],
            $dateRange[1],
            $companyId,
            $dateRange[0],
            $dateRange[1],
            $companyId,
            $dateRange[0],
            $dateRange[1]
        ]);

        return [
            'labels' => $lastDays->map(fn($date) => $date->format('d/m')),
            'income' => collect($chartDataRaw)->pluck('income')->toArray(),
            'expenses' => collect($chartDataRaw)->pluck('expenses')->toArray()
        ];
    }

    /**
     * Obtiene estadísticas de productos vendidos/comprados para el dashboard
     */
    public function getProductDashboardStats($companyId, $openingDate, $closingDate)
    {
        $productStats = DB::select("
             SELECT 
                COALESCE(sales_stats.total_products_sold, 0) as total_products_sold,
                COALESCE(purchases_stats.total_products_purchased, 0) as total_products_purchased
             FROM (
                SELECT SUM(sd.quantity) as total_products_sold
                FROM sale_details sd
                INNER JOIN sales s ON sd.sale_id = s.id
                WHERE s.company_id = ?
                AND s.created_at BETWEEN ? AND ?
             ) sales_stats
             CROSS JOIN (
                SELECT SUM(pd.quantity) as total_products_purchased
                FROM purchase_details pd
                INNER JOIN purchases p ON pd.purchase_id = p.id
                WHERE p.company_id = ?
                AND p.created_at BETWEEN ? AND ?
             ) purchases_stats
          ", [
            $companyId,
            $openingDate,
            $closingDate,
            $companyId,
            $openingDate,
            $closingDate
        ]);

        return (object)[
            'total_products_sold' => $productStats[0]->total_products_sold ?? 0,
            'total_products_purchased' => $productStats[0]->total_products_purchased ?? 0
        ];
    }

    // =========================================================================
    // Métodos movidos desde el Modelo CashCount (Modal Stats Logic)
    // =========================================================================

    public function getCustomerStats(CashCount $cashCount)
    {
        $currentStats = $this->getCurrentCashCountStats($cashCount);
        $previousStats = $this->getPreviousCashCountStats($cashCount);

        return [
            'current' => $currentStats,
            'previous' => $previousStats,
            'comparison' => $this->calculateComparison($currentStats, $previousStats)
        ];
    }

    private function getCurrentCashCountStats(CashCount $cashCount)
    {
        $sales = Sale::where('company_id', $cashCount->company_id)
            ->where('sale_date', '>=', $cashCount->opening_date)
            ->when($cashCount->closing_date, function ($query) use ($cashCount) {
                return $query->where('sale_date', '<=', $cashCount->closing_date);
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
            'average_per_customer' => $averagePerCustomer,
            'customers_data' => $this->getCustomersDetailedData($cashCount)
        ];
    }

    private function getPreviousCashCountStats(CashCount $cashCount)
    {
        $previousCashCount = $cashCount->getPreviousCashCount();

        if (!$previousCashCount) {
            return [
                'unique_customers' => 0,
                'total_sales' => 0,
                'total_debt' => 0,
                'average_per_customer' => 0
            ];
        }

        $sales = Sale::where('company_id', $cashCount->company_id)
            ->where('sale_date', '>=', $previousCashCount->opening_date)
            ->when($previousCashCount->closing_date, function ($query) use ($previousCashCount) {
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

    private function calculateComparison($current, $previous)
    {
        $comparison = [];

        foreach ($current as $key => $value) {
            if ($key === 'customers_data') continue;

            if (($previous[$key] ?? 0) > 0) {
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

    private function getCustomersDetailedData(CashCount $cashCount)
    {
        return Sale::where('company_id', $cashCount->company_id)
            ->where('sale_date', '>=', $cashCount->opening_date)
            ->when($cashCount->closing_date, function ($query) use ($cashCount) {
                return $query->where('sale_date', '<=', $cashCount->closing_date);
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

    public function getSalesStats(CashCount $cashCount)
    {
        $currentStats = $this->getCurrentSalesStats($cashCount);
        $previousStats = $this->getPreviousSalesStats($cashCount);

        return [
            'current' => $currentStats,
            'previous' => $previousStats,
            'comparison' => $this->calculateSalesComparison($currentStats, $previousStats)
        ];
    }

    private function getCurrentSalesStats(CashCount $cashCount)
    {
        $sales = Sale::where('company_id', $cashCount->company_id)
            ->where('sale_date', '>=', $cashCount->opening_date)
            ->when($cashCount->closing_date, function ($query) use ($cashCount) {
                return $query->where('sale_date', '<=', $cashCount->closing_date);
            })
            ->with(['saleDetails.product', 'customer'])
            ->get();

        $totalSales = $sales->sum('total_price');
        $salesCount = $sales->count();
        $averagePerSale = $salesCount > 0 ? $totalSales / $salesCount : 0;

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

        if ($totalSaleValue == 0 && $totalSales > 0) {
            $totalSaleValue = $totalSales;
            $totalPurchaseCost = $totalSales * 0.7;
        }

        $theoreticalBalance = $totalSaleValue - $totalPurchaseCost;
        $totalPayments = $this->getTotalPaymentsInCashCount($cashCount);
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
            'sales_data' => $this->getSalesDetailedData($cashCount)
        ];
    }

    private function getPreviousSalesStats(CashCount $cashCount)
    {
        $previousCashCount = $cashCount->getPreviousCashCount();

        if (!$previousCashCount) {
            return [
                'total_sales' => 0,
                'sales_count' => 0,
                'average_per_sale' => 0,
                'theoretical_balance' => 0,
                'real_balance' => 0
            ];
        }

        $sales = Sale::where('company_id', $cashCount->company_id)
            ->where('sale_date', '>=', $previousCashCount->opening_date)
            ->when($previousCashCount->closing_date, function ($query) use ($previousCashCount) {
                return $query->where('sale_date', '<=', $previousCashCount->closing_date);
            })
            ->with(['saleDetails.product'])
            ->get();

        $totalSales = $sales->sum('total_price');
        $salesCount = $sales->count();
        $averagePerSale = $salesCount > 0 ? $totalSales / $salesCount : 0;

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

        if ($totalSaleValue == 0 && $totalSales > 0) {
            $totalSaleValue = $totalSales;
            $totalPurchaseCost = $totalSales * 0.7;
        }

        $theoreticalBalance = $totalSaleValue - $totalPurchaseCost;
        // WARNING: Using logic from this service for previous count
        $totalPayments = $this->getTotalPaymentsInCashCount($previousCashCount);
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

    private function calculateSalesComparison($current, $previous)
    {
        $comparison = [];

        foreach ($current as $key => $value) {
            if (in_array($key, ['sales_data', 'total_purchase_cost', 'total_sale_value', 'total_payments'])) continue;

            if (($previous[$key] ?? 0) > 0) {
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

    private function getTotalPaymentsInCashCount(CashCount $cashCount)
    {
        return $cashCount->movements()
            ->where('type', 'income')
            ->sum('amount');
    }

    private function getSalesDetailedData(CashCount $cashCount)
    {
        return Sale::where('company_id', $cashCount->company_id)
            ->where('sale_date', '>=', $cashCount->opening_date)
            ->when($cashCount->closing_date, function ($query) use ($cashCount) {
                return $query->where('sale_date', '<=', $cashCount->closing_date);
            })
            ->with(['customer', 'saleDetails.product'])
            ->get()
            ->map(function ($sale) use ($cashCount) {
                $endDate = $cashCount->closing_date ?: now();
                $remainingForSale = $this->calculateRemainingForSaleFIFO($sale, $endDate, $cashCount->company_id);
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

    private function calculateRemainingForSaleFIFO(Sale $sale, $endDate, $companyId)
    {
        $customerSales = Sale::where('company_id', $companyId)
            ->where('customer_id', $sale->customer_id)
            ->where('sale_date', '<=', $endDate)
            ->orderBy('sale_date', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'total_price', 'sale_date']);

        $totalPayments = DebtPayment::where('company_id', $companyId)
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

        return (float) $sale->total_price;
    }

    public function getPaymentsStats(CashCount $cashCount)
    {
        $current = $this->getCurrentPaymentsStats($cashCount);
        $previous = $this->getPreviousPaymentsStats($cashCount);
        return [
            'current' => $current,
            'previous' => $previous,
            'comparison' => $this->calculatePaymentsComparison($current, $previous)
        ];
    }

    private function getCurrentPaymentsStats(CashCount $cashCount)
    {
        $payments = DebtPayment::where('company_id', $cashCount->company_id)
            ->where('created_at', '>=', $cashCount->opening_date)
            ->when($cashCount->closing_date, function ($q) use ($cashCount) {
                return $q->where('created_at', '<=', $cashCount->closing_date);
            })
            ->with('customer')
            ->orderBy('created_at', 'asc')
            ->get();

        $totalPayments = (float) $payments->sum('payment_amount');
        $paymentsCount = (int) $payments->count();
        $averagePerPayment = $paymentsCount > 0 ? $totalPayments / $paymentsCount : 0.0;

        $periodSales = Sale::where('company_id', $cashCount->company_id)
            ->where('sale_date', '>=', $cashCount->opening_date)
            ->when($cashCount->closing_date, function ($q) use ($cashCount) {
                return $q->where('sale_date', '<=', $cashCount->closing_date);
            })
            ->sum('total_price');

        $remainingDebt = max(0, (float) $periodSales - $totalPayments);

        return [
            'total_payments' => $totalPayments,
            'payments_count' => $paymentsCount,
            'average_per_payment' => $averagePerPayment,
            'remaining_debt' => $remainingDebt,
            'payments_data' => $this->getPaymentsDetailedData($cashCount, $payments)
        ];
    }

    private function getPreviousPaymentsStats(CashCount $cashCount)
    {
        $previous = $cashCount->getPreviousCashCount();
        if (!$previous) {
            return [
                'total_payments' => 0.0,
                'payments_count' => 0,
                'average_per_payment' => 0.0,
                'remaining_debt' => 0.0,
            ];
        }

        $payments = DebtPayment::where('company_id', $cashCount->company_id)
            ->where('created_at', '>=', $previous->opening_date)
            ->when($previous->closing_date, function ($q) use ($previous) {
                return $q->where('created_at', '<=', $previous->closing_date);
            })
            ->get();

        $totalPayments = (float) $payments->sum('payment_amount');
        $paymentsCount = (int) $payments->count();
        $averagePerPayment = $paymentsCount > 0 ? $totalPayments / $paymentsCount : 0.0;

        $periodSales = Sale::where('company_id', $cashCount->company_id)
            ->where('sale_date', '>=', $previous->opening_date)
            ->when($previous->closing_date, function ($q) use ($previous) {
                return $q->where('sale_date', '<=', $previous->closing_date);
            })
            ->sum('total_price');

        $remainingDebt = max(0, (float) $periodSales - $totalPayments);

        return [
            'total_payments' => $totalPayments,
            'payments_count' => $paymentsCount,
            'average_per_payment' => $averagePerPayment,
            'remaining_debt' => $remainingDebt,
        ];
    }

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

    private function getPaymentsDetailedData(CashCount $cashCount, $preloadedPayments = null)
    {
        $payments = $preloadedPayments ?: DebtPayment::where('company_id', $cashCount->company_id)
            ->where('created_at', '>=', $cashCount->opening_date)
            ->when($cashCount->closing_date, function ($q) use ($cashCount) {
                return $q->where('created_at', '<=', $cashCount->closing_date);
            })
            ->with('customer')
            ->orderBy('created_at', 'asc')
            ->get();

        return $payments->map(function ($p) {
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

    public function getPurchasesStats(CashCount $cashCount)
    {
        $current = $this->getCurrentPurchasesStats($cashCount);
        $previous = $this->getPreviousPurchasesStats($cashCount);
        return [
            'current' => $current,
            'previous' => $previous,
            'comparison' => $this->calculatePurchasesComparison($current, $previous)
        ];
    }

    private function getCurrentPurchasesStats(CashCount $cashCount)
    {
        $purchases = Purchase::where('company_id', $cashCount->company_id)
            ->whereBetween('created_at', [$cashCount->opening_date, $cashCount->closing_date ?: now()])
            ->get(['id', 'total_price', 'purchase_date', 'created_at']);

        $totalPurchases = (float) $purchases->sum('total_price');
        $purchasesCount = (int) $purchases->count();
        $averagePerPurchase = $purchasesCount > 0 ? $totalPurchases / $purchasesCount : 0.0;

        $details = DB::table('purchase_details as pd')
            ->join('purchases as p', 'pd.purchase_id', '=', 'p.id')
            ->join('products as pr', 'pd.product_id', '=', 'pr.id')
            ->where('p.company_id', $cashCount->company_id)
            ->whereBetween('p.created_at', [$cashCount->opening_date, $cashCount->closing_date ?: now()])
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
            'purchases_data' => $this->getPurchasesDetailedData($cashCount)
        ];
    }

    private function getPreviousPurchasesStats(CashCount $cashCount)
    {
        $previous = $cashCount->getPreviousCashCount();
        if (!$previous) {
            return [
                'total_purchases' => 0.0,
                'purchases_count' => 0,
                'average_per_purchase' => 0.0,
                'margin_percentage' => 0.0,
            ];
        }

        $purchases = Purchase::where('company_id', $cashCount->company_id)
            ->whereBetween('created_at', [$previous->opening_date, $previous->closing_date ?: now()])
            ->get(['id', 'total_price', 'purchase_date', 'created_at']);

        $totalPurchases = (float) $purchases->sum('total_price');
        $purchasesCount = (int) $purchases->count();
        $averagePerPurchase = $purchasesCount > 0 ? $totalPurchases / $purchasesCount : 0.0;

        $details = DB::table('purchase_details as pd')
            ->join('purchases as p', 'pd.purchase_id', '=', 'p.id')
            ->join('products as pr', 'pd.product_id', '=', 'pr.id')
            ->where('p.company_id', $cashCount->company_id)
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

    private function getPurchasesDetailedData(CashCount $cashCount)
    {
        $rows = DB::table('purchases as p')
            ->leftJoin('purchase_details as pd', 'pd.purchase_id', '=', 'p.id')
            ->where('p.company_id', $cashCount->company_id)
            ->whereBetween('p.created_at', [$cashCount->opening_date, $cashCount->closing_date ?: now()])
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

        return $rows->map(function ($r) {
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

    public function getProductsStats(CashCount $cashCount)
    {
        $current = $this->getCurrentProductsStats($cashCount);
        $previous = $this->getPreviousProductsStats($cashCount);
        return [
            'current' => $current,
            'previous' => $previous,
        ];
    }

    private function getCurrentProductsStats(CashCount $cashCount)
    {
        $start = $cashCount->opening_date;
        $end = $cashCount->closing_date ?: now();

        $rows = DB::table('sale_details as sd')
            ->join('sales as s', 'sd.sale_id', '=', 's.id')
            ->join('products as p', 'sd.product_id', '=', 'p.id')
            ->where('s.company_id', $cashCount->company_id)
            ->whereBetween('s.sale_date', [$start, $end])
            ->groupBy('p.id', 'p.name', 'p.stock', 'p.purchase_price', 'p.sale_price')
            ->select(
                'p.id',
                'p.name',
                'p.stock',
                'p.purchase_price',
                'p.sale_price',
                DB::raw('COALESCE(SUM(sd.quantity),0) as quantity_sold'),
                DB::raw('COALESCE(SUM(sd.quantity * p.sale_price),0) as income'),
                DB::raw('COALESCE(SUM(sd.quantity * p.purchase_price),0) as cost')
            )
            ->orderByDesc(DB::raw('COALESCE(SUM(sd.quantity),0)'))
            ->get();

        $totalQty = (int) ($rows->sum('quantity_sold') ?? 0);
        $uniqueProducts = (int) $rows->count();

        $inventoryRow = DB::table('products')
            ->where('company_id', $cashCount->company_id)
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

    private function getPreviousProductsStats(CashCount $cashCount)
    {
        $previous = $cashCount->getPreviousCashCount();
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
            ->where('s.company_id', $cashCount->company_id)
            ->whereBetween('s.sale_date', [$start, $end])
            ->select(DB::raw('COALESCE(SUM(sd.quantity),0) as quantity_sold'))
            ->get();

        $totalQty = (int) ($rows->sum('quantity_sold') ?? 0);
        $uniqueProducts = (int) $rows->count();

        $inventoryRow = DB::table('products')
            ->where('company_id', $cashCount->company_id)
            ->select(DB::raw('COALESCE(SUM(stock * purchase_price),0) as inventory_value_cost'))
            ->first();

        return [
            'total_quantity_sold' => $totalQty,
            'unique_products_sold' => $uniqueProducts,
            'inventory_value_cost' => (float) ($inventoryRow->inventory_value_cost ?? 0),
        ];
    }

    public function getOrdersStats(CashCount $cashCount)
    {
        $current = $this->getCurrentOrdersStats($cashCount);
        $previous = $this->getPreviousOrdersStats($cashCount);
        return [
            'current' => $current,
            'previous' => $previous,
            'comparison' => $this->calculateOrdersComparison($current, $previous)
        ];
    }

    private function getCurrentOrdersStats(CashCount $cashCount)
    {
        $start = $cashCount->opening_date;
        $end = $cashCount->closing_date ?: now();

        $orders = DB::table('orders as o')
            ->join('products as p', 'o.product_id', '=', 'p.id')
            ->where('p.company_id', $cashCount->company_id)
            ->whereBetween('o.created_at', [$start, $end])
            ->select('o.*')
            ->orderBy('o.created_at', 'desc')
            ->get();

        $totalOrders = (int) $orders->count();
        $totalValue = (float) $orders->sum('total_price');
        $pending = (int) $orders->where('status', 'pending')->count();
        $completed = (int) $orders->where('status', 'processed')->count();

        return [
            'total_orders' => $totalOrders,
            'pending' => $pending,
            'completed' => $completed,
            'total_value' => $totalValue,
            'orders_data' => $this->getOrdersDetailedData($cashCount, $orders)
        ];
    }

    private function getPreviousOrdersStats(CashCount $cashCount)
    {
        $previous = $cashCount->getPreviousCashCount();
        if (!$previous) {
            return [
                'total_orders' => 0,
                'pending' => 0,
                'completed' => 0,
                'total_value' => 0.0,
            ];
        }

        $orders = DB::table('orders as o')
            ->join('products as p', 'o.product_id', '=', 'p.id')
            ->where('p.company_id', $cashCount->company_id)
            ->whereBetween('o.created_at', [$previous->opening_date, $previous->closing_date ?: now()])
            ->select('o.*')
            ->get();

        $totalOrders = (int) $orders->count();
        $totalValue = (float) $orders->sum('total_price');
        $pending = (int) $orders->where('status', 'pending')->count();
        $completed = (int) $orders->where('status', 'processed')->count();

        return [
            'total_orders' => $totalOrders,
            'pending' => $pending,
            'completed' => $completed,
            'total_value' => $totalValue,
        ];
    }

    private function calculateOrdersComparison(array $current, array $previous)
    {
        $keys = ['total_orders', 'pending', 'completed', 'total_value'];
        $out = [];
        foreach ($keys as $k) {
            $prev = $previous[$k] ?? 0;
            $cur = $current[$k] ?? 0;
            if ($prev > 0) {
                $pct = (($cur - $prev) / $prev) * 100;
                $out[$k] = ['percentage' => round($pct, 1), 'is_positive' => ($k === 'pending') ? $pct <= 0 : $pct >= 0];
            } else {
                $out[$k] = ['percentage' => $cur > 0 ? 100 : 0, 'is_positive' => ($k === 'pending') ? $cur <= 0 : $cur > 0];
            }
        }
        return $out;
    }

    private function getOrdersDetailedData(CashCount $cashCount, $preloaded = null)
    {
        $rows = $preloaded ?: DB::table('orders as o')
            ->join('products as p', 'o.product_id', '=', 'p.id')
            ->where('p.company_id', $cashCount->company_id)
            ->whereBetween('o.created_at', [$cashCount->opening_date, $cashCount->closing_date ?: now()])
            ->select('o.*')
            ->orderBy('o.created_at', 'desc')
            ->get();

        return $rows->map(function ($o) {
            return [
                'id' => (int) $o->id,
                'order_date' => $o->created_at ? (new \Carbon\Carbon($o->created_at))->toISOString() : null,
                'customer_name' => (string) $o->customer_name,
                'customer_phone' => (string) $o->customer_phone,
                'unique_products' => 1,
                'total_products' => (int) $o->quantity,
                'total_amount' => (float) $o->total_price,
                'status' => (string) $o->status,
            ];
        });
    }


    // =========================================================================
    // Métodos movidos desde CashCountController (History Logic)
    // =========================================================================

    public function calculateHistoryStats(CashCount $cashCount)
    {
        $openingDate = $cashCount->opening_date;
        $closingDate = $cashCount->closing_date ?? now();

        // Ventas del arqueo
        $sales = Sale::where('company_id', $cashCount->company_id)
            ->whereBetween('created_at', [$openingDate, $closingDate])
            ->with(['saleDetails', 'customer']);

        $totalSales = $sales->count();
        $totalSalesAmount = $sales->sum('total_price');
        $productsSold = $sales->get()->sum(function ($sale) {
            return $sale->saleDetails->sum('quantity');
        });

        // Compras del arqueo
        $purchases = Purchase::where('company_id', $cashCount->company_id)
            ->whereBetween('created_at', [$openingDate, $closingDate])
            ->with(['details']);

        $totalPurchases = $purchases->count();
        $totalPurchasesAmount = $purchases->sum('total_price');
        $productsPurchased = $purchases->get()->sum(function ($purchase) {
            return $purchase->details->sum('quantity');
        });

        // Movimientos de caja
        $totalIncome = $cashCount->movements()->where('type', 'income')->sum('amount');
        $totalExpense = $cashCount->movements()->where('type', 'expense')->sum('amount');

        // Deudas generadas en este arqueo (suma de todas las ventas)
        $debtsGenerated = Sale::where('company_id', $cashCount->company_id)
            ->whereBetween('created_at', [$openingDate, $closingDate])
            ->sum('total_price');

        // Pagos recibidos en este arqueo
        $paymentsReceived = $totalIncome - $cashCount->initial_amount;

        // Ganancias reales (pagos - inversión)
        $realProfit = $paymentsReceived - $totalPurchasesAmount;

        return [
            'opening_date' => $openingDate,
            'closing_date' => $closingDate,
            'initial_amount' => $cashCount->initial_amount,
            'final_amount' => $cashCount->final_amount,
            'total_sales' => $totalSales,
            'total_sales_amount' => $totalSalesAmount,
            'products_sold' => $productsSold,
            'total_purchases' => $totalPurchases,
            'total_purchases_amount' => $totalPurchasesAmount,
            'products_purchased' => $productsPurchased,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'debts_generated' => $debtsGenerated,
            'payments_received' => $paymentsReceived,
            'real_profit' => $realProfit,
            'net_difference' => $cashCount->final_amount - $cashCount->initial_amount
        ];
    }

    public function getPendingDebts(CashCount $cashCount)
    {
        $openingDate = $cashCount->opening_date;
        $closingDate = $cashCount->closing_date ?? now();

        // Obtener clientes con deudas pendientes
        return \App\Models\Customer::where('company_id', $cashCount->company_id)
            ->where('total_debt', '>', 0)
            ->with(['sales' => function ($query) use ($openingDate, $closingDate) {
                $query->whereBetween('created_at', [$openingDate, $closingDate]);
            }])
            ->get()
            ->map(function ($customer) {
                $salesInPeriod = $customer->sales;
                return [
                    'id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'sale_date' => $salesInPeriod->first() ? $salesInPeriod->first()->created_at : now(),
                    'total_amount' => $customer->total_debt,
                    'products_count' => $salesInPeriod->sum(function ($sale) {
                        return $sale->saleDetails->count();
                    }),
                    'total_products' => $salesInPeriod->sum(function ($sale) {
                        return $sale->saleDetails->sum('quantity');
                    })
                ];
            });
    }

    public function getPreviousDebts(CashCount $cashCount)
    {
        $openingDate = $cashCount->opening_date;

        // Obtener clientes con deudas de arqueos anteriores
        return \App\Models\Customer::where('company_id', $cashCount->company_id)
            ->where('total_debt', '>', 0)
            ->with(['sales' => function ($query) use ($openingDate) {
                $query->where('created_at', '<', $openingDate);
            }])
            ->get()
            ->map(function ($customer) {
                $previousSales = $customer->sales;
                return [
                    'id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'sale_date' => $previousSales->first() ? $previousSales->first()->created_at : now(),
                    'total_amount' => $customer->total_debt,
                    'products_count' => $previousSales->sum(function ($sale) {
                        return $sale->saleDetails->count();
                    }),
                    'total_products' => $previousSales->sum(function ($sale) {
                        return $sale->saleDetails->sum('quantity');
                    }),
                    'days_pending' => $previousSales->first() ? $previousSales->first()->created_at->diffInDays(now()) : 0
                ];
            });
    }

    public function getPreviousDebtPayments(CashCount $cashCount)
    {
        $openingDate = $cashCount->opening_date;
        $closingDate = $cashCount->closing_date ?? now();

        // Obtener todos los movimientos de ingreso
        $incomeMovements = $cashCount->movements()
            ->where('type', 'income')
            ->where(function ($q) {
                $q->where('description', 'like', '%pago%deuda%')
                    ->orWhere('description', 'like', '%deuda%pago%')
                    ->orWhere('description', 'like', '%cliente%');
            })
            ->get();

        return $incomeMovements->map(function ($movement) {
            return [
                'id' => $movement->id,
                'amount' => $movement->amount,
                'description' => $movement->description,
                'date' => $movement->created_at,
                'type' => 'previous_debt_payment'
            ];
        });
    }
}
