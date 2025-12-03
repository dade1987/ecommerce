<?php

namespace Database\Factories;

use App\Models\Quoter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quoter>
 */
class QuoterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Quoter>
     */
    protected $model = Quoter::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Nella maggior parte dei casi il thread_id verrà sovrascritto dal seeder.
            'thread_id' => (string) Str::uuid(),
            'role' => $this->faker->randomElement(['user', 'chatbot']),
            'content' => $this->faker->paragraph(),
            'is_fake' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}


