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

        // Si el entorno es producción y usamos el disco privado
        if (app()->environment('production') && config('filesystems.default') === 'private') {
            // Para Laravel Cloud con Cloudflare R2, construir la URL directamente
            $awsUrl = config('filesystems.disks.private.url');
            if ($awsUrl) {
                return rtrim($awsUrl, '/') . '/' . ltrim($imagePath, '/');
            }
            // Fallback si no hay URL configurada
            return config('app.url') . '/storage/' . $imagePath;
        }

        // Para desarrollo local
        if (str_starts_with($imagePath, 'storage/')) {
            return asset($imagePath);
        }

        // Fallback para disco público
        return asset('storage/' . $imagePath);
    }

    /**
     * Get the correct storage disk based on environment
     */
    public static function getStorageDisk(): string
    {
        return config('filesystems.default');
    }
} 