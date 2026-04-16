<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
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
        ]);
    }
}
