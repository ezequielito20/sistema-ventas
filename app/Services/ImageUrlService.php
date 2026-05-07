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

        // Disco local 'public' (funciona con storage:link)
        if (Storage::disk('public')->exists($imagePath)) {
            return '/storage/' . $imagePath;
        }

        // Ruta proxy que sirve la imagen desde el disco por defecto (S3/R2)
        return route('image.serve', ['path' => $imagePath], false);
    }

    /**
     * Sirve una imagen desde el disco por defecto.
     * Usado por la ruta /img/{path}.
     */
    public static function serve(string $path): ?array
    {
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
