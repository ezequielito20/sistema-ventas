<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Empresa operadora de la consola Super Admin
    |--------------------------------------------------------------------------
    |
    | Solo usuarios isSuperAdmin y con company_id igual a este valor pueden
    | acceder a /super-admin. Usa 0 para no filtrar por empresa (solo rol).
    | Si no defines SAAS_PLATFORM_COMPANY_ID en .env, el valor por defecto es 1.
    |
    */
    'platform_company_id' => ($raw = env('SAAS_PLATFORM_COMPANY_ID')) === null || $raw === ''
        ? 1
        : (int) $raw,

];
