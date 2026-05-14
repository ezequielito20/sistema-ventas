<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * URL de retorno al listado de productos (query ?return=) para enlaces Crear/Editar.
 *
 * Durante peticiones Livewire, {@see Request::fullUrl()} apunta a /_livewire/update; usarla
 * en los enlases envenenaba ?return= con esa URL y tras guardar se hacía GET → 405.
 */
final class ProductListingReturnUrl
{
    public static function current(Request $request): string
    {
        if ($request->hasHeader('X-Livewire')) {
            $referer = (string) $request->headers->get('Referer', '');
            if ($referer !== '') {
                $safe = self::sanitizeInternalFullUrl($referer);
                if ($safe !== null) {
                    return $safe;
                }
            }

            return route('admin.products.index');
        }

        $full = $request->fullUrl();
        $safe = self::sanitizeInternalFullUrl($full);
        if ($safe !== null) {
            return $safe;
        }

        return route('admin.products.index');
    }

    /**
     * Acepta solo URLs de esta app y rechaza endpoints internos de Livewire u otras rutas no-HTML.
     */
    public static function sanitizeInternalFullUrl(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $path = parse_url($value, PHP_URL_PATH);
        $path = is_string($path) ? $path : '';

        if (self::isUnsafeReturnPath($path)) {
            return null;
        }

        $appUrl = rtrim((string) config('app.url'), '/');
        if ($appUrl !== '' && str_starts_with($value, $appUrl.'/')) {
            return $value;
        }

        if (str_starts_with($value, '/') && ! str_starts_with($value, '//')) {
            return $value;
        }

        $appHost = $appUrl !== '' ? parse_url($appUrl, PHP_URL_HOST) : null;
        $candidateHost = parse_url($value, PHP_URL_HOST);

        if ($appHost && $candidateHost && strcasecmp((string) $candidateHost, (string) $appHost) === 0) {
            return $value;
        }

        return null;
    }

    public static function isUnsafeReturnPath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if (str_contains($path, '/_livewire') || str_starts_with($path, '/_livewire')) {
            return true;
        }

        if (preg_match('#(^|/)livewire[^/]*/update$#i', $path) === 1) {
            return true;
        }

        return false;
    }
}
