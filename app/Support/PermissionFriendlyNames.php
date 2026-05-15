<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

final class PermissionFriendlyNames
{
    private static ?Collection $groupedCache = null;

    /**
     * @return array<string, string>
     */
    public static function labelMap(): array
    {
        return [
            'users.index' => 'Ver listado de usuarios',
            'users.create' => 'Crear usuarios',
            'users.edit' => 'Editar usuarios',
            'users.destroy' => 'Eliminar usuarios',
            'users.report' => 'Generar reportes de usuarios',
            'users.show' => 'Ver detalles de usuarios',

            'roles.index' => 'Ver listado de roles',
            'roles.create' => 'Crear roles',
            'roles.edit' => 'Editar roles',
            'roles.destroy' => 'Eliminar roles',
            'roles.report' => 'Generar reportes de roles',
            'roles.show' => 'Ver detalles de roles',
            'roles.permissions' => 'Asignar permisos a roles',
            'roles.assign.permissions' => 'Asignar permisos a roles',

            'permissions.index' => 'Ver listado de permisos',
            'permissions.create' => 'Crear permisos',
            'permissions.edit' => 'Editar permisos',
            'permissions.destroy' => 'Eliminar permisos',
            'permissions.report' => 'Generar reportes de permisos',
            'permissions.show' => 'Ver detalles de permisos',

            'customers.index' => 'Ver listado de clientes',
            'customers.create' => 'Crear clientes',
            'customers.edit' => 'Editar clientes',
            'customers.destroy' => 'Eliminar clientes',
            'customers.report' => 'Generar reportes de clientes',
            'customers.show' => 'Ver detalles de clientes',

            'suppliers.index' => 'Ver listado de proveedores',
            'suppliers.create' => 'Crear proveedores',
            'suppliers.edit' => 'Editar proveedores',
            'suppliers.destroy' => 'Eliminar proveedores',
            'suppliers.report' => 'Generar reportes de proveedores',
            'suppliers.show' => 'Ver detalles de proveedores',

            'products.index' => 'Ver listado de productos',
            'products.create' => 'Crear productos',
            'products.edit' => 'Editar productos',
            'products.destroy' => 'Eliminar productos',
            'products.report' => 'Generar reportes de productos',
            'products.show' => 'Ver detalles de productos',

            'categories.index' => 'Ver listado de categorías',
            'categories.create' => 'Crear categorías',
            'categories.edit' => 'Editar categorías',
            'categories.destroy' => 'Eliminar categorías',
            'categories.report' => 'Generar reportes de categorías',
            'categories.show' => 'Ver detalles de categorías',

            'sales.index' => 'Ver listado de ventas',
            'sales.create' => 'Crear ventas',
            'sales.show' => 'Ver detalles de ventas',
            'sales.destroy' => 'Eliminar ventas',
            'sales.report' => 'Generar reportes de ventas',
            'sales.print' => 'Imprimir ventas',
            'sales.details' => 'Ver detalles de productos en ventas',
            'sales.product-details' => 'Ver detalles de producto específico en ventas',
            'sales.product-by-code' => 'Buscar producto por código en ventas',

            'purchases.index' => 'Ver listado de compras',
            'purchases.create' => 'Crear compras',
            'purchases.show' => 'Ver detalles de compras',
            'purchases.destroy' => 'Eliminar compras',
            'purchases.report' => 'Generar reportes de compras',
            'purchases.details' => 'Ver detalles de productos en compras',
            'purchases.product-details' => 'Ver detalles de producto específico en compras',
            'purchases.product-by-code' => 'Buscar producto por código en compras',

            'cash-counts.index' => 'Ver listado de arqueos',
            'cash-counts.create' => 'Crear arqueos',
            'cash-counts.edit' => 'Editar arqueos',
            'cash-counts.destroy' => 'Eliminar arqueos',
            'cash-counts.report' => 'Generar reportes de arqueos',
            'cash-counts.show' => 'Ver detalles de arqueos',
            'cash-counts.store-movement' => 'Registrar movimientos de caja',
            'cash-counts.close' => 'Cerrar arqueo de caja',

            'companies.edit' => 'Editar configuración',
            'companies.create' => 'Crear configuración inicial',
            'companies.store' => 'Guardar configuración inicial',
            'companies.update' => 'Actualizar configuración',

            'orders.index' => 'Ver pedidos desde catálogo',
            'orders.update' => 'Actualizar pedidos (pago, entrega, resumen)',
            'orders.cancel' => 'Cancelar pedidos pendientes',
            'orders.settings' => 'Configurar pago y entrega del catálogo (legado)',

            'catalog-payments.index' => 'Ver métodos de pago del catálogo',
            'catalog-payments.create' => 'Crear métodos de pago del catálogo',
            'catalog-payments.edit' => 'Editar métodos de pago del catálogo',
            'catalog-payments.destroy' => 'Eliminar métodos de pago del catálogo',
            'catalog-payments.report' => 'Informes PDF de métodos de pago del catálogo',
            'catalog-payments.show' => 'Ver detalle de método de pago del catálogo',

            'catalog-deliveries.index' => 'Ver métodos de entrega del catálogo',
            'catalog-deliveries.create' => 'Crear métodos de entrega del catálogo',
            'catalog-deliveries.edit' => 'Editar métodos de entrega del catálogo',
            'catalog-deliveries.destroy' => 'Eliminar métodos de entrega del catálogo',
            'catalog-deliveries.report' => 'Informes PDF de métodos de entrega del catálogo',
            'catalog-deliveries.show' => 'Ver detalle de método de entrega del catálogo',

            'my-plan.view' => 'Ver resumen del plan y límites de la suscripción',
        ];
    }

    public static function label(string $name): string
    {
        $map = self::labelMap();

        return $map[$name] ?? ucfirst(str_replace('.', ' ', $name));
    }

    /**
     * Título en español para la tarjeta de permisos agrupados por prefijo
     * (p. ej. "customers" → "Clientes", "cash-counts" → "Arqueo de caja").
     */
    public static function permissionGroupLabel(string $prefix): string
    {
        $moduleKey = ModuleRegistry::permissionPrefixToModuleKey()[$prefix] ?? null;
        if ($moduleKey !== null) {
            $def = ModuleRegistry::modules()[$moduleKey] ?? null;
            $label = $def['label'] ?? null;
            if (is_string($label) && $label !== '') {
                return $label;
            }
        }

        return ucfirst(str_replace(['-', '_'], ' ', $prefix));
    }

    /**
     * Permisos agrupados por prefijo de módulo (segmento antes del primer punto).
     */
    public static function grouped(): Collection
    {
        if (self::$groupedCache !== null) {
            return self::$groupedCache;
        }

        self::$groupedCache = Permission::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get()
            ->map(function ($permission) {
                $permission->friendly_name = self::label($permission->name);

                return $permission;
            })
            ->groupBy(fn ($permission) => explode('.', $permission->name)[0]);

        return self::$groupedCache;
    }
}
