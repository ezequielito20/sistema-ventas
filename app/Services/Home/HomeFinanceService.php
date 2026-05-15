<?php

namespace App\Services\Home;

use App\Models\Home\HomeServiceBill;
use App\Models\Home\HomeTransaction;
use Illuminate\Support\Facades\DB;

class HomeFinanceService
{
    public function monthlyExpensesByCategory(int $companyId, ?int $year = null, ?int $month = null): array
    {
        $year = $year ?? (int) now()->format('Y');
        $month = $month ?? (int) now()->format('m');

        return HomeTransaction::where('company_id', $companyId)
            ->expense()
            ->byMonth($year, $month)
            ->groupBy('category')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->pluck('total', 'category')
            ->toArray();
    }

    public function incomeVsExpenseTrend(int $companyId, int $months = 6): array
    {
        $results = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = (int) $date->format('Y');
            $month = (int) $date->format('m');

            $income = HomeTransaction::where('company_id', $companyId)
                ->income()
                ->byMonth($year, $month)
                ->sum('amount');

            $expense = HomeTransaction::where('company_id', $companyId)
                ->expense()
                ->byMonth($year, $month)
                ->sum('amount');

            $results[] = [
                'label' => $date->isoFormat('MMM YYYY'),
                'income' => (float) $income,
                'expense' => (float) $expense,
            ];
        }

        return $results;
    }

    public function upcomingBills(int $companyId, int $days = 30): array
    {
        return HomeServiceBill::whereHas('service', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->unpaid()
            ->whereBetween('due_date', [now(), now()->addDays($days)])
            ->with('service')
            ->orderBy('due_date')
            ->get()
            ->toArray();
    }

    public function monthlyTotals(int $companyId, ?int $year = null, ?int $month = null): array
    {
        $year = $year ?? (int) now()->format('Y');
        $month = $month ?? (int) now()->format('m');

        $income = HomeTransaction::where('company_id', $companyId)
            ->income()
            ->byMonth($year, $month)
            ->sum('amount');

        $expense = HomeTransaction::where('company_id', $companyId)
            ->expense()
            ->byMonth($year, $month)
            ->sum('amount');

        return [
            'income' => (float) $income,
            'expense' => (float) $expense,
            'balance' => (float) ($income - $expense),
        ];
    }
}
