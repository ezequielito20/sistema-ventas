<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Company;
use App\Models\SubscriptionPayment;
use App\Services\SubscriptionService;
use App\Services\UsageCollectorService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class DashboardIndex extends Component
{
    use WithPagination;

    public array $stats = [];

    public array $monthlyRevenue = [];

    public array $topCompanies = [];

    public array $upcomingPayments = [];

    public string $search = '';

    public string $statusFilter = '';

    public int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        $subscriptionService = app(SubscriptionService::class);
        $usageCollector = app(UsageCollectorService::class);

        $this->stats = $subscriptionService->globalStats();
        $this->monthlyRevenue = $subscriptionService->monthlyRevenue(6);
        $this->topCompanies = $usageCollector->topByRevenue(10);

        $this->upcomingPayments = SubscriptionPayment::with(['company:id,name', 'subscription.plan:id,name'])
            ->where('status', 'pending')
            ->where('due_date', '<=', Carbon::now()->addDays(7))
            ->orderBy('due_date')
            ->take(10)
            ->get()
            ->map(fn ($payment) => [
                'id' => $payment->id,
                'company_name' => $payment->company->name,
                'plan_name' => $payment->subscription->plan->name ?? 'N/A',
                'amount' => (float) $payment->amount,
                'due_date' => $payment->due_date->format('d/m/Y'),
                'is_overdue' => $payment->due_date->isPast(),
            ])
            ->toArray();
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingPerPage(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    protected function companiesQuery()
    {
        $query = Company::with(['subscription.plan:id,name', 'subscription.latestPayment'])
            ->withCount(['users', 'sales']);

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'ILIKE', '%' . $s . '%')
                    ->orWhere('nit', 'ILIKE', '%' . $s . '%')
                    ->orWhere('email', 'ILIKE', '%' . $s . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('subscription_status', $this->statusFilter);
        }

        return $query->orderBy('name');
    }

    public function render(): View
    {
        $companies = $this->companiesQuery()->paginate($this->perPage);

        return view('livewire.super-admin.dashboard-index', [
            'companies' => $companies,
        ]);
    }
}
