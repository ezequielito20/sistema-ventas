<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Crear una suscripción para una empresa.
     */
    public function createForCompany(Company $company, Plan $plan, int $billingDay = 1): Subscription
    {
        $now = Carbon::now();
        $nextBilling = $this->calculateNextBillingDate($now, $billingDay);
        $gracePeriodEnd = $nextBilling->copy()->addDays(4);

        return Subscription::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => $now,
            'billing_day' => $billingDay,
            'next_billing_date' => $nextBilling,
            'grace_period_end' => $gracePeriodEnd,
            'amount' => $plan->base_price,
            'billing_mode' => 'from_plan',
            'custom_recurring_amount' => null,
            'auto_renew' => true,
        ]);
    }

    /**
     * Calcular el monto de la suscripción según plan + uso.
     */
    public function calculateAmount(Company $company, Plan $plan): float
    {
        $subscription = $company->subscription;

        if ($subscription && $subscription->billing_mode === 'custom' && $subscription->custom_recurring_amount !== null) {
            $amount = (float) $subscription->custom_recurring_amount;
        } else {
            $amount = (float) $plan->base_price;

            $userCount = $company->users()->count();
            if ($plan->max_users && $userCount > $plan->max_users) {
                $extraUsers = $userCount - $plan->max_users;
                $amount += $extraUsers * (float) $plan->price_per_user;
            }

            if ($plan->price_per_transaction > 0) {
                $transactionCount = $company->sales()->count();
                if ($plan->max_transactions && $transactionCount > $plan->max_transactions) {
                    $extraTransactions = $transactionCount - $plan->max_transactions;
                    $amount += $extraTransactions * (float) $plan->price_per_transaction;
                }
            }
        }

        $discount = (float) ($subscription?->discount_amount ?? 0);
        $amount = max(0, $amount - $discount);

        return round($amount, 2);
    }

    /**
     * Generar el pago mensual para una suscripción.
     */
    public function generateMonthlyPayment(Subscription $subscription): SubscriptionPayment
    {
        $plan = $subscription->plan;
        $company = $subscription->company;
        $amount = $this->calculateAmount($company, $plan);

        $now = Carbon::now();
        $periodStart = $subscription->next_billing_date->copy()->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();
        $dueDate = $subscription->next_billing_date->copy();

        $payment = SubscriptionPayment::create([
            'subscription_id' => $subscription->id,
            'company_id' => $company->id,
            'amount' => $amount,
            'status' => 'pending',
            'due_date' => $dueDate,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ]);

        $subscription->update(['amount' => $amount]);

        return $payment;
    }

    /**
     * Renovar suscripción: actualizar período y generar próximo pago.
     */
    public function renewSubscription(Subscription $subscription): void
    {
        $this->generateMonthlyPayment($subscription);

        $nextBilling = $this->calculateNextBillingDate(Carbon::now(), $subscription->billing_day);
        $gracePeriodEnd = $nextBilling->copy()->addDays(4);

        $subscription->update([
            'next_billing_date' => $nextBilling,
            'grace_period_end' => $gracePeriodEnd,
        ]);
    }

    /**
     * Activar período de gracia (4 días después del vencimiento).
     */
    public function activateGracePeriod(Subscription $subscription): void
    {
        $gracePeriodEnd = $subscription->next_billing_date->copy()->addDays(4);

        $subscription->update([
            'status' => 'grace_period',
            'grace_period_end' => $gracePeriodEnd,
        ]);

        $subscription->company->update(['subscription_status' => 'active']);
    }

    /**
     * Suspender suscripción por falta de pago.
     */
    public function suspend(Subscription $subscription, ?string $reason = null): void
    {
        $subscription->update([
            'status' => 'suspended',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason ?? 'Pago vencido. Servicio suspendido automáticamente.',
        ]);

        $subscription->company->update(['subscription_status' => 'suspended']);
    }

    /**
     * Reactivar una suscripción suspendida.
     */
    public function activate(Subscription $subscription): void
    {
        $now = Carbon::now();
        $nextBilling = $this->calculateNextBillingDate($now, $subscription->billing_day);

        $subscription->update([
            'status' => 'active',
            'cancelled_at' => null,
            'cancellation_reason' => null,
            'next_billing_date' => $nextBilling,
            'grace_period_end' => $nextBilling->copy()->addDays(4),
        ]);

        $subscription->company->update(['subscription_status' => 'active']);
    }

    /**
     * Cancelar suscripción definitivamente.
     */
    public function cancelSubscription(Subscription $subscription, string $reason): void
    {
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'auto_renew' => false,
        ]);

        $subscription->company->update(['subscription_status' => 'suspended']);
    }

    /**
     * Cambiar de plan.
     */
    public function changePlan(Subscription $subscription, Plan $newPlan): void
    {
        $amount = $this->calculateAmount($subscription->company, $newPlan);

        $subscription->update([
            'plan_id' => $newPlan->id,
            'amount' => $amount,
        ]);
    }

    /**
     * Calcular próxima fecha de cobro según el día del mes.
     */
    public function calculateNextBillingDate(Carbon $from, int $billingDay): Carbon
    {
        $billingDay = max(1, min(28, $billingDay));
        $today = $from->copy()->startOfDay();
        $currentMonthBilling = $today->copy()->setDay(min($billingDay, $today->daysInMonth));

        if ($today->greaterThan($currentMonthBilling)) {
            return $today->copy()->addMonth()->setDay(min($billingDay, $today->copy()->addMonth()->daysInMonth));
        }

        return $currentMonthBilling;
    }

    /**
     * Obtener estadísticas globales de suscripciones.
     */
    public function globalStats(): array
    {
        $totalCompanies = Company::count();
        $activeCompanies = Company::where('subscription_status', 'active')->count();
        $suspendedCompanies = Company::where('subscription_status', 'suspended')->count();

        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $trialSubscriptions = Subscription::where('status', 'trial')->count();
        $graceSubscriptions = Subscription::where('status', 'grace_period')->count();
        $suspendedSubscriptions = Subscription::where('status', 'suspended')->count();

        $pendingPayments = SubscriptionPayment::where('status', 'pending')->sum('amount');
        $overduePayments = SubscriptionPayment::where('status', 'overdue')->sum('amount');
        $mrr = Subscription::where('status', 'active')->sum('amount');

        $pendingCount = SubscriptionPayment::where('status', 'pending')->count();
        $overdueCount = SubscriptionPayment::where('status', 'overdue')->count();

        return [
            'total_companies' => $totalCompanies,
            'active_companies' => $activeCompanies,
            'suspended_companies' => $suspendedCompanies,
            'active_subscriptions' => $activeSubscriptions,
            'trial_subscriptions' => $trialSubscriptions,
            'grace_subscriptions' => $graceSubscriptions,
            'suspended_subscriptions' => $suspendedSubscriptions,
            'mrr' => (float) $mrr,
            'pending_payments_amount' => (float) $pendingPayments,
            'overdue_payments_amount' => (float) $overduePayments,
            'pending_payments_count' => $pendingCount,
            'overdue_payments_count' => $overdueCount,
        ];
    }

    /**
     * Ingresos mensuales (últimos N meses).
     */
    public function monthlyRevenue(int $months = 6): array
    {
        $data = [];
        $now = Carbon::now();

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $revenue = SubscriptionPayment::where('status', 'paid')
                ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $data[] = [
                'month' => $date->format('M Y'),
                'revenue' => (float) $revenue,
            ];
        }

        return $data;
    }
}
