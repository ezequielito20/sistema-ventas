<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'name' => 'Básico',
            'slug' => 'basico',
            'description' => 'Plan estándar para pequeñas y medianas empresas.',
            'base_price' => 0,
            'price_per_user' => 0,
            'price_per_transaction' => 0,
            'limits' => json_encode([
                'max_users' => 5,
                'max_transactions' => 1000,
                'max_products' => 500,
                'max_customers' => 2000,
            ]),
            'features' => json_encode([
                'sales',
                'purchases',
                'reports',
                'customers',
                'products',
                'categories',
                'cash_counts',
            ]),
            'max_users' => 5,
            'max_transactions' => 1000,
            'max_products' => 500,
            'max_customers' => 2000,
            'is_active' => true,
        ]);

        Plan::create([
            'name' => 'Profesional',
            'slug' => 'profesional',
            'description' => 'Para empresas en crecimiento con catálogo público y más capacidad.',
            'base_price' => 29.99,
            'price_per_user' => 5,
            'price_per_transaction' => 0,
            'limits' => json_encode([
                'max_users' => 20,
                'max_transactions' => 10000,
                'max_products' => 2000,
                'max_customers' => 10000,
            ]),
            'features' => json_encode([
                'sales',
                'purchases',
                'reports',
                'customers',
                'products',
                'categories',
                'cash_counts',
                'catalog',
            ]),
            'max_users' => 20,
            'max_transactions' => 10000,
            'max_products' => 2000,
            'max_customers' => 10000,
            'is_active' => true,
        ]);

        Plan::create([
            'name' => 'Empresarial',
            'slug' => 'empresarial',
            'description' => 'Todo incluido sin límites. Ideal para grandes volúmenes.',
            'base_price' => 99.99,
            'price_per_user' => 0,
            'price_per_transaction' => 0,
            'limits' => json_encode([
                'max_users' => null,
                'max_transactions' => null,
                'max_products' => null,
                'max_customers' => null,
            ]),
            'features' => json_encode([
                'sales',
                'purchases',
                'reports',
                'customers',
                'products',
                'categories',
                'cash_counts',
                'catalog',
            ]),
            'max_users' => null,
            'max_transactions' => null,
            'max_products' => null,
            'max_customers' => null,
            'is_active' => true,
        ]);

        $this->command->info('Planes Básico, Profesional y Empresarial creados correctamente.');
    }
}
