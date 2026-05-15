<?php

namespace App\Livewire\Home;

use App\Models\Home\HomeProduct;
use App\Models\Home\HomeShoppingList;
use App\Models\Home\HomeShoppingListItem;
use App\Services\Home\HomeShoppingListService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class HomeShoppingListIndex extends Component
{
    public string $search = '';

    public ?int $activeListId = null;

    public bool $showGenerateConfirm = false;

    public bool $hasActiveList = false;

    public array $quantities = [];

    public array $checkedItems = [];

    public array $marketPrices = [];

    public bool $showPriceEditor = false;

    protected $listeners = ['list-generated' => '$refresh', 'list-completed' => '$refresh', 'prices-saved' => '$refresh'];

    public function mount(): void
    {
        Gate::authorize('home.shopping_list.index');

        $list = HomeShoppingList::where('company_id', Auth::user()->company_id)
            ->active()
            ->first();

        $this->hasActiveList = $list !== null;
        $this->activeListId = $list?->id;

        if ($list) {
            $this->loadCheckState($list);
        }
    }

    private function loadCheckState(HomeShoppingList $list): void
    {
        foreach ($list->items as $item) {
            $this->checkedItems[$item->id] = $item->is_purchased;
            $this->quantities[$item->id] = $item->actual_purchased_quantity ?? $item->suggested_quantity;
            $this->marketPrices[$item->id] = $item->product?->purchase_price ?? 0;
        }
    }

    public function generate(HomeShoppingListService $service): void
    {
        Gate::authorize('home.shopping_list.create');

        $companyId = (int) Auth::user()->company_id;

        if ($service->hasActiveList($companyId) && !$this->showGenerateConfirm) {
            $this->showGenerateConfirm = true;
            return;
        }

        $force = $this->showGenerateConfirm;
        $this->showGenerateConfirm = false;

        $list = $service->generate($companyId, $force);

        $this->activeListId = $list->id;
        $this->hasActiveList = true;

        $list->load('items.product');
        $this->loadCheckState($list);

        $this->dispatch('list-generated');
    }

    public function toggleItem(int $itemId): void
    {
        Gate::authorize('home.shopping_list.edit');

        $item = HomeShoppingListItem::findOrFail($itemId);

        $checked = !($this->checkedItems[$itemId] ?? false);

        $item->update([
            'is_purchased' => $checked,
            'actual_purchased_quantity' => $checked ? $this->quantities[$itemId] : null,
        ]);

        $this->checkedItems[$itemId] = $checked;
    }

    public function changeQuantity($value, $key): void
    {
        $itemId = (int) $key;
        $quantity = (int) max(0, $value);

        $this->quantities[$itemId] = $quantity;

        $item = HomeShoppingListItem::find($itemId);
        if ($item) {
            $item->update(['actual_purchased_quantity' => $quantity]);
        }
    }

    public function complete(HomeShoppingListService $service): void
    {
        Gate::authorize('home.shopping_list.edit');

        if (!$this->activeListId) {
            return;
        }

        $list = HomeShoppingList::where('company_id', Auth::user()->company_id)
            ->findOrFail($this->activeListId);

        foreach ($this->checkedItems as $itemId => $checked) {
            $item = $list->items()->find($itemId);
            if ($item) {
                $item->update([
                    'is_purchased' => $checked,
                    'actual_purchased_quantity' => $checked ? ($this->quantities[$itemId] ?? $item->suggested_quantity) : null,
                ]);
            }
        }

        $service->complete($list->fresh());

        $this->activeListId = null;
        $this->hasActiveList = false;
        $this->checkedItems = [];
        $this->quantities = [];

        $this->dispatch('list-completed');
    }

    public function saveMarketPrices(): void
    {
        Gate::authorize('home.shopping_list.edit');

        $count = 0;
        foreach ($this->marketPrices as $itemId => $price) {
            $item = HomeShoppingListItem::with('product')->find($itemId);
            if ($item && $item->product && (float) $price >= 0) {
                $item->product->update(['purchase_price' => (float) $price]);
                $count++;
            }
        }

        $this->showPriceEditor = false;

        $this->dispatch('prices-saved', message: "Precios actualizados para {$count} producto(s).");
    }

    public function cancelList(): void
    {
        if (!$this->activeListId) {
            return;
        }

        Gate::authorize('home.shopping_list.destroy');

        HomeShoppingList::find($this->activeListId)?->delete();

        $this->activeListId = null;
        $this->hasActiveList = false;
        $this->checkedItems = [];
        $this->quantities = [];
    }

    public function render(): View
    {
        $companyId = (int) Auth::user()->company_id;

        $productsToBuy = HomeProduct::where('company_id', $companyId)
            ->toBuy()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $lowStockCount = $productsToBuy->count();
        $outOfStockCount = $productsToBuy->where('quantity', '<=', 0)->count();

        $lists = HomeShoppingList::where('company_id', $companyId)
            ->withCount('items')
            ->latest()
            ->paginate(8);

        $activeList = null;
        if ($this->activeListId) {
            $activeList = HomeShoppingList::with(['items.product:id,name,brand,purchase_price,unit,image,quantity,min_quantity'])
                ->find($this->activeListId);
        }

        return view('livewire.home-shopping-list-index', [
            'productsToBuy' => $productsToBuy,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'lists' => $lists,
            'activeList' => $activeList,
            'canCreate' => Gate::allows('home.shopping_list.create'),
            'canEdit' => Gate::allows('home.shopping_list.edit'),
        ]);
    }
}
