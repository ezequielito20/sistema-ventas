<?php

namespace App\Livewire;

use App\Models\CashCount;
use App\Models\DebtPayment;
use App\Models\Purchase;
use App\Models\Sale;
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

    // ─── Modal de eliminación ───────────────────────────
    public bool $showDeleteModal = false;
    public ?int $deleteTargetId = null;
    public string $deleteTargetName = '';

    // ─── Modal de eliminación masiva ─────────────────────
    public bool $showBulkDeleteModal = false;

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

    public function openDeleteModal(int $id, string $name): void
    {
        $this->deleteTargetId = $id;
        $this->deleteTargetName = $name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
        $this->deleteTargetName = '';
    }

    public function confirmDeleteCashCount(): void
    {
        if (! $this->deleteTargetId) {
            return;
        }

        $companyId = Auth::user()->company_id;
        $cashCount = CashCount::where('company_id', $companyId)->findOrFail($this->deleteTargetId);

        if (! $cashCount->closing_date) {
            $this->toast('No se puede eliminar una caja abierta. Cerrala primero.', 'error');
            $this->closeDeleteModal();
            return;
        }

        // Verificar registros asociados
        $openingDate = $cashCount->opening_date;
        $closingDate = $cashCount->closing_date;

        $hasSales = Sale::where('company_id', $companyId)
            ->whereBetween('sale_date', [$openingDate, $closingDate])
            ->exists();

        $hasPurchases = Purchase::where('company_id', $companyId)
            ->whereBetween('purchase_date', [$openingDate, $closingDate])
            ->exists();

        $hasDebtPayments = DebtPayment::where('company_id', $companyId)
            ->whereBetween('created_at', [$openingDate, $closingDate])
            ->exists();

        if ($hasSales || $hasPurchases || $hasDebtPayments) {
            $this->toast('No se puede eliminar este arqueo porque tiene registros asociados (ventas, compras o pagos de deuda).', 'error');
            $this->closeDeleteModal();
            return;
        }

        $cashCount->delete();
        $this->toast('Arqueo eliminado correctamente.');
        $this->closeDeleteModal();
    }

    public function openBulkDeleteModal(): void
    {
        if (empty($this->selectedCashCountIds)) {
            $this->toast('Seleccioná al menos un arqueo para eliminar.', 'warning');
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
        if (empty($this->selectedCashCountIds)) {
            $this->closeBulkDeleteModal();
            return;
        }

        $companyId = Auth::user()->company_id;
        $ids = array_map('intval', $this->selectedCashCountIds);

        $deleted = 0;
        $skipped = 0;

        foreach ($ids as $id) {
            $cashCount = CashCount::where('company_id', $companyId)->find($id);
            if (! $cashCount) {
                continue;
            }

            if (! $cashCount->closing_date) {
                $skipped++;
                continue;
            }

            $hasSales = Sale::where('company_id', $companyId)
                ->whereBetween('sale_date', [$cashCount->opening_date, $cashCount->closing_date])
                ->exists();

            $hasPurchases = Purchase::where('company_id', $companyId)
                ->whereBetween('purchase_date', [$cashCount->opening_date, $cashCount->closing_date])
                ->exists();

            $hasDebtPayments = DebtPayment::where('company_id', $companyId)
                ->whereBetween('created_at', [$cashCount->opening_date, $cashCount->closing_date])
                ->exists();

            if ($hasSales || $hasPurchases || $hasDebtPayments) {
                $skipped++;
                continue;
            }

            $cashCount->delete();
            $deleted++;
        }

        if ($deleted > 0 && $skipped === 0) {
            $this->toast("{$deleted} arqueo(s) eliminado(s) correctamente.");
        } elseif ($deleted > 0 && $skipped > 0) {
            $this->toast("{$deleted} eliminado(s). {$skipped} no se pudieron eliminar (abiertos o con registros).", 'warning');
        } elseif ($deleted === 0 && $skipped > 0) {
            $this->toast("No se pudo eliminar ningún arqueo. Están abiertos o tienen registros asociados.", 'error');
        }

        $this->selectedCashCountIds = [];
        $this->closeBulkDeleteModal();
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

    public function render()
    {
        return view('livewire.cash-counts-index', [
            'cashCounts' => $this->cashCounts,
            'currentCashCount' => $this->currentCashCount,
            'permFlags' => $this->permFlags,
            'currency' => $this->currency,
            'allCurrentPageSelected' => $this->allCurrentPageSelected,
        ]);
    }
}
