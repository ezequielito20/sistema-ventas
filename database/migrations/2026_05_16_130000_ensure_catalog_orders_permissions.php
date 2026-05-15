<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Instalaciones anteriores al PermissionSeeder con orders.* no tenían filas en «permissions»;
 * el modal de roles agrupa por prefijo y sin filas no aparece la tarjeta «Pedidos desde catálogo».
 */
return new class extends Migration
{
    public function up(): void
    {
        $names = [
            'orders.index',
            'orders.update',
            'orders.cancel',
            'orders.settings',
        ];

        $permissions = [];
        foreach ($names as $name) {
            $permissions[] = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
            );
        }

        Role::query()
            ->where(function ($q) {
                $q->where('name', 'super-admin')
                    ->whereNull('company_id')
                    ->orWhere(function ($q2) {
                        $q2->whereNotNull('company_id')
                            ->where('name', 'administrador');
                    });
            })
            ->each(function (Role $role) use ($permissions) {
                foreach ($permissions as $perm) {
                    if (! $role->hasPermissionTo($perm)) {
                        $role->givePermissionTo($perm);
                    }
                }
            });
    }

    public function down(): void
    {
        $names = ['orders.index', 'orders.update', 'orders.cancel', 'orders.settings'];
        foreach ($names as $name) {
            $perm = Permission::query()
                ->where('name', $name)
                ->where('guard_name', 'web')
                ->first();
            if ($perm) {
                $perm->roles()->detach();
                $perm->delete();
            }
        }
    }
};
