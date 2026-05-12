<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\SubscriptionService;
use App\Services\UsageCollectorService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CompaniesIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $planFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public int $perPage = 10;

    public bool $showDetailModal = false;

    public ?array $detailCompany = null;

    public bool $showDeleteModal = false;

    public ?int $deleteTargetId = null;

    public string $deleteTargetName = '';

    public bool $selectionMode = false;

    public array $selectedIds = [];

    public bool $showBulkDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'planFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        if (!auth()->user() || !auth()->user()->isSuperAdmin()) {
            abort(403);
        }
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingPlanFilter(): void { $this->resetPage(); }
    public function updatingDateFrom(): void { $this->resetPage(); }
    public function updatingDateTo(): void { $this->resetPage(); }
    public function updatingPerPage(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->planFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function updatingPage(): void
    {
        $this->selectedIds = [];
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

    public function openDetailModal(int $id): void
    {
        $company = Company::with(['subscription.plan', 'subscription.latestPayment'])
            ->withCount(['users', 'customers', 'products', 'sales'])
            ->findOrFail($id);

        $totalRevenue = (float) $company->sales()->sum('total_price');

        $this->detailCompany = [
            'id' => $company->id,
            'name' => $company->name,
            'nit' => $company->nit,
            'email' => $company->email,
            'phone' => $company->phone,
            'address' => $company->address,
            'business_type' => $company->business_type,
            'subscription_status' => $company->subscription_status,
            'plan_name' => $company->subscription?->plan?->name ?? 'Sin plan',
            'billing_day' => $company->subscription?->billing_day ?? 1,
            'next_billing_date' => $company->subscription?->next_billing_date?->format('d/m/Y') ?? 'N/A',
            'grace_period_end' => $company->subscription?->grace_period_end?->format('d/m/Y') ?? 'N/A',
            'last_payment_date' => $company->subscription?->latestPayment?->paid_at?->format('d/m/Y') ?? 'N/A',
            'last_payment_amount' => (float) ($company->subscription?->latestPayment?->amount ?? 0),
            'amount' => (float) ($company->subscription?->amount ?? 0),
            'users_count' => $company->users_count,
            'customers_count' => $company->customers_count,
            'products_count' => $company->products_count,
            'sales_count' => $company->sales_count,
            'total_revenue' => $totalRevenue,
            'created_at' => $company->created_at?->format('d/m/Y H:i'),
        ];

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailCompany = null;
    }

    public function openDeleteModal(int $id): void
    {
        $company = Company::find($id);
        if (!$company) {
            $this->toast('Empresa no encontrada.', 'error');
            return;
        }
        $this->deleteTargetId = $id;
        $this->deleteTargetName = $company->name;
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
            $company = Company::findOrFail($this->deleteTargetId);
            $name = $company->name;
            $company->delete();
            $this->closeDeleteModal();
            $this->toast("Empresa \"{$name}\" eliminada correctamente.", 'success');
        } catch (\Throwable $e) {
            $this->closeDeleteModal();
            $this->toast('Error al eliminar la empresa: ' . $e->getMessage(), 'error');
        }
    }

    public function toggleSelectionMode(): void
    {
        $this->selectionMode = !$this->selectionMode;
        if (!$this->selectionMode) {
            $this->selectedIds = [];
        }
    }

    public function toggleSelection(int $id): void
    {
        if (!$this->selectionMode) return;
        if (in_array($id, $this->selectedIds, true)) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, [$id]));
        } else {
            $this->selectedIds[] = $id;
        }
    }

    public function toggleSelectAll(): void
    {
        if (!$this->selectionMode) return;
        $pageCompanies = $this->query()
            ->withCount(['users', 'customers', 'products', 'sales', 'purchases'])
            ->paginate($this->perPage);

        // Only select companies without associated records
        $pageIds = $pageCompanies
            ->filter(fn ($c) => $c->users_count === 0 && $c->customers_count === 0 && $c->products_count === 0 && $c->sales_count === 0 && $c->purchases_count === 0)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $allSelected = $pageIds !== [] && count(array_intersect($pageIds, $this->selectedIds)) === count($pageIds);

        if ($allSelected) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, $pageIds));
        } else {
            $this->selectedIds = array_values(array_unique(array_merge($this->selectedIds, $pageIds)));
        }
    }

    public function openBulkDeleteModal(): void
    {
        if ($this->selectedIds === []) {
            $this->toast('Selecciona al menos una empresa.', 'warning');
            return;
        }
        $this->showBulkDeleteModal = true;
    }

    public function closeBulkDeleteModal(): void
    {
        $this->showBulkDeleteModal = false;
    }

    public function confirmBulkDelete(): void
    {
        if ($this->selectedIds === []) return;

        $deleted = 0;
        $skipped = 0;
        foreach ($this->selectedIds as $id) {
            try {
                $company = Company::withCount(['users', 'customers', 'products', 'sales', 'purchases'])->findOrFail($id);
                if ($company->users_count > 0 || $company->customers_count > 0 || $company->products_count > 0 || $company->sales_count > 0 || $company->purchases_count > 0) {
                    $skipped++;
                    continue;
                }
                $company->delete();
                $deleted++;
            } catch (\Throwable $e) {
                $skipped++;
            }
        }

        $this->closeBulkDeleteModal();
        $this->selectedIds = [];
        $this->selectionMode = false;
        $this->resetPage();

        $msg = "{$deleted} empresa(s) eliminada(s) correctamente.";
        if ($skipped > 0) {
            $msg .= " {$skipped} omitidas por tener registros.";
        }
        $this->toast($msg, $skipped > 0 ? 'warning' : 'success');
    }

    protected function query()
    {
        $query = Company::with(['subscription.plan:id,name', 'subscription.latestPayment'])
            ->withCount(['users', 'customers', 'products', 'sales', 'purchases']);

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

        if ($this->planFilter !== '') {
            $query->whereHas('subscription', function ($q) {
                $q->where('plan_id', (int) $this->planFilter);
            });
        }

        if ($this->dateFrom !== '') {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== '') {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query->orderBy('name');
    }

    public function render(): View
    {
        $companies = $this->query()->paginate($this->perPage);
        $currentPageIds = $companies->pluck('id')->map(fn ($id) => (int) $id)->all();
        $deletablePageIds = $companies
            ->filter(fn ($c) => $c->users_count === 0 && $c->customers_count === 0 && $c->products_count === 0 && $c->sales_count === 0 && $c->purchases_count === 0)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $allCurrentPageSelected = $deletablePageIds !== []
            && count(array_intersect($deletablePageIds, $this->selectedIds)) === count($deletablePageIds);

        $stats = [
            'total' => Company::count(),
            'active' => Company::where('subscription_status', 'active')->count(),
            'suspended' => Company::where('subscription_status', 'suspended')->count(),
            'trial' => Company::where('subscription_status', 'trial')->count(),
        ];

        $plans = Plan::active()->orderBy('name')->get();

        return view('livewire.super-admin.companies-index', [
            'companies' => $companies,
            'stats' => $stats,
            'plans' => $plans,
            'currentPageIds' => $currentPageIds,
            'allCurrentPageSelected' => $allCurrentPageSelected,
        ]);
    }
}
