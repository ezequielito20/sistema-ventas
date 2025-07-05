<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixR2PublicAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'r2:fix-public-access {--test : Test access after configuration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix R2 public access using signed URLs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Solucionando acceso público a R2...');
        $this->newLine();
        
        // Paso 1: Configurar archivos como públicos
        $this->info('📁 Configurando archivos como públicos...');
        $this->configureFiles();
        
        // Paso 2: Actualizar ImageUrlService para usar URLs firmadas
        $this->info('🔗 Configurando URLs firmadas...');
        $this->configureSignedUrls();
        
        // Paso 3: Limpiar cache
        $this->info('🧹 Limpiando cache...');
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');
        
        $this->newLine();
        $this->info('✅ Configuración completada');
        
        if ($this->option('test')) {
            $this->testAccess();
        }
        
        return 0;
    }
    
    /**
     * Configurar archivos como públicos
     */
    private function configureFiles(): void
    {
        try {
            $disk = Storage::disk('private');
            $productFiles = $disk->files('products');
            
            if (count($productFiles) > 0) {
                $this->line("   Configurando " . count($productFiles) . " archivos...");
                
                foreach ($productFiles as $file) {
                    try {
                        $disk->setVisibility($file, 'public');
                        $this->line("   ✅ {$file}");
                    } catch (\Exception $e) {
                        $this->line("   ⚠️  {$file}: " . $e->getMessage());
                    }
                }
            } else {
                $this->line('   ℹ️  No hay archivos de productos');
            }
            
        } catch (\Exception $e) {
            $this->error('   ❌ Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Configurar URLs firmadas
     */
    private function configureSignedUrls(): void
    {
        try {
            // Verificar que el ImageUrlService existe
            $imageService = app(\App\Services\ImageUrlService::class);
            $this->line('   ✅ ImageUrlService disponible');
            
            // Probar generación de URL firmada
            $disk = Storage::disk('private');
            $product = \App\Models\Product::whereNotNull('image')->first();
            
            if ($product && $disk->exists($product->image)) {
                // Generar URL firmada (válida por 1 hora)
                try {
                    $signedUrl = $disk->temporaryUrl($product->image, now()->addHour());
                    $this->line("   ✅ URL firmada generada");
                    $this->line("   🔗 Ejemplo: " . substr($signedUrl, 0, 80) . "...");
                } catch (\Exception $e) {
                    $this->line("   ⚠️  URLs firmadas no disponibles: " . $e->getMessage());
                }
            }
            
        } catch (\Exception $e) {
            $this->line('   ⚠️  URLs firmadas: ' . $e->getMessage());
        }
    }
    
    /**
     * Probar acceso
     */
    private function testAccess(): void
    {
        $this->newLine();
        $this->info('🧪 Probando acceso...');
        
        $product = \App\Models\Product::whereNotNull('image')->first();
        
        if (!$product) {
            $this->warn('⚠️  No hay productos con imágenes');
            return;
        }
        
        $disk = Storage::disk('private');
        
        if (!$disk->exists($product->image)) {
            $this->error('❌ Archivo no existe: ' . $product->image);
            return;
        }
        
        $this->line("📦 Producto: {$product->name}");
        $this->line("🖼️  Imagen: {$product->image}");
        $this->newLine();
        
        // Probar diferentes tipos de URLs
        $this->info('🔍 Probando diferentes URLs...');
        
        // 1. URL directa
        $directUrl = $this->getDirectUrl($product->image);
        $this->line("1. URL directa: {$directUrl}");
        
        // 2. URL firmada
        try {
            if (method_exists($disk, 'temporaryUrl')) {
                $signedUrl = $disk->temporaryUrl($product->image, now()->addHour());
                $this->line("2. URL firmada: " . substr($signedUrl, 0, 80) . "...");
            } else {
                $this->line("2. URL firmada: No disponible en esta versión");
            }
        } catch (\Exception $e) {
            $this->line("2. URL firmada: Error - " . $e->getMessage());
        }
        
        // 3. URL del servicio
        try {
            $imageService = app(\App\Services\ImageUrlService::class);
            $serviceUrl = $imageService->getImageUrl($product->image);
            $this->line("3. URL del servicio: {$serviceUrl}");
        } catch (\Exception $e) {
            $this->line("3. URL del servicio: Error - " . $e->getMessage());
        }
        
        $this->newLine();
        $this->info('📋 Instrucciones:');
        $this->line('1. Copia la URL firmada (opción 2)');
        $this->line('2. Pégala en tu navegador');
        $this->line('3. Si funciona, las URLs firmadas son la solución');
        $this->line('4. Si no funciona, contacta soporte de Laravel Cloud');
        $this->newLine();
        
        $this->info('💡 Recomendación:');
        $this->line('Si las URLs firmadas funcionan, podemos actualizar el');
        $this->line('ImageUrlService para usar URLs firmadas automáticamente');
    }
    
    /**
     * Obtener URL directa
     */
    private function getDirectUrl(string $imagePath): string
    {
        $bucket = $this->getBucket();
        $endpoint = $this->getEndpoint();
        
        return $endpoint . '/' . $bucket . '/' . $imagePath;
    }
    
    /**
     * Obtener bucket
     */
    private function getBucket(): string
    {
        $bucket = env('AWS_BUCKET');
        
        if (!$bucket) {
            $cloudConfig = env('LARAVEL_CLOUD_DISK_CONFIG');
            if ($cloudConfig) {
                $cloudDisks = json_decode($cloudConfig, true);
                if (is_array($cloudDisks) && count($cloudDisks) > 0) {
                    $privateDisk = collect($cloudDisks)->firstWhere('disk', 'private');
                    if ($privateDisk) {
                        $bucket = $privateDisk['bucket'] ?? null;
                    }
                }
            }
        }
        
        return $bucket ?? 'unknown';
    }
    
    /**
     * Obtener endpoint
     */
    private function getEndpoint(): string
    {
        $endpoint = env('AWS_ENDPOINT');
        
        if (!$endpoint) {
            $cloudConfig = env('LARAVEL_CLOUD_DISK_CONFIG');
            if ($cloudConfig) {
                $cloudDisks = json_decode($cloudConfig, true);
                if (is_array($cloudDisks) && count($cloudDisks) > 0) {
                    $privateDisk = collect($cloudDisks)->firstWhere('disk', 'private');
                    if ($privateDisk) {
                        $endpoint = $privateDisk['endpoint'] ?? null;
                    }
                }
            }
        }
        
        return $endpoint ?? 'unknown';
    }
}
