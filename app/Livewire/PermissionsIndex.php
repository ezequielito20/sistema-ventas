<?php

namespace App\Livewire;

use App\Services\PermissionService;
use App\Support\PermissionFriendlyNames;
use Illuminate\Contracts\View\View;
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
            ->where('role_has_permissions.permission_id', $id)
            ->distinct('users.id')
            ->count('users.id');

        $this->detailPermission = [
            'id' => $permission->id,
            'name' => $permission->name,
            'label' => PermissionFriendlyNames::label($permission->name),
            'guard_name' => $permission->guard_name,
            'roles_count' => $permission->roles->count(),
            'users_count' => $usersCount,
            'created_at' => $permission->created_at->format('d/m/Y H:i'),
            'updated_at' => $permission->updated_at->format('d/m/Y H:i'),
            'roles' => $permission->roles->pluck('name')->values()->all(),
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

            $name = $permission->name;
            $permissionService->deletePermission($permission);

            $this->toast('Permiso "'.$name.'" eliminado correctamente.', 'success');
            $this->resetPage();
        } catch (\RuntimeException $e) {
            $this->toast($e->getMessage(), 'error');
        } catch (\Throwable $e) {
            $this->toast('Error al eliminar el permiso: '.$e->getMessage(), 'error');
        }
    }

    protected function permissionsQuery()
    {
        $query = Permission::query()->withCount('roles');

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'ILIKE', '%'.$s.'%')
                    ->orWhere('guard_name', 'ILIKE', '%'.$s.'%');
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
        $stats = $permissionService->statistics();
        $userCounts = $permissionService->usersCountByPermissionMap();

        $permissions = $this->permissionsQuery()->paginate(10);

        $permissions->getCollection()->transform(function ($permission) use ($userCounts) {
            $permission->users_count = $userCounts[$permission->id] ?? 0;
            $permission->friendly_label = PermissionFriendlyNames::label($permission->name);

            return $permission;
        });

        $permFlags = [
            'can_report' => Gate::allows('permissions.report'),
            'can_create' => Gate::allows('permissions.create'),
            'can_edit' => Gate::allows('permissions.edit'),
            'can_show' => Gate::allows('permissions.show'),
            'can_destroy' => Gate::allows('permissions.destroy'),
        ];

        return view('livewire.permissions-index', [
            'permissions' => $permissions,
            'stats' => $stats,
            'permFlags' => $permFlags,
            'moduleOptions' => $permissionService->validModules(),
        ]);
    }
}
