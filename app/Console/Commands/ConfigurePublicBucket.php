<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;

class ConfigurePublicBucket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bucket:configure-public {--test : Test after configuration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure R2 bucket for public access';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Configurando bucket R2 para acceso público...');
        $this->newLine();
        
        try {
            // Obtener configuración - primero intentar variables individuales, luego Laravel Cloud
            $bucket = env('AWS_BUCKET');
            $accessKey = env('AWS_ACCESS_KEY_ID');
            $secretKey = env('AWS_SECRET_ACCESS_KEY');
            $endpoint = env('AWS_ENDPOINT');
            $region = env('AWS_DEFAULT_REGION', 'auto');
            
            // Si no hay variables individuales, intentar Laravel Cloud config
            if (!$bucket || !$accessKey || !$secretKey || !$endpoint) {
                $this->line('🔍 Variables AWS individuales no encontradas, buscando configuración de Laravel Cloud...');
                
                $cloudConfig = env('LARAVEL_CLOUD_DISK_CONFIG');
                if ($cloudConfig) {
                    $cloudDisks = json_decode($cloudConfig, true);
                    
                    if (is_array($cloudDisks) && count($cloudDisks) > 0) {
                        $privateDisk = collect($cloudDisks)->firstWhere('disk', 'private');
                        
                        if ($privateDisk) {
                            $bucket = $privateDisk['bucket'] ?? null;
                            $accessKey = $privateDisk['access_key_id'] ?? null;
                            $secretKey = $privateDisk['access_key_secret'] ?? null;
                            $endpoint = $privateDisk['endpoint'] ?? null;
                            $region = $privateDisk['default_region'] ?? 'auto';
                            
                            $this->line('   ✅ Configuración de Laravel Cloud encontrada');
                        }
                    }
                }
            }
            
            if (!$bucket || !$accessKey || !$secretKey || !$endpoint) {
                $this->error('❌ Configuración de R2 incompleta');
                $this->line('No se encontraron las credenciales en:');
                $this->line('- Variables AWS individuales (AWS_BUCKET, AWS_ACCESS_KEY_ID, etc.)');
                $this->line('- Configuración de Laravel Cloud (LARAVEL_CLOUD_DISK_CONFIG)');
                return 1;
            }
            
            $this->info('📋 Configuración encontrada:');
            $this->line("   Bucket: {$bucket}");
            $this->line("   Endpoint: {$endpoint}");
            $this->line("   Region: {$region}");
            $this->newLine();
            
            // Crear cliente S3
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $region,
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => false,
                'credentials' => [
                    'key' => $accessKey,
                    'secret' => $secretKey,
                ],
            ]);
            
            // Configurar política del bucket para acceso público de lectura
            $this->info('🔓 Configurando política de acceso público...');
            
            $bucketPolicy = [
                'Version' => '2012-10-17',
                'Statement' => [
                    [
                        'Sid' => 'PublicReadGetObject',
                        'Effect' => 'Allow',
                        'Principal' => '*',
                        'Action' => 's3:GetObject',
                        'Resource' => 'arn:aws:s3:::' . $bucket . '/*'
                    ]
                ]
            ];
            
            try {
                $s3Client->putBucketPolicy([
                    'Bucket' => $bucket,
                    'Policy' => json_encode($bucketPolicy)
                ]);
                $this->line('   ✅ Política de bucket configurada correctamente');
            } catch (\Exception $e) {
                $this->error('   ❌ Error configurando política: ' . $e->getMessage());
                $this->line('   ℹ️  Esto puede ser normal si el bucket ya está configurado');
            }
            
            // Configurar CORS
            $this->info('🌐 Configurando CORS...');
            
            $corsConfiguration = [
                'CORSRules' => [
                    [
                        'AllowedHeaders' => ['*'],
                        'AllowedMethods' => ['GET', 'HEAD'],
                        'AllowedOrigins' => ['*'],
                        'MaxAgeSeconds' => 3600,
                    ]
                ]
            ];
            
            try {
                $s3Client->putBucketCors([
                    'Bucket' => $bucket,
                    'CORSConfiguration' => $corsConfiguration
                ]);
                $this->line('   ✅ CORS configurado correctamente');
            } catch (\Exception $e) {
                $this->error('   ❌ Error configurando CORS: ' . $e->getMessage());
                $this->line('   ℹ️  Esto puede ser normal si el bucket ya está configurado');
            }
            
            // Configurar archivos existentes como públicos
            $this->info('📁 Configurando archivos existentes como públicos...');
            
            try {
                $disk = Storage::disk('private');
                $productFiles = $disk->files('products');
                
                if (count($productFiles) > 0) {
                    $fileCount = count($productFiles);
                    $this->line("   Configurando {$fileCount} archivos...");
                    
                    foreach ($productFiles as $file) {
                        try {
                            $disk->setVisibility($file, 'public');
                        } catch (\Exception $e) {
                            $this->line("   ⚠️  No se pudo configurar: {$file}");
                        }
                    }
                    
                    $this->line('   ✅ Archivos configurados como públicos');
                } else {
                    $this->line('   ℹ️  No hay archivos de productos para configurar');
                }
            } catch (\Exception $e) {
                $this->error('   ❌ Error configurando archivos: ' . $e->getMessage());
            }
            
            $this->newLine();
            $this->info('✅ Configuración completada');
            $this->line('⏳ Espera 2-3 minutos para que los cambios se propaguen');
            
            // Probar si se solicita
            if ($this->option('test')) {
                $this->testConfiguration();
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error general: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Probar la configuración
     */
    private function testConfiguration(): void
    {
        $this->newLine();
        $this->info('🧪 Probando configuración...');
        
        // Buscar un producto con imagen
        $product = \App\Models\Product::whereNotNull('image')->first();
        
        if (!$product) {
            $this->warn('⚠️  No hay productos con imágenes para probar');
            return;
        }
        
        // Obtener configuración (igual que en handle())
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
        
        $testUrl = $endpoint . '/' . $bucket . '/' . $product->image;
        
        $this->line("📦 Producto de prueba: {$product->name}");
        $this->line("🔗 URL de prueba: {$testUrl}");
        $this->newLine();
        
        $this->info('🧪 Instrucciones de prueba:');
        $this->line('1. Copia la URL de arriba');
        $this->line('2. Pégala en tu navegador');
        $this->line('3. Si funciona, las imágenes deberían mostrarse en el sistema');
        $this->line('4. Ejecuta: php artisan config:cache para limpiar cache');
        $this->newLine();
        
        $this->info('📋 Siguiente paso:');
        $this->line('Si la URL funciona, ejecuta: php artisan test:image-quick');
        $this->line('para verificar que todo el sistema funciona correctamente');
    }
}
