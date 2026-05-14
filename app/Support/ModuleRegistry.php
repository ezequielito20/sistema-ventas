<?php

namespace App\Support;

final class ModuleRegistry
{
    /** @var array<string, array<string, mixed>>|null */
    private static ?array $modulesCache = null;

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function modules(): array
    {
        if (self::$modulesCache !== null) {
            return self::$modulesCache;
        }

        /** @var array<string, array<string, mixed>> $modules */
        $modules = config('plan_modules.modules', []);

        self::$modulesCache = $modules;

        return self::$modulesCache;
    }

    public static function clearCache(): void
    {
        self::$modulesCache = null;
    }

    /**
     * Prefijo de permiso (primer segmento) -> clave de módulo.
     *
     * @return array<string, string>
     */
    public static function permissionPrefixToModuleKey(): array
    {
        $map = [];
        foreach (self::modules() as $key => $def) {
            foreach ($def['permission_prefixes'] ?? [] as $prefix) {
                $map[$prefix] = $key;
            }
        }

        return $map;
    }

    public static function moduleKeyForPermissionName(string $permissionName): ?string
    {
        $prefix = explode('.', $permissionName, 2)[0] ?? '';

        return self::permissionPrefixToModuleKey()[$prefix] ?? null;
    }

    /**
     * Módulos que se muestran al definir un plan (super admin).
     *
     * @return array<string, array<string, mixed>>
     */
    public static function modulesForPlanForm(): array
    {
        $out = [];
        foreach (self::modules() as $key => $def) {
            if (! empty($def['platform_console_only'])) {
                continue;
            }
            if (! empty($def['super_admin_only'])) {
                continue;
            }
            if (($def['in_plan_form'] ?? true) !== true) {
                continue;
            }
            $out[$key] = $def;
        }

        return $out;
    }

    /**
     * Prefijos de permiso que nunca deben asignarse en roles de tenant.
     *
     * @return list<string>
     */
    public static function tenantForbiddenPermissionPrefixes(): array
    {
        $prefixes = [];
        foreach (self::modules() as $def) {
            if (! empty($def['platform_console_only'])) {
                foreach ($def['permission_prefixes'] ?? [] as $p) {
                    $prefixes[] = $p;
                }
            }
        }

        return array_values(array_unique($prefixes));
    }
}
