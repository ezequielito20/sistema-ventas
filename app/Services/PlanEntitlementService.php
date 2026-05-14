<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Subscription;
use App\Models\User;
use App\Support\ModuleRegistry;
use Illuminate\Database\Eloquent\Builder;
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

        if (! empty($def['always_visible_for_tenant'])) {
            return true;
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
     * Módulos cuyo cupo en el plan es por día natural (p. ej. ventas/compras), no tope total de filas.
     *
     * @return list<string>
     */
    public static function moduleKeysWithDailyPlanLimit(): array
    {
        $keys = [];
        foreach (ModuleRegistry::modules() as $key => $def) {
            if (! empty($def['plan_limit_is_daily'])) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * Límite de registros para un módulo (null = ilimitado).
     */
    public function moduleRecordLimit(Company $company, string $moduleKey): ?int
    {
        if (in_array($moduleKey, self::moduleKeysWithDailyPlanLimit(), true)) {
            return null;
        }

        if (! $this->companyHasModule($company, $moduleKey)) {
            return 0;
        }

        $plan = $company->plan;
        if (! $plan) {
            return null;
        }

        return $this->readModuleRecordLimitFromPlan($plan, $moduleKey);
    }

    /**
     * Lee el tope de registros del JSON del plan (sin comprobar si el módulo está activo para la empresa).
     */
    private function readModuleRecordLimitFromPlan(Plan $plan, string $moduleKey): ?int
    {
        if (in_array($moduleKey, self::moduleKeysWithDailyPlanLimit(), true)) {
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
                'plan' => 'No se pudo validar el plan de la empresa.',
            ]);
        }

        if ($user->isSuperAdmin()) {
            return;
        }

        $company = $user->company;
        if (! $this->companyHasModule($company, $moduleKey)) {
            throw ValidationException::withMessages([
                'plan' => 'Tu plan no incluye este módulo.',
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
            $plan = $company->plan;
            $noun = $this->planQuotaNounPluralForModule($moduleKey);
            $suffix = ($plan && filled($plan->name))
                ? 'en el plan «'.$plan->name.'».'
                : 'en el plan al que está suscrita tu empresa.';
            $message = 'Has alcanzado el límite de tu suscripción para este módulo. '
                .'(Máx. '.$limit.' '.$noun.' '.$suffix.')';

            throw ValidationException::withMessages([
                'plan' => $message,
            ]);
        }
    }

    /**
     * Texto en plural para mensajes de cupo (p. ej. "clientes", "productos").
     */
    private function planQuotaNounPluralForModule(string $moduleKey): string
    {
        $def = ModuleRegistry::modules()[$moduleKey] ?? null;
        if (! $def) {
            return 'registros';
        }

        $custom = $def['plan_quota_noun_plural'] ?? null;
        if (is_string($custom) && $custom !== '') {
            return $custom;
        }

        $label = (string) ($def['label'] ?? 'registros');

        return mb_strtolower($label, 'UTF-8');
    }

    /**
     * Tope de documentos (ventas o compras) por día natural según el plan (null = sin tope).
     *
     * @param  'sales'|'purchases'  $kind
     */
    public function dailyDocumentsLimit(?Company $company, string $kind): ?int
    {
        if (! $company) {
            return null;
        }

        $plan = $company->plan;
        if (! $plan || ! $plan->is_active) {
            return null;
        }

        return $this->readDailyDocumentsLimitFromPlan($plan, $kind);
    }

    /**
     * @param  'sales'|'purchases'  $kind
     */
    private function readDailyDocumentsLimitFromPlan(Plan $plan, string $kind): ?int
    {
        $key = $kind === 'purchases' ? 'purchases_daily' : 'sales_daily';
        $limits = $plan->limits ?? [];
        if (! array_key_exists($key, $limits)) {
            return null;
        }

        $v = $limits[$key];
        if ($v === null || $v === '') {
            return null;
        }

        $n = (int) $v;

        return $n > 0 ? $n : null;
    }

    /**
     * Cantidad de ventas o compras registradas en un día (zona horaria de la app).
     *
     * @param  'sales'|'purchases'  $kind
     */
    public function countDocumentsOnDate(Company $company, string $kind, string $dateYmd): int
    {
        if ($kind === 'purchases') {
            return (int) Purchase::query()
                ->where('company_id', $company->id)
                ->whereDate('purchase_date', $dateYmd)
                ->count();
        }

        return (int) Sale::query()
            ->where('company_id', $company->id)
            ->whereDate('sale_date', $dateYmd)
            ->count();
    }

    /**
     * Impide crear otra venta/compra si ya se alcanzó el tope diario del plan.
     *
     * @param  'sales'|'purchases'  $kind
     */
    public function assertCanCreateDocumentOnDate(?User $user, string $kind, string $dateYmd): void
    {
        if (! $user || $user->isSuperAdmin()) {
            return;
        }

        $company = $user->company;
        if (! $company) {
            return;
        }

        $limit = $this->dailyDocumentsLimit($company, $kind);
        if ($limit === null) {
            return;
        }

        $count = $this->countDocumentsOnDate($company, $kind, $dateYmd);
        if ($count >= $limit) {
            $plan = $company->plan;
            $noun = $kind === 'purchases' ? 'compras' : 'ventas';
            $suffix = ($plan && filled($plan->name))
                ? 'en el plan «'.$plan->name.'».'
                : 'en el plan al que está suscrita tu empresa.';
            $msg = 'Has alcanzado el límite diario de tu suscripción para '.$noun.'. '
                .'(Máx. '.$limit.' '.$noun.' por día natural '.$suffix.')';

            throw ValidationException::withMessages([
                'plan' => $msg,
            ]);
        }
    }

    public function planContractIncludesModule(Plan $plan, string $moduleKey): bool
    {
        $features = $plan->features ?? [];
        if ($features === [] || $features === null) {
            return true;
        }

        return in_array($moduleKey, $features, true);
    }

    /**
     * Resumen de módulos, límites y uso para la pantalla «Mi plan» (tenant).
     *
     * @return array{subscription: Subscription|null, plan: Plan|null, plan_is_active: bool, today: string, rows: list<array{module_key: string, label: string, effective_access: bool, limit_label: string, usage_label: string}>}
     */
    public function tenantPlanOverviewForCompany(Company $company): array
    {
        $company->loadMissing(['subscription.plan']);
        $subscription = $company->subscription;
        $plan = $subscription?->plan;
        $today = now()->timezone(config('app.timezone'))->toDateString();

        $rows = [];
        foreach (ModuleRegistry::modulesForPlanForm() as $moduleKey => $def) {
            if (! $plan || ! $this->planContractIncludesModule($plan, $moduleKey)) {
                continue;
            }

            $label = (string) ($def['label'] ?? $moduleKey);
            $effectiveAccess = $this->companyHasModule($company, $moduleKey);

            $limitLabel = '—';
            $usageLabel = '—';

            if (! empty($def['plan_limit_is_daily'])) {
                $kind = $moduleKey === 'purchases' ? 'purchases' : 'sales';
                if (! $plan) {
                    $limitLabel = '—';
                    $usageLabel = '—';
                } else {
                    $configured = $this->readDailyDocumentsLimitFromPlan($plan, $kind);
                    if ($configured === null) {
                        $limitLabel = 'Sin tope diario en la suscripción';
                        $usageLabel = '—';
                    } else {
                        $noun = $kind === 'purchases' ? 'compras' : 'ventas';
                        $limitLabel = 'Hasta '.$configured.' '.$noun.' por día natural (zona horaria de la app)';
                        if ($effectiveAccess) {
                            $cnt = $this->countDocumentsOnDate($company, $kind, $today);
                            $usageLabel = 'Hoy ('.$today.'): '.$cnt.' / '.$configured;
                        } else {
                            $usageLabel = 'Sin acceso efectivo al módulo';
                        }
                    }
                }
            } elseif (! empty($def['limit_relation'])) {
                $current = $this->currentCount($company, $moduleKey);
                if (! $plan) {
                    $limitLabel = '—';
                    $usageLabel = (string) $current.' en total';
                } else {
                    $configured = $this->readModuleRecordLimitFromPlan($plan, $moduleKey);
                    if ($configured === null) {
                        $limitLabel = 'Registros ilimitados según el contrato';
                        $usageLabel = (string) $current.' actuales';
                    } else {
                        $noun = $this->planQuotaNounPluralForModule($moduleKey);
                        $limitLabel = 'Máximo '.$configured.' '.$noun;
                        $usageLabel = $current.' / '.$configured.' usados';
                    }
                }
            } else {
                $limitLabel = 'Sin cupo por cantidad de registros';
                $usageLabel = '—';
            }

            $rows[] = [
                'module_key' => $moduleKey,
                'label' => $label,
                'effective_access' => $effectiveAccess,
                'limit_label' => $limitLabel,
                'usage_label' => $usageLabel,
            ];
        }

        return [
            'subscription' => $subscription,
            'plan' => $plan,
            'plan_is_active' => (bool) ($plan?->is_active),
            'today' => $today,
            'rows' => $rows,
        ];
    }

    /**
     * Al listar un solo día, solo se exponen hasta N documentos más recientes (según plan).
     *
     * @param  'sales'|'purchases'  $kind
     */
    public function applyDailyDocumentsViewCapToQuery(
        Builder $query,
        ?User $user,
        string $kind,
        string $dateFrom,
        string $dateTo,
        string $dateColumn
    ): void {
        if ($dateFrom === '' || $dateTo === '' || $dateFrom !== $dateTo) {
            return;
        }

        if (! $user || $user->isSuperAdmin()) {
            return;
        }

        $company = $user->company;
        if (! $company) {
            return;
        }

        $limit = $this->dailyDocumentsLimit($company, $kind);
        if ($limit === null || $limit <= 0) {
            return;
        }

        $table = $query->getModel()->getTable();
        $companyId = (int) $company->id;
        $day = $dateFrom;

        $query->whereIn($table.'.id', function ($sub) use ($table, $companyId, $dateColumn, $day, $limit) {
            $sub->from($table)
                ->select('id')
                ->where($table.'.company_id', $companyId)
                ->whereDate($table.'.'.$dateColumn, $day)
                ->orderByDesc($table.'.'.$dateColumn)
                ->orderByDesc($table.'.id')
                ->limit($limit);
        });
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

            $def = ModuleRegistry::modules()[$moduleKey] ?? [];
            if (! empty($def['always_visible_for_tenant'])) {
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
