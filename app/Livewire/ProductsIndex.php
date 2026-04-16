<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class ProductsIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $category_id = '';

    public string $stock_status = '';

    public int $perPage = 12;

    public bool $selectionMode = false;

    /** @var array<int> */
    public array $selectedProductIds = [];

    public bool $showBulkDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'category_id' => ['except' => ''],
        'stock_status' => ['except' => ''],
        'perPage' => ['except' => 12],
    ];

    public function mount(): void
    {
        Gate::authorize('products.index');
    }

    public function updated($name, $value = null): void
    {
        if (in_array($name, ['search', 'category_id', 'stock_status', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->category_id = '';
        $this->stock_status = '';
        $this->resetPage();
    }

    public function updatingPage(): void
    {
        $this->selectedProductIds = [];
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
            $this->selectedProductIds = [];
            $this->closeBulkDeleteModal();
        }
    }

    public function toggleProductSelection(int $id): void
    {
        if (! $this->selectionMode) {
            return;
        }

        if (in_array($id, $this->selectedProductIds, true)) {
            $this->selectedProductIds = array_values(array_diff($this->selectedProductIds, [$id]));
        } else {
            $this->selectedProductIds[] = $id;
            $this->selectedProductIds = array_values(array_unique(array_map('intval', $this->selectedProductIds)));
        }
    }

    public function toggleSelectAllCurrentPage(): void
    {
        if (! $this->selectionMode) {
            return;
        }

        $pageIds = $this->productsQuery()
            ->paginate($this->perPage)
            ->pluck('id')
            ->map(fn ($pid) => (int) $pid)
            ->all();

        $allSelected = $pageIds !== []
            && count(array_intersect($pageIds, $this->selectedProductIds)) === count($pageIds);

        if ($allSelected) {
            $this->selectedProductIds = array_values(array_diff($this->selectedProductIds, $pageIds));
        } else {
            $this->selectedProductIds = array_values(array_unique(array_merge($this->selectedProductIds, $pageIds)));
        }
    }

    public function openBulkDeleteModal(): void
    {
        Gate::authorize('products.destroy');

        if ($this->selectedProductIds === []) {
            $this->toast('Selecciona al menos un producto para continuar.', 'warning');

            return;
        }

        $this->showBulkDeleteModal = true;
    }

    public function closeBulkDeleteModal(): void
    {
        $this->showBulkDeleteModal = false;
    }

    public function confirmBulkDelete(ProductService $productService): void
    {
        Gate::authorize('products.destroy');

        if ($this->selectedProductIds === []) {
            $this->closeBulkDeleteModal();

            return;
        }

        try {
            $companyId = (int) Auth::user()->company_id;
            $results = $productService->bulkDeleteProducts($companyId, $this->selectedProductIds);

            $deleted = array_values(array_filter($results, fn ($r) => $r['deleted'] === true));
            $blocked = array_values(array_filter($results, fn ($r) => $r['deleted'] === false));

            $messages = [];

            if ($deleted !== []) {
                $messages[] = count($deleted).' producto(s) eliminado(s)';
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
                $messages[] = 'No hubo cambios en los productos seleccionados.';
            }

            $this->closeBulkDeleteModal();
            $this->selectedProductIds = [];
            $this->selectionMode = false;
            $this->resetPage();

            $this->toast(
                implode('. ', $messages).'.',
                $blocked !== [] ? 'warning' : 'success'
            );
        } catch (\Throwable $e) {
            $this->closeBulkDeleteModal();
            $this->toast('Error al eliminar los productos seleccionados: '.$e->getMessage(), 'error');
        }
    }

    protected function productsQuery()
    {
        $companyId = (int) Auth::user()->company_id;

        $query = Product::query()
            ->select([
                'id', 'name', 'code', 'description', 'stock', 'min_stock', 'max_stock',
                'purchase_price', 'sale_price', 'image', 'category_id', 'company_id', 'created_at', 'updated_at',
            ])
            ->with(['category:id,name'])
            ->where('company_id', $companyId);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('code', 'ILIKE', "%{$search}%")
                    ->orWhereHas('category', function ($cq) use ($search) {
                        $cq->where('name', 'ILIKE', "%{$search}%");
                    });
            });
        }

        if ($this->category_id !== '') {
            $query->where('category_id', $this->category_id);
        }

        if ($this->stock_status !== '') {
            $stock = $this->stock_status;
            if ($stock === 'low') {
                $query->whereColumn('stock', '<=', 'min_stock');
            } elseif ($stock === 'high') {
                $query->whereColumn('stock', '>=', 'max_stock');
            } else {
                $query->whereColumn('stock', '>', 'min_stock')
                    ->whereColumn('stock', '<', 'max_stock');
            }
        }

        return $query->orderBy('name');
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

    public function render(): View
    {
        $companyId = (int) Auth::user()->company_id;

        $products = $this->productsQuery()->paginate($this->perPage);

        $categories = Category::query()
            ->select('id', 'name', 'company_id')
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $currency = $this->resolveCurrency();

        $stats = Product::query()
            ->where('company_id', $companyId)
            ->selectRaw('
                COUNT(*) as total_products,
                SUM(CASE WHEN stock <= min_stock THEN 1 ELSE 0 END) as low_stock_products,
                SUM(stock * purchase_price) as total_purchase_value,
                SUM(stock * sale_price) as total_sale_value
            ')
            ->first();

        $totalProducts = (int) ($stats->total_products ?? 0);
        $totalPurchaseValue = $stats->total_purchase_value ?? 0;
        $totalSaleValue = $stats->total_sale_value ?? 0;
        $potentialProfit = $totalSaleValue - $totalPurchaseValue;
        $profitPercentage = $totalPurchaseValue > 0
            ? (($totalSaleValue - $totalPurchaseValue) / $totalPurchaseValue) * 100
            : 0;

        $permissions = [
            'products.report' => Gate::allows('products.report'),
            'products.create' => Gate::allows('products.create'),
            'products.show' => Gate::allows('products.show'),
            'products.edit' => Gate::allows('products.edit'),
            'products.destroy' => Gate::allows('products.destroy'),
        ];

        $filtersOpen = $this->search !== '' || $this->category_id !== '' || $this->stock_status !== '';

        $currentPageProductIds = $products->pluck('id')->map(fn ($pid) => (int) $pid)->all();
        $allCurrentPageSelected = $currentPageProductIds !== []
            && count(array_intersect($currentPageProductIds, $this->selectedProductIds)) === count($currentPageProductIds);

        return view('livewire.products-index', [
            'products' => $products,
            'categories' => $categories,
            'currency' => $currency,
            'totalProducts' => $totalProducts,
            'totalPurchaseValue' => $totalPurchaseValue,
            'totalSaleValue' => $totalSaleValue,
            'potentialProfit' => $potentialProfit,
            'profitPercentage' => $profitPercentage,
            'permissions' => $permissions,
            'filtersOpen' => $filtersOpen,
            'allCurrentPageSelected' => $allCurrentPageSelected,
        ]);
    }
}
