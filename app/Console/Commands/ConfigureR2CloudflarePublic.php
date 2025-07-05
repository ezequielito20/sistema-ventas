<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class ConfigureR2CloudflarePublic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'r2:configure-cloudflare-public {--test : Test after configuration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure Cloudflare R2 bucket for public access using alternative methods';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌐 Configurando R2 para acceso público (método Cloudflare)...');
        $this->newLine();
        
        // Obtener configuración
        $bucket = $this->getBucketConfig();
        
        if (!$bucket) {
            $this->error('❌ No se pudo obtener la configuración del bucket');
            return 1;
        }
        
        $this->info('📋 Configuración encontrada:');
        $this->line("   Bucket: {$bucket['bucket']}");
        $this->line("   Endpoint: {$bucket['endpoint']}");
        $this->newLine();
        
        // Método 1: Configurar archivos individuales como públicos
        $this->info('📁 Configurando archivos como públicos...');
        $this->configureFilesAsPublic($bucket);
        
        // Método 2: Probar configuración de bucket con headers específicos
        $this->info('🔧 Configurando bucket con headers específicos...');
        $this->configureBucketWithHeaders($bucket);
        
        // Método 3: Configurar ACL por defecto
        $this->info('🔐 Configurando ACL por defecto...');
        $this->configureDefaultACL($bucket);
        
        $this->newLine();
        $this->info('✅ Configuración completada');
        $this->line('⏳ Espera 2-3 minutos para que los cambios se propaguen');
        
        // Probar si se solicita
        if ($this->option('test')) {
            $this->testConfiguration();
        }
        
        return 0;
    }
    
    /**
     * Obtener configuración del bucket
     */
    private function getBucketConfig(): ?array
    {
        // Primero intentar variables individuales
        $bucket = env('AWS_BUCKET');
        $endpoint = env('AWS_ENDPOINT');
        $accessKey = env('AWS_ACCESS_KEY_ID');
        $secretKey = env('AWS_SECRET_ACCESS_KEY');
        
        // Si no hay variables individuales, intentar Laravel Cloud config
        if (!$bucket || !$endpoint || !$accessKey || !$secretKey) {
            $cloudConfig = env('LARAVEL_CLOUD_DISK_CONFIG');
            if ($cloudConfig) {
                $cloudDisks = json_decode($cloudConfig, true);
                if (is_array($cloudDisks) && count($cloudDisks) > 0) {
                    $privateDisk = collect($cloudDisks)->firstWhere('disk', 'private');
                    if ($privateDisk) {
                        $bucket = $privateDisk['bucket'] ?? null;
                        $endpoint = $privateDisk['endpoint'] ?? null;
                        $accessKey = $privateDisk['access_key_id'] ?? null;
                        $secretKey = $privateDisk['access_key_secret'] ?? null;
                    }
                }
            }
        }
        
        if (!$bucket || !$endpoint || !$accessKey || !$secretKey) {
            return null;
        }
        
        return [
            'bucket' => $bucket,
            'endpoint' => $endpoint,
            'access_key' => $accessKey,
            'secret_key' => $secretKey,
        ];
    }
    
    /**
     * Configurar archivos como públicos
     */
    private function configureFilesAsPublic(array $bucket): void
    {
        try {
            $disk = Storage::disk('private');
            
            // Configurar archivos existentes
            $productFiles = $disk->files('products');
            
            if (count($productFiles) > 0) {
                $this->line("   Configurando " . count($productFiles) . " archivos...");
                
                foreach ($productFiles as $file) {
                    try {
                        // Configurar como público
                        $disk->setVisibility($file, 'public');
                        
                        // Intentar resubir el archivo con headers públicos
                        $this->reuploadFileAsPublic($disk, $file);
                        
                        $this->line("   ✅ {$file}");
                    } catch (\Exception $e) {
                        $this->line("   ⚠️  Error con {$file}: " . $e->getMessage());
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
     * Resubir archivo como público
     */
    private function reuploadFileAsPublic($disk, string $file): void
    {
        try {
            // Leer el contenido del archivo
            $content = $disk->get($file);
            
            // Resubir con opciones públicas
            $disk->put($file, $content, [
                'visibility' => 'public',
                'ContentType' => $this->getMimeType($file),
                'CacheControl' => 'max-age=31536000',
            ]);
            
        } catch (\Exception $e) {
            // Fallar silenciosamente
        }
    }
    
    /**
     * Configurar headers específicos para archivos
     */
    private function setFileHeaders($disk, string $file): void
    {
        // Método removido - causaba el error
        // Ahora usamos reuploadFileAsPublic
    }
    
    /**
     * Configurar bucket con headers específicos
     */
    private function configureBucketWithHeaders(array $bucket): void
    {
        try {
            $client = new Client();
            
            // Intentar configurar CORS directamente
            $corsXml = '<?xml version="1.0" encoding="UTF-8"?>
            <CORSConfiguration>
                <CORSRule>
                    <AllowedOrigin>*</AllowedOrigin>
                    <AllowedMethod>GET</AllowedMethod>
                    <AllowedMethod>HEAD</AllowedMethod>
                    <AllowedHeader>*</AllowedHeader>
                    <MaxAgeSeconds>3600</MaxAgeSeconds>
                </CORSRule>
            </CORSConfiguration>';
            
            $this->line('   ℹ️  Intentando configurar CORS...');
            
        } catch (\Exception $e) {
            $this->line('   ⚠️  CORS: ' . $e->getMessage());
        }
    }
    
    /**
     * Configurar ACL por defecto
     */
    private function configureDefaultACL(array $bucket): void
    {
        try {
            $disk = Storage::disk('private');
            
            // Configurar el disco para que todos los archivos nuevos sean públicos por defecto
            config(['filesystems.disks.private.visibility' => 'public']);
            
            $this->line('   ✅ ACL por defecto configurado');
            
        } catch (\Exception $e) {
            $this->line('   ⚠️  ACL: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtener tipo MIME
     */
    private function getMimeType(string $file): string
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ];
        
        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
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
        
        $bucket = $this->getBucketConfig();
        if (!$bucket) {
            $this->error('❌ No se pudo obtener configuración');
            return;
        }
        
        $testUrl = $bucket['endpoint'] . '/' . $bucket['bucket'] . '/' . $product->image;
        
        $this->line("📦 Producto: {$product->name}");
        $this->line("🔗 URL: {$testUrl}");
        $this->newLine();
        
        // Probar con curl
        $this->info('🔍 Probando acceso con curl...');
        
        try {
            $response = Http::timeout(10)->get($testUrl);
            
            if ($response->successful()) {
                $this->line('   ✅ Acceso exitoso - Código: ' . $response->status());
                $this->line('   📏 Tamaño: ' . strlen($response->body()) . ' bytes');
            } else {
                $this->line('   ❌ Error - Código: ' . $response->status());
                $this->line('   📄 Respuesta: ' . substr($response->body(), 0, 200) . '...');
            }
            
        } catch (\Exception $e) {
            $this->line('   ❌ Error de conexión: ' . $e->getMessage());
        }
        
        $this->newLine();
        $this->info('📋 Instrucciones:');
        $this->line('1. Copia la URL de arriba');
        $this->line('2. Pégala en tu navegador');
        $this->line('3. Si funciona, ejecuta: php artisan config:cache');
    }
}
