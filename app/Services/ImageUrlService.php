<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ImageUrlService
{
    /**
     * Resuelve la URL pública de una imagen.
     *
     * Prioridad:
     * 1. URL absoluta (http/https) → se devuelve tal cual
     * 2. Disco 'public' local → para desarrollo (storage:link)
     * 3. Disco por defecto (s3 en producción) → para Laravel Cloud / S3 / R2
     * 4. Archivo directo en filesystem → fallback sin symlink
     * 5. Imagen de fallback
     */
    public static function getImageUrl(?string $imagePath, string $fallbackImage = 'img/no-image.svg'): string
    {
        if ($imagePath === null || trim($imagePath) === '') {
            return asset($fallbackImage);
        }

        $trimmed = trim($imagePath);

        // Ya es una URL absoluta (S3/R2 suelen devolver URLs completas)
        if (preg_match('#^https?://#i', $trimmed)) {
            return $trimmed;
        }

        $relative = self::normalizePath($trimmed);

        if ($relative === '') {
            return asset($fallbackImage);
        }

        // 1. Disco 'public' local (desarrollo con storage:link)
        if (Storage::disk('public')->exists($relative)) {
            return Storage::disk('public')->url($relative);
        }

        // 2. Disco por defecto (s3 en producción, local en dev sin storage:link)
        try {
            $defaultDisk = config('filesystems.default', 'public');
            $disk = Storage::disk($defaultDisk);
            if ($disk->exists($relative)) {
                try {
                    return $disk->temporaryUrl($relative, now()->addHours(24));
                } catch (\Throwable) {
                    // S3 sin soporte de temporaryUrl (ej. algunos endpoints R2)
                }
                if (method_exists($disk, 'url')) {
                    return $disk->url($relative);
                }
                // Último recurso: asset con ruta relativa
                return asset('storage/' . $relative);
            }
        } catch (\Throwable) {
            // Disco no configurado o error de conexión
        }

        // 3. Archivo directo en public/storage/ (symlink funcionando)
        $publicFile = public_path('storage/' . str_replace('\\', '/', $relative));
        if (is_file($publicFile) && is_readable($publicFile)) {
            return asset('storage/' . $relative);
        }

        // 4. Archivo directo en storage/app/public/ (sin symlink)
        $storageFile = storage_path('app/public/' . str_replace('\\', '/', $relative));
        if (is_file($storageFile) && is_readable($storageFile)) {
            return asset('storage/' . $relative);
        }

        return asset($fallbackImage);
    }

    /**
     * Convierte valores típicos en BD a ruta relativa al disco.
     *
     * Ej: "storage/products/xyz.jpg" → "products/xyz.jpg"
     *     "company_logos/xyz.jpg" → "company_logos/xyz.jpg"
     *     "https://bucket.s3.region.amazonaws.com/xyz.jpg" → URL completa (no se normaliza)
     */
    private static function normalizePath(string $imagePath): string
    {
        $imagePath = trim($imagePath);

        // URLs absolutas: no normalizar
        if (preg_match('#^https?://#i', $imagePath)) {
            return $imagePath;
        }

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
