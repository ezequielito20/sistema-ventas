<?php

namespace App\Console\Commands;

use App\Services\ImageUrlService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DiagnoseStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:diagnose {--test : Run storage tests}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose storage configuration and test file operations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Diagnosticando configuración de almacenamiento...');
        
        // Mostrar configuración actual
        $this->showStorageConfig();
        
        // Mostrar discos disponibles
        $this->showAvailableDisks();
        
        // Probar operaciones de archivos si se solicita
        if ($this->option('test')) {
            $this->testStorageOperations();
        }
        
        // Mostrar recomendaciones
        $this->showRecommendations();
        
        return 0;
    }

    /**
     * Mostrar configuración de almacenamiento
     */
    private function showStorageConfig(): void
    {
        $this->info('📋 Configuración de Almacenamiento:');
        
        $defaultDisk = config('filesystems.default');
        $this->line("  Disco por defecto: <fg=yellow>{$defaultDisk}</>");
        
        $environment = app()->environment();
        $this->line("  Entorno: <fg=yellow>{$environment}</>");
        
        // Configuración del disco por defecto
        $diskConfig = config("filesystems.disks.{$defaultDisk}");
        if ($diskConfig) {
            $this->line("  Configuración del disco '{$defaultDisk}':");
            $this->line("    Driver: " . ($diskConfig['driver'] ?? 'No configurado'));
            
            if ($diskConfig['driver'] === 's3') {
                $this->line("    Bucket: " . ($diskConfig['bucket'] ?? 'No configurado'));
                $this->line("    Region: " . ($diskConfig['region'] ?? 'No configurado'));
                $this->line("    Endpoint: " . ($diskConfig['endpoint'] ?? 'No configurado'));
                $this->line("    URL: " . ($diskConfig['url'] ?? 'No configurado'));
                $this->line("    Key configurado: " . ($diskConfig['key'] ? 'Sí' : 'No'));
                $this->line("    Secret configurado: " . ($diskConfig['secret'] ? 'Sí' : 'No'));
            } else {
                $this->line("    Root: " . ($diskConfig['root'] ?? 'No configurado'));
                $this->line("    URL: " . ($diskConfig['url'] ?? 'No configurado'));
            }
        }
        
        $this->newLine();
    }

    /**
     * Mostrar discos disponibles
     */
    private function showAvailableDisks(): void
    {
        $this->info('💾 Discos Disponibles:');
        
        $disks = config('filesystems.disks');
        foreach ($disks as $name => $config) {
            $status = $this->testDiskConnection($name);
            $statusIcon = $status ? '✅' : '❌';
            $this->line("  {$statusIcon} {$name} ({$config['driver']})");
        }
        
        $this->newLine();
    }

    /**
     * Probar conexión a un disco
     */
    private function testDiskConnection(string $diskName): bool
    {
        try {
            // Intentar listar archivos (operación básica)
            Storage::disk($diskName)->files();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Probar operaciones de almacenamiento
     */
    private function testStorageOperations(): void
    {
        $this->info('🧪 Probando operaciones de almacenamiento...');
        
        $disk = ImageUrlService::getStorageDisk();
        $this->line("  Usando disco: <fg=yellow>{$disk}</>");
        
        // Test 1: Escribir archivo
        $this->line('  📝 Probando escritura de archivo...');
        $testFile = 'diagnostic_test_' . time() . '.txt';
        $testContent = 'Test content for storage diagnostic';
        
        try {
            $success = Storage::disk($disk)->put($testFile, $testContent);
            if ($success) {
                $this->line('    ✅ Escritura exitosa');
            } else {
                $this->line('    ❌ Fallo en escritura');
                return;
            }
        } catch (\Exception $e) {
            $this->error('    ❌ Error en escritura: ' . $e->getMessage());
            return;
        }
        
        // Test 2: Leer archivo
        $this->line('  📖 Probando lectura de archivo...');
        try {
            $content = Storage::disk($disk)->get($testFile);
            if ($content === $testContent) {
                $this->line('    ✅ Lectura exitosa');
            } else {
                $this->line('    ❌ Contenido no coincide');
            }
        } catch (\Exception $e) {
            $this->error('    ❌ Error en lectura: ' . $e->getMessage());
        }
        
        // Test 3: Verificar existencia
        $this->line('  🔍 Probando verificación de existencia...');
        try {
            $exists = Storage::disk($disk)->exists($testFile);
            if ($exists) {
                $this->line('    ✅ Verificación exitosa');
            } else {
                $this->line('    ❌ Archivo no encontrado');
            }
        } catch (\Exception $e) {
            $this->error('    ❌ Error en verificación: ' . $e->getMessage());
        }
        
        // Test 4: Obtener URL
        $this->line('  🔗 Probando generación de URL...');
        try {
            $url = ImageUrlService::getImageUrl($testFile);
            $this->line("    URL generada: <fg=cyan>{$url}</>");
        } catch (\Exception $e) {
            $this->error('    ❌ Error generando URL: ' . $e->getMessage());
        }
        
        // Test 5: Eliminar archivo
        $this->line('  🗑️  Probando eliminación de archivo...');
        try {
            $deleted = Storage::disk($disk)->delete($testFile);
            if ($deleted) {
                $this->line('    ✅ Eliminación exitosa');
            } else {
                $this->line('    ❌ Fallo en eliminación');
            }
        } catch (\Exception $e) {
            $this->error('    ❌ Error en eliminación: ' . $e->getMessage());
        }
        
        $this->newLine();
    }

    /**
     * Mostrar recomendaciones
     */
    private function showRecommendations(): void
    {
        $this->info('💡 Recomendaciones:');
        
        $defaultDisk = config('filesystems.default');
        $environment = app()->environment();
        
        if ($environment === 'production') {
            if ($defaultDisk === 'private') {
                $diskConfig = config('filesystems.disks.private');
                if (!$diskConfig['key'] || !$diskConfig['secret']) {
                    $this->warn('  ⚠️  Credenciales de S3/R2 no configuradas correctamente');
                    $this->line('      Verificar variables: AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY');
                }
                
                if (!$diskConfig['bucket']) {
                    $this->warn('  ⚠️  Bucket de S3/R2 no configurado');
                    $this->line('      Verificar variable: AWS_BUCKET');
                }
                
                if (!$diskConfig['endpoint']) {
                    $this->warn('  ⚠️  Endpoint de R2 no configurado');
                    $this->line('      Verificar variable: AWS_ENDPOINT');
                }
            } else {
                $this->warn('  ⚠️  En producción se recomienda usar disco "private" para mayor seguridad');
                $this->line('      Configurar FILESYSTEM_DISK=private en .env');
            }
        } else {
            if ($defaultDisk !== 'public') {
                $this->info('  💡 En desarrollo se recomienda usar disco "public" para simplicidad');
                $this->line('      Configurar FILESYSTEM_DISK=public en .env');
            }
            
            $this->info('  💡 Asegurar que el enlace simbólico está creado:');
            $this->line('      php artisan storage:link');
        }
        
        $this->newLine();
        $this->info('🚀 Para probar operaciones de almacenamiento, ejecutar:');
        $this->line('    php artisan storage:diagnose --test');
    }
}
