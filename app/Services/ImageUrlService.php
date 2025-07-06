<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ImageUrlService
{
    /**
     * Get the correct URL for an image based on the environment and storage configuration
     */
    public static function getImageUrl(?string $imagePath, string $fallbackImage = 'img/no-image.svg'): string
    {
        if (!$imagePath) {
            return asset($fallbackImage);
        }

        // En desarrollo local
        if (app()->environment('local')) {
            // Si el disco por defecto es S3, usar el bucket (para testing)
            if (config('filesystems.default') === 's3') {
                return self::getProductionImageUrl($imagePath, $fallbackImage);
            }
            
            // Si el archivo existe en storage público local, usarlo
            if (Storage::disk('public')->exists($imagePath)) {
                return Storage::disk('public')->url($imagePath);
            }
            // Si no existe localmente, usar la imagen por defecto
            return asset($fallbackImage);
        }

        // En producción, intentar diferentes métodos para obtener la URL
        return self::getProductionImageUrl($imagePath, $fallbackImage);
    }

    /**
     * Get image URL for production environment
     */
    private static function getProductionImageUrl(string $imagePath, string $fallbackImage): string
    {
        // Método 1: Usar Laravel Cloud disk config si está disponible
        $cloudConfig = env('LARAVEL_CLOUD_DISK_CONFIG');
        if ($cloudConfig) {
            $disks = json_decode($cloudConfig, true);
            if (is_array($disks) && count($disks) > 0) {
                // Buscar el disco por defecto o el primero disponible
                $disk = collect($disks)->firstWhere('is_default', true) ?? $disks[0];
                if ($disk && isset($disk['endpoint'], $disk['bucket'])) {
                    return rtrim($disk['endpoint'], '/') . '/' . $disk['bucket'] . '/' . ltrim($imagePath, '/');
                }
            }
        }

        // Método 2: Usar configuración manual de S3
        $defaultDisk = config('filesystems.default');
        $diskConfig = config("filesystems.disks.{$defaultDisk}");
        
        if ($diskConfig && isset($diskConfig['endpoint'], $diskConfig['bucket'])) {
            return rtrim($diskConfig['endpoint'], '/') . '/' . $diskConfig['bucket'] . '/' . ltrim($imagePath, '/');
        }

        // Método 3: Intentar con variables de entorno directas
        $endpoint = env('AWS_ENDPOINT');
        $bucket = env('AWS_BUCKET');
        
        if ($endpoint && $bucket) {
            return rtrim($endpoint, '/') . '/' . $bucket . '/' . ltrim($imagePath, '/');
        }

        // Método 4: Usar Storage facade si el disco está configurado
        try {
            $disk = Storage::disk($defaultDisk);
            if (method_exists($disk, 'url')) {
                return $disk->url($imagePath);
            }
        } catch (\Exception $e) {
            // Continuar con fallback
        }

        // Fallback
        return asset($fallbackImage);
    }

    /**
     * Get the correct storage disk based on environment
     */
    public static function getStorageDisk(): string
    {
        // En desarrollo usar público local, en producción usar S3
        return app()->environment('local') ? 'public' : 's3';
    }
} 