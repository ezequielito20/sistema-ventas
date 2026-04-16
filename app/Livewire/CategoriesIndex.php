<?php

namespace App\Livewire;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class CategoriesIndex extends Component
{
    use WithPagination;

    public string $search = '';

    /** '', 'yes', 'no' */
    public string $hasProducts = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $productsMin = '';

    public string $productsMax = '';

    public bool $showDetailModal = false;

    /** @var array<string, mixed>|null */
    public ?array $detailCategory = null;

    public bool $showDeleteModal = false;

    public ?int $deleteTargetId = null;

    public string $deleteTargetName = '';

    public bool $selectionMode = false;

    /** @var array<int> */
    public array $selectedCategoryIds = [];

    public bool $showBulkDeleteModal = false;

    public int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'hasProducts' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'productsMin' => ['except' => ''],
        'productsMax' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        Gate::authorize('categories.index');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingHasProducts(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingProductsMin(): void
    {
        $this->resetPage();
    }

    public function updatingProductsMax(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->hasProducts = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->productsMin = '';
        $this->productsMax = '';
        $this->resetPage();
    }

    public function updatingPage(): void
    {
        $this->selectedCategoryIds = [];
    }

    public function setPerPage(int $value): void
    {
        $allowed = [10, 25, 50, 100];
        if (! in_array($value, $allowed, true)) {
            $value = 10;
        }

        $this->perPage = $value;
        $this->resetPage();
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

    public function openDetailModal(int $id): void
    {
        Gate::authorize('categories.show');

        $category = Category::query()
            ->withCount('products')
            ->where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->firstOrFail();

        $this->detailCategory = [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description ?? 'Sin descripción',
            'products_count' => $category->products_count,
            'created_at' => $category->created_at->format('d/m/Y H:i'),
            'updated_at' => $category->updated_at->format('d/m/Y H:i'),
        ];

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailCategory = null;
    }

    public function openDeleteModal(int $id): void
    {
        Gate::authorize('categories.destroy');

        $category = Category::query()
            ->where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->first();

        if (! $category) {
            $this->toast('Categoría no encontrada.', 'error');

            return;
        }

        $this->deleteTargetId = $id;
        $this->deleteTargetName = $category->name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
        $this->deleteTargetName = '';
    }

    public function toggleSelectionMode(): void
    {
        $this->selectionMode = ! $this->selectionMode;

        if (! $this->selectionMode) {
            $this->selectedCategoryIds = [];
            $this->closeBulkDeleteModal();
        }
    }

    public function toggleCategorySelection(int $id): void
    {
        if (! $this->selectionMode) {
            return;
        }

        if (in_array($id, $this->selectedCategoryIds, true)) {
            $this->selectedCategoryIds = array_values(array_diff($this->selectedCategoryIds, [$id]));
        } else {
            $this->selectedCategoryIds[] = $id;
            $this->selectedCategoryIds = array_values(array_unique(array_map('intval', $this->selectedCategoryIds)));
        }
    }

    public function toggleSelectAllCurrentPage(): void
    {
        if (! $this->selectionMode) {
            return;
        }

        $pageIds = $this->categoriesQuery()
            ->paginate($this->perPage)
            ->pluck('id')
            ->map(fn ($cid) => (int) $cid)
            ->all();

        $allSelected = $pageIds !== [] && count(array_intersect($pageIds, $this->selectedCategoryIds)) === count($pageIds);

        if ($allSelected) {
            $this->selectedCategoryIds = array_values(array_diff($this->selectedCategoryIds, $pageIds));
        } else {
            $this->selectedCategoryIds = array_values(array_unique(array_merge($this->selectedCategoryIds, $pageIds)));
        }
    }

    public function openBulkDeleteModal(): void
    {
        Gate::authorize('categories.destroy');

        if ($this->selectedCategoryIds === []) {
            $this->toast('Selecciona al menos una categoría para continuar.', 'warning');

            return;
        }

        $this->showBulkDeleteModal = true;
    }

    public function closeBulkDeleteModal(): void
    {
        $this->showBulkDeleteModal = false;
    }

    public function confirmDeleteCategory(): void
    {
        if ($this->deleteTargetId === null) {
            return;
        }

        $id = $this->deleteTargetId;
        $this->closeDeleteModal();
        $this->deleteCategory($id);
    }

    public function deleteCategory(int $id): void
    {
        Gate::authorize('categories.destroy');

        $companyId = (int) Auth::user()->company_id;

        try {
            $category = Category::query()
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->first();

            if (! $category) {
                $this->toast('Categoría no encontrada.', 'error');

                return;
            }

            $service = app(CategoryService::class);
            $result = $service->deleteCategoryWithResult($category, $companyId);

            if (! $result['deleted']) {
                $this->toast('No se pudo eliminar "'.$result['name'].'": '.$result['reason'].'.', 'error');

                return;
            }

            $this->selectedCategoryIds = array_values(array_diff($this->selectedCategoryIds, [$id]));
            $this->toast('Categoría "'.$result['name'].'" eliminada correctamente.', 'success');
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->toast('Error al eliminar la categoría: '.$e->getMessage(), 'error');
        }
    }

    public function confirmBulkDelete(): void
    {
        Gate::authorize('categories.destroy');

        if ($this->selectedCategoryIds === []) {
            $this->closeBulkDeleteModal();

            return;
        }

        try {
            $companyId = (int) Auth::user()->company_id;
            $results = app(CategoryService::class)->bulkDeleteCategories($companyId, $this->selectedCategoryIds);

            $deleted = array_values(array_filter($results, fn ($r) => $r['deleted'] === true));
            $blocked = array_values(array_filter($results, fn ($r) => $r['deleted'] === false));

            $messages = [];

            if ($deleted !== []) {
                $messages[] = count($deleted).' categoría(s) eliminada(s)';
            }

            if ($blocked !== []) {
                $blockedSummary = collect($blocked)
                    ->take(4)
                    ->map(fn ($r) => $r['name'].': '.$r['reason'])
                    ->implode(' | ');

                if (count($blocked) > 4) {
                    $blockedSummary .= ' | y '.(count($blocked) - 4).' más';
                }

                $messages[] = 'No eliminadas: '.$blockedSummary;
            }

            if ($messages === []) {
                $messages[] = 'No hubo cambios en las categorías seleccionadas.';
            }

            $this->closeBulkDeleteModal();
            $this->selectedCategoryIds = [];
            $this->selectionMode = false;
            $this->resetPage();

            $this->toast(
                implode('. ', $messages).'.',
                $blocked !== [] ? 'warning' : 'success'
            );
        } catch (\Throwable $e) {
            $this->closeBulkDeleteModal();
            $this->toast('Error al eliminar las categorías seleccionadas: '.$e->getMessage(), 'error');
        }
    }

    protected function categoriesQuery()
    {
        $companyId = Auth::user()->company_id;

        $query = Category::query()
            ->withCount('products')
            ->where('company_id', $companyId);

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'ILIKE', '%'.$s.'%')
                    ->orWhere('description', 'ILIKE', '%'.$s.'%');
            });
        }

        // Filtros por cantidad de productos: usar whereHas/has en lugar de having sobre el alias
        // products_count (withCount), porque PostgreSQL no permite ese alias en HAVING.
        if ($this->hasProducts === 'yes') {
            $query->whereHas('products');
        } elseif ($this->hasProducts === 'no') {
            $query->whereDoesntHave('products');
        }

        if ($this->dateFrom !== '') {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== '') {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        if ($this->productsMin !== '' && is_numeric($this->productsMin)) {
            $min = (int) $this->productsMin;
            if ($min > 0) {
                $query->has('products', '>=', $min);
            }
        }

        if ($this->productsMax !== '' && is_numeric($this->productsMax)) {
            $max = (int) $this->productsMax;
            if ($max === 0) {
                $query->whereDoesntHave('products');
            } else {
                $query->has('products', '<=', $max);
            }
        }

        return $query->orderBy('name');
    }

    public function render(CategoryService $categoryService): View
    {
        $companyId = (int) Auth::user()->company_id;
        $stats = $categoryService->statistics($companyId);

        $categories = $this->categoriesQuery()->paginate($this->perPage);
        $currentPageCategoryIds = $categories->pluck('id')->map(fn ($cid) => (int) $cid)->all();
        $allCurrentPageSelected = $currentPageCategoryIds !== []
            && count(array_intersect($currentPageCategoryIds, $this->selectedCategoryIds)) === count($currentPageCategoryIds);

        $permFlags = [
            'can_report' => Gate::allows('categories.report'),
            'can_create' => Gate::allows('categories.create'),
            'can_edit' => Gate::allows('categories.edit'),
            'can_show' => Gate::allows('categories.show'),
            'can_destroy' => Gate::allows('categories.destroy'),
        ];

        return view('livewire.categories-index', [
            'categories' => $categories,
            'stats' => $stats,
            'permFlags' => $permFlags,
            'currentPageCategoryIds' => $currentPageCategoryIds,
            'allCurrentPageSelected' => $allCurrentPageSelected,
        ]);
    }
}
