<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class LiveAvatarSessionControllerTest extends TestCase
{
    public function test_start_requires_avatar_id_and_context_id(): void
    {
        // Arrange
        $payload = [
            'language' => 'it',
        ];

        // Act
        $res = $this->postJson('/api/liveavatar/start', $payload);

        // Assert
        $res->assertStatus(422);
        $res->assertJsonValidationErrors(['avatar_id', 'context_id']);
    }

    public function test_start_fails_when_server_api_key_missing(): void
    {
        // Arrange
        Config::set('services.liveavatar.api_key', '');
        Config::set('services.liveavatar.server_url', 'https://api.liveavatar.com');

        $payload = [
            'avatar_id' => '073b60a9-89a8-45aa-8902-c358f64d2852',
            'context_id' => '53ccc554-e3c7-452b-8ac0-1c614562f048',
            'language' => 'it',
        ];

        // Act
        $res = $this->postJson('/api/liveavatar/start', $payload);

        // Assert
        $res->assertStatus(500);
        $res->assertJson([
            'code' => 5000,
        ]);
    }

    public function test_stop_requires_session_token(): void
    {
        // Arrange
        $payload = [];

        // Act
        $res = $this->postJson('/api/liveavatar/stop', $payload);

        // Assert
        $res->assertStatus(422);
        $res->assertJsonValidationErrors(['session_token']);
    }
}

