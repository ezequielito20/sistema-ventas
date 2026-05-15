<?php

namespace App\Livewire;

use App\Models\CompanyPaymentMethod;
use App\Models\Order;
use App\Services\CatalogCheckoutBulkDeleteService;
use App\Services\PlanEntitlementService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CatalogPaymentMethodsIndex extends Component
{
    use WithPagination;

    public string $search = '';

    /** '', 'yes', 'no' */
    public string $activeOnly = '';

    public int $perPage = 10;

    public bool $selectionMode = false;

    /** @var array<int> */
    public array $selectedPaymentMethodIds = [];

    public bool $showBulkDeleteModal = false;

    public bool $showDetailModal = false;

    /** @var array<string, mixed>|null */
    public ?array $detailPaymentMethod = null;

    public bool $showDeleteModal = false;

    public ?int $deleteTargetId = null;

    public string $deleteTargetName = '';

    protected int $companyId = 0;

    protected function entitlement(): PlanEntitlementService
    {
        return app(PlanEntitlementService::class);
    }

    protected function authorizePayments(string $abilitySuffix): void
    {
        abort_unless(
            $this->entitlement()->tenantUserMayUseCatalogPaymentsAbility(Auth::user(), $abilitySuffix),
            403
        );
    }

    public function mount(): void
    {
        $this->authorizePayments('index');
        $this->companyId = (int) Auth::user()->company_id;
    }

    public function updated($name, $value = null): void
    {
        if (in_array($name, ['search', 'activeOnly', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->activeOnly = '';
        $this->resetPage();
    }

    public function updatingPage(): void
    {
        $this->selectedPaymentMethodIds = [];
    }

    public function setPerPage(int $value): void
    {
        $allowed = [10, 25, 50, 100];
        if (! in_array($value, $allowed, true)) {
            $value = 10;
        }
        $this->perPage = $value;
        $this->resetPage();
    }

    protected function toast(string $message, string $type = 'success'): void
    {
        $titles = [
            'success' => 'Listo',
            'error' => 'Atención',
            'warning' => 'Atención',
            'info' => 'Información',
        ];
        $uiType = in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info';
        $title = $titles[$uiType] ?? $titles['info'];
        $timeout = $uiType === 'error' ? 7200 : 4800;

        $options = json_encode([
            'type' => $uiType,
            'title' => $title,
            'timeout' => $timeout,
            'theme' => 'futuristic',
        ], JSON_THROW_ON_ERROR);

        $msg = json_encode($message, JSON_THROW_ON_ERROR);

        $this->js(
            'if (window.uiNotifications && typeof window.uiNotifications.showToast === "function") {'
            .'window.uiNotifications.showToast('.$msg.', '.$options.');}'
        );
    }

    public function openDetailModal(int $id): void
    {
        $this->authorizePayments('show');

        $m = CompanyPaymentMethod::query()
            ->withCount('orders')
            ->where('company_id', $this->companyId)
            ->whereKey($id)
            ->firstOrFail();

        $this->detailPaymentMethod = [
            'id' => $m->id,
            'name' => $m->name,
            'discount_percent' => (string) $m->discount_percent,
            'sort_order' => (int) $m->sort_order,
            'is_active' => (bool) $m->is_active,
            'orders_count' => (int) $m->orders_count,
            'instructions' => trim((string) ($m->instructions ?? '')) ?: '—',
            'created_at' => $m->created_at->format('d/m/Y H:i'),
            'updated_at' => $m->updated_at->format('d/m/Y H:i'),
        ];
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailPaymentMethod = null;
    }

    public function openDeleteModal(int $id): void
    {
        $this->authorizePayments('destroy');

        $m = CompanyPaymentMethod::query()
            ->where('company_id', $this->companyId)
            ->whereKey($id)
            ->first();

        if (! $m) {
            $this->toast('Método de pago no encontrado.', 'error');

            return;
        }

        $this->deleteTargetId = $id;
        $this->deleteTargetName = $m->name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
        $this->deleteTargetName = '';
    }

    public function confirmDeletePaymentMethod(): void
    {
        if ($this->deleteTargetId === null) {
            return;
        }

        $id = $this->deleteTargetId;
        $this->closeDeleteModal();

        $this->authorizePayments('destroy');

        if (Order::query()->where('company_payment_method_id', $id)->exists()) {
            $this->toast('No se puede eliminar: hay pedidos que usan este método.', 'error');

            return;
        }

        $deleted = CompanyPaymentMethod::query()
            ->where('company_id', $this->companyId)
            ->whereKey($id)
            ->delete();

        if ($deleted === 0) {
            $this->toast('Método no encontrado.', 'error');

            return;
        }

        $this->selectedPaymentMethodIds = array_values(array_diff($this->selectedPaymentMethodIds, [$id]));
        $this->toast('Método de pago eliminado.', 'success');
        $this->resetPage();
    }

    public function toggleSelectionMode(): void
    {
        $this->selectionMode = ! $this->selectionMode;

        if (! $this->selectionMode) {
            $this->selectedPaymentMethodIds = [];
            $this->closeBulkDeleteModal();
        }
    }

    public function togglePaymentMethodSelection(int $id): void
    {
        if (! $this->selectionMode) {
            return;
        }

        if (in_array($id, $this->selectedPaymentMethodIds, true)) {
            $this->selectedPaymentMethodIds = array_values(array_diff($this->selectedPaymentMethodIds, [$id]));
        } else {
            $this->selectedPaymentMethodIds[] = $id;
            $this->selectedPaymentMethodIds = array_values(array_unique(array_map('intval', $this->selectedPaymentMethodIds)));
        }
    }

    public function toggleSelectAllCurrentPage(): void
    {
        if (! $this->selectionMode) {
            return;
        }

        $pageIds = $this->paymentMethodsQuery()
            ->paginate($this->perPage)
            ->pluck('id')
            ->map(fn ($pid) => (int) $pid)
            ->all();

        $allSelected = $pageIds !== []
            && count(array_intersect($pageIds, $this->selectedPaymentMethodIds)) === count($pageIds);

        if ($allSelected) {
            $this->selectedPaymentMethodIds = array_values(array_diff($this->selectedPaymentMethodIds, $pageIds));
        } else {
            $this->selectedPaymentMethodIds = array_values(array_unique(array_merge($this->selectedPaymentMethodIds, $pageIds)));
        }
    }

    public function openBulkDeleteModal(): void
    {
        $this->authorizePayments('destroy');

        if ($this->selectedPaymentMethodIds === []) {
            $this->toast('Selecciona al menos un método para continuar.', 'warning');

            return;
        }

        $this->showBulkDeleteModal = true;
    }

    public function closeBulkDeleteModal(): void
    {
        $this->showBulkDeleteModal = false;
    }

    public function confirmBulkDelete(CatalogCheckoutBulkDeleteService $bulk): void
    {
        $this->authorizePayments('destroy');

        if ($this->selectedPaymentMethodIds === []) {
            $this->closeBulkDeleteModal();

            return;
        }

        try {
            $results = $bulk->bulkDeletePaymentMethods($this->companyId, $this->selectedPaymentMethodIds);

            $deleted = array_values(array_filter($results, fn ($r) => $r['deleted'] === true));
            $blocked = array_values(array_filter($results, fn ($r) => $r['deleted'] === false));

            $messages = [];

            if ($deleted !== []) {
                $messages[] = count($deleted).' método(s) eliminado(s)';
            }

            if ($blocked !== []) {
                $blockedSummary = collect($blocked)
                    ->take(4)
                    ->map(fn ($r) => $r['name'].': '.($r['reason'] ?? ''))
                    ->implode(' | ');

                if (count($blocked) > 4) {
                    $blockedSummary .= ' | y '.(count($blocked) - 4).' más';
                }

                $messages[] = 'No eliminados: '.$blockedSummary;
            }

            if ($messages === []) {
                $messages[] = 'No hubo cambios en la selección.';
            }

            $this->closeBulkDeleteModal();
            $this->selectedPaymentMethodIds = [];
            $this->selectionMode = false;
            $this->resetPage();

            $this->toast(
                implode('. ', $messages).'.',
                $blocked !== [] ? 'warning' : 'success'
            );
        } catch (\Throwable $e) {
            $this->closeBulkDeleteModal();
            $this->toast('Error al eliminar: '.$e->getMessage(), 'error');
        }
    }

    protected function paymentMethodsQuery()
    {
        $query = CompanyPaymentMethod::query()
            ->withCount('orders')
            ->where('company_id', $this->companyId);

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('instructions', 'like', $term);
            });
        }

        if ($this->activeOnly === 'yes') {
            $query->where('is_active', true);
        } elseif ($this->activeOnly === 'no') {
            $query->where('is_active', false);
        }

        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function render(): View
    {
        $methodsPaginator = $this->paymentMethodsQuery()->paginate($this->perPage);

        $currentPageIds = $methodsPaginator->pluck('id')->map(fn ($pid) => (int) $pid)->all();
        $allCurrentPageSelected = $currentPageIds !== []
            && count(array_intersect($currentPageIds, $this->selectedPaymentMethodIds)) === count($currentPageIds);

        $base = CompanyPaymentMethod::query()->withCount('orders')->where('company_id', $this->companyId);
        $total = (clone $base)->count();
        $active = (clone $base)->where('is_active', true)->count();
        $withOrders = (clone $base)->has('orders')->count();

        $user = Auth::user();
        $svc = $this->entitlement();

        return view('livewire.catalog-payment-methods-index', [
            'paymentMethods' => $methodsPaginator,
            'permFlags' => [
                'can_report' => $svc->tenantUserMayUseCatalogPaymentsAbility($user, 'report'),
                'can_create' => $svc->tenantUserMayUseCatalogPaymentsAbility($user, 'create'),
                'can_edit' => $svc->tenantUserMayUseCatalogPaymentsAbility($user, 'edit'),
                'can_show' => $svc->tenantUserMayUseCatalogPaymentsAbility($user, 'show'),
                'can_destroy' => $svc->tenantUserMayUseCatalogPaymentsAbility($user, 'destroy'),
            ],
            'stats' => [
                'total' => $total,
                'active' => $active,
                'with_orders' => $withOrders,
            ],
            'allCurrentPageSelected' => $allCurrentPageSelected,
            'filtersOpen' => $this->search !== '' || $this->activeOnly !== '',
        ]);
    }
}
