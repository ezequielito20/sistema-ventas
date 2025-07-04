<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
   public function run(): void
   {
      // Obtener todas las categorías
      $categories = Category::all();

      // Obtener todos los proveedores
      $suppliers = \App\Models\Supplier::all();

      $totalProducts = 0;

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
                  'supplier_id' => $randomSupplier->id ?? $suppliers->first()->id,
               ]);
            
            $totalProducts++;
         }
      }
      
      $this->adjustAutoIncrement('products');
      
      $this->command->info('Se han creado '.$totalProducts.' productos.');
   }

   protected function adjustAutoIncrement(string $table)
   {
      $maxId = DB::table($table)->max('id') + 1;

      $driver = DB::getDriverName();

      if ($driver === 'pgsql') {
         $sequenceName = $table . '_id_seq';
         DB::statement("SELECT setval('$sequenceName', $maxId)");
      } elseif ($driver === 'mysql' or $driver === 'mariadb') {
         DB::statement("ALTER TABLE $table AUTO_INCREMENT = $maxId");
      } elseif ($driver === 'sqlite') {
         DB::statement("UPDATE sqlite_sequence SET seq = $maxId WHERE name = '$table'");
      } else {
         throw new \Exception('Unsupported database driver: ' . $driver);
      }
   }
}
