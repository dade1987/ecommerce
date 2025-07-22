<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InternalProduct>
 */
class InternalProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Prodotto di Test ' . $this->faker->word,
            'code' => 'TEST-' . $this->faker->unique()->numberBetween(1000, 9999),
            'unit_of_measure' => 'pz',
            'weight' => $this->faker->randomFloat(2, 1, 100),
            'emission_factor' => $this->faker->randomFloat(2, 0.1, 0.5),
        ];
    }
}
