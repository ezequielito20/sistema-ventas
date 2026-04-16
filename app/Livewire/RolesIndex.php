<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use App\Services\RoleService;
use App\Support\PermissionFriendlyNames;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

class RolesIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $role_type = '';

    public bool $showDetailModal = false;

    /** @var array<string, mixed>|null */
    public ?array $detailRole = null;

    public bool $showPermissionsModal = false;

    public ?int $permissionsRoleId = null;

    public string $permissionsRoleName = '';

    /** @var array<int> */
    public array $selectedPermissionIds = [];

    public string $permissionSearch = '';

    public bool $showDeleteModal = false;

    public ?int $deleteTargetId = null;

    public string $deleteTargetName = '';

    public bool $selectionMode = false;

    /** @var array<int> */
    public array $selectedRoleIds = [];

    public bool $showBulkDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'role_type' => ['except' => ''],
    ];

    public function mount(): void
    {
        Gate::authorize('roles.index');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleType(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->role_type = '';
        $this->resetPage();
    }

    public function updatingPage(): void
    {
        $this->selectedRoleIds = [];
    }

    /**
     * Muestra toast global (ui-toast) tras el render; no depende del DOM del componente.
     */
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
        Gate::authorize('roles.show');

        $role = Role::query()
            ->with(['permissions', 'users'])
            ->where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->firstOrFail();

        $this->detailRole = [
            'id' => $role->id,
            'name' => $role->name,
            'created_at' => $role->created_at->format('d/m/Y H:i'),
            'updated_at' => $role->updated_at->format('d/m/Y H:i'),
            'users_count' => $role->users->count(),
            'permissions_count' => $role->permissions->count(),
            'is_system_role' => in_array($role->name, ['admin', 'user', 'superadmin'], true),
        ];

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailRole = null;
    }

    public function openPermissionsModal(int $id): void
    {
        Gate::authorize('roles.permissions');

        $role = Role::query()
            ->where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->with('permissions')
            ->firstOrFail();

        $systemRoles = ['admin', 'superadmin', 'administrator', 'root', 'administrador'];
        if (in_array($role->name, $systemRoles, true)) {
            $this->toast('No se pueden modificar los permisos de roles del sistema ('.$role->name.').', 'error');

            return;
        }

        $this->permissionsRoleId = $role->id;
        $this->permissionsRoleName = $role->name;
        $this->selectedPermissionIds = $role->permissions->pluck('id')->map(fn ($pid) => (int) $pid)->values()->all();
        $this->permissionSearch = '';
        $this->showPermissionsModal = true;
    }

    public function closePermissionsModal(): void
    {
        $this->showPermissionsModal = false;
        $this->permissionsRoleId = null;
        $this->permissionsRoleName = '';
        $this->selectedPermissionIds = [];
        $this->permissionSearch = '';
    }

    public function savePermissions(): void
    {
        Gate::authorize('roles.assign.permissions');

        if ($this->permissionsRoleId === null) {
            return;
        }

        try {
            DB::beginTransaction();

            $role = Role::query()
                ->where('company_id', Auth::user()->company_id)
                ->where('id', $this->permissionsRoleId)
                ->firstOrFail();

            $systemRoles = ['admin', 'superadmin', 'administrator', 'root', 'administrador'];
            if (in_array($role->name, $systemRoles, true)) {
                throw new \Exception('No se pueden modificar los permisos de roles del sistema ('.$role->name.')');
            }

            $ids = array_map('intval', $this->selectedPermissionIds);
            $valid = [];
            if ($ids !== []) {
                $valid = Permission::query()->whereIn('id', $ids)->pluck('id')->map(fn ($v) => (int) $v)->all();
            }

            $role->syncPermissions($valid);

            DB::commit();

            $this->closePermissionsModal();
            $this->toast('Permisos actualizados correctamente.', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->toast($e->getMessage(), 'error');
        }
    }

    public function toggleModulePermissions(string $module): void
    {
        $grouped = PermissionFriendlyNames::grouped();
        $group = $grouped->get($module);
        if (! $group) {
            return;
        }

        $ids = $group->pluck('id')->map(fn ($id) => (int) $id)->all();
        $allOn = $ids !== [] && count(array_intersect($ids, $this->selectedPermissionIds)) === count($ids);

        if ($allOn) {
            $this->selectedPermissionIds = array_values(array_diff($this->selectedPermissionIds, $ids));
        } else {
            $this->selectedPermissionIds = array_values(array_unique(array_merge($this->selectedPermissionIds, $ids)));
        }
    }

    public function selectAllPermissions(bool $select): void
    {
        if ($select) {
            $this->selectedPermissionIds = Permission::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        } else {
            $this->selectedPermissionIds = [];
        }
    }

    public function openDeleteModal(int $id): void
    {
        Gate::authorize('roles.destroy');

        $role = Role::query()
            ->where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->first();

        if (! $role) {
            $this->toast('Rol no encontrado.', 'error');

            return;
        }

        $this->deleteTargetId = $id;
        $this->deleteTargetName = $role->name;
        $this->showDeleteModal = true;
    }

    public function toggleSelectionMode(): void
    {
        $this->selectionMode = ! $this->selectionMode;

        if (! $this->selectionMode) {
            $this->selectedRoleIds = [];
            $this->closeBulkDeleteModal();
        }
    }

    public function toggleRoleSelection(int $id): void
    {
        if (! $this->selectionMode) {
            return;
        }

        if (in_array($id, $this->selectedRoleIds, true)) {
            $this->selectedRoleIds = array_values(array_diff($this->selectedRoleIds, [$id]));
        } else {
            $this->selectedRoleIds[] = $id;
            $this->selectedRoleIds = array_values(array_unique(array_map('intval', $this->selectedRoleIds)));
        }
    }

    public function toggleSelectAllCurrentPage(): void
    {
        if (! $this->selectionMode) {
            return;
        }

        $pageIds = $this->rolesQuery()
            ->paginate(10)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $allSelected = $pageIds !== [] && count(array_intersect($pageIds, $this->selectedRoleIds)) === count($pageIds);

        if ($allSelected) {
            $this->selectedRoleIds = array_values(array_diff($this->selectedRoleIds, $pageIds));
        } else {
            $this->selectedRoleIds = array_values(array_unique(array_merge($this->selectedRoleIds, $pageIds)));
        }
    }

    public function openBulkDeleteModal(): void
    {
        Gate::authorize('roles.destroy');

        if ($this->selectedRoleIds === []) {
            $this->toast('Selecciona al menos un rol para continuar.', 'warning');

            return;
        }

        $this->showBulkDeleteModal = true;
    }

    public function closeBulkDeleteModal(): void
    {
        $this->showBulkDeleteModal = false;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
        $this->deleteTargetName = '';
    }

    public function confirmDeleteRole(): void
    {
        if ($this->deleteTargetId === null) {
            return;
        }

        $id = $this->deleteTargetId;
        $this->closeDeleteModal();
        $this->deleteRole($id);
    }

    public function deleteRole(int $id): void
    {
        Gate::authorize('roles.destroy');

        try {
            $role = Role::query()
                ->where('company_id', Auth::user()->company_id)
                ->where('id', $id)
                ->first();

            if (! $role) {
                $this->toast('Rol no encontrado.', 'error');

                return;
            }

            $roleService = app(RoleService::class);
            $guard = $roleService->deletionGuard($role);
            if (! $guard['can_delete']) {
                $this->toast('No se pudo eliminar el rol "'.$role->name.'": '.$guard['reason'].'.', 'error');

                return;
            }

            $result = $roleService->deleteRole($role);

            $this->selectedRoleIds = array_values(array_diff($this->selectedRoleIds, [$id]));
            $this->toast('Rol "'.$result['name'].'" eliminado correctamente.', 'success');
            $this->resetPage();
        } catch (\Exception $e) {
            $this->toast('Error al eliminar el rol: '.$e->getMessage(), 'error');
        }
    }

    public function confirmBulkDelete(): void
    {
        Gate::authorize('roles.destroy');

        if ($this->selectedRoleIds === []) {
            $this->closeBulkDeleteModal();

            return;
        }

        try {
            $results = app(RoleService::class)->bulkDeleteRoles(Auth::user()->company_id, $this->selectedRoleIds);

            $deleted = array_values(array_filter($results, fn ($result) => $result['deleted'] === true));
            $blocked = array_values(array_filter($results, fn ($result) => $result['deleted'] === false));

            $messages = [];

            if ($deleted !== []) {
                $messages[] = count($deleted).' rol(es) eliminado(s)';
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
                $messages[] = 'No hubo cambios en los roles seleccionados.';
            }

            $this->closeBulkDeleteModal();
            $this->selectedRoleIds = [];
            $this->selectionMode = false;
            $this->resetPage();

            $this->toast(
                implode('. ', $messages).'.',
                $blocked !== [] ? 'warning' : 'success'
            );
        } catch (\Throwable $e) {
            $this->closeBulkDeleteModal();
            $this->toast('Error al eliminar los roles seleccionados: '.$e->getMessage(), 'error');
        }
    }

    protected function rolesQuery()
    {
        $companyId = Auth::user()->company_id;

        $query = Role::query()
            ->select(['id', 'name', 'created_at', 'updated_at', 'company_id'])
            ->withCount(['users', 'permissions'])
            ->byCompany($companyId);

        if ($this->search !== '') {
            $query->where('name', 'ILIKE', '%'.$this->search.'%');
        }

        if ($this->role_type === 'system') {
            $query->whereIn('name', ['admin', 'user', 'superadmin']);
        } elseif ($this->role_type === 'custom') {
            $query->whereNotIn('name', ['admin', 'user', 'superadmin']);
        }

        return $query->orderBy('name');
    }

    public function render(): View
    {
        $companyId = Auth::user()->company_id;

        $roles = $this->rolesQuery()->paginate(10);
        $currentPageRoleIds = $roles->pluck('id')->map(fn ($id) => (int) $id)->all();
        $allCurrentPageSelected = $currentPageRoleIds !== []
            && count(array_intersect($currentPageRoleIds, $this->selectedRoleIds)) === count($currentPageRoleIds);

        $stats = [
            'roles_total' => Role::query()->byCompany($companyId)->count(),
            'users_company' => User::query()->where('company_id', $companyId)->count(),
            'permissions_total' => Permission::query()->count(),
            'system_roles' => Role::query()->byCompany($companyId)->whereIn('name', ['admin', 'user', 'superadmin'])->count(),
        ];

        $permFlags = [
            'can_report' => Gate::allows('roles.report'),
            'can_create' => Gate::allows('roles.create'),
            'can_edit' => Gate::allows('roles.edit'),
            'can_show' => Gate::allows('roles.show'),
            'can_destroy' => Gate::allows('roles.destroy'),
            'can_assign_permissions' => Gate::allows('roles.permissions'),
        ];

        return view('livewire.roles-index', [
            'roles' => $roles,
            'stats' => $stats,
            'permFlags' => $permFlags,
            'permissionsGrouped' => PermissionFriendlyNames::grouped(),
            'currentPageRoleIds' => $currentPageRoleIds,
            'allCurrentPageSelected' => $allCurrentPageSelected,
        ]);
    }
}
