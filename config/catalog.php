<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Carrito público del catálogo
    |--------------------------------------------------------------------------
    */
    'cart_cookie_lifetime_minutes' => (int) env('CATALOG_CART_COOKIE_MINUTES', 60 * 24 * 30),

    /*
    |--------------------------------------------------------------------------
    | Enlace de resumen del pedido (token único)
    |--------------------------------------------------------------------------
    */
    'summary_link_ttl_hours' => (int) env('CATALOG_ORDER_SUMMARY_TTL_HOURS', 168),

    /*
    |--------------------------------------------------------------------------
    | URL firmada para catálogo privado (desde ajustes de empresa)
    |--------------------------------------------------------------------------
    */
    'private_catalog_signed_link_days' => (int) env('CATALOG_PRIVATE_SIGNED_LINK_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Anti-abuso en checkout público (pedidos desde catálogo)
    |--------------------------------------------------------------------------
    */
    'order_max_per_ip_per_hour' => (int) env('CATALOG_ORDER_MAX_PER_IP_PER_HOUR', 3),

    'order_ip_rate_limit_decay_seconds' => (int) env('CATALOG_ORDER_IP_RATE_LIMIT_DECAY_SECONDS', 3600),

    'order_max_pending_per_phone' => (int) env('CATALOG_ORDER_MAX_PENDING_PER_PHONE', 1),
];
