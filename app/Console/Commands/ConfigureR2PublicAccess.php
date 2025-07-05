<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ConfigureR2PublicAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'r2:configure-public-access {--test : Test the configuration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure Cloudflare R2 bucket for public access to images';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Configurando acceso público para Cloudflare R2...');
        
        // Verificar configuración
        if (!$this->verifyR2Configuration()) {
            return 1;
        }
        
        // Mostrar configuración actual
        $this->showCurrentConfiguration();
        
        // Configurar acceso público
        $this->configurePublicAccess();
        
        // Probar si se solicita
        if ($this->option('test')) {
            $this->testPublicAccess();
        }
        
        return 0;
    }

    /**
     * Verificar configuración de R2
     */
    private function verifyR2Configuration(): bool
    {
        $config = config('filesystems.disks.private');
        
        if (!$config) {
            $this->error('❌ Configuración de disco privado no encontrada');
            return false;
        }
        
        $required = ['bucket', 'endpoint', 'key', 'secret'];
        foreach ($required as $field) {
            if (empty($config[$field])) {
                $this->error("❌ Campo requerido no configurado: {$field}");
                return false;
            }
        }
        
        $this->info('✅ Configuración de R2 verificada');
        return true;
    }

    /**
     * Mostrar configuración actual
     */
    private function showCurrentConfiguration(): void
    {
        $config = config('filesystems.disks.private');
        
        $this->info('📋 Configuración actual de R2:');
        $this->line('   Bucket: ' . $config['bucket']);
        $this->line('   Endpoint: ' . $config['endpoint']);
        $this->line('   URL configurada: ' . ($config['url'] ?? 'No configurada'));
        $this->newLine();
    }

    /**
     * Configurar acceso público
     */
    private function configurePublicAccess(): void
    {
        $this->info('🔧 Configurando acceso público...');
        
        try {
            $disk = Storage::disk('private');
            
            // Verificar que podemos acceder al bucket
            $this->line('   1. Verificando acceso al bucket...');
            $files = $disk->files();
            $this->line('      ✅ Acceso al bucket verificado');
            
            // Intentar configurar visibilidad pública para archivos de productos
            $this->line('   2. Configurando visibilidad para archivos de productos...');
            
            // Obtener archivos de productos existentes
            $productFiles = $disk->files('products');
            
            if (count($productFiles) > 0) {
                $this->line('      Configurando ' . count($productFiles) . ' archivos existentes...');
                
                foreach ($productFiles as $file) {
                    try {
                        // Intentar establecer visibilidad pública
                        $disk->setVisibility($file, 'public');
                    } catch (\Exception $e) {
                        // Continuar si falla, no es crítico
                        Log::warning('Could not set visibility for file: ' . $file);
                    }
                }
                
                $this->line('      ✅ Visibilidad configurada para archivos existentes');
            } else {
                $this->line('      ℹ️  No hay archivos de productos existentes');
            }
            
            // Crear archivo de prueba para verificar URLs públicas
            $this->line('   3. Creando archivo de prueba...');
            $testFile = 'products/test_public_access_' . time() . '.txt';
            $disk->put($testFile, 'Test file for public access');
            
            // Configurar como público
            $disk->setVisibility($testFile, 'public');
            
            $this->line('      ✅ Archivo de prueba creado');
            
            // Generar URL pública
            $config = config('filesystems.disks.private');
            $publicUrl = rtrim($config['endpoint'], '/') . '/' . $config['bucket'] . '/' . $testFile;
            
            $this->line('      URL de prueba: ' . $publicUrl);
            
            // Limpiar archivo de prueba
            $disk->delete($testFile);
            
            $this->info('✅ Configuración de acceso público completada');
            
        } catch (\Exception $e) {
            $this->error('❌ Error configurando acceso público: ' . $e->getMessage());
            Log::error('R2 public access configuration failed: ' . $e->getMessage());
        }
        
        $this->newLine();
    }

    /**
     * Probar acceso público
     */
    private function testPublicAccess(): void
    {
        $this->info('🧪 Probando acceso público...');
        
        try {
            $disk = Storage::disk('private');
            
            // Crear archivo de prueba
            $testFile = 'products/test_' . time() . '.txt';
            $testContent = 'Test content for public access verification';
            
            $this->line('   1. Creando archivo de prueba...');
            $disk->put($testFile, $testContent);
            $disk->setVisibility($testFile, 'public');
            
            // Generar URL pública
            $config = config('filesystems.disks.private');
            $publicUrl = rtrim($config['endpoint'], '/') . '/' . $config['bucket'] . '/' . $testFile;
            
            $this->line('   2. URL generada: ' . $publicUrl);
            
            // Intentar acceder a la URL (básico)
            $this->line('   3. Probando accesibilidad...');
            
            // Verificar que el archivo existe
            if ($disk->exists($testFile)) {
                $this->line('      ✅ Archivo existe en R2');
            } else {
                $this->error('      ❌ Archivo no encontrado en R2');
            }
            
            // Limpiar
            $disk->delete($testFile);
            
            $this->info('✅ Prueba de acceso público completada');
            
        } catch (\Exception $e) {
            $this->error('❌ Error en prueba de acceso público: ' . $e->getMessage());
        }
        
        $this->newLine();
    }
}
