<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $perm = Permission::firstOrCreate(
            ['name' => 'my-plan.view', 'guard_name' => 'web'],
        );

        Role::query()
            ->where(function ($q) {
                $q->whereNotNull('company_id')
                    ->orWhere('name', 'super-admin');
            })
            ->each(function (Role $role) use ($perm) {
                if (! $role->hasPermissionTo($perm)) {
                    $role->givePermissionTo($perm);
                }
            });
    }

    public function down(): void
    {
        $perm = Permission::query()
            ->where('name', 'my-plan.view')
            ->where('guard_name', 'web')
            ->first();

        if ($perm) {
            $perm->roles()->detach();
            $perm->delete();
        }
    }
};
