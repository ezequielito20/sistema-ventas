<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DiagnoseBucket extends Command
{
    protected $signature = 'bucket:diagnose';
    protected $description = 'Diagnose bucket configuration';

    public function handle()
    {
        $this->info('🔍 Diagnóstico de Configuración del Bucket');
        $this->line('');

        // Información del entorno
        $this->info('📋 Información del Entorno:');
        $this->line('  Entorno: ' . app()->environment());
        $this->line('  Disco por defecto: ' . config('filesystems.default'));
        $this->line('');

        // Variables de entorno relacionadas con AWS/S3
        $this->info('📋 Variables de Entorno AWS/S3:');
        $envVars = [
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
            'AWS_DEFAULT_REGION',
            'AWS_BUCKET',
            'AWS_URL',
            'AWS_ENDPOINT',
            'AWS_USE_PATH_STYLE_ENDPOINT',
            'FILESYSTEM_DISK',
            'LARAVEL_CLOUD_DISK_CONFIG'
        ];

        foreach ($envVars as $var) {
            $value = env($var);
            if ($value) {
                if (str_contains($var, 'SECRET') || str_contains($var, 'KEY')) {
                    $this->line("  {$var}: " . str_repeat('*', min(strlen($value), 20)));
                } else {
                    $this->line("  {$var}: {$value}");
                }
            } else {
                $this->line("  {$var}: <fg=red>No configurado</>");
            }
        }
        $this->line('');

        // Configuración de discos
        $this->info('📋 Configuración de Discos:');
        $disks = ['local', 'public', 's3', 'private'];
        foreach ($disks as $diskName) {
            $this->line("  Disco '{$diskName}':");
            $diskConfig = config("filesystems.disks.{$diskName}");
            if ($diskConfig) {
                foreach ($diskConfig as $key => $value) {
                    if (str_contains($key, 'secret') || str_contains($key, 'key')) {
                        $this->line("    {$key}: " . str_repeat('*', min(strlen($value ?? ''), 20)));
                    } else {
                        $this->line("    {$key}: " . ($value ?? 'null'));
                    }
                }
            } else {
                $this->line("    <fg=red>No configurado</>");
            }
            $this->line('');
        }

        // Probar conectividad
        $this->info('🧪 Probando Conectividad:');
        $defaultDisk = config('filesystems.default');
        
        try {
            $disk = Storage::disk($defaultDisk);
            $this->line("  Disco '{$defaultDisk}': <fg=green>Conectado</>");
            
            // Intentar listar archivos
            $files = $disk->files('products');
            $this->line("  Archivos en 'products/': " . count($files));
            
            if (count($files) > 0) {
                $this->line("  Primeros 5 archivos:");
                foreach (array_slice($files, 0, 5) as $file) {
                    $this->line("    - {$file}");
                }
            }
            
        } catch (\Exception $e) {
            $this->line("  Disco '{$defaultDisk}': <fg=red>Error: {$e->getMessage()}</>");
        }

        // Probar generación de URLs
        $this->info('🔗 Probando Generación de URLs:');
        $testImage = 'products/test-image.jpg';
        
        try {
            $url = \App\Services\ImageUrlService::getImageUrl($testImage);
            $this->line("  URL generada: {$url}");
            
            // Verificar si es una URL válida
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $this->line("  <fg=green>✓ URL válida</>");
            } else {
                $this->line("  <fg=red>✗ URL inválida</>");
            }
            
        } catch (\Exception $e) {
            $this->line("  <fg=red>Error generando URL: {$e->getMessage()}</>");
        }

        return 0;
    }
} 