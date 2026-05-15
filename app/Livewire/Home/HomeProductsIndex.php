<?php

namespace App\Livewire\Home;

use App\Models\Home\HomeProduct;
use App\Support\Home\HomeProductCategories;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class HomeProductsIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $category = '';

    public string $stock_status = '';

    public int $perPage = 12;

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'stock_status' => ['except' => ''],
        'perPage' => ['except' => 12],
    ];

    public function mount(): void
    {
        Gate::authorize('home.inventory.index');
    }

    public function updated($name): void
    {
        if (in_array($name, ['search', 'category', 'stock_status', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = '';
        $this->stock_status = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $companyId = (int) Auth::user()->company_id;

        $query = HomeProduct::where('company_id', $companyId);

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'ILIKE', "%{$s}%")
                    ->orWhere('brand', 'ILIKE', "%{$s}%")
                    ->orWhere('barcode', 'ILIKE', "%{$s}%");
            });
        }

        if ($this->category !== '') {
            $query->where('category', $this->category);
        }

        if ($this->stock_status === 'low') {
            $query->lowStock();
        } elseif ($this->stock_status === 'excedent') {
            $query->excedent();
        }

        $products = $query->orderBy('name')->paginate($this->perPage);

        $stats = HomeProduct::where('company_id', $companyId)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN quantity < min_quantity THEN 1 ELSE 0 END) as low')
            ->first();

        return view('livewire.home-products-index', [
            'products' => $products,
            'categories' => HomeProductCategories::options(),
            'stats' => $stats,
            'filtersOpen' => $this->search !== '' || $this->category !== '' || $this->stock_status !== '',
            'permissions' => [
                'create' => Gate::allows('home.inventory.create'),
                'edit' => Gate::allows('home.inventory.edit'),
                'show' => Gate::allows('home.inventory.show'),
                'destroy' => Gate::allows('home.inventory.destroy'),
            ],
        ]);
    }
}
