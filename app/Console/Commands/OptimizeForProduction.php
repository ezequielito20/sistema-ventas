<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OptimizeForProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-production';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the application for production deployment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Optimizando aplicación para producción...');

        // Limpiar cachés
        $this->info('📦 Limpiando cachés...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        // Optimizar para producción
        $this->info('⚡ Optimizando configuración...');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        // Ejecutar migraciones si es necesario
        $this->info('🗄️ Verificando base de datos...');
        Artisan::call('migrate', ['--force' => true]);

        // Optimizar autoloader
        $this->info('🔧 Optimizando autoloader...');
        exec('composer install --optimize-autoloader --no-dev');

        $this->info('✅ ¡Aplicación optimizada para producción!');
        $this->info('💡 Recuerda configurar las variables de entorno en producción:');
        $this->info('   - APP_ENV=production');
        $this->info('   - APP_DEBUG=false');
        $this->info('   - CACHE_DRIVER=file');
        $this->info('   - SESSION_DRIVER=file');
        $this->info('   - QUEUE_CONNECTION=sync');
    }
}
