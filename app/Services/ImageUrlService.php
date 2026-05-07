<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ImageUrlService
{
    /**
     * Resuelve la URL pública de una imagen.
     *
     * En local: usa disco 'public' + symlink storage:link.
     * Si el archivo no está en disco local, se sirve vía ruta proxy (/img/...)
     * que lee del disco por defecto (S3/R2) y devuelve la imagen directamente.
     */
    public static function getImageUrl(?string $imagePath, string $fallbackImage = 'img/no-image.svg'): string
    {
        if ($imagePath === null || trim($imagePath) === '') {
            return asset($fallbackImage);
        }

        $imagePath = self::normalizePath(trim($imagePath));

        if ($imagePath === '') {
            return asset($fallbackImage);
        }

        if (preg_match('#^https?://#i', $imagePath)) {
            return $imagePath;
        }

        // Disco local 'public' (funciona con storage:link en desarrollo local)
        // Verificar que el archivo realmente exista en el filesystem local
        if (Storage::disk('public')->exists($imagePath)) {
            $localPath = storage_path('app/public/' . str_replace('\\', '/', $imagePath));
            if (is_file($localPath) && is_readable($localPath)) {
                return '/storage/' . $imagePath;
            }
        }

        // Ruta proxy que sirve la imagen desde el disco por defecto (S3/R2)
        return route('image.serve', ['path' => $imagePath], false);
    }

    /**
     * Sirve una imagen desde el disco por defecto (o local).
     * Usado por la ruta /img/{path} y por los PDFs.
     */
    public static function serve(string $path): ?array
    {
        // 1. Archivo local real
        $localPath = storage_path('app/public/' . str_replace('\\', '/', $path));
        if (is_file($localPath) && is_readable($localPath)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = $ext === 'png' ? 'image/png' : ($ext === 'gif' ? 'image/gif' : ($ext === 'webp' ? 'image/webp' : ($ext === 'svg' ? 'image/svg+xml' : 'image/jpeg')));
            return [
                'content' => file_get_contents($localPath),
                'mime' => $mime,
                'size' => filesize($localPath),
                'lastModified' => filemtime($localPath),
            ];
        }

        // 2. Disco por defecto (s3/R2)
        $disk = Storage::disk(config('filesystems.default', 'public'));

        if (!$disk->exists($path)) {
            return null;
        }

        return [
            'content' => $disk->get($path),
            'mime' => $disk->mimeType($path) ?: 'application/octet-stream',
            'size' => $disk->size($path),
            'lastModified' => $disk->lastModified($path),
        ];
    }

    /**
     * Disco recomendado para nuevas subidas.
     */
    public static function getStorageDisk(): string
    {
        return config('filesystems.default', 'public');
    }

    private static function normalizePath(string $imagePath): string
    {
        if (str_starts_with($imagePath, 'storage/')) {
            return substr($imagePath, 8);
        }
        return $imagePath;
    }
}
