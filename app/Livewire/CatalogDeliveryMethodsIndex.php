<?php

namespace App\Livewire;

use App\Models\CompanyDeliveryMethod;
use App\Models\Order;
use App\Services\CatalogCheckoutBulkDeleteService;
use App\Services\PlanEntitlementService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CatalogDeliveryMethodsIndex extends Component
{
    use WithPagination;

    public string $search = '';

    /** '', 'yes', 'no' */
    public string $activeOnly = '';

    /** '', 'pickup', 'delivery' */
    public string $typeFilter = '';

    public int $perPage = 10;

    public bool $selectionMode = false;

    /** @var array<int> */
    public array $selectedDeliveryMethodIds = [];

    public bool $showBulkDeleteModal = false;

    public bool $showDetailModal = false;

    /** @var array<string, mixed>|null */
    public ?array $detailDeliveryMethod = null;

    public bool $showDeleteModal = false;

    public ?int $deleteTargetId = null;

    public string $deleteTargetName = '';

    protected int $companyId = 0;

    public function hydrate(): void
    {
        $this->companyId = (int) (Auth::user()?->company_id ?? 0);
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'activeOnly' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    protected function entitlement(): PlanEntitlementService
    {
        return app(PlanEntitlementService::class);
    }

    protected function authorizeDeliveries(string $abilitySuffix): void
    {
        abort_unless(
            $this->entitlement()->tenantUserMayUseCatalogDeliveriesAbility(Auth::user(), $abilitySuffix),
            403
        );
    }

    public function mount(): void
    {
        $this->authorizeDeliveries('index');
        $this->companyId = (int) Auth::user()->company_id;
    }

    public function updated($name, $value = null): void
    {
        if (in_array($name, ['search', 'activeOnly', 'typeFilter', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->activeOnly = '';
        $this->typeFilter = '';
        $this->resetPage();
    }

    public function updatingPage(): void
    {
        $this->selectedDeliveryMethodIds = [];
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
        $this->authorizeDeliveries('show');

        $m = CompanyDeliveryMethod::query()
            ->withCount(['orders', 'zones', 'deliverySlots'])
            ->where('company_id', $this->companyId)
            ->whereKey($id)
            ->firstOrFail();

        $this->detailDeliveryMethod = [
            'id' => $m->id,
            'name' => $m->name,
            'type' => $m->type,
            'type_label' => $m->type === CompanyDeliveryMethod::TYPE_DELIVERY ? 'Delivery' : 'Entrega',
            'pickup_address' => (string) ($m->pickup_address ?? ''),
            'instructions' => trim((string) ($m->instructions ?? '')) ?: '—',
            'sort_order' => (int) $m->sort_order,
            'is_active' => (bool) $m->is_active,
            'orders_count' => (int) $m->orders_count,
            'zones_count' => (int) $m->zones_count,
            'delivery_slots_count' => (int) $m->delivery_slots_count,
            'created_at' => $m->created_at->format('d/m/Y H:i'),
            'updated_at' => $m->updated_at->format('d/m/Y H:i'),
        ];
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailDeliveryMethod = null;
    }

    public function openDeleteModal(int $id): void
    {
        $this->authorizeDeliveries('destroy');

        $m = CompanyDeliveryMethod::query()
            ->where('company_id', $this->companyId)
            ->whereKey($id)
            ->first();

        if (! $m) {
            $this->toast('Método no encontrado.', 'error');

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

    public function confirmDeleteDeliveryMethod(): void
    {
        if ($this->deleteTargetId === null) {
            return;
        }

        $id = $this->deleteTargetId;
        $this->closeDeleteModal();

        $this->authorizeDeliveries('destroy');

        if (Order::query()->where('company_delivery_method_id', $id)->exists()) {
            $this->toast('No se puede eliminar: hay pedidos que usan este método.', 'error');

            return;
        }

        $deleted = CompanyDeliveryMethod::query()
            ->where('company_id', $this->companyId)
            ->whereKey($id)
            ->delete();

        if ($deleted === 0) {
            $this->toast('Método no encontrado.', 'error');

            return;
        }

        $this->selectedDeliveryMethodIds = array_values(array_diff($this->selectedDeliveryMethodIds, [$id]));
        $this->toast('Método de entrega eliminado.', 'success');
        $this->resetPage();
    }

    public function toggleSelectionMode(): void
    {
        $this->selectionMode = ! $this->selectionMode;

        if (! $this->selectionMode) {
            $this->selectedDeliveryMethodIds = [];
            $this->closeBulkDeleteModal();
        }
    }

    public function toggleDeliveryMethodSelection(int $id): void
    {
        if (! $this->selectionMode) {
            return;
        }

        if (in_array($id, $this->selectedDeliveryMethodIds, true)) {
            $this->selectedDeliveryMethodIds = array_values(array_diff($this->selectedDeliveryMethodIds, [$id]));
        } else {
            $this->selectedDeliveryMethodIds[] = $id;
            $this->selectedDeliveryMethodIds = array_values(array_unique(array_map('intval', $this->selectedDeliveryMethodIds)));
        }
    }

    public function toggleSelectAllCurrentPage(): void
    {
        if (! $this->selectionMode) {
            return;
        }

        $pageIds = $this->deliveryMethodsQuery()
            ->paginate($this->perPage)
            ->pluck('id')
            ->map(fn ($pid) => (int) $pid)
            ->all();

        $allSelected = $pageIds !== []
            && count(array_intersect($pageIds, $this->selectedDeliveryMethodIds)) === count($pageIds);

        if ($allSelected) {
            $this->selectedDeliveryMethodIds = array_values(array_diff($this->selectedDeliveryMethodIds, $pageIds));
        } else {
            $this->selectedDeliveryMethodIds = array_values(array_unique(array_merge($this->selectedDeliveryMethodIds, $pageIds)));
        }
    }

    public function openBulkDeleteModal(): void
    {
        $this->authorizeDeliveries('destroy');

        if ($this->selectedDeliveryMethodIds === []) {
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
        $this->authorizeDeliveries('destroy');

        if ($this->selectedDeliveryMethodIds === []) {
            $this->closeBulkDeleteModal();

            return;
        }

        try {
            $results = $bulk->bulkDeleteDeliveryMethods($this->companyId, $this->selectedDeliveryMethodIds);

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
            $this->selectedDeliveryMethodIds = [];
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

    protected function deliveryMethodsQuery()
    {
        $query = CompanyDeliveryMethod::query()
            ->withCount(['orders', 'zones', 'deliverySlots'])
            ->where('company_id', $this->companyId);

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('instructions', 'like', $term)
                    ->orWhere('pickup_address', 'like', $term);
            });
        }

        if ($this->activeOnly === 'yes') {
            $query->where('is_active', true);
        } elseif ($this->activeOnly === 'no') {
            $query->where('is_active', false);
        }

        if ($this->typeFilter === 'pickup') {
            $query->where('type', CompanyDeliveryMethod::TYPE_PICKUP);
        } elseif ($this->typeFilter === 'delivery') {
            $query->where('type', CompanyDeliveryMethod::TYPE_DELIVERY);
        }

        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function render(): View
    {
        $methodsPaginator = $this->deliveryMethodsQuery()->paginate($this->perPage);

        $currentPageIds = $methodsPaginator->pluck('id')->map(fn ($pid) => (int) $pid)->all();
        $allCurrentPageSelected = $currentPageIds !== []
            && count(array_intersect($currentPageIds, $this->selectedDeliveryMethodIds)) === count($currentPageIds);

        $base = CompanyDeliveryMethod::query()->withCount(['orders', 'zones', 'deliverySlots'])->where('company_id', $this->companyId);
        $total = (clone $base)->count();
        $delivery = (clone $base)->where('type', CompanyDeliveryMethod::TYPE_DELIVERY)->count();
        $pickup = (clone $base)->where('type', CompanyDeliveryMethod::TYPE_PICKUP)->count();
        $active = (clone $base)->where('is_active', true)->count();

        $user = Auth::user();
        $svc = $this->entitlement();

        return view('livewire.catalog-delivery-methods-index', [
            'deliveryMethods' => $methodsPaginator,
            'permFlags' => [
                'can_report' => $svc->tenantUserMayUseCatalogDeliveriesAbility($user, 'report'),
                'can_create' => $svc->tenantUserMayUseCatalogDeliveriesAbility($user, 'create'),
                'can_edit' => $svc->tenantUserMayUseCatalogDeliveriesAbility($user, 'edit'),
                'can_show' => $svc->tenantUserMayUseCatalogDeliveriesAbility($user, 'show'),
                'can_destroy' => $svc->tenantUserMayUseCatalogDeliveriesAbility($user, 'destroy'),
            ],
            'stats' => [
                'total' => $total,
                'delivery' => $delivery,
                'pickup' => $pickup,
                'active' => $active,
            ],
            'allCurrentPageSelected' => $allCurrentPageSelected,
            'filtersOpen' => $this->search !== '' || $this->activeOnly !== '' || $this->typeFilter !== '',
        ]);
    }
}
