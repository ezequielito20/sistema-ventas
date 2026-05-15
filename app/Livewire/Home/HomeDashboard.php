<?php

namespace App\Livewire\Home;

use App\Models\Home\HomeProduct;
use App\Models\Home\HomeProductMovement;
use App\Models\Home\HomeServiceBill;
use App\Models\Home\HomeShoppingList;
use App\Models\Home\HomeTransaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class HomeDashboard extends Component
{
    public int $totalProducts = 0;

    public int $lowStockProducts = 0;

    public int $activeLists = 0;

    public float $monthlyExpenses = 0;

    public array $recentMovements = [];

    public ?array $activeList = null;

    public array $upcomingBills = [];

    public function mount(): void
    {
        $companyId = (int) Auth::user()->company_id;

        $this->totalProducts = HomeProduct::where('company_id', $companyId)->count();
        $this->lowStockProducts = HomeProduct::where('company_id', $companyId)->lowStock()->count();

        $this->activeLists = HomeShoppingList::where('company_id', $companyId)->active()->count();

        $this->monthlyExpenses = (float) HomeTransaction::where('company_id', $companyId)
            ->expense()
            ->byMonth((int) now()->format('Y'), (int) now()->format('m'))
            ->sum('amount');

        $this->recentMovements = HomeProductMovement::where('company_id', $companyId)
            ->with(['product:id,name', 'user:id,name'])
            ->latest()
            ->take(10)
            ->get()
            ->toArray();

        $activeListModel = HomeShoppingList::where('company_id', $companyId)
            ->active()
            ->with('items.product')
            ->first();

        if ($activeListModel) {
            $this->activeList = [
                'id' => $activeListModel->id,
                'generated_at' => $activeListModel->generated_at->diffForHumans(),
                'items_count' => $activeListModel->items->count(),
                'purchased_count' => $activeListModel->items->where('is_purchased', true)->count(),
                'total_estimated' => $activeListModel->items->sum(fn ($i) => ($i->product?->purchase_price ?? 0) * $i->suggested_quantity),
            ];
        }

        $this->upcomingBills = HomeServiceBill::whereHas('service', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->unpaid()
            ->whereBetween('due_date', [now(), now()->addDays(30)])
            ->with('service:id,name')
            ->orderBy('due_date')
            ->take(5)
            ->get()
            ->toArray();
    }

    public function render(): View
    {
        return view('livewire.home-home-dashboard');
    }
}
