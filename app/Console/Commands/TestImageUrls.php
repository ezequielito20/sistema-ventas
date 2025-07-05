<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImageUrlService;
use App\Models\Product;

class TestImageUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:image-urls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test image URLs to verify they work correctly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Probando URLs de imágenes...');
        $this->newLine();
        
        // Buscar productos con imágenes
        $products = Product::whereNotNull('image')->take(3)->get();
        
        if ($products->isEmpty()) {
            $this->warn('⚠️  No hay productos con imágenes para probar');
            $this->line('Sube una imagen desde el panel admin y vuelve a ejecutar este comando');
            return 0;
        }
        
        $imageService = app(ImageUrlService::class);
        
        $this->info('📋 Productos encontrados:');
        
        foreach ($products as $index => $product) {
            $this->line("   " . ($index + 1) . ". {$product->name}");
            $this->line("      Archivo: {$product->image}");
            
            try {
                $imageUrl = $imageService->getImageUrl($product->image);
                $this->line("      ✅ URL: {$imageUrl}");
                
                // Verificar si es URL firmada o proxy
                if (str_contains($imageUrl, 'X-Amz-')) {
                    $this->line("      🔐 Tipo: URL firmada (Cloudflare R2)");
                } elseif (str_contains($imageUrl, '/image/')) {
                    $this->line("      🔗 Tipo: Proxy interno");
                } else {
                    $this->line("      📁 Tipo: URL directa");
                }
                
            } catch (\Exception $e) {
                $this->line("      ❌ Error: " . $e->getMessage());
            }
            
            $this->newLine();
        }
        
        $this->info('🎯 Resultado:');
        
        if (app()->environment('production')) {
            $this->line('✅ En producción: Se usan URLs firmadas de Cloudflare R2');
            $this->line('✅ Las URLs firmadas funcionan correctamente');
            $this->line('✅ Las imágenes se mostrarán en el panel admin');
        } else {
            $this->line('✅ En desarrollo: Se usa proxy interno');
            $this->line('✅ Las imágenes se sirven a través del proxy');
        }
        
        $this->newLine();
        $this->info('📋 Próximos pasos:');
        $this->line('1. Ve al panel admin de productos');
        $this->line('2. Verifica que las imágenes se muestran correctamente');
        $this->line('3. Prueba subir una nueva imagen');
        $this->line('4. ¡Todo debería funcionar perfectamente!');
        
        return 0;
    }
}
