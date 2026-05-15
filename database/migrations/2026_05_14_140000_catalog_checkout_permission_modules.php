<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Permisos para los módulos «Métodos de pago del catálogo» y «Métodos de entrega del catálogo».
 */
return new class extends Migration
{
    public function up(): void
    {
        $names = [
            'catalog-payments.index',
            'catalog-payments.create',
            'catalog-payments.edit',
            'catalog-payments.destroy',
            'catalog-payments.report',
            'catalog-payments.show',

            'catalog-deliveries.index',
            'catalog-deliveries.create',
            'catalog-deliveries.edit',
            'catalog-deliveries.destroy',
            'catalog-deliveries.report',
            'catalog-deliveries.show',
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
        $names = [
            'catalog-payments.index',
            'catalog-payments.create',
            'catalog-payments.edit',
            'catalog-payments.destroy',
            'catalog-payments.report',
            'catalog-payments.show',

            'catalog-deliveries.index',
            'catalog-deliveries.create',
            'catalog-deliveries.edit',
            'catalog-deliveries.destroy',
            'catalog-deliveries.report',
            'catalog-deliveries.show',
        ];

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
