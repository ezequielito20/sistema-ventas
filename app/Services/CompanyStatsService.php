<?php

namespace App\Services;

use App\Models\CashCount;
use App\Models\CashMovement;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompanyStatsService
{
    /**
     * Get comprehensive dashboard statistics for a company.
     */
    public function getDashboardStats(int $companyId): array
    {
        return [
            'top_products' => $this->getTopProducts($companyId),
            'top_customers' => $this->getTopCustomers($companyId),
            'sales_by_category' => $this->getSalesByCategory($companyId),
            'monthly_sales' => $this->getMonthlySales($companyId),
            'sales_analysis' => $this->getSalesAnalysis($companyId),
            'cash_count' => $this->getCashCountData($companyId),
            'customer_stats' => $this->getCustomerStats($companyId),
        ];
    }

    /**
     * Top 10 products by quantity sold.
     */
    protected function getTopProducts(int $companyId): \Illuminate\Support\Collection
    {
        return DB::table('sale_details as sd')
            ->select(
                'p.id',
                'p.name',
                'p.sale_price',
                DB::raw('COUNT(sd.id) as times_sold'),
                DB::raw('SUM(sd.quantity) as total_quantity'),
                DB::raw('SUM(sd.subtotal) as total_revenue')
            )
            ->join('products as p', 'sd.product_id', '=', 'p.id')
            ->join('sales as s', 'sd.sale_id', '=', 's.id')
            ->where('s.company_id', $companyId)
            ->groupBy('p.id', 'p.name', 'p.sale_price')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();
    }

    /**
     * Top 5 customers by total spent.
     */
    protected function getTopCustomers(int $companyId): \Illuminate\Support\Collection
    {
        return DB::table('customers as c')
            ->select(
                'c.id',
                'c.name',
                DB::raw('COALESCE(SUM(s.total_price), 0) as total_spent'),
                DB::raw('COUNT(DISTINCT s.id) as total_sales')
            )
            ->join('sales as s', 's.customer_id', '=', 'c.id')
            ->where('s.company_id', $companyId)
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();
    }

    /**
     * Sales grouped by category with Chart.js-ready arrays.
     */
    protected function getSalesByCategory(int $companyId): array
    {
        $rows = DB::table('sale_details as sd')
            ->select(
                DB::raw("COALESCE(cat.name, 'Sin Categoría') as name"),
                DB::raw('SUM(sd.subtotal) as total_revenue'),
                DB::raw('SUM(sd.quantity) as total_quantity')
            )
            ->join('products as p', 'sd.product_id', '=', 'p.id')
            ->leftJoin('categories as cat', 'p.category_id', '=', 'cat.id')
            ->join('sales as s', 'sd.sale_id', '=', 's.id')
            ->where('s.company_id', $companyId)
            ->groupBy('cat.id', 'cat.name')
            ->orderByDesc('total_revenue')
            ->get();

        return [
            'categories' => $rows,
            'labels' => $rows->pluck('name')->toArray(),
            'data' => $rows->pluck('total_revenue')->map(fn($v) => (float) $v)->toArray(),
        ];
    }

    /**
     * Monthly sales trend for the last 6 months with profit calculation.
     */
    protected function getMonthlySales(int $companyId): array
    {
        // Build label scaffolding for last 6 months
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = [
                'label' => $date->format('M Y'),
                'month' => $date->month,
                'year' => $date->year,
            ];
        }

        // Sales + profit + transactions in one query
        $stats = DB::select("
            SELECT
                EXTRACT(MONTH FROM s.sale_date) as month,
                EXTRACT(YEAR FROM s.sale_date) as year,
                SUM(s.total_price) as sale_total,
                SUM(sd.quantity * (p.sale_price - p.purchase_price)) as profit_total,
                COUNT(DISTINCT s.id) as transactions_count
            FROM sales s
            JOIN sale_details sd ON s.id = sd.sale_id
            JOIN products p ON sd.product_id = p.id
            WHERE s.company_id = ?
              AND s.sale_date >= DATE_TRUNC('month', CURRENT_DATE - INTERVAL '5 months')
            GROUP BY EXTRACT(MONTH FROM s.sale_date), EXTRACT(YEAR FROM s.sale_date)
        ", [$companyId]);

        $labels = [];
        $salesData = [];
        $profitData = [];
        $transactionsData = [];

        foreach ($months as $m) {
            $labels[] = $m['label'];

            $match = collect($stats)->first(fn($row) => (int) $row->month === $m['month'] && (int) $row->year === $m['year']);

            $salesData[] = $match ? (float) $match->sale_total : 0;
            $profitData[] = $match ? (float) $match->profit_total : 0;
            $transactionsData[] = $match ? (int) $match->transactions_count : 0;
        }

        return [
            'labels' => $labels,
            'sales_data' => $salesData,
            'profit_data' => $profitData,
            'transactions_data' => $transactionsData,
        ];
    }

    /**
     * Sales analysis widgets: weekly, today, average, profit, monthly.
     */
    protected function getSalesAnalysis(int $companyId): array
    {
        $stats = DB::select("
            SELECT
                COALESCE(SUM(CASE WHEN DATE(sale_date) = CURRENT_DATE THEN total_price ELSE 0 END), 0) as today_sales,
                COALESCE(SUM(CASE WHEN sale_date >= DATE_TRUNC('week', CURRENT_DATE) THEN total_price ELSE 0 END), 0) as weekly_sales,
                COALESCE(AVG(total_price), 0) as average_customer_spend,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM sale_date) = EXTRACT(MONTH FROM CURRENT_DATE)
                                  AND EXTRACT(YEAR FROM sale_date) = EXTRACT(YEAR FROM CURRENT_DATE)
                                  THEN total_price ELSE 0 END), 0) as monthly_sales
            FROM sales
            WHERE company_id = ?
        ", [$companyId]);

        $row = $stats[0];

        $totalProfit = (float) DB::table('sale_details as sd')
            ->join('products as p', 'sd.product_id', '=', 'p.id')
            ->join('sales as s', 'sd.sale_id', '=', 's.id')
            ->where('s.company_id', $companyId)
            ->sum(DB::raw('sd.quantity * (p.sale_price - p.purchase_price)'));

        return [
            'weekly_sales' => (float) $row->weekly_sales,
            'today_sales' => (float) $row->today_sales,
            'average_customer_spend' => (float) $row->average_customer_spend,
            'total_profit' => $totalProfit,
            'monthly_sales' => (float) $row->monthly_sales,
        ];
    }

    /**
     * Cash count data: mirrors AdminController balance calculation.
     * Balance = sales - current_debt - purchases + old_debt_recovered
     */
    protected function getCashCountData(int $companyId): array
    {
        $currentCashCount = CashCount::where('company_id', $companyId)
            ->whereNull('closing_date')
            ->orderByDesc('opening_date')
            ->first();

        $currentCashData = [
            'sales' => 0.0,
            'purchases' => 0.0,
            'debt' => 0.0,
            'balance' => 0.0,
            'debt_payments' => 0.0,
            'opening_date' => null,
        ];

        if ($currentCashCount) {
            $cashOpenDate = $currentCashCount->opening_date;
            $currentCashData['opening_date'] = $cashOpenDate;

            $currentCashData['sales'] = (float) DB::table('sales')
                ->where('company_id', $companyId)
                ->where('sale_date', '>=', $cashOpenDate)
                ->sum('total_price');

            $currentCashData['purchases'] = (float) DB::table('purchases')
                ->where('company_id', $companyId)
                ->where('purchase_date', '>=', $cashOpenDate)
                ->sum('total_price');

            $hasDebtPayments = Schema::hasTable('debt_payments');

            // Debt payments: only count for customers with sales in current period
            $totalOldDebtRecovered = 0.0;
            if ($hasDebtPayments) {
                $allPayments = DB::table('debt_payments')
                    ->where('company_id', $companyId)
                    ->where('created_at', '>=', $cashOpenDate)
                    ->get();

                $customerSalesData = DB::table('customers')
                    ->select('customers.id', DB::raw('COALESCE(SUM(sales.total_price), 0) as sales_in_period'))
                    ->leftJoin('sales', function ($join) use ($cashOpenDate) {
                        $join->on('customers.id', '=', 'sales.customer_id')
                            ->where('sales.sale_date', '>=', $cashOpenDate);
                    })
                    ->whereIn('customers.id', $allPayments->pluck('customer_id'))
                    ->groupBy('customers.id')
                    ->get()
                    ->keyBy('id');

                $currentCashData['debt_payments'] = (float) $allPayments->sum(function ($p) use ($customerSalesData) {
                    $cd = $customerSalesData->get($p->customer_id);
                    return ($cd && $cd->sales_in_period > 0) ? $p->payment_amount : 0;
                });

                $totalOldDebtRecovered = (float) $allPayments->sum(function ($p) use ($customerSalesData) {
                    $cd = $customerSalesData->get($p->customer_id);
                    return ($cd && $cd->sales_in_period == 0) ? $p->payment_amount : 0;
                });

                // Current period debt
                $debtData = DB::select("
                    SELECT
                        c.id,
                        c.name,
                        c.total_debt,
                        COALESCE(sd.sales_in_current, 0) as sales_in_current,
                        COALESCE(pd.payments_in_current, 0) as payments_in_current
                    FROM customers c
                    LEFT JOIN (
                        SELECT customer_id, SUM(total_price) as sales_in_current
                        FROM sales WHERE company_id = ? AND sale_date >= ?
                        GROUP BY customer_id
                    ) sd ON c.id = sd.customer_id
                    LEFT JOIN (
                        SELECT customer_id, SUM(payment_amount) as payments_in_current
                        FROM debt_payments WHERE company_id = ? AND created_at >= ?
                        GROUP BY customer_id
                    ) pd ON c.id = pd.customer_id
                    WHERE c.company_id = ? AND c.total_debt > 0
                ", [$companyId, $cashOpenDate, $companyId, $cashOpenDate, $companyId]);

                $debtInCurrent = 0;
                foreach ($debtData as $c) {
                    $debtInCurrent += max(0, (float) ($c->sales_in_current - $c->payments_in_current));
                }
                $currentCashData['debt'] = (float) $debtInCurrent;
            }

            // Mirror AdminController balance formula
            $realCashFromSales = $currentCashData['sales'] - $currentCashData['debt'];
            $currentCashData['balance'] = (float) ($realCashFromSales - $currentCashData['purchases'] + $totalOldDebtRecovered);
        }

        $closedCounts = CashCount::where('company_id', $companyId)
            ->whereNotNull('closing_date')
            ->orderByDesc('opening_date')
            ->limit(10)
            ->get()
            ->map(fn($cc) => [
                'id' => $cc->id,
                'opening_date_formatted' => $cc->opening_date?->format('d/m/Y'),
                'closing_date_formatted' => $cc->closing_date?->format('d/m/Y'),
                'initial_amount' => (float) $cc->initial_amount,
                'final_amount' => (float) $cc->final_amount,
            ]);

        return [
            'current_cash_count' => $currentCashCount,
            'current_cash_data' => $currentCashData,
            'closed_cash_counts' => $closedCounts,
        ];
    }

    /**
     * Customer stats: total, new this month, verified.
     */
    protected function getCustomerStats(int $companyId): array
    {
        $stats = DB::select("
            SELECT
                (SELECT COUNT(*) FROM customers WHERE company_id = ?) as total_customers,
                (SELECT COUNT(*) FROM customers WHERE company_id = ?
                 AND EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE)
                 AND EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE)) as new_customers,
                (SELECT COUNT(*) FROM customers WHERE company_id = ?
                 AND nit_number IS NOT NULL) as verified_customers
        ", [$companyId, $companyId, $companyId]);

        $row = $stats[0];

        return [
            'total_customers' => (int) $row->total_customers,
            'new_customers' => (int) $row->new_customers,
            'verified_customers' => (int) $row->verified_customers,
        ];
    }
}
