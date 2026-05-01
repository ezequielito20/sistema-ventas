<?php

namespace App\Livewire;

use App\Models\CashCount;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class SalesIndex extends Component
{
    use WithPagination;

    // ─── FILTROS ──────────────────────────────────────────
    public string $search = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $amountMin = '';
    public string $amountMax = '';
    public int $perPage = 15;
    public bool $showFilters = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'amountMin' => ['except' => ''],
        'amountMax' => ['except' => ''],
    ];

    // ─── STATS ────────────────────────────────────────────
    public array $statsSinceCashOpen = [];
    public array $statsThisWeek = [];
    public array $statsToday = [];
    public array $weekPercentages = [];
    public bool $hasCashOpen = false;

    // ─── MODAL DETALLE ────────────────────────────────────
    public bool $showDetailModal = false;

    /** @var array<string, mixed>|null */
    public ?array $detailSale = null;

    // ─── MODAL BORRADO INDIVIDUAL ─────────────────────────
    public bool $showDeleteModal = false;
    public ?int $deleteTargetId = null;
    public string $deleteTargetName = '';

    // ─── SELECCIÓN MÚLTIPLE ───────────────────────────────
    public bool $selectionMode = false;

    /** @var array<int> */
    public array $selectedSaleIds = [];

    public bool $showBulkDeleteModal = false;

    // ─── PERMISOS ─────────────────────────────────────────
    public array $permissions = [];

    // ─── LIFECYCLE ────────────────────────────────────────

    public function mount(): void
    {
        Gate::authorize('sales.index');
        $this->permissions = [
            'can_report' => Gate::allows('sales.report'),
            'can_create' => Gate::allows('sales.create'),
            'can_edit' => Gate::allows('sales.edit'),
            'can_show' => Gate::allows('sales.details'),
            'can_destroy' => Gate::allows('sales.destroy'),
            'can_print' => Gate::allows('sales.print'),
        ];
        $this->loadStats();
    }

    public function updated($name): void
    {
        if (in_array($name, [
            'search', 'dateFrom', 'dateTo', 'amountMin', 'amountMax', 'perPage',
        ], true)) {
            $this->resetPage();
        }
    }

    public function updatingPage(): void
    {
        $this->selectedSaleIds = [];
    }

    // ─── STATS ────────────────────────────────────────────

    public function loadStats(): void
    {
        $companyId = (int) Auth::user()->company_id;
        $service = app(SaleService::class);

        $this->hasCashOpen = CashCount::where('company_id', $companyId)
            ->whereNull('closing_date')
            ->exists();

        $this->statsSinceCashOpen = $service->getStatsSinceCashOpen($companyId);
        $this->statsThisWeek = $service->getStatsThisWeek($companyId);
        $this->statsToday = $service->getStatsToday($companyId);
        $this->weekPercentages = $service->getWeekPercentages($companyId);
    }

    // ─── FILTROS ──────────────────────────────────────────

    public function clearFilters(): void
    {
        $this->search = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->amountMin = '';
        $this->amountMax = '';
        $this->resetPage();
    }

    public function setPerPage(int $value): void
    {
        $allowed = [10, 15, 25, 50, 100];
        if (! in_array($value, $allowed, true)) {
            $value = 15;
        }
        $this->perPage = $value;
        $this->resetPage();
    }

    // ─── TOAST ────────────────────────────────────────────

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

    // ─── MODAL DETALLE ────────────────────────────────────

    public function openDetailModal(int $id): void
    {
        Gate::authorize('sales.details');

        $sale = app(SaleService::class)->getSaleDetails($id, (int) Auth::user()->company_id);

        if (! $sale) {
            $this->toast('Venta no encontrada.', 'error');
            return;
        }

        $this->detailSale = [
            'id' => $sale->id,
            'customer_name' => $sale->customer->name ?? '—',
            'customer_email' => $sale->customer->email ?? '—',
            'customer_phone' => $sale->customer->phone ?? '—',
            'sale_date' => $sale->sale_date->format('d/m/Y H:i'),
            'total_price' => (float) $sale->total_price,
            'note' => $sale->note,
            'general_discount_value' => (float) ($sale->general_discount_value ?? 0),
            'general_discount_type' => $sale->general_discount_type ?? 'fixed',
            'subtotal_before_discount' => (float) ($sale->subtotal_before_discount ?? $sale->total_price),
            'total_with_discount' => (float) ($sale->total_with_discount ?? $sale->total_price),
            'details' => $sale->saleDetails->map(function ($detail) {
                $finalPrice = $detail->final_price ?? $detail->original_price ?? $detail->unit_price ?? 0;
                return [
                    'code' => $detail->product->code ?? '—',
                    'name' => $detail->product->name ?? '—',
                    'category' => $detail->product->category->name ?? 'N/A',
                    'quantity' => $detail->quantity,
                    'unit_price' => (float) ($detail->original_price ?? $detail->unit_price ?? 0),
                    'final_price' => (float) $finalPrice,
                    'discount_value' => (float) ($detail->discount_value ?? 0),
                    'discount_type' => $detail->discount_type ?? 'fixed',
                    'subtotal' => round($detail->quantity * $finalPrice, 2),
                ];
            })->all(),
        ];

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailSale = null;
    }

    // ─── BORRADO INDIVIDUAL ───────────────────────────────

    public function openDeleteModal(int $id): void
    {
        Gate::authorize('sales.destroy');

        $sale = Sale::where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->first();

        if (! $sale) {
            $this->toast('Venta no encontrada.', 'error');
            return;
        }

        $this->deleteTargetId = $id;
        $this->deleteTargetName = 'Venta #' . $sale->id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
        $this->deleteTargetName = '';
    }

    public function confirmDeleteSale(): void
    {
        if ($this->deleteTargetId === null) {
            return;
        }

        $id = $this->deleteTargetId;
        $this->closeDeleteModal();
        $this->deleteSaleById($id);
    }

    public function deleteSaleById(int $id): void
    {
        Gate::authorize('sales.destroy');

        try {
            $service = app(SaleService::class);
            $result = $service->deleteSale($id, (int) Auth::user()->company_id);

            $this->selectedSaleIds = array_values(array_diff($this->selectedSaleIds, [$id]));
            $this->toast($result['message'], $result['type']);
            $this->resetPage();
            $this->loadStats();
        } catch (\Throwable $e) {
            $this->toast('Error al eliminar la venta: ' . $e->getMessage(), 'error');
        }
    }

    // ─── SELECCIÓN MÚLTIPLE ───────────────────────────────

    public function toggleSelectionMode(): void
    {
        $this->selectionMode = ! $this->selectionMode;

        if (! $this->selectionMode) {
            $this->selectedSaleIds = [];
            $this->closeBulkDeleteModal();
        }
    }

    public function toggleSaleSelection(int $id): void
    {
        if (! $this->selectionMode) {
            return;
        }

        if (in_array($id, $this->selectedSaleIds, true)) {
            $this->selectedSaleIds = array_values(array_diff($this->selectedSaleIds, [$id]));
        } else {
            $this->selectedSaleIds[] = $id;
            $this->selectedSaleIds = array_values(array_unique(array_map('intval', $this->selectedSaleIds)));
        }
    }

    public function toggleSelectAllCurrentPage(): void
    {
        if (! $this->selectionMode) {
            return;
        }

        $pageIds = app(SaleService::class)
            ->searchSalesQuery((int) Auth::user()->company_id, [
                'search' => $this->search,
                'dateFrom' => $this->dateFrom,
                'dateTo' => $this->dateTo,
                'amountMin' => $this->amountMin,
                'amountMax' => $this->amountMax,
            ])
            ->pluck('id')
            ->map(fn ($sid) => (int) $sid)
            ->all();

        $allSelected = $pageIds !== []
            && count(array_intersect($pageIds, $this->selectedSaleIds)) === count($pageIds);

        if ($allSelected) {
            $this->selectedSaleIds = array_values(array_diff($this->selectedSaleIds, $pageIds));
        } else {
            $this->selectedSaleIds = array_values(array_unique(array_merge($this->selectedSaleIds, $pageIds)));
        }
    }

    public function openBulkDeleteModal(): void
    {
        Gate::authorize('sales.destroy');

        if ($this->selectedSaleIds === []) {
            $this->toast('Selecciona al menos una venta para continuar.', 'warning');
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
        Gate::authorize('sales.destroy');

        if ($this->selectedSaleIds === []) {
            $this->closeBulkDeleteModal();
            return;
        }

        try {
            $results = app(SaleService::class)->bulkDeleteSales(
                $this->selectedSaleIds,
                (int) Auth::user()->company_id
            );

            $deleted = array_values(array_filter($results, fn ($r) => $r['deleted'] === true));
            $blocked = array_values(array_filter($results, fn ($r) => $r['deleted'] === false));

            $messages = [];

            if ($deleted !== []) {
                $messages[] = count($deleted) . ' venta(s) eliminada(s)';
            }

            if ($blocked !== []) {
                $blockedSummary = collect($blocked)
                    ->take(4)
                    ->map(fn ($r) => $r['name'] . ': ' . $r['reason'])
                    ->implode(' | ');

                if (count($blocked) > 4) {
                    $blockedSummary .= ' | y ' . (count($blocked) - 4) . ' más';
                }

                $messages[] = 'No eliminadas: ' . $blockedSummary;
            }

            if ($messages === []) {
                $messages[] = 'No hubo cambios en las ventas seleccionadas.';
            }

            $this->closeBulkDeleteModal();
            $this->selectedSaleIds = [];
            $this->selectionMode = false;
            $this->resetPage();
            $this->loadStats();

            $this->toast(
                implode('. ', $messages) . '.',
                $blocked !== [] ? 'warning' : 'success'
            );
        } catch (\Throwable $e) {
            $this->closeBulkDeleteModal();
            $this->toast('Error al eliminar las ventas seleccionadas: ' . $e->getMessage(), 'error');
        }
    }

    // ─── IMPRIMIR ──────────────────────────────────────────

    public function printSale(int $saleId): void
    {
        $this->redirect(route('admin.sales.print', $saleId));
    }

    // ─── QUERY ────────────────────────────────────────────

    protected function salesQuery()
    {
        $companyId = (int) Auth::user()->company_id;
        $filters = [
            'search' => $this->search,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'amountMin' => $this->amountMin,
            'amountMax' => $this->amountMax,
        ];

        return app(SaleService::class)->searchSales($companyId, $filters, $this->perPage);
    }

    // ─── CURRENCY ─────────────────────────────────────────

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

    // ─── RENDER ───────────────────────────────────────────

    public function render(): View
    {
        $currency = $this->resolveCurrency();

        $sales = $this->salesQuery();
        $currentPageIds = $sales->pluck('id')->map(fn ($sid) => (int) $sid)->all();
        $allCurrentPageSelected = $currentPageIds !== []
            && count(array_intersect($currentPageIds, $this->selectedSaleIds)) === count($currentPageIds);

        return view('livewire.sales-index', [
            'sales' => $sales,
            'currency' => $currency,
            'currentPageIds' => $currentPageIds,
            'allCurrentPageSelected' => $allCurrentPageSelected,
        ]);
    }
}