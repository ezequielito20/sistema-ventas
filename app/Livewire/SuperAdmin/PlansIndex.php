<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Plan;
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

    public bool $hasSales = false;

    public bool $hasPurchases = false;

    public bool $hasReports = false;

    public bool $hasCustomers = false;

    public bool $hasProducts = false;

    public bool $hasCategories = false;

    public bool $hasCashCounts = false;

    public bool $showDeleteModal = false;

    public ?int $deleteTargetId = null;

    public string $deleteTargetName = '';

    public function mount(): void
    {
        if (!auth()->user() || !auth()->user()->isSuperAdmin()) {
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
            . 'window.uiNotifications.showToast(' . $msg . ', ' . $options . ');}');
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditing = false;
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

        $features = $plan->features ?? [];
        $this->hasSales = in_array('sales', $features);
        $this->hasPurchases = in_array('purchases', $features);
        $this->hasReports = in_array('reports', $features);
        $this->hasCustomers = in_array('customers', $features);
        $this->hasProducts = in_array('products', $features);
        $this->hasCategories = in_array('categories', $features);
        $this->hasCashCounts = in_array('cash_counts', $features);

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
        $this->hasSales = false;
        $this->hasPurchases = false;
        $this->hasReports = false;
        $this->hasCustomers = false;
        $this->hasProducts = false;
        $this->hasCategories = false;
        $this->hasCashCounts = false;
    }

    protected function buildFeatures(): array
    {
        $features = [];
        if ($this->hasSales) $features[] = 'sales';
        if ($this->hasPurchases) $features[] = 'purchases';
        if ($this->hasReports) $features[] = 'reports';
        if ($this->hasCustomers) $features[] = 'customers';
        if ($this->hasProducts) $features[] = 'products';
        if ($this->hasCategories) $features[] = 'categories';
        if ($this->hasCashCounts) $features[] = 'cash_counts';
        return $features;
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
        ];

        if ($this->isEditing) {
            $rules['slug'] = ['required', 'string', 'max:255', 'unique:plans,slug,' . $this->editingPlanId];
        } else {
            $rules['slug'] = ['required', 'string', 'max:255', 'unique:plans,slug'];
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description ?: null,
            'base_price' => $this->basePrice,
            'price_per_user' => $this->pricePerUser,
            'price_per_transaction' => $this->pricePerTransaction,
            'limits' => json_encode([
                'max_users' => $this->maxUsers,
                'max_transactions' => $this->maxTransactions,
                'max_products' => $this->maxProducts,
                'max_customers' => $this->maxCustomers,
            ]),
            'features' => json_encode($this->buildFeatures()),
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
            $this->toast('Error al guardar el plan: ' . $e->getMessage(), 'error');
        }
    }

    public function openDeleteModal(int $id): void
    {
        $plan = Plan::find($id);
        if (!$plan) {
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
        if ($this->deleteTargetId === null) return;

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
            $this->toast('Error al eliminar: ' . $e->getMessage(), 'error');
        }
    }

    public function render(): View
    {
        $query = Plan::query()->withCount('subscriptions');

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'ILIKE', '%' . $s . '%')
                    ->orWhere('slug', 'ILIKE', '%' . $s . '%');
            });
        }

        $plans = $query->orderBy('name')->get();

        return view('livewire.super-admin.plans-index', [
            'plans' => $plans,
        ]);
    }
}
