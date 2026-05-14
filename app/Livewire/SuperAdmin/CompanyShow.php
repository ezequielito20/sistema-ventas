<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Company;
use App\Models\Plan;
use App\Models\SecurityQuestion;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\CompanyStatsService;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use App\Services\UsageCollectorService;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CompanyShow extends Component
{
    use WithFileUploads, WithPagination;

    public int $companyId;

    public ?Company $company = null;

    public ?Subscription $subscription = null;

    public array $stats = [];

    public array $dashboardStats = [];

    public string $activeTab = 'info';

    public bool $showSuspendModal = false;

    public string $suspendReason = '';

    public bool $showChangePlanModal = false;

    public ?int $newPlanId = null;

    public bool $showPaymentModal = false;

    public ?int $selectedPaymentId = null;

    public $receiptFile = null;

    public string $transactionReference = '';

    public string $paymentNotes = '';

    // ── Edit Subscription Modal ──
    public bool $showEditSubscriptionModal = false;

    public string $editStartedAt = '';

    public int $editBillingDay = 1;

    public string $editNextBillingDate = '';

    public string $editGracePeriodEnd = '';

    public string $editBillingMode = 'from_plan';

    public string $editCustomRecurringAmount = '0.00';

    public string $editDiscountAmount = '0';

    public string $editDiscountReason = '';

    public bool $editAutoRenew = true;

    // ── Edit User Modal ──
    public bool $showEditUserModal = false;

    public ?int $editUserId = null;

    public string $editUserName = '';

    public string $editUserEmail = '';

    public string $editUserPassword = '';

    public string $editUserPasswordConfirmation = '';

    public function mount(int $companyId): void
    {
        if (! auth()->user() || ! auth()->user()->canAccessPlatformConsole()) {
            abort(403);
        }

        $this->companyId = $companyId;
        $this->loadCompany();
    }

    public function loadCompany(): void
    {
        $this->company = Company::with(['subscription.plan', 'subscription.latestPayment'])
            ->withCount(['users', 'customers', 'products', 'sales'])
            ->findOrFail($this->companyId);

        $this->subscription = $this->company->subscription;

        $usageCollector = app(UsageCollectorService::class);
        $this->stats = $usageCollector->getCompanyStats($this->companyId);

        $this->dashboardStats = app(CompanyStatsService::class)->getDashboardStats($this->companyId);
    }

    protected function toast(string $message, string $type = 'success'): void
    {
        $titles = ['success' => 'Listo', 'error' => 'Atención', 'warning' => 'Atención', 'info' => 'Información'];
        $uiType = in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info';
        $title = $titles[$uiType] ?? $titles['info'];
        $timeout = $uiType === 'error' ? 7200 : 4800;
        $options = json_encode(['type' => $uiType, 'title' => $title, 'timeout' => $timeout, 'theme' => 'futuristic'], JSON_THROW_ON_ERROR);
        $msg = json_encode($message, JSON_THROW_ON_ERROR);
        $this->js('if (window.uiNotifications && typeof window.uiNotifications.showToast === "function") {'
            .'window.uiNotifications.showToast('.$msg.', '.$options.');}');
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ── Edit Subscription Modal ──
    public function openEditSubscriptionModal(): void
    {
        $sub = $this->subscription;
        $this->editStartedAt = $sub?->started_at?->format('Y-m-d') ?? '';
        $this->editBillingDay = $sub?->billing_day ?? 1;
        $this->editNextBillingDate = $sub?->next_billing_date?->format('Y-m-d') ?? '';
        $this->editGracePeriodEnd = $sub?->grace_period_end?->format('Y-m-d') ?? '';
        $this->editBillingMode = $sub?->billing_mode ?? 'from_plan';
        $this->editCustomRecurringAmount = $sub && $sub->custom_recurring_amount !== null
            ? number_format((float) $sub->custom_recurring_amount, 2, '.', '')
            : number_format((float) ($sub?->plan?->base_price ?? 0), 2, '.', '');
        $this->editDiscountAmount = $sub ? number_format((float) ($sub->discount_amount ?? 0), 2, '.', '') : '0.00';
        $this->editDiscountReason = $sub?->discount_reason ?? '';
        $this->editAutoRenew = $sub?->auto_renew ?? true;
        $this->showEditSubscriptionModal = true;
    }

    public function closeEditSubscriptionModal(): void
    {
        $this->showEditSubscriptionModal = false;
    }

    public function saveSubscription(): void
    {
        if (! $this->subscription) {
            $this->toast('No hay suscripción para editar.', 'error');

            return;
        }

        $this->validate([
            'editStartedAt' => 'required|date',
            'editBillingDay' => 'required|integer|min:1|max:28',
            'editNextBillingDate' => 'required|date',
            'editGracePeriodEnd' => 'nullable|date|after_or_equal:editNextBillingDate',
            'editBillingMode' => 'required|in:from_plan,custom',
            'editCustomRecurringAmount' => 'required_if:editBillingMode,custom|nullable|numeric|min:0',
            'editDiscountAmount' => 'nullable|numeric|min:0',
            'editDiscountReason' => 'nullable|string|max:500',
            'editAutoRenew' => 'boolean',
        ]);

        $discount = (float) ($this->editDiscountAmount ?: 0);
        $subService = app(SubscriptionService::class);

        $this->subscription->update([
            'started_at' => $this->editStartedAt,
            'billing_day' => (int) $this->editBillingDay,
            'next_billing_date' => $this->editNextBillingDate,
            'grace_period_end' => $this->editGracePeriodEnd ?: null,
            'discount_amount' => $discount,
            'discount_reason' => $this->editDiscountReason ?: null,
            'auto_renew' => $this->editAutoRenew,
        ]);

        if ($this->editBillingMode === 'custom') {
            $custom = (float) $this->editCustomRecurringAmount;
            $this->subscription->update([
                'billing_mode' => 'custom',
                'custom_recurring_amount' => $custom,
                'amount' => max(0, round($custom - $discount, 2)),
            ]);
        } else {
            $this->subscription->update([
                'billing_mode' => 'from_plan',
                'custom_recurring_amount' => null,
            ]);
            $this->subscription->refresh();
            $plan = $this->subscription->plan;
            if ($plan) {
                $this->subscription->update([
                    'amount' => $subService->calculateAmount($this->company, $plan),
                ]);
            }
        }

        $this->closeEditSubscriptionModal();
        $this->loadCompany();
        $this->toast('Suscripción actualizada correctamente.', 'success');
    }

    // ── Edit User Modal ──
    public function openEditUserModal(int $userId): void
    {
        $user = User::where('company_id', $this->companyId)->findOrFail($userId);
        $this->editUserId = $user->id;
        $this->editUserName = $user->name;
        $this->editUserEmail = $user->email;
        $this->editUserPassword = '';
        $this->editUserPasswordConfirmation = '';
        $this->showEditUserModal = true;
    }

    public function closeEditUserModal(): void
    {
        $this->showEditUserModal = false;
        $this->editUserId = null;
    }

    public function saveUser(): void
    {
        if (! $this->editUserId) {
            $this->toast('No hay usuario seleccionado.', 'error');

            return;
        }

        $user = User::where('company_id', $this->companyId)->findOrFail($this->editUserId);

        $rules = [
            'editUserName' => 'required|string|max:255',
            'editUserEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ];

        if ($this->editUserPassword !== '') {
            $rules['editUserPassword'] = 'required|string|min:8|confirmed';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->editUserName,
            'email' => $this->editUserEmail,
        ];

        if ($this->editUserPassword !== '') {
            $data['password'] = Hash::make($this->editUserPassword);
        }

        $user->update($data);

        $this->closeEditUserModal();
        $this->toast('Usuario actualizado correctamente.', 'success');
    }

    public function resetSecurityQuestions(int $userId): void
    {
        $user = User::where('company_id', $this->companyId)->findOrFail($userId);
        SecurityQuestion::where('user_id', $user->id)->delete();
        $user->update(['security_questions_setup' => false]);
        $this->toast("Preguntas de seguridad de {$user->name} reseteada(s). Deberá configurarlas al iniciar sesión.", 'success');
    }

    public function openSuspendModal(): void
    {
        $this->suspendReason = '';
        $this->showSuspendModal = true;
    }

    public function closeSuspendModal(): void
    {
        $this->showSuspendModal = false;
        $this->suspendReason = '';
    }

    public function suspend(): void
    {
        if (! $this->subscription) {
            $this->toast('Esta empresa no tiene suscripción activa.', 'error');

            return;
        }

        try {
            app(SubscriptionService::class)->suspend($this->subscription, $this->suspendReason);
            $this->closeSuspendModal();
            $this->loadCompany();
            $this->toast('Empresa suspendida correctamente.', 'success');
        } catch (\Throwable $e) {
            $this->toast('Error al suspender: '.$e->getMessage(), 'error');
        }
    }

    public function activate(): void
    {
        if (! $this->subscription) {
            $this->toast('Esta empresa no tiene suscripción.', 'error');

            return;
        }

        try {
            app(SubscriptionService::class)->activate($this->subscription);
            $this->loadCompany();
            $this->toast('Empresa reactivada correctamente.', 'success');
        } catch (\Throwable $e) {
            $this->toast('Error al reactivar: '.$e->getMessage(), 'error');
        }
    }

    public function openChangePlanModal(): void
    {
        $this->newPlanId = $this->subscription?->plan_id;
        $this->showChangePlanModal = true;
    }

    public function closeChangePlanModal(): void
    {
        $this->showChangePlanModal = false;
    }

    public function changePlan(): void
    {
        if (! $this->newPlanId) {
            $this->toast('Selecciona un plan válido.', 'warning');

            return;
        }

        try {
            $plan = Plan::findOrFail($this->newPlanId);
            $subService = app(SubscriptionService::class);

            if (! $this->subscription) {
                $subService->createForCompany($this->company, $plan, 1);
                $this->company->update(['subscription_status' => 'active']);
                $message = 'Plan asignado correctamente. Se creó la suscripción para esta empresa.';
            } else {
                $subService->changePlan($this->subscription, $plan);
                $message = 'Plan actualizado correctamente.';
            }

            $this->closeChangePlanModal();
            $this->loadCompany();
            $this->toast($message, 'success');
        } catch (\Throwable $e) {
            $this->toast('Error al cambiar plan: '.$e->getMessage(), 'error');
        }
    }

    public function openPaymentModal(int $paymentId): void
    {
        $this->selectedPaymentId = $paymentId;
        $this->receiptFile = null;
        $this->transactionReference = '';
        $this->paymentNotes = '';
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->selectedPaymentId = null;
    }

    public function markAsPaid(): void
    {
        if (! $this->selectedPaymentId) {
            return;
        }

        $payment = SubscriptionPayment::findOrFail($this->selectedPaymentId);

        $this->validate([
            'receiptFile' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:4096',
            'transactionReference' => 'nullable|string|max:255',
        ]);

        try {
            $paymentService = app(PaymentService::class);
            $paymentService->markAsPaid(
                $payment,
                (int) auth()->id(),
                $this->receiptFile
            );

            $payment->update([
                'transaction_reference' => $this->transactionReference ?: null,
                'notes' => $this->paymentNotes ?: null,
            ]);

            $this->closePaymentModal();
            $this->loadCompany();
            $this->toast('Pago registrado correctamente.', 'success');
        } catch (\Throwable $e) {
            $this->toast('Error al registrar pago: '.$e->getMessage(), 'error');
        }
    }

    public function getPaymentsProperty()
    {
        if (! $this->subscription) {
            return new LengthAwarePaginator([], 0, 10, 1, ['pageName' => 'payments-page']);
        }

        return SubscriptionPayment::where('subscription_id', $this->subscription->id)
            ->orderBy('due_date', 'desc')
            ->paginate(10, pageName: 'payments-page');
    }

    public function render(): View
    {
        $plans = Plan::active()->orderBy('name')->get();
        $payments = $this->payments;
        $users = User::where('company_id', $this->companyId)
            ->with('roles')
            ->orderBy('name')
            ->get();

        return view('livewire.super-admin.company-show', [
            'plans' => $plans,
            'payments' => $payments,
            'dashboardStats' => $this->dashboardStats,
            'users' => $users,
        ]);
    }
}
