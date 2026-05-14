<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use App\Support\ModuleRegistry;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;

class PlanEntitlementService
{
    /**
     * Usuario puede ver/usar un módulo en el panel tenant (menú, rutas generales).
     */
    public function userCanAccessModule(?User $user, string $moduleKey): bool
    {
        if (! $user) {
            return false;
        }

        $def = ModuleRegistry::modules()[$moduleKey] ?? null;
        if (! $def) {
            return false;
        }

        if (! empty($def['platform_console_only'])) {
            return false;
        }

        if (! empty($def['super_admin_only'])) {
            return $user->isSuperAdmin();
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $company = $user->company;
        if (! $company) {
            return false;
        }

        return $this->companyHasModule($company, $moduleKey);
    }

    public function companyHasModule(Company $company, string $moduleKey): bool
    {
        $plan = $company->plan;
        if (! $plan || ! $plan->is_active) {
            return false;
        }

        $features = $plan->features ?? [];
        if ($features === [] || $features === null) {
            return true;
        }

        return in_array($moduleKey, $features, true);
    }

    /**
     * Límite de registros para un módulo (null = ilimitado).
     */
    public function moduleRecordLimit(Company $company, string $moduleKey): ?int
    {
        if (! $this->companyHasModule($company, $moduleKey)) {
            return 0;
        }

        $plan = $company->plan;
        if (! $plan) {
            return null;
        }

        $limits = $plan->limits ?? [];

        if (array_key_exists($moduleKey, $limits)) {
            $v = $limits[$moduleKey];

            return $v === null ? null : (int) $v;
        }

        $legacyKeys = [
            'users' => 'max_users',
            'customers' => 'max_customers',
            'products' => 'max_products',
        ];
        if (isset($legacyKeys[$moduleKey])) {
            $lk = $legacyKeys[$moduleKey];
            if (array_key_exists($lk, $limits)) {
                $v = $limits[$lk];

                return $v === null ? null : (int) $v;
            }
            if (isset($plan->{$lk})) {
                $v = $plan->{$lk};

                return $v === null ? null : (int) $v;
            }
        }

        return null;
    }

    public function currentCount(Company $company, string $moduleKey): int
    {
        $def = ModuleRegistry::modules()[$moduleKey] ?? null;
        $relation = $def['limit_relation'] ?? null;
        if (! $relation || ! method_exists($company, $relation)) {
            return 0;
        }

        $rel = $company->{$relation}();
        if ($rel === null) {
            return 0;
        }

        return (int) $rel->count();
    }

    public function assertCanCreate(?User $user, string $moduleKey): void
    {
        if (! $user || ! $user->company) {
            throw ValidationException::withMessages([
                '_' => 'No se pudo validar el plan de la empresa.',
            ]);
        }

        if ($user->isSuperAdmin()) {
            return;
        }

        $company = $user->company;
        if (! $this->companyHasModule($company, $moduleKey)) {
            throw ValidationException::withMessages([
                '_' => 'Tu plan no incluye este módulo.',
            ]);
        }

        $def = ModuleRegistry::modules()[$moduleKey] ?? null;
        $relationName = $def['limit_relation'] ?? null;
        if (! $relationName) {
            return;
        }

        $limit = $this->moduleRecordLimit($company, $moduleKey);
        if ($limit === null) {
            return;
        }

        $current = $this->currentCount($company, $moduleKey);
        if ($current >= $limit) {
            throw ValidationException::withMessages([
                '_' => 'Has alcanzado el límite de registros de tu plan para este módulo ('.$limit.').',
            ]);
        }
    }

    /**
     * IDs de permisos que una empresa tenant puede asignar a roles.
     *
     * @return list<int>
     */
    public function allowedPermissionIdsForTenantCompany(int $companyId): array
    {
        $company = Company::query()->find($companyId);
        if (! $company) {
            return [];
        }

        $forbidden = ModuleRegistry::tenantForbiddenPermissionPrefixes();
        $prefixToModule = ModuleRegistry::permissionPrefixToModuleKey();

        $ids = [];
        foreach (Permission::query()->select(['id', 'name'])->cursor() as $permission) {
            $prefix = explode('.', $permission->name, 2)[0] ?? '';
            if (in_array($prefix, $forbidden, true)) {
                continue;
            }

            $moduleKey = $prefixToModule[$prefix] ?? null;
            if ($moduleKey === null) {
                $ids[] = (int) $permission->id;

                continue;
            }

            if ($this->companyHasModule($company, $moduleKey)) {
                $ids[] = (int) $permission->id;
            }
        }

        return $ids;
    }
}
