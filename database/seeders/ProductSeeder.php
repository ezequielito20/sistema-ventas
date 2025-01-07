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

      // Obtener todos los proveedores
      $suppliers = \App\Models\Supplier::all();

      // Para cada categoría, crear entre 3 y 5 productos
      foreach ($categories as $category) {
         $numberOfProducts = rand(7, 10);

         for ($i = 0; $i < $numberOfProducts; $i++) {
            // Seleccionar un proveedor al azar
            $randomSupplier = $suppliers->random();

            Product::factory()
               ->create([
                  'category_id' => $category->id,
                  'company_id' => 1,
                  'supplier_id' => $randomSupplier->id,
               ]);
         }
      }
   }
}
