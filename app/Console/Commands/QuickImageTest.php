<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class QuickImageTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:image-quick';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quick test of image URLs in production';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Prueba rápida de URLs de imágenes...');
        $this->newLine();
        
        // Obtener un producto con imagen
        $product = Product::whereNotNull('image')->first();
        
        if (!$product) {
            $this->error('❌ No hay productos con imágenes');
            return 1;
        }
        
        // Mostrar información del producto
        $this->info("📦 Producto encontrado:");
        $this->line("   Nombre: {$product->name}");
        $this->line("   ID: {$product->id}");
        $this->line("   Imagen en DB: {$product->image}");
        $this->newLine();
        
        // Obtener configuración de R2
        $config = config('filesystems.disks.private');
        $this->info("⚙️  Configuración R2:");
        $this->line("   Endpoint: " . ($config['endpoint'] ?? 'NO CONFIGURADO'));
        $this->line("   Bucket: " . ($config['bucket'] ?? 'NO CONFIGURADO'));
        $this->line("   URL configurada: " . ($config['url'] ?? 'NO CONFIGURADO'));
        $this->newLine();
        
        // Construir URL directa
        if ($config['endpoint'] && $config['bucket']) {
            $directUrl = $config['endpoint'] . '/' . $config['bucket'] . '/' . $product->image;
            $this->info("🔗 URLs generadas:");
            $this->line("   URL directa: {$directUrl}");
            
            // Mostrar URL que genera el sistema
            $systemUrl = $product->image_url;
            $this->line("   URL del sistema: {$systemUrl}");
            
            // Comparar URLs
            if ($directUrl === $systemUrl) {
                $this->line("   ✅ Las URLs coinciden");
            } else {
                $this->error("   ❌ Las URLs NO coinciden!");
            }
        } else {
            $this->error("❌ Configuración de R2 incompleta");
        }
        
        $this->newLine();
        
        // Verificar si el archivo existe en R2
        try {
            $this->info("📁 Verificando archivo en R2...");
            $exists = Storage::disk('private')->exists($product->image);
            $this->line("   Archivo existe: " . ($exists ? 'SÍ ✅' : 'NO ❌'));
            
            if ($exists) {
                // Obtener información adicional
                try {
                    $size = Storage::disk('private')->size($product->image);
                    $this->line("   Tamaño: " . $this->formatBytes($size));
                } catch (\Exception $e) {
                    $this->line("   Tamaño: No se pudo obtener");
                }
                
                try {
                    $visibility = Storage::disk('private')->getVisibility($product->image);
                    $this->line("   Visibilidad: {$visibility}");
                } catch (\Exception $e) {
                    $this->line("   Visibilidad: No se pudo determinar");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error verificando archivo: " . $e->getMessage());
        }
        
        $this->newLine();
        
        // Instrucciones para el usuario
        if (isset($directUrl)) {
            $this->info("🧪 Para probar:");
            $this->line("1. Copia esta URL: {$directUrl}");
            $this->line("2. Pégala en tu navegador");
            $this->line("3. Si se muestra la imagen, el problema es en el sistema");
            $this->line("4. Si NO se muestra, el problema es en los permisos de R2");
        }
        
        return 0;
    }

    /**
     * Formatear bytes a formato legible
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
