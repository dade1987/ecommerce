<?php

namespace Database\Factories;

use App\Models\Thread;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Thread>
 */
class ThreadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Thread>
     */
    protected $model = Thread::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'thread_id' => (string) Str::uuid(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'url' => $this->faker->url(),
            'method' => $this->faker->randomElement(['GET', 'POST']),
            'team_slug' => $this->faker->randomElement(['demo', 'marketing', 'sales', 'support']),
            'activity_uuid' => (string) Str::uuid(),
            'cookies' => null,
            'headers' => null,
            'server_params' => null,
            'is_fake' => false,
            'created_at' => now()->subDays($this->faker->numberBetween(0, 14)),
            'updated_at' => now(),
        ];
    }
}


