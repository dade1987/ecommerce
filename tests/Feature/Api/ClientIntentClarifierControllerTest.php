<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class ClientIntentClarifierControllerTest extends TestCase
{
    public function test_clarify_requires_focus_text(): void
    {
        // Arrange & Act
        $response = $this->postJson('/api/chatbot/interpreter-clarify-intent', [
            // nessun focus_text
            'interlocutor_role' => 'recruiter',
        ]);

        // Assert (stato + contenuto/logica)
        $response->assertStatus(422);
        $response->assertJsonStructure(['error']);
        $this->assertStringContainsString('focus_text', (string) $response->json('error'));
    }
}
