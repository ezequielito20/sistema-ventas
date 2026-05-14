<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Plan;
use App\Support\ModuleRegistry;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PlansIndex extends Component
{
    public string $search = '';

    public bool $showFormModal = false;

    public bool $isEditing = false;

    public ?int $editingPlanId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public float $basePrice = 0;

    public float $pricePerUser = 0;

    public float $pricePerTransaction = 0;

    public ?int $maxUsers = null;

    public ?int $maxTransactions = null;

    public ?int $maxProducts = null;

    public ?int $maxCustomers = null;

    public bool $isActive = true;

    /** @var array<string, bool> */
    public array $moduleEnabled = [];

    /** @var array<string, string> límite por módulo (vacío = sin tope explícito en JSON) */
    public array $moduleLimit = [];

    public bool $showDeleteModal = false;

    public ?int $deleteTargetId = null;

    public string $deleteTargetName = '';

    public function mount(): void
    {
        if (! auth()->user() || ! auth()->user()->canAccessPlatformConsole()) {
            abort(403);
        }
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

    protected function syncModuleStateFromPlan(?Plan $plan): void
    {
        $this->moduleEnabled = [];
        $this->moduleLimit = [];
        $features = $plan?->features ?? [];
        $limits = $plan?->limits ?? [];

        foreach (ModuleRegistry::modulesForPlanForm() as $key => $def) {
            $this->moduleEnabled[$key] = $plan === null ? false : in_array($key, $features, true);
            $lim = $limits[$key] ?? null;
            $this->moduleLimit[$key] = $lim === null || $lim === '' ? '' : (string) (int) $lim;
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->syncModuleStateFromPlan(null);
        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $plan = Plan::findOrFail($id);
        $this->editingPlanId = $id;
        $this->isEditing = true;
        $this->name = $plan->name;
        $this->slug = $plan->slug;
        $this->description = $plan->description ?? '';
        $this->basePrice = (float) $plan->base_price;
        $this->pricePerUser = (float) $plan->price_per_user;
        $this->pricePerTransaction = (float) $plan->price_per_transaction;
        $this->maxUsers = $plan->max_users;
        $this->maxTransactions = $plan->max_transactions;
        $this->maxProducts = $plan->max_products;
        $this->maxCustomers = $plan->max_customers;
        $this->isActive = $plan->is_active;
        $this->syncModuleStateFromPlan($plan);
        $this->showFormModal = true;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editingPlanId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->basePrice = 0;
        $this->pricePerUser = 0;
        $this->pricePerTransaction = 0;
        $this->maxUsers = null;
        $this->maxTransactions = null;
        $this->maxProducts = null;
        $this->maxCustomers = null;
        $this->isActive = true;
        $this->moduleEnabled = [];
        $this->moduleLimit = [];
    }

    /**
     * @return list<string>
     */
    protected function buildFeatures(): array
    {
        $out = [];
        foreach (ModuleRegistry::modulesForPlanForm() as $key => $_def) {
            if (! empty($this->moduleEnabled[$key])) {
                $out[] = $key;
            }
        }

        return $out;
    }

    /**
     * @return array<string, int|null>
     */
    protected function buildLimitsArray(): array
    {
        $limits = [
            'max_users' => $this->maxUsers,
            'max_transactions' => $this->maxTransactions,
            'max_products' => $this->maxProducts,
            'max_customers' => $this->maxCustomers,
        ];

        foreach (ModuleRegistry::modulesForPlanForm() as $key => $def) {
            if (empty($def['limit_relation'])) {
                continue;
            }
            $raw = $this->moduleLimit[$key] ?? '';
            if ($raw === '' || $raw === null) {
                continue;
            }
            $limits[$key] = max(0, (int) $raw);
        }

        return $limits;
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'basePrice' => ['required', 'numeric', 'min:0'],
            'pricePerUser' => ['nullable', 'numeric', 'min:0'],
            'pricePerTransaction' => ['nullable', 'numeric', 'min:0'],
            'isActive' => ['boolean'],
            'moduleEnabled' => ['array'],
            'moduleLimit' => ['array'],
        ];

        if ($this->isEditing) {
            $rules['slug'] = ['required', 'string', 'max:255', 'unique:plans,slug,'.$this->editingPlanId];
        } else {
            $rules['slug'] = ['required', 'string', 'max:255', 'unique:plans,slug'];
        }

        foreach (ModuleRegistry::modulesForPlanForm() as $key => $def) {
            if (empty($def['limit_relation'])) {
                continue;
            }
            $rules['moduleLimit.'.$key] = ['nullable', 'integer', 'min:0'];
        }

        $this->validate($rules);

        $limits = $this->buildLimitsArray();
        $features = $this->buildFeatures();

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description ?: null,
            'base_price' => $this->basePrice,
            'price_per_user' => $this->pricePerUser,
            'price_per_transaction' => $this->pricePerTransaction,
            'limits' => $limits,
            'features' => $features,
            'max_users' => $this->maxUsers,
            'max_transactions' => $this->maxTransactions,
            'max_products' => $this->maxProducts,
            'max_customers' => $this->maxCustomers,
            'is_active' => $this->isActive,
        ];

        try {
            if ($this->isEditing) {
                Plan::findOrFail($this->editingPlanId)->update($data);
                $this->toast('Plan actualizado correctamente.', 'success');
            } else {
                Plan::create($data);
                $this->toast('Plan creado correctamente.', 'success');
            }
            $this->closeFormModal();
        } catch (\Throwable $e) {
            $this->toast('Error al guardar el plan: '.$e->getMessage(), 'error');
        }
    }

    public function openDeleteModal(int $id): void
    {
        $plan = Plan::find($id);
        if (! $plan) {
            $this->toast('Plan no encontrado.', 'error');

            return;
        }
        $this->deleteTargetId = $id;
        $this->deleteTargetName = $plan->name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
        $this->deleteTargetName = '';
    }

    public function confirmDelete(): void
    {
        if ($this->deleteTargetId === null) {
            return;
        }

        try {
            $plan = Plan::findOrFail($this->deleteTargetId);
            $name = $plan->name;

            if ($plan->subscriptions()->count() > 0) {
                $this->toast("El plan \"{$name}\" tiene suscripciones activas. Desactivalo en lugar de eliminarlo.", 'error');
                $this->closeDeleteModal();

                return;
            }

            $plan->delete();
            $this->closeDeleteModal();
            $this->toast("Plan \"{$name}\" eliminado correctamente.", 'success');
        } catch (\Throwable $e) {
            $this->closeDeleteModal();
            $this->toast('Error al eliminar: '.$e->getMessage(), 'error');
        }
    }

    public function render(): View
    {
        $query = Plan::query()->withCount('subscriptions');

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'ILIKE', '%'.$s.'%')
                    ->orWhere('slug', 'ILIKE', '%'.$s.'%');
            });
        }

        $plans = $query->orderBy('name')->get();

        return view('livewire.super-admin.plans-index', [
            'plans' => $plans,
            'planFormModules' => ModuleRegistry::modulesForPlanForm(),
        ]);
    }
}
