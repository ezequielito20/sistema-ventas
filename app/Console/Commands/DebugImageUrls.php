<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ImageUrlService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DebugImageUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:image-urls {--product-id= : Specific product ID to debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug image URLs being generated for products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Debugeando URLs de imágenes...');
        
        // Mostrar configuración actual
        $this->showConfiguration();
        
        // Debugear productos específicos
        if ($this->option('product-id')) {
            $this->debugSpecificProduct($this->option('product-id'));
        } else {
            $this->debugAllProducts();
        }
        
        return 0;
    }

    /**
     * Mostrar configuración actual
     */
    private function showConfiguration(): void
    {
        $this->info('📋 Configuración actual:');
        
        $environment = app()->environment();
        $this->line("   Entorno: {$environment}");
        
        $defaultDisk = config('filesystems.default');
        $this->line("   Disco por defecto: {$defaultDisk}");
        
        if ($defaultDisk === 'private') {
            $config = config('filesystems.disks.private');
            $this->line('   Configuración R2:');
            $this->line('     - Endpoint: ' . ($config['endpoint'] ?? 'No configurado'));
            $this->line('     - Bucket: ' . ($config['bucket'] ?? 'No configurado'));
            $this->line('     - URL: ' . ($config['url'] ?? 'No configurado'));
        }
        
        $this->newLine();
    }

    /**
     * Debugear producto específico
     */
    private function debugSpecificProduct(int $productId): void
    {
        $product = Product::find($productId);
        
        if (!$product) {
            $this->error("❌ Producto con ID {$productId} no encontrado");
            return;
        }
        
        $this->info("🔍 Debugeando producto: {$product->name} (ID: {$product->id})");
        
        $this->debugProduct($product);
    }

    /**
     * Debugear todos los productos con imágenes
     */
    private function debugAllProducts(): void
    {
        $products = Product::whereNotNull('image')->get();
        
        if ($products->isEmpty()) {
            $this->warn('⚠️  No hay productos con imágenes en la base de datos');
            return;
        }
        
        $this->info("🔍 Debugeando {$products->count()} productos con imágenes:");
        $this->newLine();
        
        foreach ($products as $product) {
            $this->debugProduct($product);
            $this->newLine();
        }
    }

    /**
     * Debugear un producto específico
     */
    private function debugProduct(Product $product): void
    {
        $this->line("📦 Producto: {$product->name}");
        $this->line("   ID: {$product->id}");
        $this->line("   Código: {$product->code}");
        
        // Ruta de imagen en base de datos
        $imagePath = $product->image;
        $this->line("   Ruta en DB: " . ($imagePath ?? 'NULL'));
        
        if (!$imagePath) {
            $this->line("   ⚠️  Sin imagen configurada");
            return;
        }
        
        // URL generada por el accessor
        $accessorUrl = $product->image_url;
        $this->line("   URL del accessor: {$accessorUrl}");
        
        // URL generada directamente por el servicio
        $serviceUrl = ImageUrlService::getImageUrl($imagePath);
        $this->line("   URL del servicio: {$serviceUrl}");
        
        // Verificar si son iguales
        if ($accessorUrl === $serviceUrl) {
            $this->line("   ✅ URLs coinciden");
        } else {
            $this->error("   ❌ URLs no coinciden!");
        }
        
        // Probar diferentes escenarios
        $this->testUrlGeneration($imagePath);
    }

    /**
     * Probar generación de URLs
     */
    private function testUrlGeneration(string $imagePath): void
    {
        $this->line("   🧪 Pruebas de generación:");
        
        // Prueba 1: URL directa
        $config = config('filesystems.disks.private');
        if ($config && $config['endpoint'] && $config['bucket']) {
            $directUrl = rtrim($config['endpoint'], '/') . '/' . $config['bucket'] . '/' . ltrim($imagePath, '/');
            $this->line("   📎 URL directa: {$directUrl}");
        }
        
        // Prueba 2: Con diferentes formatos de path
        $cleanPath = ltrim($imagePath, '/');
        $serviceUrlClean = ImageUrlService::getImageUrl($cleanPath);
        $this->line("   📎 URL con path limpio: {$serviceUrlClean}");
        
        // Prueba 3: Verificar si el archivo existe en R2
        try {
            $disk = ImageUrlService::getStorageDisk();
            $exists = Storage::disk($disk)->exists($imagePath);
            $this->line("   📁 Archivo existe en R2: " . ($exists ? 'SÍ' : 'NO'));
            
            if ($exists) {
                // Obtener metadatos del archivo
                $size = Storage::disk($disk)->size($imagePath);
                $this->line("   📏 Tamaño: " . $this->formatBytes($size));
                
                try {
                    $visibility = Storage::disk($disk)->getVisibility($imagePath);
                    $this->line("   👁️  Visibilidad: {$visibility}");
                } catch (\Exception $e) {
                    $this->line("   👁️  Visibilidad: No se pudo determinar");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando archivo: " . $e->getMessage());
        }
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
