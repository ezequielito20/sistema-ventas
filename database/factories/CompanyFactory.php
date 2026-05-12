<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'country' => $this->faker->randomElement(['VE', 'CO', 'US']),
            'business_type' => $this->faker->randomElement(['Retail', 'Wholesale', 'Service']),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'tax_amount' => $this->faker->numberBetween(0, 16),
            'tax_name' => $this->faker->randomElement(['IVA', 'IGTF', 'IT']),
            'currency' => $this->faker->randomElement(['VES', 'USD', 'COP']),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
        ];
    }
}
