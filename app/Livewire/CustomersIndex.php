<?php

namespace App\Livewire;

use App\Services\CustomerBulkDeleteService;
use App\Services\CustomerListingService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class CustomersIndex extends Component
{
    use WithPagination;

    public string $search = '';

    /** '' | defaulters | current_debt */
    public string $filter = '';

    public int $perPage = 10;

    public bool $selectionMode = false;

    /** @var array<int> */
    public array $selectedCustomerIds = [];

    public bool $showBulkDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filter' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        Gate::authorize('customers.index');
    }

    public function updated($name, $value = null): void
    {
        if (in_array($name, ['search', 'filter', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->filter = '';
        $this->resetPage();
    }

    public function updatingPage(): void
    {
        $this->selectedCustomerIds = [];
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

    public function toggleSelectionMode(): void
    {
        $this->selectionMode = ! $this->selectionMode;

        if (! $this->selectionMode) {
            $this->selectedCustomerIds = [];
            $this->closeBulkDeleteModal();
        }
    }

    public function toggleCustomerSelection(int $id): void
    {
        if (! $this->selectionMode) {
            return;
        }

        if (in_array($id, $this->selectedCustomerIds, true)) {
            $this->selectedCustomerIds = array_values(array_diff($this->selectedCustomerIds, [$id]));
        } else {
            $this->selectedCustomerIds[] = $id;
            $this->selectedCustomerIds = array_values(array_unique(array_map('intval', $this->selectedCustomerIds)));
        }
    }

    public function toggleSelectAllCurrentPage(CustomerListingService $listingService): void
    {
        if (! $this->selectionMode) {
            return;
        }

        $companyId = (int) Auth::user()->company_id;
        $currency = $this->resolveCurrency();
        $filterParam = $this->filter !== '' ? $this->filter : null;
        $searchParam = $this->search !== '' ? $this->search : null;

        $payload = $listingService->getIndexPayload(
            $companyId,
            $currency,
            $searchParam,
            $filterParam,
            max(1, $this->perPage),
            $this->getPage(),
        );

        $pageIds = Collection::make($payload['customers']->items())
            ->pluck('id')
            ->map(fn ($cid) => (int) $cid)
            ->all();

        $allSelected = $pageIds !== []
            && count(array_intersect($pageIds, $this->selectedCustomerIds)) === count($pageIds);

        if ($allSelected) {
            $this->selectedCustomerIds = array_values(array_diff($this->selectedCustomerIds, $pageIds));
        } else {
            $this->selectedCustomerIds = array_values(array_unique(array_merge($this->selectedCustomerIds, $pageIds)));
        }
    }

    public function openBulkDeleteModal(): void
    {
        Gate::authorize('customers.destroy');

        if ($this->selectedCustomerIds === []) {
            $this->toast('Selecciona al menos un cliente para continuar.', 'warning');

            return;
        }

        $this->showBulkDeleteModal = true;
    }

    public function closeBulkDeleteModal(): void
    {
        $this->showBulkDeleteModal = false;
    }

    public function confirmBulkDelete(CustomerBulkDeleteService $bulkDeleteService): void
    {
        Gate::authorize('customers.destroy');

        if ($this->selectedCustomerIds === []) {
            $this->closeBulkDeleteModal();

            return;
        }

        try {
            $companyId = (int) Auth::user()->company_id;
            $results = $bulkDeleteService->bulkDeleteCustomers($companyId, $this->selectedCustomerIds);

            $deleted = array_values(array_filter($results, fn ($r) => $r['deleted'] === true));
            $blocked = array_values(array_filter($results, fn ($r) => $r['deleted'] === false));

            $messages = [];

            if ($deleted !== []) {
                $messages[] = count($deleted).' cliente(s) eliminado(s)';
            }

            if ($blocked !== []) {
                $blockedSummary = collect($blocked)
                    ->take(4)
                    ->map(fn ($r) => $r['name'].': '.$r['reason'])
                    ->implode(' | ');

                if (count($blocked) > 4) {
                    $blockedSummary .= ' | y '.(count($blocked) - 4).' más';
                }

                $messages[] = 'No eliminados: '.$blockedSummary;
            }

            if ($messages === []) {
                $messages[] = 'No hubo cambios en los clientes seleccionados.';
            }

            $this->closeBulkDeleteModal();
            $this->selectedCustomerIds = [];
            $this->selectionMode = false;
            $this->resetPage();

            $this->toast(
                implode('. ', $messages).'.',
                $blocked !== [] ? 'warning' : 'success'
            );
        } catch (\Throwable $e) {
            $this->closeBulkDeleteModal();
            $this->toast('Error al eliminar los clientes seleccionados: '.$e->getMessage(), 'error');
        }
    }

    /**
     * Filtro por segmento (misma lógica que el índice legacy: todos / arqueo actual / morosos).
     */
    public function setFilter(string $value): void
    {
        if (! in_array($value, ['', 'defaulters', 'current_debt'], true)) {
            return;
        }

        $this->filter = $value;
        $this->resetPage();
    }

    /**
     * @return object{id: int, name: string, code: string, symbol: string, country_id: int|null}
     */
    protected function resolveCurrency(): object
    {
        $company = Auth::user()->company;

        if ($company && $company->currency) {
            $row = DB::table('currencies')
                ->select('id', 'name', 'code', 'symbol', 'country_id')
                ->where('code', $company->currency)
                ->first();
            if ($row) {
                return $row;
            }
        }

        $fallback = DB::table('currencies')
            ->select('id', 'name', 'code', 'symbol', 'country_id')
            ->where('country_id', $company->country)
            ->first();

        return $fallback ?? (object) [
            'id' => 0,
            'name' => '',
            'code' => '',
            'symbol' => '$',
            'country_id' => null,
        ];
    }

    public function render(CustomerListingService $listingService): View
    {
        $companyId = (int) Auth::user()->company_id;
        $currency = $this->resolveCurrency();

        $filterParam = $this->filter !== '' ? $this->filter : null;
        $searchParam = $this->search !== '' ? $this->search : null;

        $payload = $listingService->getIndexPayload(
            $companyId,
            $currency,
            $searchParam,
            $filterParam,
            max(1, $this->perPage),
            $this->getPage(),
        );

        $permissions = [
            'can_report' => Gate::allows('customers.report'),
            'can_create' => Gate::allows('customers.create'),
            'can_edit' => Gate::allows('customers.edit'),
            'can_show' => Gate::allows('customers.show'),
            'can_destroy' => Gate::allows('customers.destroy'),
            'can_create_sales' => Gate::allows('sales.create'),
        ];

        $filtersOpen = $searchParam !== null || $filterParam !== null;

        $customers = $payload['customers'];
        $currentPageCustomerIds = Collection::make($customers->items())
            ->pluck('id')
            ->map(fn ($cid) => (int) $cid)
            ->all();
        $allCurrentPageSelected = $currentPageCustomerIds !== []
            && count(array_intersect($currentPageCustomerIds, $this->selectedCustomerIds)) === count($currentPageCustomerIds);

        return view('livewire.customers-index', array_merge($payload, [
            'permissions' => $permissions,
            'filtersOpen' => $filtersOpen,
            'allCurrentPageSelected' => $allCurrentPageSelected,
        ]));
    }
}
