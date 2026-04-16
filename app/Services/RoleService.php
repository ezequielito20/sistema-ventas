<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Lógica de negocio compartida entre RoleController (store/update) y Livewire RoleForm.
 */
class RoleService
{
    /**
     * @var array<int, string>
     */
    private array $systemRoles = ['admin', 'user', 'superadmin', 'administrator', 'root', 'administrador'];

    /**
     * @throws \RuntimeException Mismos mensajes que el flujo legacy al fallar reglas de negocio
     */
    public function createRole(int $companyId, string $validatedName): Role
    {
        return DB::transaction(function () use ($companyId, $validatedName) {
            $roleName = trim(strtolower($validatedName));

            if (in_array($roleName, $this->systemRoles, true)) {
                throw new \RuntimeException('No se pueden crear roles del sistema');
            }

            $exists = Role::query()
                ->where('company_id', $companyId)
                ->whereRaw('LOWER(name) = ?', [$roleName])
                ->exists();

            if ($exists) {
                throw new \RuntimeException('Este nombre de rol ya existe en la empresa actual');
            }

            return Role::create([
                'name' => $roleName,
                'guard_name' => 'web',
                'company_id' => $companyId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * @throws \RuntimeException
     */
    public function updateRole(Role $role, string $validatedName): void
    {
        DB::transaction(function () use ($role, $validatedName) {
            $roleName = trim(strtolower($validatedName));

            if (in_array($roleName, $this->systemRoles, true) && ! in_array($role->name, $this->systemRoles, true)) {
                throw new \RuntimeException('No se puede cambiar a un nombre de rol del sistema');
            }

            if (in_array($role->name, $this->systemRoles, true) && $role->name !== $roleName) {
                throw new \RuntimeException('No se pueden modificar roles del sistema');
            }

            $exists = Role::query()
                ->where('company_id', $role->company_id)
                ->where('id', '!=', $role->id)
                ->whereRaw('LOWER(name) = ?', [$roleName])
                ->exists();

            if ($exists) {
                throw new \RuntimeException('Este nombre de rol ya existe en la empresa actual');
            }

            $role->update([
                'name' => $roleName,
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * @return array{can_delete: bool, reason: ?string}
     */
    public function deletionGuard(Role $role): array
    {
        if (in_array($role->name, $this->systemRoles, true)) {
            return [
                'can_delete' => false,
                'reason' => 'Es un rol del sistema',
            ];
        }

        $usersCount = $role->users()->count();
        if ($usersCount > 0) {
            return [
                'can_delete' => false,
                'reason' => 'Tiene '.$usersCount.' usuario(s) asignado(s)',
            ];
        }

        return [
            'can_delete' => true,
            'reason' => null,
        ];
    }

    /**
     * @return array{id:int,name:string,deleted:bool,reason:?string}
     */
    public function deleteRole(Role $role): array
    {
        $guard = $this->deletionGuard($role);

        if (! $guard['can_delete']) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'deleted' => false,
                'reason' => $guard['reason'],
            ];
        }

        DB::transaction(function () use ($role) {
            $role->delete();
        });

        return [
            'id' => $role->id,
            'name' => $role->name,
            'deleted' => true,
            'reason' => null,
        ];
    }

    /**
     * @param  array<int>  $roleIds
     * @return array<int, array{id:int,name:string,deleted:bool,reason:?string}>
     */
    public function bulkDeleteRoles(int $companyId, array $roleIds): array
    {
        /** @var Collection<int, Role> $roles */
        $roles = Role::query()
            ->where('company_id', $companyId)
            ->whereIn('id', array_map('intval', $roleIds))
            ->withCount('users')
            ->orderBy('name')
            ->get();

        $results = [];

        /** @var Role $role */
        foreach ($roles as $role) {
            $results[] = $this->deleteRole($role);
        }

        return $results;
    }
}
