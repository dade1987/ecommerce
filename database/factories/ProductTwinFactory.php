<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductTwin>
 */
class ProductTwinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'internal_product_id' => \App\Models\InternalProduct::factory(),
            'current_warehouse_id' => \App\Models\Warehouse::factory(),
            'lifecycle_status' => $this->faker->randomElement(['in_stock', 'in_transit', 'in_use']),
            'co2_emissions_production' => $this->faker->randomFloat(2, 5, 20),
            'co2_emissions_logistics' => $this->faker->randomFloat(2, 1, 5),
            'co2_emissions_total' => $this->faker->randomFloat(2, 6, 25),
            'metadata' => ['batch' => $this->faker->uuid, 'line' => 'L01'],
        ];
    }
}
