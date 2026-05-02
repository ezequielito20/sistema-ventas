<?php

namespace App\Livewire;

use App\Models\CashCount;
use App\Services\CashCountService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CashCountsIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public bool $showFilters = false;

    public bool $selectionMode = false;
    public array $selectedCashCountIds = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'dateFrom' => ['except' => null],
        'dateTo' => ['except' => null],
    ];

    public function updated($name): void
    {
        if (in_array($name, ['search', 'status', 'dateFrom', 'dateTo'])) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function toggleSelectionMode(): void
    {
        $this->selectionMode = ! $this->selectionMode;
        $this->selectedCashCountIds = [];
    }

    public function toggleSelectAllCurrentPage(): void
    {
        $currentIds = $this->cashCounts->pluck('id')->map('strval')->all();
        $allSelected = count(array_intersect($currentIds, $this->selectedCashCountIds)) === count($currentIds);

        if ($allSelected) {
            $this->selectedCashCountIds = array_values(array_diff($this->selectedCashCountIds, $currentIds));
        } else {
            $this->selectedCashCountIds = array_values(array_unique(array_merge($this->selectedCashCountIds, $currentIds)));
        }
    }

    public function toggleCashCountSelection(int $id): void
    {
        $idStr = (string) $id;
        if (in_array($idStr, $this->selectedCashCountIds)) {
            $this->selectedCashCountIds = array_values(array_diff($this->selectedCashCountIds, [$idStr]));
        } else {
            $this->selectedCashCountIds[] = $idStr;
        }
    }

    public function getAllCurrentPageSelectedProperty(): bool
    {
        if ($this->cashCounts->isEmpty()) {
            return false;
        }
        $currentIds = $this->cashCounts->pluck('id')->map('strval')->all();
        return count(array_intersect($currentIds, $this->selectedCashCountIds)) === count($currentIds);
    }

    public function getCashCountsProperty()
    {
        $companyId = Auth::user()->company_id;

        $query = CashCount::select([
                'id',
                'initial_amount',
                'final_amount',
                'opening_date',
                'closing_date',
                'observations',
                'created_at',
            ])
            ->where('company_id', $companyId);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'ILIKE', "%{$search}%")
                  ->orWhereRaw("TO_CHAR(opening_date, 'DD/MM/YYYY') ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("TO_CHAR(opening_date, 'YYYY-MM-DD') ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("TO_CHAR(closing_date, 'DD/MM/YYYY') ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("TO_CHAR(closing_date, 'YYYY-MM-DD') ILIKE ?", ["%{$search}%"]);
            });
        }

        if ($this->status === 'open') {
            $query->whereNull('closing_date');
        } elseif ($this->status === 'closed') {
            $query->whereNotNull('closing_date');
        }

        if ($this->dateFrom) {
            $query->whereDate('opening_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('opening_date', '<=', $this->dateTo);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getCurrentCashCountProperty(): ?CashCount
    {
        return CashCount::where('company_id', Auth::user()->company_id)
            ->whereNull('closing_date')
            ->first();
    }

    public function getPermFlagsProperty(): array
    {
        return [
            'can_create' => Auth::user()->can('cash-counts.create'),
            'can_edit' => Auth::user()->can('cash-counts.edit'),
            'can_destroy' => Auth::user()->can('cash-counts.destroy'),
            'can_report' => Auth::user()->can('cash-counts.report'),
            'can_close' => Auth::user()->can('cash-counts.close'),
        ];
    }

    public function getCurrencyProperty()
    {
        $company = DB::table('companies')
            ->select('currency', 'country')
            ->where('id', Auth::user()->company_id)
            ->first();

        $currency = null;
        if ($company && $company->currency) {
            $currency = DB::table('currencies')
                ->select('id', 'name', 'code', 'symbol')
                ->where('code', $company->currency)
                ->first();
        }

        if (! $currency && $company && $company->country) {
            $currency = DB::table('currencies')
                ->select('id', 'name', 'code', 'symbol')
                ->where('country_id', $company->country)
                ->first();
        }

        return $currency ?? (object) ['symbol' => '$', 'code' => 'USD'];
    }

    public function deleteCashCount(int $id): void
    {
        $cashCount = CashCount::where('company_id', Auth::user()->company_id)->findOrFail($id);

        if (! $cashCount->closing_date) {
            $this->dispatch('notify', type: 'error', message: 'No se puede eliminar una caja abierta. Cerrala primero.');
            return;
        }

        $cashCount->delete();
        $this->dispatch('notify', type: 'success', message: 'Arqueo eliminado correctamente.');
    }

    public function render()
    {
        return view('livewire.cash-counts-index', [
            'cashCounts' => $this->cashCounts,
            'currentCashCount' => $this->currentCashCount,
            'permFlags' => $this->permFlags,
            'currency' => $this->currency,
        ]);
    }
}
