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

        // Si el entorno es producción y usamos el disco privado (Cloudflare R2)
        if (app()->environment('production') && config('filesystems.default') === 'private') {
            // Para Laravel Cloud con Cloudflare R2, construir la URL directamente
            $awsUrl = config('filesystems.disks.private.url');
            if ($awsUrl) {
                return rtrim($awsUrl, '/') . '/' . ltrim($imagePath, '/');
            }
            
            // Fallback: construir URL manualmente usando configuración del bucket
            $bucket = config('filesystems.disks.private.bucket');
            $endpoint = config('filesystems.disks.private.endpoint');
            if ($bucket && $endpoint) {
                return rtrim($endpoint, '/') . '/' . $bucket . '/' . ltrim($imagePath, '/');
            }
            
            Log::warning('R2 configuration incomplete for image: ' . $imagePath);
            return asset($fallbackImage);
        }

        // Para desarrollo local
        if (str_starts_with($imagePath, 'storage/')) {
            return asset($imagePath);
        }

        // Fallback para disco público
        return asset('storage/' . $imagePath);
    }

    /**
     * Get the correct storage disk based on environment with production-first logic
     */
    public static function getStorageDisk(): string
    {
        $defaultDisk = config('filesystems.default');
        
        // En producción, usar siempre el disco privado (R2)
        if (app()->environment('production')) {
            // Verificar que las credenciales están configuradas
            $privateConfig = config('filesystems.disks.private');
            if ($privateConfig && $privateConfig['key'] && $privateConfig['secret'] && $privateConfig['bucket']) {
                return 'private';
            } else {
                Log::error('Production environment but R2 credentials not properly configured');
                throw new \Exception('Storage not configured for production environment');
            }
        }
        
        // En desarrollo, usar disco público por simplicidad
        return $defaultDisk === 'local' ? 'public' : $defaultDisk;
    }

    /**
     * Test if the storage disk is working properly - optimized for production
     */
    public static function testStorageDisk(): bool
    {
        try {
            $disk = self::getStorageDisk();
            $testFile = 'test_' . time() . '.txt';
            
            // Intentar escribir un archivo de prueba
            $success = Storage::disk($disk)->put($testFile, 'test content');
            
            if (!$success) {
                Log::error('Failed to write test file to storage disk: ' . $disk);
                return false;
            }
            
            // Verificar que existe
            $exists = Storage::disk($disk)->exists($testFile);
            
            // Limpiarlo
            Storage::disk($disk)->delete($testFile);
            
            return $exists;
        } catch (\Exception $e) {
            Log::error('Storage disk test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Store an uploaded file with proper error handling for production
     */
    public static function storeUploadedFile($file, string $directory = 'products'): string
    {
        if (!$file || !$file->isValid()) {
            throw new \Exception('Invalid file uploaded');
        }

        try {
            $disk = self::getStorageDisk();
            
            // Generar nombre único para el archivo
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $directory . '/' . $filename;
            
            // Intentar subir el archivo
            $success = Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));
            
            if (!$success) {
                throw new \Exception('Failed to store file to ' . $disk . ' disk');
            }
            
            // Verificar que se subió correctamente
            if (!Storage::disk($disk)->exists($path)) {
                throw new \Exception('File was not found after upload');
            }
            
            Log::info('File uploaded successfully to ' . $disk . ': ' . $path);
            return $path;
            
        } catch (\Exception $e) {
            Log::error('Error storing uploaded file: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete an image file safely
     */
    public static function deleteImage(?string $imagePath): bool
    {
        if (!$imagePath) {
            return true;
        }

        try {
            $disk = self::getStorageDisk();
            
            if (Storage::disk($disk)->exists($imagePath)) {
                $deleted = Storage::disk($disk)->delete($imagePath);
                if ($deleted) {
                    Log::info('Image deleted successfully: ' . $imagePath);
                } else {
                    Log::warning('Failed to delete image: ' . $imagePath);
                }
                return $deleted;
            }
            
            return true; // File doesn't exist, consider it "deleted"
            
        } catch (\Exception $e) {
            Log::error('Error deleting image: ' . $imagePath . ' - ' . $e->getMessage());
            return false;
        }
    }
} 