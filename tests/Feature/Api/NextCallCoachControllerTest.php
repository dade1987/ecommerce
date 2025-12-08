<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class NextCallCoachControllerTest extends TestCase
{
    public function test_improve_requires_goal(): void
    {
        // Arrange & Act
        $response = $this->postJson('/api/chatbot/interview-next-call', [
            // nessun goal
            'transcript_text' => 'Trascrizione di test.',
        ]);

        // Assert (stato + contenuto/logica)
        $response->assertStatus(422);
        $response->assertJsonStructure(['error']);
        $this->assertStringContainsString('goal', (string) $response->json('error'));
    }
}
