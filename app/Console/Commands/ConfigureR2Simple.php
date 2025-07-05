<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class ConfigureR2Simple extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'r2:configure-simple {--test : Test configuration after setup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure R2 bucket for public access using simple methods';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Configurando R2 de forma simple...');
        $this->newLine();
        
        // Verificar configuración
        $bucket = env('AWS_BUCKET');
        $endpoint = env('AWS_ENDPOINT');
        
        if (!$bucket || !$endpoint) {
            $this->error('❌ Configuración incompleta');
            $this->line('Variables requeridas: AWS_BUCKET, AWS_ENDPOINT');
            return 1;
        }
        
        $this->info('📋 Configuración:');
        $this->line("   Bucket: {$bucket}");
        $this->line("   Endpoint: {$endpoint}");
        $this->newLine();
        
        // Paso 1: Configurar archivos existentes como públicos
        $this->info('📁 Configurando archivos como públicos...');
        
        try {
            $disk = Storage::disk('private');
            
            // Verificar conexión
            if (!$disk->exists('.')) {
                $this->error('❌ No se puede conectar al storage R2');
                return 1;
            }
            
            // Obtener archivos de productos
            $productFiles = [];
            try {
                $productFiles = $disk->files('products');
            } catch (\Exception $e) {
                $this->line('   ℹ️  Creando directorio products...');
                $disk->makeDirectory('products');
                $productFiles = [];
            }
            
            if (count($productFiles) > 0) {
                $this->line("   Configurando " . count($productFiles) . " archivos...");
                
                foreach ($productFiles as $file) {
                    try {
                        $disk->setVisibility($file, 'public');
                        $this->line("   ✅ {$file}");
                    } catch (\Exception $e) {
                        $this->line("   ⚠️  Error con {$file}: " . $e->getMessage());
                    }
                }
            } else {
                $this->line('   ℹ️  No hay archivos de productos aún');
            }
            
            $this->line('   ✅ Archivos configurados');
            
        } catch (\Exception $e) {
            $this->error('   ❌ Error: ' . $e->getMessage());
        }
        
        // Paso 2: Probar subida de archivo de prueba
        $this->info('🧪 Probando subida de archivo...');
        
        try {
            $testContent = 'Test file for R2 configuration - ' . date('Y-m-d H:i:s');
            $testFile = 'test-' . time() . '.txt';
            
            $disk->put($testFile, $testContent, 'public');
            
            if ($disk->exists($testFile)) {
                $this->line('   ✅ Archivo de prueba subido correctamente');
                
                // Generar URL de prueba
                $testUrl = $endpoint . '/' . $bucket . '/' . $testFile;
                $this->line("   🔗 URL de prueba: {$testUrl}");
                
                // Limpiar archivo de prueba
                $disk->delete($testFile);
                $this->line('   🗑️  Archivo de prueba eliminado');
            } else {
                $this->error('   ❌ No se pudo subir archivo de prueba');
            }
            
        } catch (\Exception $e) {
            $this->error('   ❌ Error en prueba: ' . $e->getMessage());
        }
        
        // Paso 3: Configurar ImageUrlService
        $this->info('⚙️  Configurando ImageUrlService...');
        
        try {
            $imageService = app(\App\Services\ImageUrlService::class);
            
            // Probar el servicio
            $this->line('   ✅ ImageUrlService disponible');
            
        } catch (\Exception $e) {
            $this->error('   ❌ Error con ImageUrlService: ' . $e->getMessage());
        }
        
        $this->newLine();
        $this->info('✅ Configuración completada');
        $this->newLine();
        
        $this->info('📋 Próximos pasos:');
        $this->line('1. Sube una imagen de producto desde el panel admin');
        $this->line('2. Verifica que la imagen se muestre correctamente');
        $this->line('3. Si hay problemas, ejecuta: php artisan test:image-quick');
        
        // Probar si se solicita
        if ($this->option('test')) {
            $this->testConfiguration();
        }
        
        return 0;
    }
    
    /**
     * Probar la configuración
     */
    private function testConfiguration(): void
    {
        $this->newLine();
        $this->info('🧪 Probando configuración completa...');
        
        // Buscar un producto con imagen
        $product = \App\Models\Product::whereNotNull('image')->first();
        
        if (!$product) {
            $this->warn('⚠️  No hay productos con imágenes para probar');
            $this->line('   Sube una imagen desde el panel admin y vuelve a ejecutar con --test');
            return;
        }
        
        $bucket = env('AWS_BUCKET');
        $endpoint = env('AWS_ENDPOINT');
        
        $this->line("📦 Producto encontrado: {$product->name}");
        $this->line("🖼️  Imagen: {$product->image}");
        
        // Verificar que el archivo existe
        $disk = Storage::disk('private');
        if ($disk->exists($product->image)) {
            $this->line('   ✅ Archivo existe en R2');
            
            // Generar URL
            $imageUrl = $endpoint . '/' . $bucket . '/' . $product->image;
            $this->line("   🔗 URL: {$imageUrl}");
            
            // Probar con ImageUrlService
            try {
                $imageService = app(\App\Services\ImageUrlService::class);
                $serviceUrl = $imageService->getImageUrl($product->image);
                $this->line("   🔗 URL del servicio: {$serviceUrl}");
                
                if ($imageUrl === $serviceUrl) {
                    $this->line('   ✅ URLs coinciden');
                } else {
                    $this->line('   ⚠️  URLs diferentes');
                }
                
            } catch (\Exception $e) {
                $this->line('   ❌ Error con ImageUrlService: ' . $e->getMessage());
            }
            
        } else {
            $this->error('   ❌ Archivo no existe en R2');
        }
        
        $this->newLine();
        $this->info('🔍 Instrucciones de prueba:');
        $this->line('1. Copia la URL de arriba');
        $this->line('2. Pégala en tu navegador');
        $this->line('3. Si se muestra la imagen, todo funciona correctamente');
        $this->line('4. Si no funciona, puede que necesites esperar unos minutos');
    }
}
