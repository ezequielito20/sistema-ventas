<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Facades\DB;

/**
 * Lógica de negocio compartida entre RoleController (store/update) y Livewire RoleForm.
 */
class RoleService
{
    /**
     * @throws \RuntimeException Mismos mensajes que el flujo legacy al fallar reglas de negocio
     */
    public function createRole(int $companyId, string $validatedName): Role
    {
        return DB::transaction(function () use ($companyId, $validatedName) {
            $roleName = trim(strtolower($validatedName));

            $systemRoles = ['admin', 'superadmin', 'administrator', 'root'];
            if (in_array($roleName, $systemRoles, true)) {
                throw new \RuntimeException('No se pueden crear roles del sistema');
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

            $systemRoles = ['admin', 'superadmin', 'administrator', 'root', 'user'];
            if (in_array($roleName, $systemRoles, true) && ! in_array($role->name, $systemRoles, true)) {
                throw new \RuntimeException('No se puede cambiar a un nombre de rol del sistema');
            }

            if (in_array($role->name, $systemRoles, true) && $role->name !== $roleName) {
                throw new \RuntimeException('No se pueden modificar roles del sistema');
            }

            $role->update([
                'name' => $roleName,
                'updated_at' => now(),
            ]);
        });
    }
}
