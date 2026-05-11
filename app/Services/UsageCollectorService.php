<?php

namespace App\Services;

use App\Models\Company;
use App\Models\SubscriptionUsageLog;
use Carbon\Carbon;

class UsageCollectorService
{
    /**
     * Recolectar estadísticas de uso para una empresa específica.
     */
    public function collectForCompany(int $companyId, ?Carbon $periodStart = null, ?Carbon $periodEnd = null): SubscriptionUsageLog
    {
        $company = Company::withCount([
            'users',
            'customers',
        ])->findOrFail($companyId);

        if (!$periodStart) {
            $periodStart = Carbon::now()->subMonth()->startOfMonth();
        }
        if (!$periodEnd) {
            $periodEnd = Carbon::now()->subMonth()->endOfMonth();
        }

        $saleCount = $company->sales()
            ->whereBetween('sale_date', [$periodStart, $periodEnd])
            ->count();

        $transactionCount = $saleCount
            + $company->purchases()
                ->whereBetween('purchase_date', [$periodStart, $periodEnd])
                ->count();

        $totalRevenue = (float) $company->sales()
            ->whereBetween('sale_date', [$periodStart, $periodEnd])
            ->sum('total_price');

        $productCount = $company->products()->count();

        $userCount = $company->users()->count();

        $customerCount = $company->customers()->count();

        return SubscriptionUsageLog::create([
            'company_id' => $companyId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'user_count' => $userCount,
            'transaction_count' => $transactionCount,
            'sale_count' => $saleCount,
            'product_count' => $productCount,
            'customer_count' => $customerCount,
            'total_revenue' => $totalRevenue,
            'calculated_amount' => 0,
        ]);
    }

    /**
     * Recolectar estadísticas para todas las empresas activas.
     */
    public function collectForAllActiveCompanies(): array
    {
        $now = Carbon::now();
        $periodStart = $now->copy()->subMonth()->startOfMonth();
        $periodEnd = $now->copy()->subMonth()->endOfMonth();

        $companies = Company::where('subscription_status', 'active')->get();

        $logs = [];

        foreach ($companies as $company) {
            try {
                $logs[] = $this->collectForCompany($company->id, $periodStart, $periodEnd);
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $logs;
    }

    /**
     * Obtener estadísticas de la empresa para mostrar en el panel.
     */
    public function getCompanyStats(int $companyId): array
    {
        $company = Company::withCount([
            'users',
            'customers',
            'products',
            'sales',
        ])->findOrFail($companyId);

        $totalRevenue = (float) $company->sales()->sum('total_price');

        $latestLog = SubscriptionUsageLog::where('company_id', $companyId)
            ->latest('period_end')
            ->first();

        $previousLog = SubscriptionUsageLog::where('company_id', $companyId)
            ->where('id', '!=', $latestLog?->id)
            ->latest('period_end')
            ->first();

        return [
            'users_count' => $company->users_count,
            'customers_count' => $company->customers_count,
            'products_count' => $company->products_count,
            'sales_count' => $company->sales_count,
            'total_revenue' => $totalRevenue,
            'current_period' => $latestLog,
            'previous_period' => $previousLog,
        ];
    }

    /**
     * Top empresas por facturación.
     */
    public function topByRevenue(int $limit = 10): array
    {
        return Company::withSum(['sales as total_revenue' => function ($q) {
            $q->whereBetween('sale_date', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->endOfMonth()]);
        }], 'total_price')
            ->orderByDesc('total_revenue')
            ->take($limit)
            ->get()
            ->map(fn ($company) => [
                'id' => $company->id,
                'name' => $company->name,
                'nit' => $company->nit,
                'total_revenue' => (float) ($company->total_revenue ?? 0),
            ])
            ->toArray();
    }

    /**
     * Top empresas por cantidad de ventas.
     */
    public function topBySales(int $limit = 10): array
    {
        return Company::withCount(['sales' => function ($q) {
            $q->whereBetween('sale_date', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->endOfMonth()]);
        }])
            ->orderByDesc('sales_count')
            ->take($limit)
            ->get()
            ->map(fn ($company) => [
                'id' => $company->id,
                'name' => $company->name,
                'sales_count' => $company->sales_count,
            ])
            ->toArray();
    }

    /**
     * Top empresas por cantidad de clientes.
     */
    public function topByCustomers(int $limit = 10): array
    {
        return Company::withCount('customers')
            ->orderByDesc('customers_count')
            ->take($limit)
            ->get()
            ->map(fn ($company) => [
                'id' => $company->id,
                'name' => $company->name,
                'customers_count' => $company->customers_count,
            ])
            ->toArray();
    }
}
