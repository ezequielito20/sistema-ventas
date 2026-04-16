<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

/**
 * Reglas y operaciones de permisos (Spatie) compartidas entre controlador y Livewire.
 */
class PermissionService
{
    /**
     * Prefijos de módulo permitidos en nombres `modulo.accion`.
     *
     * @return list<string>
     */
    public function validModules(): array
    {
        return [
            'users',
            'categories',
            'suppliers',
            'products',
            'customers',
            'purchases',
            'sales',
            'cash_counts',
            'cash_movements',
            'debt_payments',
            'companies',
            'roles',
            'permissions',
            'orders',
            'notifications',
            'countries',
            'states',
            'cities',
            'municipalities',
            'parishes',
            'currencies',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationMessages(): array
    {
        return [
            'name.required' => 'El nombre del permiso es obligatorio.',
            'name.string' => 'El nombre del permiso debe ser texto.',
            'name.max' => 'El nombre del permiso no puede tener más de :max caracteres.',
            'name.unique' => 'Ya existe un permiso con este nombre.',
            'name.regex' => 'El nombre del permiso debe seguir el formato: modulo.accion',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rulesForCreate(): array
    {
        $validModules = $this->validModules();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:permissions,name',
                'regex:/^[a-z]+\.[a-z]+$/',
                function ($attribute, $value, $fail) use ($validModules) {
                    $module = explode('.', $value)[0];
                    if (! in_array($module, $validModules, true)) {
                        $fail('El módulo "'.$module.'" no es válido. Módulos válidos: '.implode(', ', $validModules).'.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rulesForUpdate(Permission $permission): array
    {
        $validModules = $this->validModules();
        $id = $permission->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:permissions,name,'.$id,
                'regex:/^[a-z]+\.[a-z]+$/',
                function ($attribute, $value, $fail) use ($validModules) {
                    $module = explode('.', $value)[0];
                    if (! in_array($module, $validModules, true)) {
                        $fail('El módulo "'.$module.'" no es válido. Módulos válidos: '.implode(', ', $validModules).'.');
                    }
                },
                function ($attribute, $value, $fail) use ($permission) {
                    $newName = strtolower((string) $value);
                    if (
                        $permission->name !== $newName
                        && ($permission->roles()->count() > 0 || $permission->users()->count() > 0)
                    ) {
                        $fail('No se puede modificar el nombre del permiso porque está en uso por roles o usuarios.');
                    }
                },
            ],
        ];
    }

    public function createPermission(string $validatedName): Permission
    {
        return Permission::create([
            'name' => strtolower($validatedName),
            'guard_name' => 'web',
        ]);
    }

    public function updatePermission(Permission $permission, string $validatedName): void
    {
        $permission->update([
            'name' => strtolower($validatedName),
        ]);
    }

    /**
     * @throws \RuntimeException
     */
    public function deletePermission(Permission $permission): void
    {
        if ($permission->roles()->count() > 0 || $permission->users()->count() > 0) {
            throw new \RuntimeException('No se puede eliminar el permiso porque está en uso.');
        }

        $permission->delete();
    }

    /**
     * @return array{total: int, active: int, roles_with_permissions: int, unused: int}
     */
    public function statistics(): array
    {
        $total = Permission::query()->count();

        $active = Permission::query()
            ->where(function ($q) {
                $q->whereHas('roles')->orWhereHas('users');
            })
            ->count();

        $rolesWithPermissions = DB::table('roles')
            ->join('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
            ->distinct('roles.id')
            ->count('roles.id');

        $unused = Permission::query()
            ->whereDoesntHave('roles')
            ->whereDoesntHave('users')
            ->count();

        return [
            'total' => $total,
            'active' => $active,
            'roles_with_permissions' => $rolesWithPermissions,
            'unused' => $unused,
        ];
    }

    /**
     * Conteo de usuarios por permiso (vía roles).
     *
     * @return array<int, int> permission_id => users_count
     */
    public function usersCountByPermissionMap(): array
    {
        return DB::table('permissions')
            ->leftJoin('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->leftJoin('roles', 'role_has_permissions.role_id', '=', 'roles.id')
            ->leftJoin('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftJoin('users', 'model_has_roles.model_id', '=', 'users.id')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->select('permissions.id', DB::raw('COUNT(DISTINCT users.id) as users_count'))
            ->groupBy('permissions.id')
            ->pluck('users_count', 'permissions.id')
            ->map(fn ($c) => (int) $c)
            ->all();
    }
}
