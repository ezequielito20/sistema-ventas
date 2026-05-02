<?php

namespace App\Livewire;

use App\Models\CashCount;
use App\Services\CashCountService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class CashCountDetail extends Component
{
    public bool $show = false;
    public ?int $cashCountId = null;
    public string $activeTab = 'clientes';

    public array $customerStats = [];
    public array $salesStats = [];
    public array $paymentsStats = [];
    public array $purchasesStats = [];
    public array $productsStats = [];
    public array $ordersStats = [];
    public array $generalInfo = [];

    protected ?CashCountService $cashCountService = null;

    public function mount(CashCountService $service): void
    {
        $this->cashCountService = $service;
    }

    #[On('show-detail-modal')]
    public function handleShow(int $id): void
    {
        $this->cashCountId = $id;
        $this->activeTab = 'clientes';
        $this->loadData();
        $this->show = true;
    }

    public function close(): void
    {
        $this->show = false;
        $this->cashCountId = null;
        $this->resetStats();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    private function loadData(): void
    {
        $companyId = Auth::user()->company_id;
        $cashCount = CashCount::where('company_id', $companyId)
            ->with(['movements' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }])
            ->find($this->cashCountId);

        if (! $cashCount) {
            $this->close();
            return;
        }

        $totalIncome = (float) $cashCount->movements->where('type', 'income')->sum('amount');
        $totalExpenses = (float) $cashCount->movements->where('type', 'expense')->sum('amount');
        $currentBalance = (float) $cashCount->initial_amount + $totalIncome - $totalExpenses;

        $this->generalInfo = [
            'id' => (int) $cashCount->id,
            'initial_amount' => (float) $cashCount->initial_amount,
            'final_amount' => $cashCount->final_amount !== null ? (float) $cashCount->final_amount : null,
            'opening_date' => $cashCount->opening_date,
            'closing_date' => $cashCount->closing_date,
            'observations' => $cashCount->observations,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'current_balance' => $currentBalance,
            'movements_count' => (int) $cashCount->movements->count(),
            'movements' => $cashCount->movements->map(fn ($m) => [
                'id' => (int) $m->id,
                'type' => $m->type,
                'amount' => (float) $m->amount,
                'description' => $m->description,
                'created_at' => $m->created_at->toISOString(),
            ])->toArray(),
        ];

        $this->customerStats = $this->cashCountService->getCustomerStats($cashCount);
        $this->salesStats = $this->cashCountService->getSalesStats($cashCount);
        $this->paymentsStats = $this->cashCountService->getPaymentsStats($cashCount);
        $this->purchasesStats = $this->cashCountService->getPurchasesStats($cashCount);
        $this->productsStats = $this->cashCountService->getProductsStats($cashCount);
        $this->ordersStats = $this->cashCountService->getOrdersStats($cashCount);
    }

    private function resetStats(): void
    {
        $this->generalInfo = [];
        $this->customerStats = [];
        $this->salesStats = [];
        $this->paymentsStats = [];
        $this->purchasesStats = [];
        $this->productsStats = [];
        $this->ordersStats = [];
    }

    public function getCurrencySymbolProperty(): string
    {
        $company = \Illuminate\Support\Facades\DB::table('companies')
            ->select('currency', 'country')
            ->where('id', Auth::user()->company_id)
            ->first();

        $currency = null;
        if ($company && $company->currency) {
            $currency = \Illuminate\Support\Facades\DB::table('currencies')
                ->select('symbol')
                ->where('code', $company->currency)
                ->first();
        }

        if (! $currency && $company && $company->country) {
            $currency = \Illuminate\Support\Facades\DB::table('currencies')
                ->select('symbol')
                ->where('country_id', $company->country)
                ->first();
        }

        return $currency->symbol ?? '$';
    }

    public function render()
    {
        return view('livewire.cash-count-detail');
    }
}
