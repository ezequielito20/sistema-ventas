<?php

namespace App\Livewire;

use App\Models\CashCount;
use App\Models\Product;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class PurchasesIndex extends Component
{
    use WithPagination;

    // ─── FILTROS ──────────────────────────────────────────
    public string $search = '';
    public string $product_id = '';
    public string $payment_status = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $amountMin = '';
    public string $amountMax = '';

    public int $perPage = 15;

    public bool $showFilters = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'product_id' => ['except' => ''],
        'payment_status' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'amountMin' => ['except' => ''],
        'amountMax' => ['except' => ''],
        'perPage' => ['except' => 15],
    ];

    // ─── MODALES ──────────────────────────────────────────
    public bool $showDetailModal = false;

    /** @var array<string, mixed>|null */
    public ?array $detailPurchase = null;

    public bool $showDeleteModal = false;
    public ?int $deleteTargetId = null;
    public string $deleteTargetName = '';

    // ─── SELECCIÓN MÚLTIPLE ───────────────────────────────
    public bool $selectionMode = false;

    /** @var array<int> */
    public array $selectedPurchaseIds = [];

    public bool $showBulkDeleteModal = false;

    public function mount(): void
    {
        Gate::authorize('purchases.index');
    }

    public function updated($name): void
    {
        if (in_array($name, [
            'search', 'product_id', 'payment_status',
            'dateFrom', 'dateTo', 'amountMin', 'amountMax', 'perPage',
        ], true)) {
            $this->resetPage();
        }
    }

    public function updatingPage(): void
    {
        $this->selectedPurchaseIds = [];
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->product_id = '';
        $this->payment_status = '';
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

    // ─── DETALLE ──────────────────────────────────────────
    public function openDetailModal(int $id): void
    {
        Gate::authorize('purchases.show');

        $purchase = Purchase::with(['details.product.category'])
            ->where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->firstOrFail();

        $subtotalBefore = $purchase->details->sum(
            fn ($d) => $d->quantity * ($d->original_price ?? 0)
        );

        $this->detailPurchase = [
            'id' => $purchase->id,
            'payment_receipt' => $purchase->payment_receipt,
            'purchase_date' => $purchase->purchase_date->format('d/m/Y H:i'),
            'total_price' => $purchase->total_price,
            'general_discount_value' => $purchase->general_discount_value,
            'general_discount_type' => $purchase->general_discount_type,
            'subtotal_before_discount' => $subtotalBefore,
            'total_with_discount' => $purchase->total_with_discount,
            'details' => $purchase->details->map(function ($detail) {
                $finalPrice = $detail->final_price ?? $detail->original_price ?? 0;
                return [
                    'code' => $detail->product->code ?? '—',
                    'name' => $detail->product->name ?? '—',
                    'category' => $detail->product->category->name ?? 'N/A',
                    'quantity' => $detail->quantity,
                    'original_price' => $detail->original_price,
                    'final_price' => $finalPrice,
                    'discount_value' => $detail->discount_value,
                    'discount_type' => $detail->discount_type,
                    'subtotal' => round($detail->quantity * $finalPrice, 2),
                ];
            })->all(),
        ];

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailPurchase = null;
    }

    // ─── BORRADO INDIVIDUAL ───────────────────────────────
    public function openDeleteModal(int $id): void
    {
        Gate::authorize('purchases.destroy');

        $purchase = Purchase::where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->first();

        if (! $purchase) {
            $this->toast('Compra no encontrada.', 'error');
            return;
        }

        $this->deleteTargetId = $id;
        $this->deleteTargetName = 'Compra #' . $purchase->id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
        $this->deleteTargetName = '';
    }

    public function confirmDeletePurchase(): void
    {
        if ($this->deleteTargetId === null) {
            return;
        }

        $id = $this->deleteTargetId;
        $this->closeDeleteModal();
        $this->deletePurchase($id);
    }

    public function deletePurchase(int $id): void
    {
        Gate::authorize('purchases.destroy');

        try {
            $service = app(PurchaseService::class);
            $result = $service->deletePurchase((int) Auth::user()->company_id, $id);

            $this->selectedPurchaseIds = array_values(array_diff($this->selectedPurchaseIds, [$id]));
            $this->toast($result['message'], $result['type']);
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->toast('Error al eliminar la compra: ' . $e->getMessage(), 'error');
        }
    }

    // ─── SELECCIÓN MÚLTIPLE ───────────────────────────────
    public function toggleSelectionMode(): void
    {
        $this->selectionMode = ! $this->selectionMode;

        if (! $this->selectionMode) {
            $this->selectedPurchaseIds = [];
            $this->closeBulkDeleteModal();
        }
    }

    public function togglePurchaseSelection(int $id): void
    {
        if (! $this->selectionMode) {
            return;
        }

        if (in_array($id, $this->selectedPurchaseIds, true)) {
            $this->selectedPurchaseIds = array_values(array_diff($this->selectedPurchaseIds, [$id]));
        } else {
            $this->selectedPurchaseIds[] = $id;
            $this->selectedPurchaseIds = array_values(array_unique(array_map('intval', $this->selectedPurchaseIds)));
        }
    }

    public function toggleSelectAllCurrentPage(): void
    {
        if (! $this->selectionMode) {
            return;
        }

        $pageIds = $this->purchasesQuery()
            ->paginate($this->perPage)
            ->pluck('id')
            ->map(fn ($pid) => (int) $pid)
            ->all();

        $allSelected = $pageIds !== []
            && count(array_intersect($pageIds, $this->selectedPurchaseIds)) === count($pageIds);

        if ($allSelected) {
            $this->selectedPurchaseIds = array_values(array_diff($this->selectedPurchaseIds, $pageIds));
        } else {
            $this->selectedPurchaseIds = array_values(array_unique(array_merge($this->selectedPurchaseIds, $pageIds)));
        }
    }

    public function openBulkDeleteModal(): void
    {
        Gate::authorize('purchases.destroy');

        if ($this->selectedPurchaseIds === []) {
            $this->toast('Selecciona al menos una compra para continuar.', 'warning');
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
        Gate::authorize('purchases.destroy');

        if ($this->selectedPurchaseIds === []) {
            $this->closeBulkDeleteModal();
            return;
        }

        try {
            $results = app(PurchaseService::class)->bulkDeletePurchases(
                (int) Auth::user()->company_id,
                $this->selectedPurchaseIds
            );

            $deleted = array_values(array_filter($results, fn ($r) => $r['deleted'] === true));
            $blocked = array_values(array_filter($results, fn ($r) => $r['deleted'] === false));

            $messages = [];

            if ($deleted !== []) {
                $messages[] = count($deleted) . ' compra(s) eliminada(s)';
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
                $messages[] = 'No hubo cambios en las compras seleccionadas.';
            }

            $this->closeBulkDeleteModal();
            $this->selectedPurchaseIds = [];
            $this->selectionMode = false;
            $this->resetPage();

            $this->toast(
                implode('. ', $messages) . '.',
                $blocked !== [] ? 'warning' : 'success'
            );
        } catch (\Throwable $e) {
            $this->closeBulkDeleteModal();
            $this->toast('Error al eliminar las compras seleccionadas: ' . $e->getMessage(), 'error');
        }
    }

    // ─── QUERY ────────────────────────────────────────────
    protected function purchasesQuery()
    {
        $companyId = Auth::user()->company_id;

        $query = Purchase::select(['id', 'purchase_date', 'payment_receipt', 'total_price', 'company_id'])
            ->with([
                'details' => fn ($q) => $q->select(['id', 'purchase_id', 'product_id', 'supplier_id', 'quantity']),
                'details.product' => fn ($q) => $q->select(['id', 'name', 'code', 'image']),
            ])
            ->where('company_id', $companyId);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_receipt', 'ILIKE', "%{$search}%")
                    ->orWhereRaw('purchase_date::text ILIKE ?', ["%{$search}%"]);

                if (is_numeric($search)) {
                    $q->orWhereRaw('total_price::text ILIKE ?', ["%{$search}%"]);
                }

                $q->orWhereHas('details.product', fn ($q2) => $q2->where('name', 'ILIKE', "%{$search}%"));
            });
        }

        if ($this->product_id !== '') {
            $query->whereHas('details', fn ($q) => $q->where('product_id', $this->product_id));
        }

        if ($this->payment_status !== '') {
            if ($this->payment_status === 'completed') {
                $query->whereNotNull('payment_receipt');
            } elseif ($this->payment_status === 'pending') {
                $query->whereNull('payment_receipt');
            }
        }

        if ($this->dateFrom !== '') {
            $query->whereDate('purchase_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== '') {
            $query->whereDate('purchase_date', '<=', $this->dateTo);
        }

        if ($this->amountMin !== '' && is_numeric($this->amountMin)) {
            $query->where('total_price', '>=', (float) $this->amountMin);
        }

        if ($this->amountMax !== '' && is_numeric($this->amountMax)) {
            $query->where('total_price', '<=', (float) $this->amountMax);
        }

        return $query->orderBy('created_at', 'desc');
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

    // ─── RENDER ───────────────────────────────────────────
    public function render(): View
    {
        $companyId = (int) Auth::user()->company_id;
        $currency = $this->resolveCurrency();

        $purchases = $this->purchasesQuery()->paginate($this->perPage);
        $currentPageIds = $purchases->pluck('id')->map(fn ($pid) => (int) $pid)->all();
        $allCurrentPageSelected = $currentPageIds !== []
            && count(array_intersect($currentPageIds, $this->selectedPurchaseIds)) === count($currentPageIds);

        // Stats (solo en carga completa, no en updates parciales de Livewire)
        $totalPurchases = DB::table('purchase_details')
            ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->where('purchases.company_id', $companyId)
            ->distinct('purchase_details.product_id')
            ->count('purchase_details.product_id');

        $totalAmount = (float) DB::table('purchases')
            ->where('company_id', $companyId)
            ->sum('total_price');

        $monthlyPurchases = (int) DB::table('purchases')
            ->where('company_id', $companyId)
            ->whereYear('purchase_date', now()->year)
            ->whereMonth('purchase_date', now()->month)
            ->count();

        $pendingDeliveries = (int) DB::table('purchases')
            ->where('company_id', $companyId)
            ->whereNull('payment_receipt')
            ->count();

        $cashCount = CashCount::where('company_id', $companyId)
            ->whereNull('closing_date')
            ->first();

        $products = Product::select(['id', 'name', 'code', 'category_id', 'company_id'])
            ->with(['category' => fn ($q) => $q->select(['id', 'name'])])
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $permFlags = [
            'can_report' => Gate::allows('purchases.report'),
            'can_create' => Gate::allows('purchases.create'),
            'can_edit' => Gate::allows('purchases.edit'),
            'can_show' => Gate::allows('purchases.show'),
            'can_destroy' => Gate::allows('purchases.destroy'),
        ];

        return view('livewire.purchases-index', [
            'purchases' => $purchases,
            'currency' => $currency,
            'totalPurchases' => $totalPurchases,
            'totalAmount' => $totalAmount,
            'monthlyPurchases' => $monthlyPurchases,
            'pendingDeliveries' => $pendingDeliveries,
            'cashCount' => $cashCount,
            'products' => $products,
            'permFlags' => $permFlags,
            'currentPageIds' => $currentPageIds,
            'allCurrentPageSelected' => $allCurrentPageSelected,
        ]);
    }
}
