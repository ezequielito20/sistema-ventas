<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ImageUrlService
{
    /**
     * Resuelve la URL pública de una imagen.
     *
     * Los archivos se almacenan SIEMPRE en el disco 'public' (storage/app/public/),
     * independientemente del entorno. El symlink public/storage → storage/app/public
     * debe existir (ejecutar `php artisan storage:link` en cada deploy).
     *
     * Prioridad:
     * 1. URL absoluta (http/https) → se devuelve tal cual
     * 2. Disco 'public' vía Storage::url() → requiere symlink
     * 3. Archivo directo en public/storage/ → fallback sin symlink
     * 4. Archivo directo en storage/app/public/ → fallback por si symlink roto
     * 5. Imagen de fallback
     */
    public static function getImageUrl(?string $imagePath, string $fallbackImage = 'img/no-image.svg'): string
    {
        if ($imagePath === null || trim($imagePath) === '') {
            return asset($fallbackImage);
        }

        $trimmed = trim($imagePath);

        // Ya es una URL absoluta
        if (preg_match('#^https?://#i', $trimmed)) {
            return $trimmed;
        }

        $relative = self::normalizePath($trimmed);

        if ($relative === '') {
            return asset($fallbackImage);
        }

        // 1. Disco 'public' — ruta canónica (requiere symlink storage:link)
        if (Storage::disk('public')->exists($relative)) {
            return Storage::disk('public')->url($relative);
        }

        // 2. Archivo en public/storage/ — el symlink puede funcionar aunque Storage::exists falle
        $publicFile = public_path('storage/' . str_replace('\\', '/', $relative));
        if (is_file($publicFile) && is_readable($publicFile)) {
            return asset('storage/' . $relative);
        }

        // 3. Archivo en storage/app/public/ — acceso directo sin symlink
        $storageFile = storage_path('app/public/' . str_replace('\\', '/', $relative));
        if (is_file($storageFile) && is_readable($storageFile)) {
            return asset('storage/' . $relative);
        }

        return asset($fallbackImage);
    }

    /**
     * Convierte valores típicos en BD a ruta relativa al disco 'public'.
     *
     * Ej: "storage/products/xyz.jpg" → "products/xyz.jpg"
     *     "/storage/products/xyz.jpg" → "products/xyz.jpg"
     *     "company_logos/xyz.jpg" → "company_logos/xyz.jpg"
     *     "products/xyz.jpg" → "products/xyz.jpg"
     */
    private static function normalizePath(string $imagePath): string
    {
        $imagePath = trim($imagePath);

        if (str_contains($imagePath, '://')) {
            $path = parse_url($imagePath, PHP_URL_PATH);
            if (is_string($path) && $path !== '') {
                $imagePath = $path;
            }
        }

        $imagePath = ltrim($imagePath, '/');

        if (str_starts_with($imagePath, 'storage/')) {
            $imagePath = substr($imagePath, strlen('storage/'));
        }

        return $imagePath;
    }
}
