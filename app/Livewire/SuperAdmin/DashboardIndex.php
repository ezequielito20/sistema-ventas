<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\SubscriptionService;
use App\Services\UsageCollectorService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class DashboardIndex extends Component
{
    public array $stats = [];

    public array $monthlyRevenue = [];

    public array $topCompanies = [];

    public array $upcomingPayments = [];

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

    public function render(): View
    {
        return view('livewire.super-admin.dashboard-index');
    }
}
