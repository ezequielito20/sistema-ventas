<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateMonthlyInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(SubscriptionService $subscriptionService): void
    {
        $subscriptions = Subscription::where('status', 'active')
            ->where('auto_renew', true)
            ->whereDate('next_billing_date', '<=', now()->startOfDay())
            ->get();

        foreach ($subscriptions as $subscription) {
            try {
                $subscriptionService->renewSubscription($subscription);
            } catch (\Throwable $e) {
                continue;
            }
        }
    }
}
