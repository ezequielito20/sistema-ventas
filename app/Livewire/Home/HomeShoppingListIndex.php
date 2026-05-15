<?php

namespace App\Livewire\Home;

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

    protected $listeners = ['list-generated' => '$refresh', 'list-completed' => '$refresh'];

    public function mount(): void
    {
        Gate::authorize('home.shopping_list.index');

        $list = HomeShoppingList::where('company_id', Auth::user()->company_id)
            ->active()
            ->first();

        $this->hasActiveList = $list !== null;
        $this->activeListId = $list?->id;
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

        $this->dispatch('list-generated', message: 'Lista de mercado generada correctamente.');
    }

    public function toggleItem(int $itemId): void
    {
        $item = HomeShoppingListItem::findOrFail($itemId);

        Gate::authorize('home.shopping_list.edit');

        $item->update([
            'is_purchased' => !$item->is_purchased,
            'actual_purchased_quantity' => !$item->is_purchased ? $item->suggested_quantity : null,
        ]);
    }

    public function updatePurchasedQuantity(int $itemId, int $quantity): void
    {
        if ($quantity < 0) {
            return;
        }

        $item = HomeShoppingListItem::findOrFail($itemId);
        $item->update(['actual_purchased_quantity' => $quantity]);
    }

    public function complete(HomeShoppingListService $service): void
    {
        Gate::authorize('home.shopping_list.edit');

        if (!$this->activeListId) {
            return;
        }

        $list = HomeShoppingList::where('company_id', Auth::user()->company_id)
            ->findOrFail($this->activeListId);

        $service->complete($list);

        $this->activeListId = null;
        $this->hasActiveList = false;

        $this->dispatch('list-completed', message: 'Lista completada. Stock actualizado.');
    }

    public function render(): View
    {
        $companyId = (int) Auth::user()->company_id;

        $lists = HomeShoppingList::where('company_id', $companyId)
            ->withCount('items')
            ->latest()
            ->paginate(10);

        $activeList = null;
        if ($this->activeListId) {
            $activeList = HomeShoppingList::with(['items.product:id,name,brand,purchase_price,unit,image'])
                ->find($this->activeListId);
        }

        return view('livewire.home-shopping-list-index', [
            'lists' => $lists,
            'activeList' => $activeList,
            'canCreate' => Gate::allows('home.shopping_list.create'),
        ]);
    }
}
