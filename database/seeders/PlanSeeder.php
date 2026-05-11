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

        $this->command->info('Plan Básico creado correctamente.');
    }
}
