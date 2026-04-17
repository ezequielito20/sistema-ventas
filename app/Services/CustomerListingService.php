<?php

namespace App\Services;

use App\Http\Controllers\CustomerController;
use App\Models\CashCount;
use App\Models\Customer;
use App\Models\ExchangeRate;
use App\Traits\SmartPaginationTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Lógica de listado de clientes (filtros, estadísticas, deuda por fila).
 * Extraída de {@see CustomerController::index} para reutilizar en Livewire sin duplicar reglas.
 */
class CustomerListingService
{
    use SmartPaginationTrait;

    /**
     * @return array{
     *     customers: LengthAwarePaginator,
     *     customersData: array<int, array<string, mixed>>,
     *     totalCustomers: int,
     *     activeCustomers: int,
     *     newCustomers: int,
     *     customerGrowth: float|int,
     *     totalRevenue: mixed,
     *     totalDebt: mixed,
     *     defaultersCount: int,
     *     currentDebtorsCount: int,
     *     previousCashCountDebtTotal: mixed,
     *     currentCashCountDebtTotal: mixed,
     *     exchangeRate: float,
     *     exchangeRateUpdatedAt: string|null,
     * }
     */
    public function getIndexPayload(
        int $companyId,
        object $currency,
        ?string $search,
        ?string $filter,
        int $perPage,
        ?int $currentPage = null,
    ): array {
        $currentCashCount = CashCount::where('company_id', $companyId)
            ->whereNull('closing_date')
            ->first();

        $openingDate = $currentCashCount ? $currentCashCount->opening_date : now();

        $query = Customer::where('company_id', $companyId);

        if ($search !== null && $search !== '') {
            $searchTerm = $search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ILIKE', "%{$searchTerm}%")
                    ->orWhere('email', 'ILIKE', "%{$searchTerm}%")
                    ->orWhere('phone', 'ILIKE', "%{$searchTerm}%")
                    ->orWhere('nit_number', 'ILIKE', "%{$searchTerm}%");
            });
        }

        if ($filter === 'defaulters') {
            $defaulterIds = DB::table('customers')
                ->where('customers.company_id', $companyId)
                ->whereRaw('(
                  SELECT COALESCE(SUM(sales.total_price), 0)
                  FROM sales
                  WHERE sales.customer_id = customers.id
                  AND sales.company_id = customers.company_id
                  AND sales.sale_date < ?
               ) > (
                  SELECT COALESCE(SUM(debt_payments.payment_amount), 0)
                  FROM debt_payments
                  WHERE debt_payments.customer_id = customers.id
                  AND debt_payments.company_id = customers.company_id
               )', [$openingDate])
                ->pluck('customers.id');

            if ($defaulterIds->isNotEmpty()) {
                $query->whereIn('id', $defaulterIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($filter === 'current_debt') {
            $currentDebtIds = DB::table('customers')
                ->where('customers.company_id', $companyId)
                ->where('customers.total_debt', '>', 0)
                ->whereRaw('EXISTS (
                  SELECT 1 FROM sales
                  WHERE sales.customer_id = customers.id
                  AND sales.company_id = customers.company_id
                  AND sales.sale_date >= ?
               )', [$openingDate])
                ->pluck('customers.id');

            if ($currentDebtIds->isNotEmpty()) {
                $query->whereIn('id', $currentDebtIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $page = max(1, $currentPage ?? (int) request()->input('page', 1));

        $customers = $query->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString();

        $customers = $this->generateSmartPagination($customers, 2);

        $stats = DB::table('customers')
            ->where('company_id', $companyId)
            ->selectRaw('
               COUNT(*) as total_customers,
               SUM(total_debt) as total_debt,
               COUNT(CASE WHEN EXTRACT(MONTH FROM created_at) = ? AND EXTRACT(YEAR FROM created_at) = ? THEN 1 END) as new_customers
            ', [now()->month, now()->year])
            ->first();

        $totalCustomers = (int) ($stats->total_customers ?? 0);
        $totalDebt = $stats->total_debt ?? 0;
        $newCustomers = $stats->new_customers ?? 0;
        $customerGrowth = $totalCustomers > 0 ? round(($newCustomers / $totalCustomers) * 100) : 0;

        $activeCustomers = DB::table('customers')
            ->join('sales', 'customers.id', '=', 'sales.customer_id')
            ->where('customers.company_id', $companyId)
            ->distinct()
            ->count('customers.id');

        $totalRevenue = DB::table('sales')
            ->where('company_id', $companyId)
            ->sum('total_price');

        $globalStats = DB::table('customers as c')
            ->leftJoinSub(
                DB::table('sales')
                    ->where('company_id', $companyId)
                    ->selectRaw(
                        'customer_id,
                     SUM(CASE WHEN sale_date < ? THEN total_price ELSE 0 END) as sales_before,
                     SUM(CASE WHEN sale_date >= ? THEN total_price ELSE 0 END) as sales_after',
                        [$openingDate, $openingDate]
                    )
                    ->groupBy('customer_id'),
                's',
                'c.id',
                '=',
                's.customer_id'
            )
            ->leftJoinSub(
                DB::table('debt_payments')
                    ->where('company_id', $companyId)
                    ->selectRaw('customer_id, SUM(payment_amount) as total_payments')
                    ->groupBy('customer_id'),
                'p',
                'c.id',
                '=',
                'p.customer_id'
            )
            ->where('c.company_id', $companyId)
            ->selectRaw('
               COUNT(CASE WHEN (COALESCE(s.sales_before, 0) - COALESCE(p.total_payments, 0)) > 0 THEN 1 END) as defaulters_count,
               SUM(CASE WHEN (COALESCE(s.sales_before, 0) - COALESCE(p.total_payments, 0)) > 0
                  THEN (COALESCE(s.sales_before, 0) - COALESCE(p.total_payments, 0)) ELSE 0 END) as previous_total_debt,
               COUNT(CASE WHEN c.total_debt > 0 AND (COALESCE(s.sales_before, 0) - COALESCE(p.total_payments, 0)) <= 0 THEN 1 END) as current_debtors_count,
               SUM(CASE WHEN c.total_debt > 0 AND (COALESCE(s.sales_before, 0) - COALESCE(p.total_payments, 0)) <= 0
                  THEN COALESCE(s.sales_after, 0) ELSE 0 END) as current_total_debt
            ')
            ->first();

        $defaultersCount = $globalStats->defaulters_count ?? 0;
        $previousCashCountDebtTotal = $globalStats->previous_total_debt ?? 0;
        $currentDebtorsCount = $globalStats->current_debtors_count ?? 0;
        $currentCashCountDebtTotal = $globalStats->current_total_debt ?? 0;

        $customerIds = $customers->pluck('id')->toArray();

        if ($customerIds === []) {
            $customersData = [];
        } else {
            $salesData = DB::table('sales')
                ->whereIn('customer_id', $customerIds)
                ->where('company_id', $companyId)
                ->selectRaw('
                  customer_id,
                  SUM(CASE WHEN sale_date < ? THEN total_price ELSE 0 END) as sales_before,
                  SUM(CASE WHEN sale_date >= ? THEN total_price ELSE 0 END) as sales_after,
                  COUNT(CASE WHEN sale_date < ? THEN 1 END) as sales_before_count
               ', [$openingDate, $openingDate, $openingDate])
                ->groupBy('customer_id')
                ->get()
                ->keyBy('customer_id');

            $paymentsData = DB::table('debt_payments')
                ->whereIn('customer_id', $customerIds)
                ->where('company_id', $companyId)
                ->selectRaw('customer_id, SUM(payment_amount) as total_payments')
                ->groupBy('customer_id')
                ->get()
                ->keyBy('customer_id');

            $customersData = [];

            foreach ($customers as $customer) {
                $sales = $salesData->get($customer->id);
                $payments = $paymentsData->get($customer->id);

                $salesBefore = $sales ? $sales->sales_before : 0;
                $salesAfter = $sales ? $sales->sales_after : 0;
                $totalPayments = $payments ? $payments->total_payments : 0;

                $previousDebt = max(0, $salesBefore - $totalPayments);
                $currentDebt = max(0, $salesAfter);
                $isDefaulter = $previousDebt > 0;

                $customersData[$customer->id] = [
                    'isDefaulter' => $isDefaulter,
                    'previousDebt' => $previousDebt,
                    'currentDebt' => $currentDebt,
                    'hasOldSales' => $sales ? $sales->sales_before_count > 0 : false,
                ];
            }
        }

        $exchangeRateRecord = ExchangeRate::currentRecord();
        $exchangeRate = $exchangeRateRecord ? (float) $exchangeRateRecord->rate : 134.0;
        $exchangeRateUpdatedAt = $exchangeRateRecord ? $exchangeRateRecord->updated_at->format('d/m/Y H:i') : null;

        return [
            'customers' => $customers,
            'customersData' => $customersData,
            'totalCustomers' => $totalCustomers,
            'activeCustomers' => $activeCustomers,
            'newCustomers' => $newCustomers,
            'customerGrowth' => $customerGrowth,
            'totalRevenue' => $totalRevenue,
            'totalDebt' => $totalDebt,
            'defaultersCount' => (int) $defaultersCount,
            'currentDebtorsCount' => (int) $currentDebtorsCount,
            'previousCashCountDebtTotal' => $previousCashCountDebtTotal,
            'currentCashCountDebtTotal' => $currentCashCountDebtTotal,
            'exchangeRate' => $exchangeRate,
            'exchangeRateUpdatedAt' => $exchangeRateUpdatedAt,
            'currency' => $currency,
        ];
    }
}
