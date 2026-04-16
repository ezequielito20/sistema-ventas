<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageUrlService
{
    /**
     * Resuelve la URL pública de una imagen almacenada en BD (ruta relativa al disco o URL absoluta).
     *
     * Prioridad: URL absoluta → archivo en disco `public` (storage/app/public) → `public/storage` en disco
     * → lógica remota (S3, firmadas, etc.).
     */
    public static function getImageUrl(?string $imagePath, string $fallbackImage = 'img/no-image.svg'): string
    {
        if ($imagePath === null || trim($imagePath) === '') {
            return asset($fallbackImage);
        }

        $trimmed = trim($imagePath);

        if (preg_match('#^https?://#i', $trimmed)) {
            return $trimmed;
        }

        $relative = self::normalizePath($trimmed);

        if ($relative === '') {
            return asset($fallbackImage);
        }

        if (Storage::disk('public')->exists($relative)) {
            return Storage::disk('public')->url($relative);
        }

        // Symlink roto u otro despliegue: el archivo sigue en public/storage/...
        $publicFile = public_path('storage'.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative));
        if (is_file($publicFile)) {
            return asset('storage/'.$relative);
        }

        return self::getProductionImageUrl($relative, $fallbackImage);
    }

    /**
     * URLs en entornos donde el archivo no está en el disco local público (p. ej. solo S3).
     */
    private static function getProductionImageUrl(string $imagePath, string $fallbackImage): string
    {
        try {
            $defaultDisk = config('filesystems.default');
            $disk = Storage::disk($defaultDisk);

            if ($disk->exists($imagePath)) {
                try {
                    return $disk->temporaryUrl($imagePath, now()->addHours(24));
                } catch (\Throwable $e) {
                    if (method_exists($disk, 'url')) {
                        return $disk->url($imagePath);
                    }

                    throw $e;
                }
            }
        } catch (\Throwable $e) {
            Log::debug('ImageUrlService: no se pudo resolver en disco por defecto', [
                'path' => $imagePath,
                'disk' => config('filesystems.default'),
                'message' => $e->getMessage(),
            ]);
        }

        $cloudConfig = env('LARAVEL_CLOUD_DISK_CONFIG');
        if ($cloudConfig) {
            $disks = json_decode($cloudConfig, true);
            if (is_array($disks) && count($disks) > 0) {
                $disk = collect($disks)->firstWhere('is_default', true) ?? $disks[0];
                if ($disk && isset($disk['endpoint'], $disk['bucket'])) {
                    return rtrim($disk['endpoint'], '/').'/'.$disk['bucket'].'/'.ltrim($imagePath, '/');
                }
            }
        }

        $defaultDisk = config('filesystems.default');
        $diskConfig = config("filesystems.disks.{$defaultDisk}");

        if ($diskConfig && isset($diskConfig['endpoint'], $diskConfig['bucket'])) {
            return rtrim($diskConfig['endpoint'], '/').'/'.$diskConfig['bucket'].'/'.ltrim($imagePath, '/');
        }

        $endpoint = env('AWS_ENDPOINT');
        $bucket = env('AWS_BUCKET');

        if ($endpoint && $bucket) {
            return rtrim($endpoint, '/').'/'.$bucket.'/'.ltrim($imagePath, '/');
        }

        return asset($fallbackImage);
    }

    /**
     * Disco recomendado para subidas nuevas (compatibilidad con código legado).
     */
    public static function getStorageDisk(): string
    {
        return app()->environment('local') ? 'public' : 's3';
    }

    /**
     * Convierte valores típicos en BD (`storage/products/x.jpg`, `/storage/...`) a ruta relativa al disco `public`.
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
