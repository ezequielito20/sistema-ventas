<?php

namespace App\Livewire\Home;

use App\Services\Home\HomeFinanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class HomeFinancesDashboard extends Component
{
    public array $expensesByCategory = [];

    public array $incomeExpenseTrend = [];

    public array $monthlyTotals = [];

    public array $upcomingBills = [];

    public function mount(): void
    {
        Gate::authorize('home.finances.index');

        $companyId = (int) Auth::user()->company_id;
        $finance = app(HomeFinanceService::class);

        $this->expensesByCategory = $finance->monthlyExpensesByCategory($companyId);
        $this->incomeExpenseTrend = $finance->incomeVsExpenseTrend($companyId);
        $this->monthlyTotals = $finance->monthlyTotals($companyId);
        $this->upcomingBills = $finance->upcomingBills($companyId);
    }

    public function render(): View
    {
        return view('livewire.home-finances-dashboard');
    }
}
