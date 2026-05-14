<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Módulos facturables / visibles en planes (tenant)
    |--------------------------------------------------------------------------
    |
    | - permission_prefixes: primer segmento del permiso Spatie (ej. users de users.index)
    | - limit_relation: relación en App\Models\Company para contar cupos (null = no limitable por registros)
    | - super_admin_only: solo usuarios isSuperAdmin ven el ítem (ej. escáner)
    | - platform_console_only: consola plataforma; no en formulario de plan ni en menú tenant
    | - plan_limit_is_daily: si es true, el cupo del formulario de plan se guarda como límite diario
    |   (p. ej. ventas/compras por día natural), no como tope total de registros.
    */
    'modules' => [
        'companies' => [
            'label' => 'Configuración de empresa',
            'permission_prefixes' => ['companies'],
            'limit_relation' => null,
            'super_admin_only' => false,
            'platform_console_only' => false,
            'in_plan_form' => true,
        ],
        'users' => [
            'label' => 'Usuarios',
            'permission_prefixes' => ['users'],
            'limit_relation' => 'users',
            'super_admin_only' => false,
            'platform_console_only' => false,
            'in_plan_form' => true,
        ],
        'roles' => [
            'label' => 'Roles',
            'permission_prefixes' => ['roles'],
            'limit_relation' => null,
            'super_admin_only' => false,
            'platform_console_only' => false,
            'in_plan_form' => true,
        ],
        'permissions' => [
            'label' => 'Permisos',
            'permission_prefixes' => ['permissions'],
            'limit_relation' => null,
            'super_admin_only' => false,
            'platform_console_only' => false,
            'in_plan_form' => true,
        ],
        'categories' => [
            'label' => 'Categorías',
            'permission_prefixes' => ['categories'],
            'limit_relation' => 'categories',
            'super_admin_only' => false,
            'platform_console_only' => false,
            'in_plan_form' => true,
        ],
        'products' => [
            'label' => 'Productos',
            'permission_prefixes' => ['products'],
            'limit_relation' => 'products',
            'super_admin_only' => false,
            'platform_console_only' => false,
            'in_plan_form' => true,
        ],
        'suppliers' => [
            'label' => 'Proveedores',
            'permission_prefixes' => ['suppliers'],
            'limit_relation' => 'suppliers',
            'super_admin_only' => false,
            'platform_console_only' => false,
            'in_plan_form' => true,
        ],
        'customers' => [
            'label' => 'Clientes',
            'permission_prefixes' => ['customers'],
            'limit_relation' => 'customers',
            'super_admin_only' => false,
            'platform_console_only' => false,
            'in_plan_form' => true,
        ],
        'sales' => [
            'label' => 'Ventas',
            'permission_prefixes' => ['sales'],
            'limit_relation' => 'sales',
            'plan_limit_is_daily' => true,
            'super_admin_only' => false,
            'platform_console_only' => false,
            'in_plan_form' => true,
        ],
        'purchases' => [
            'label' => 'Compras',
            'permission_prefixes' => ['purchases'],
            'limit_relation' => 'purchases',
            'plan_limit_is_daily' => true,
            'super_admin_only' => false,
            'platform_console_only' => false,
            'in_plan_form' => true,
        ],
        'cash_counts' => [
            'label' => 'Arqueo de caja',
            'permission_prefixes' => ['cash-counts'],
            'limit_relation' => 'cashCounts',
            'super_admin_only' => false,
            'platform_console_only' => false,
            'in_plan_form' => true,
        ],
        'reports' => [
            'label' => 'Reportes',
            'permission_prefixes' => [],
            'limit_relation' => null,
            'super_admin_only' => false,
            'platform_console_only' => false,
            'in_plan_form' => true,
        ],
        'catalog' => [
            'label' => 'Catálogo público',
            'permission_prefixes' => [],
            'limit_relation' => null,
            'super_admin_only' => false,
            'platform_console_only' => false,
            'in_plan_form' => true,
        ],
        'scanner' => [
            'label' => 'Escáner de precios',
            'permission_prefixes' => [],
            'limit_relation' => null,
            'super_admin_only' => true,
            'platform_console_only' => false,
            'in_plan_form' => false,
        ],
        'plans_console' => [
            'label' => 'Consola de planes (plataforma)',
            'permission_prefixes' => ['plans'],
            'limit_relation' => null,
            'super_admin_only' => false,
            'platform_console_only' => true,
            'in_plan_form' => false,
        ],
        'subscriptions_console' => [
            'label' => 'Consola de suscripciones (plataforma)',
            'permission_prefixes' => ['subscriptions'],
            'limit_relation' => null,
            'super_admin_only' => false,
            'platform_console_only' => true,
            'in_plan_form' => false,
        ],
        'system_console' => [
            'label' => 'Sistema (plataforma)',
            'permission_prefixes' => ['system', 'super-admin'],
            'limit_relation' => null,
            'super_admin_only' => false,
            'platform_console_only' => true,
            'in_plan_form' => false,
        ],
    ],

];
