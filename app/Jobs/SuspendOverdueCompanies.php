<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SuspendOverdueCompanies implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(SubscriptionService $subscriptionService, PaymentService $paymentService): void
    {
        $paymentService->markOverduePayments();

        $subscriptions = Subscription::where('status', 'grace_period')
            ->whereDate('grace_period_end', '<', now()->startOfDay())
            ->get();

        foreach ($subscriptions as $subscription) {
            try {
                $subscriptionService->suspend($subscription, 'Pago vencido. Servicio suspendido automáticamente por superar el período de gracia.');
            } catch (\Throwable $e) {
                continue;
            }
        }
    }
}
