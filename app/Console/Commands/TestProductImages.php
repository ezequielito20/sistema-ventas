<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class TestProductImages extends Command
{
    protected $signature = 'test:product-images';
    protected $description = 'Test product image URLs';

    public function handle()
    {
        $this->info('🖼️ Probando URLs de Imágenes de Productos');
        $this->line('');

        $products = Product::whereNotNull('image')->take(5)->get();

        if ($products->count() === 0) {
            $this->warn('No se encontraron productos con imágenes');
            return 0;
        }

        foreach ($products as $product) {
            $this->line("📦 Producto: {$product->name}");
            $this->line("   ID: {$product->id}");
            $this->line("   Imagen (BD): {$product->image}");
            $this->line("   URL generada: {$product->image_url}");
            
            // Verificar si la URL es válida
            if (filter_var($product->image_url, FILTER_VALIDATE_URL)) {
                $this->line("   ✅ URL válida");
            } else {
                $this->line("   ❌ URL inválida");
            }
            
            $this->line('');
        }

        // Mostrar información del entorno
        $this->info('📋 Información del Entorno:');
        $this->line("   Entorno: " . app()->environment());
        $this->line("   Disco por defecto: " . config('filesystems.default'));
        $this->line("   URL de la aplicación: " . config('app.url'));

        return 0;
    }
} 