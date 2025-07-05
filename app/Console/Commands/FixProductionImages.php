<?php

namespace App\Console\Commands;

use App\Services\ImageUrlService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class FixProductionImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:fix-production {--test : Run tests after fixing} {--force : Force execution even if not in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix image storage issues in production environment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando corrección de imágenes en producción...');
        
        // Verificar entorno
        if (!$this->verifyEnvironment()) {
            return 1;
        }
        
        // Mostrar configuración actual
        $this->showCurrentConfiguration();
        
        // Ejecutar correcciones
        $this->executeFixSteps();
        
        // Probar si se solicita
        if ($this->option('test')) {
            $this->runTests();
        }
        
        $this->showSummary();
        
        return 0;
    }

    /**
     * Verificar que estamos en el entorno correcto
     */
    private function verifyEnvironment(): bool
    {
        $environment = app()->environment();
        
        if ($environment !== 'production' && !$this->option('force')) {
            $this->error('⚠️  Este comando está diseñado para producción');
            $this->line('   Entorno actual: ' . $environment);
            $this->line('   Usar --force para ejecutar en otros entornos');
            return false;
        }
        
        $this->info("✅ Entorno verificado: {$environment}");
        return true;
    }

    /**
     * Mostrar configuración actual
     */
    private function showCurrentConfiguration(): void
    {
        $this->info('📋 Configuración actual:');
        
        $defaultDisk = config('filesystems.default');
        $this->line("   Disco por defecto: {$defaultDisk}");
        
        if ($defaultDisk === 'private') {
            $config = config('filesystems.disks.private');
            $this->line('   Configuración R2:');
            $this->line('     - Bucket: ' . ($config['bucket'] ?? 'No configurado'));
            $this->line('     - Endpoint: ' . ($config['endpoint'] ?? 'No configurado'));
            $this->line('     - URL: ' . ($config['url'] ?? 'No configurado'));
            $this->line('     - Key: ' . ($config['key'] ? 'Configurado' : 'No configurado'));
            $this->line('     - Secret: ' . ($config['secret'] ? 'Configurado' : 'No configurado'));
        }
        
        $this->newLine();
    }

    /**
     * Ejecutar pasos de corrección
     */
    private function executeFixSteps(): void
    {
        $this->info('🔧 Ejecutando correcciones...');
        
        // Paso 1: Limpiar caché de configuración
        $this->line('   1. Limpiando caché de configuración...');
        try {
            Artisan::call('config:clear');
            Artisan::call('config:cache');
            $this->line('      ✅ Caché de configuración actualizado');
        } catch (\Exception $e) {
            $this->error('      ❌ Error al limpiar caché: ' . $e->getMessage());
        }
        
        // Paso 2: Verificar disco de almacenamiento
        $this->line('   2. Verificando disco de almacenamiento...');
        try {
            $disk = ImageUrlService::getStorageDisk();
            $this->line("      ✅ Disco seleccionado: {$disk}");
        } catch (\Exception $e) {
            $this->error('      ❌ Error con disco: ' . $e->getMessage());
            return;
        }
        
        // Paso 3: Probar conectividad básica
        $this->line('   3. Probando conectividad básica...');
        try {
            $testResult = $this->testBasicConnectivity();
            if ($testResult) {
                $this->line('      ✅ Conectividad básica funcionando');
            } else {
                $this->error('      ❌ Problemas de conectividad detectados');
            }
        } catch (\Exception $e) {
            $this->error('      ❌ Error de conectividad: ' . $e->getMessage());
        }
        
        // Paso 4: Limpiar otros cachés
        $this->line('   4. Limpiando cachés adicionales...');
        try {
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            $this->line('      ✅ Cachés limpiados');
        } catch (\Exception $e) {
            $this->error('      ❌ Error al limpiar cachés: ' . $e->getMessage());
        }
        
        // Paso 5: Optimizar para producción
        $this->line('   5. Optimizando para producción...');
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            $this->line('      ✅ Optimización completada');
        } catch (\Exception $e) {
            $this->error('      ❌ Error en optimización: ' . $e->getMessage());
        }
        
        $this->newLine();
    }

    /**
     * Probar conectividad básica
     */
    private function testBasicConnectivity(): bool
    {
        try {
            $disk = ImageUrlService::getStorageDisk();
            
            // Intentar listar archivos (operación básica)
            Storage::disk($disk)->files();
            
            return true;
        } catch (\Exception $e) {
            Log::error('Basic connectivity test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ejecutar pruebas completas
     */
    private function runTests(): void
    {
        $this->info('🧪 Ejecutando pruebas...');
        
        try {
            // Ejecutar comando de diagnóstico con pruebas
            $exitCode = Artisan::call('storage:diagnose', ['--test' => true]);
            
            if ($exitCode === 0) {
                $this->line('   ✅ Todas las pruebas pasaron correctamente');
            } else {
                $this->error('   ❌ Algunas pruebas fallaron');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Error ejecutando pruebas: ' . $e->getMessage());
        }
        
        $this->newLine();
    }

    /**
     * Mostrar resumen final
     */
    private function showSummary(): void
    {
        $this->info('📊 Resumen de corrección:');
        
        // Verificar estado final
        $diskStatus = $this->getFinalDiskStatus();
        $configStatus = $this->getConfigurationStatus();
        
        $this->line('   Estado del disco: ' . ($diskStatus ? '✅ Funcionando' : '❌ Con problemas'));
        $this->line('   Configuración: ' . ($configStatus ? '✅ Correcta' : '❌ Necesita revisión'));
        
        $this->newLine();
        
        if ($diskStatus && $configStatus) {
            $this->info('🎉 Corrección completada exitosamente!');
            $this->line('');
            $this->line('✅ Próximos pasos:');
            $this->line('   1. Probar subida de imágenes en productos');
            $this->line('   2. Verificar que las imágenes se muestran correctamente');
            $this->line('   3. Monitorear logs para errores: storage/logs/laravel.log');
        } else {
            $this->error('⚠️  Corrección completada con advertencias');
            $this->line('');
            $this->line('🔍 Verificar:');
            $this->line('   1. Variables de entorno AWS_* en .env');
            $this->line('   2. Configuración de Cloudflare R2');
            $this->line('   3. Permisos del bucket');
            $this->line('   4. Configuración de CORS');
            $this->line('');
            $this->line('📋 Para diagnóstico detallado:');
            $this->line('   php artisan storage:diagnose --test');
        }
    }

    /**
     * Obtener estado final del disco
     */
    private function getFinalDiskStatus(): bool
    {
        try {
            return ImageUrlService::testStorageDisk();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener estado de la configuración
     */
    private function getConfigurationStatus(): bool
    {
        try {
            $defaultDisk = config('filesystems.default');
            
            if ($defaultDisk === 'private') {
                $config = config('filesystems.disks.private');
                return !empty($config['key']) && 
                       !empty($config['secret']) && 
                       !empty($config['bucket']) && 
                       !empty($config['endpoint']);
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
