<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ImageUrlService
{
    /**
     * Obtener URL de imagen con fallback a diferentes métodos
     */
    public function getImageUrl(?string $imagePath): string
    {
        if (!$imagePath) {
            return $this->getDefaultImageUrl();
        }

        try {
            $disk = Storage::disk('private');
            
            // Verificar que el archivo existe
            if (!$disk->exists($imagePath)) {
                Log::warning("Imagen no encontrada: {$imagePath}");
                return $this->getDefaultImageUrl();
            }

            // Método 1: Intentar URL firmada (válida por 24 horas)
            $signedUrl = $this->getSignedUrl($imagePath);
            if ($signedUrl) {
                return $signedUrl;
            }

            // Método 2: URL directa (puede no funcionar en producción)
            $directUrl = $this->getDirectUrl($imagePath);
            
            // Método 3: Usar ruta proxy como fallback
            return route('image.proxy', ['path' => base64_encode($imagePath)]);
            
        } catch (\Exception $e) {
            Log::error("Error obteniendo URL de imagen: {$e->getMessage()}", [
                'image_path' => $imagePath,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->getDefaultImageUrl();
        }
    }

    /**
     * Obtener URL firmada (válida por 24 horas)
     */
    private function getSignedUrl(string $imagePath): ?string
    {
        try {
            $disk = Storage::disk('private');
            
            // Verificar si el método existe
            if (!method_exists($disk, 'temporaryUrl')) {
                return null;
            }
            
            // Usar cache para evitar generar URLs constantemente
            $cacheKey = 'signed_url_' . md5($imagePath);
            
            return Cache::remember($cacheKey, now()->addHours(20), function () use ($disk, $imagePath) {
                return $disk->temporaryUrl($imagePath, now()->addHours(24));
            });
            
        } catch (\Exception $e) {
            Log::debug("URL firmada no disponible: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Obtener URL directa
     */
    private function getDirectUrl(string $imagePath): string
    {
        $config = $this->getR2Config();
        
        if (!$config) {
            return $this->getDefaultImageUrl();
        }
        
        return $config['endpoint'] . '/' . $config['bucket'] . '/' . $imagePath;
    }

    /**
     * Obtener configuración de R2
     */
    private function getR2Config(): ?array
    {
        // Intentar variables individuales primero
        $bucket = env('AWS_BUCKET');
        $endpoint = env('AWS_ENDPOINT');
        
        // Si no hay variables individuales, intentar Laravel Cloud config
        if (!$bucket || !$endpoint) {
            $cloudConfig = env('LARAVEL_CLOUD_DISK_CONFIG');
            if ($cloudConfig) {
                $cloudDisks = json_decode($cloudConfig, true);
                if (is_array($cloudDisks) && count($cloudDisks) > 0) {
                    $privateDisk = collect($cloudDisks)->firstWhere('disk', 'private');
                    if ($privateDisk) {
                        $bucket = $privateDisk['bucket'] ?? null;
                        $endpoint = $privateDisk['endpoint'] ?? null;
                    }
                }
            }
        }
        
        if (!$bucket || !$endpoint) {
            return null;
        }
        
        return [
            'bucket' => $bucket,
            'endpoint' => $endpoint,
        ];
    }

    /**
     * Obtener URL de imagen por defecto
     */
    private function getDefaultImageUrl(): string
    {
        return asset('img/no-image.svg');
    }

    /**
     * Almacenar archivo subido
     */
    public function storeUploadedFile($file, string $directory = 'products'): string
    {
        try {
            $disk = Storage::disk('private');
            
            // Generar nombre único
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $directory . '/' . $filename;
            
            // Subir archivo con visibilidad pública
            $disk->putFileAs($directory, $file, $filename, [
                'visibility' => 'public',
                'ContentType' => $file->getMimeType(),
                'CacheControl' => 'max-age=31536000',
            ]);
            
            Log::info("Imagen subida exitosamente: {$path}");
            
            return $path;
            
        } catch (\Exception $e) {
            Log::error("Error subiendo imagen: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Eliminar imagen
     */
    public function deleteImage(?string $imagePath): bool
    {
        if (!$imagePath) {
            return true;
        }

        try {
            $disk = Storage::disk('private');
            
            if ($disk->exists($imagePath)) {
                $disk->delete($imagePath);
                
                // Limpiar cache de URL firmada
                $cacheKey = 'signed_url_' . md5($imagePath);
                Cache::forget($cacheKey);
                
                Log::info("Imagen eliminada: {$imagePath}");
                return true;
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Error eliminando imagen: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Probar conectividad del storage
     */
    public function testStorageDisk(): array
    {
        $results = [];
        
        try {
            $disk = Storage::disk('private');
            
            // Test 1: Verificar conexión
            $results['connection'] = $disk->exists('.') ? 'OK' : 'FAIL';
            
            // Test 2: Verificar escritura
            $testFile = 'test_' . time() . '.txt';
            $disk->put($testFile, 'test content', 'public');
            $results['write'] = $disk->exists($testFile) ? 'OK' : 'FAIL';
            
            // Test 3: Verificar lectura
            $content = $disk->get($testFile);
            $results['read'] = ($content === 'test content') ? 'OK' : 'FAIL';
            
            // Test 4: Verificar URL firmada
            try {
                if (method_exists($disk, 'temporaryUrl')) {
                    $signedUrl = $disk->temporaryUrl($testFile, now()->addMinutes(5));
                    $results['signed_url'] = !empty($signedUrl) ? 'OK' : 'FAIL';
                } else {
                    $results['signed_url'] = 'NOT_AVAILABLE';
                }
            } catch (\Exception $e) {
                $results['signed_url'] = 'ERROR: ' . $e->getMessage();
            }
            
            // Test 5: Verificar eliminación
            $disk->delete($testFile);
            $results['delete'] = !$disk->exists($testFile) ? 'OK' : 'FAIL';
            
            // Test 6: Configuración
            $config = $this->getR2Config();
            $results['config'] = $config ? 'OK' : 'FAIL';
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }
} 