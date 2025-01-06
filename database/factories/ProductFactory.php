<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purchase_price = $this->faker->randomFloat(2, 10, 1000);
        // El precio de venta será entre 20% y 50% más que el precio de compra
        $sale_price = $purchase_price * (1 + $this->faker->randomFloat(2, 0.2, 0.5));

        return [
            'code' => $this->faker->unique()->ean13(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'image' => null, // Por defecto sin imagen
            'stock' => $this->faker->numberBetween(5, 100),
            'min_stock' => $this->faker->numberBetween(5, 10),
            'max_stock' => $this->faker->numberBetween(100, 200),
            'purchase_price' => $purchase_price,
            'sale_price' => $sale_price,
            'entry_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'category_id' => Category::factory(),
            'company_id' => 1, // Valor por defecto como solicitado
        ];
    }
}
