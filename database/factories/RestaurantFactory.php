<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Restaurant>
 */
class RestaurantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'price_range' => $this->faker->randomElement(['E', 'EE', 'EEE', 'EEEE']),
            'phone_number' => $this->faker->phoneNumber,
            'website' => $this->faker->url,
            'email' => $this->faker->email,
        ];
    }
}
