<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electrónica',
                'description' => 'Productos electrónicos, gadgets y accesorios tecnológicos',
                'company_id' => 1
            ],
            [
                'name' => 'Ropa y Accesorios',
                'description' => 'Vestimenta, calzado y accesorios de moda',
                'company_id' => 1
            ],
            [
                'name' => 'Hogar y Decoración',
                'description' => 'Artículos para el hogar y elementos decorativos',
                'company_id' => 1
            ],
            [
                'name' => 'Deportes y Fitness',
                'description' => 'Equipamiento deportivo y artículos para ejercicio',
                'company_id' => 1
            ],
            [
                'name' => 'Alimentos y Bebidas',
                'description' => 'Productos alimenticios y bebidas de consumo',
                'company_id' => 1
            ],
            [
                'name' => 'Belleza y Cuidado Personal',
                'description' => 'Productos de belleza, cosméticos y cuidado personal',
                'company_id' => 1
            ],
            [
                'name' => 'Juguetes y Juegos',
                'description' => 'Juguetes, juegos de mesa y entretenimiento',
                'company_id' => 1
            ],
            [
                'name' => 'Libros y Papelería',
                'description' => 'Libros, útiles escolares y artículos de oficina',
                'company_id' => 1
            ],
            [
                'name' => 'Mascotas',
                'description' => 'Alimentos y accesorios para mascotas',
                'company_id' => 1
            ],
            [
                'name' => 'Herramientas',
                'description' => 'Herramientas y equipamiento para mantenimiento',
                'company_id' => 1
            ],
            [
                'name' => 'Automotriz',
                'description' => 'Accesorios y productos para vehículos',
                'company_id' => 1
            ],
            [
                'name' => 'Jardín y Exterior',
                'description' => 'Productos para jardinería y espacios exteriores',
                'company_id' => 1
            ],
            [
                'name' => 'Salud y Bienestar',
                'description' => 'Productos para el cuidado de la salud y bienestar',
                'company_id' => 1
            ],
            [
                'name' => 'Arte y Manualidades',
                'description' => 'Materiales para arte y trabajos manuales',
                'company_id' => 1
            ],
            [
                'name' => 'Electrodomésticos',
                'description' => 'Aparatos y electrodomésticos para el hogar',
                'company_id' => 1
            ]
        ];

        try {
            DB::beginTransaction();

            foreach ($categories as $category) {
                Category::create($category);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error seeding categories: ' . $e->getMessage());
        }
    }
}
