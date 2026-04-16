<?php

namespace App\Livewire;

use App\Services\PermissionService;
use App\Support\PermissionFriendlyNames;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

class PermissionsIndex extends Component
{
    use WithPagination;

    public string $search = '';

    /** Prefijo de módulo (primer segmento) o vacío = todos */
    public string $module = '';

    public string $guard = '';

    public bool $showDetailModal = false;

    /** @var array<string, mixed>|null */
    public ?array $detailPermission = null;

    public bool $showDeleteModal = false;

    public ?int $deleteTargetId = null;

    public string $deleteTargetName = '';

    public bool $selectionMode = false;

    /** @var array<int> */
    public array $selectedPermissionIds = [];

    public bool $showBulkDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'module' => ['except' => ''],
        'guard' => ['except' => ''],
    ];

    public function mount(): void
    {
        Gate::authorize('permissions.index');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingModule(): void
    {
        $this->resetPage();
    }

    public function updatingGuard(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->module = '';
        $this->guard = '';
        $this->resetPage();
    }

    public function updatingPage(): void
    {
        $this->selectedPermissionIds = [];
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
        Gate::authorize('permissions.show');

        $permission = Permission::query()
            ->with(['roles'])
            ->where('id', $id)
            ->firstOrFail();

        $usersCount = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->join('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->where('users.company_id', Auth::user()->company_id)
            ->where('role_has_permissions.permission_id', $id)
            ->distinct('users.id')
            ->count('users.id');

        $rolesCount = $permission->roles()
            ->where('company_id', Auth::user()->company_id)
            ->count();

        $roleNames = $permission->roles()
            ->where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        $this->detailPermission = [
            'id' => $permission->id,
            'name' => $permission->name,
            'label' => PermissionFriendlyNames::label($permission->name),
            'guard_name' => $permission->guard_name,
            'roles_count' => $rolesCount,
            'users_count' => $usersCount,
            'created_at' => $permission->created_at->format('d/m/Y H:i'),
            'updated_at' => $permission->updated_at->format('d/m/Y H:i'),
            'roles' => $roleNames,
        ];

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailPermission = null;
    }

    public function openDeleteModal(int $id): void
    {
        Gate::authorize('permissions.destroy');

        $permission = Permission::query()->where('id', $id)->first();
        if (! $permission) {
            $this->toast('Permiso no encontrado.', 'error');

            return;
        }

        $this->deleteTargetId = $id;
        $this->deleteTargetName = $permission->name;
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
            $this->selectedPermissionIds = [];
            $this->closeBulkDeleteModal();
        }
    }

    public function togglePermissionSelection(int $id): void
    {
        if (! $this->selectionMode) {
            return;
        }

        if (in_array($id, $this->selectedPermissionIds, true)) {
            $this->selectedPermissionIds = array_values(array_diff($this->selectedPermissionIds, [$id]));
        } else {
            $this->selectedPermissionIds[] = $id;
            $this->selectedPermissionIds = array_values(array_unique(array_map('intval', $this->selectedPermissionIds)));
        }
    }

    public function toggleSelectAllCurrentPage(): void
    {
        if (! $this->selectionMode) {
            return;
        }

        $pageIds = $this->permissionsQuery()
            ->paginate(10)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $allSelected = $pageIds !== [] && count(array_intersect($pageIds, $this->selectedPermissionIds)) === count($pageIds);

        if ($allSelected) {
            $this->selectedPermissionIds = array_values(array_diff($this->selectedPermissionIds, $pageIds));
        } else {
            $this->selectedPermissionIds = array_values(array_unique(array_merge($this->selectedPermissionIds, $pageIds)));
        }
    }

    public function openBulkDeleteModal(): void
    {
        Gate::authorize('permissions.destroy');

        if ($this->selectedPermissionIds === []) {
            $this->toast('Selecciona al menos un permiso para continuar.', 'warning');

            return;
        }

        $this->showBulkDeleteModal = true;
    }

    public function closeBulkDeleteModal(): void
    {
        $this->showBulkDeleteModal = false;
    }

    public function confirmDeletePermission(): void
    {
        if ($this->deleteTargetId === null) {
            return;
        }

        $id = $this->deleteTargetId;
        $this->closeDeleteModal();
        $this->deletePermission($id);
    }

    public function deletePermission(int $id): void
    {
        Gate::authorize('permissions.destroy');

        $permissionService = app(PermissionService::class);

        try {
            $permission = Permission::query()->where('id', $id)->first();
            if (! $permission) {
                $this->toast('Permiso no encontrado.', 'error');

                return;
            }

            $guard = $permissionService->deletionGuard($permission);
            if (! $guard['can_delete']) {
                $this->toast('No se pudo eliminar el permiso "'.$permission->name.'": '.$guard['reason'].'.', 'error');

                return;
            }

            $result = $permissionService->deletePermissionWithResult($permission);

            $this->selectedPermissionIds = array_values(array_diff($this->selectedPermissionIds, [$id]));
            $this->toast('Permiso "'.$result['name'].'" eliminado correctamente.', 'success');
            $this->resetPage();
        } catch (\RuntimeException $e) {
            $this->toast($e->getMessage(), 'error');
        } catch (\Throwable $e) {
            $this->toast('Error al eliminar el permiso: '.$e->getMessage(), 'error');
        }
    }

    public function confirmBulkDelete(): void
    {
        Gate::authorize('permissions.destroy');

        if ($this->selectedPermissionIds === []) {
            $this->closeBulkDeleteModal();

            return;
        }

        try {
            $results = app(PermissionService::class)->bulkDeletePermissions($this->selectedPermissionIds);

            $deleted = array_values(array_filter($results, fn ($result) => $result['deleted'] === true));
            $blocked = array_values(array_filter($results, fn ($result) => $result['deleted'] === false));

            $messages = [];

            if ($deleted !== []) {
                $messages[] = count($deleted).' permiso(s) eliminado(s)';
            }

            if ($blocked !== []) {
                $blockedSummary = collect($blocked)
                    ->take(4)
                    ->map(fn ($result) => $result['name'].': '.$result['reason'])
                    ->implode(' | ');

                if (count($blocked) > 4) {
                    $blockedSummary .= ' | y '.(count($blocked) - 4).' más';
                }

                $messages[] = 'No eliminados: '.$blockedSummary;
            }

            if ($messages === []) {
                $messages[] = 'No hubo cambios en los permisos seleccionados.';
            }

            $this->closeBulkDeleteModal();
            $this->selectedPermissionIds = [];
            $this->selectionMode = false;
            $this->resetPage();

            $this->toast(
                implode('. ', $messages).'.',
                $blocked !== [] ? 'warning' : 'success'
            );
        } catch (\Throwable $e) {
            $this->closeBulkDeleteModal();
            $this->toast('Error al eliminar los permisos seleccionados: '.$e->getMessage(), 'error');
        }
    }

    protected function permissionsQuery()
    {
        $query = Permission::query()->withCount([
            'roles as roles_count' => function ($roleQuery) {
                $roleQuery->where('company_id', Auth::user()->company_id);
            },
        ]);

        if ($this->search !== '') {
            $s = $this->search;
            $matchingFriendlyNames = collect(PermissionFriendlyNames::labelMap())
                ->filter(fn ($label, $permissionName) => str_contains(mb_strtolower($label), mb_strtolower($s))
                    || str_contains(mb_strtolower($permissionName), mb_strtolower($s)))
                ->keys()
                ->values()
                ->all();

            $query->where(function ($q) use ($s, $matchingFriendlyNames) {
                $q->where('name', 'ILIKE', '%'.$s.'%')
                    ->orWhere('guard_name', 'ILIKE', '%'.$s.'%');

                if ($matchingFriendlyNames !== []) {
                    $q->orWhereIn('name', $matchingFriendlyNames);
                }
            });
        }

        if ($this->module !== '') {
            $query->where('name', 'ILIKE', $this->module.'.%');
        }

        if ($this->guard !== '') {
            $query->where('guard_name', $this->guard);
        }

        return $query->orderBy('name');
    }

    public function render(PermissionService $permissionService): View
    {
        $companyId = (int) Auth::user()->company_id;
        $stats = $permissionService->statistics($companyId);
        $userCounts = $permissionService->usersCountByPermissionMap($companyId);

        $permissions = $this->permissionsQuery()->paginate(10);
        $currentPagePermissionIds = $permissions->pluck('id')->map(fn ($id) => (int) $id)->all();
        $allCurrentPageSelected = $currentPagePermissionIds !== []
            && count(array_intersect($currentPagePermissionIds, $this->selectedPermissionIds)) === count($currentPagePermissionIds);

        $permissions->getCollection()->transform(function ($permission) use ($userCounts) {
            $permission->users_count = $userCounts[$permission->id] ?? 0;
            $permission->friendly_label = PermissionFriendlyNames::label($permission->name);

            return $permission;
        });

        $permFlags = [
            'can_report' => Gate::allows('permissions.report'),
            'can_edit' => Gate::allows('permissions.edit'),
            'can_show' => Gate::allows('permissions.show'),
            'can_destroy' => Gate::allows('permissions.destroy'),
        ];

        return view('livewire.permissions-index', [
            'permissions' => $permissions,
            'stats' => $stats,
            'permFlags' => $permFlags,
            'moduleOptions' => $permissionService->validModules(),
            'currentPagePermissionIds' => $currentPagePermissionIds,
            'allCurrentPageSelected' => $allCurrentPageSelected,
        ]);
    }
}
