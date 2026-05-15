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
];
