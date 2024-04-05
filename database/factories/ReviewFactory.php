<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\User;
use Digikraaft\ReviewRating\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence,
            'review' => fake()->paragraph,
            'rating' => fake()->numberBetween(1, 5), // Genera un punteggio casuale compreso tra 1 e 5
            'author_type' => User::class,
            'author_id' => User::factory()->create()->id,
            'model_type' => Restaurant::class,
            'model_id' => Restaurant::factory()->create()->id,

        ];
    }
}
