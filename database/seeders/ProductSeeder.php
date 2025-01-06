<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener todas las categorías
        $categories = Category::all();

        // Para cada categoría, crear entre 3 y 5 productos
        foreach ($categories as $category) {
            $numberOfProducts = rand(3, 5);
            
            Product::factory()
                ->count($numberOfProducts)
                ->create([
                    'category_id' => $category->id,
                    'company_id' => 1
                ]);
        }
    }
}
