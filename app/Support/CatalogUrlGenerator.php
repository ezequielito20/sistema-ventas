<?php

namespace App\Support;

use App\Models\Company;
use Illuminate\Support\Facades\URL;

class CatalogUrlGenerator
{
    public static function cartShow(Company $company): string
    {
        return self::maybeSign($company, 'catalog.cart.show', []);
    }

    public static function cartSync(Company $company): string
    {
        return self::maybeSign($company, 'catalog.cart.sync', []);
    }

    public static function cartRemove(Company $company, int $productId): string
    {
        return self::maybeSign($company, 'catalog.cart.remove', ['product' => $productId]);
    }

    public static function checkout(Company $company): string
    {
        return self::maybeSign($company, 'catalog.checkout', []);
    }

    public static function catalogIndex(Company $company): string
    {
        return self::maybeSign($company, 'catalog.index', []);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected static function maybeSign(Company $company, string $route, array $params): string
    {
        $slug = ['company' => $company->slug];
        $merged = array_merge($slug, $params);

        if ($company->catalog_is_public) {
            return route($route, $merged);
        }

        $days = (int) config('catalog.private_catalog_signed_link_days', 7);

        return URL::temporarySignedRoute($route, now()->addDays($days), $merged);
    }
}
