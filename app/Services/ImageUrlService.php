<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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

        // Normalizar el path - remover 'storage/' si está presente
        $imagePath = self::normalizePath($imagePath);

        // En desarrollo local - siempre usar storage público local
        if (app()->environment('local')) {
            // Verificar si el archivo existe en storage público local
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
        // Método 1: Usar URLs firmadas (temporaryUrl) - Funciona siempre
        try {
            $defaultDisk = config('filesystems.default');
            $disk = Storage::disk($defaultDisk);
            
            // Debug: Log información
            Log::info("ImageUrlService Debug", [
                'imagePath' => $imagePath,
                'defaultDisk' => $defaultDisk,
                'diskConfig' => config("filesystems.disks.{$defaultDisk}")
            ]);
            
            // Verificar que el archivo existe
            if ($disk->exists($imagePath)) {
                Log::info("File exists, generating temporaryUrl");
                // Generar URL firmada válida por 24 horas
                $url = $disk->temporaryUrl($imagePath, now()->addHours(24));
                Log::info("Generated URL: " . $url);
                return $url;
            } else {
                Log::warning("File does not exist: " . $imagePath);
            }
        } catch (\Exception $e) {
            Log::error("Error in temporaryUrl: " . $e->getMessage());
            // Si falla temporaryUrl, continuar con otros métodos
        }

        // Método 2: Usar Laravel Cloud disk config para URLs directas
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

        // Método 3: Usar configuración manual de S3
        $defaultDisk = config('filesystems.default');
        $diskConfig = config("filesystems.disks.{$defaultDisk}");
        
        if ($diskConfig && isset($diskConfig['endpoint'], $diskConfig['bucket'])) {
            return rtrim($diskConfig['endpoint'], '/') . '/' . $diskConfig['bucket'] . '/' . ltrim($imagePath, '/');
        }

        // Método 4: Intentar con variables de entorno directas
        $endpoint = env('AWS_ENDPOINT');
        $bucket = env('AWS_BUCKET');
        
        if ($endpoint && $bucket) {
            return rtrim($endpoint, '/') . '/' . $bucket . '/' . ltrim($imagePath, '/');
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

    /**
     * Normalize image path - remove 'storage/' prefix if present
     */
    private static function normalizePath(string $imagePath): string
    {
        // Remover 'storage/' del inicio si está presente
        if (str_starts_with($imagePath, 'storage/')) {
            return substr($imagePath, 8); // Remover 'storage/'
        }
        
        return $imagePath;
    }
} 