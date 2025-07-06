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

        // En desarrollo local, usar storage público
        if (app()->environment('local')) {
            // Si el archivo existe en storage público local, usarlo
            if (Storage::disk('public')->exists($imagePath)) {
                return Storage::disk('public')->url($imagePath);
            }
            // Si no existe localmente, usar la imagen por defecto
            return asset($fallbackImage);
        }

        // En producción, usar el bucket público de R2
        // Como es público, construir la URL directamente
        $endpoint = config('filesystems.disks.s3.endpoint');
        $bucket = config('filesystems.disks.s3.bucket');
        
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
} 