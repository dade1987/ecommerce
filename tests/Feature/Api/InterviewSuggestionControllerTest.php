<?php

namespace Tests\Feature\Api;

use App\Models\Quoter;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InterviewSuggestionControllerTest extends TestCase
{
    use WithFaker;

    public function test_suggest_requires_cv_text_and_utterance(): void
    {
        // Arrange & Act
        $response = $this->postJson('/api/chatbot/interview-suggestion', [
            // niente cv_text / utterance
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonStructure(['error']);
    }

    public function test_suggest_returns_empty_suggestions_when_topic_does_not_change(): void
    {
        // Arrange
        $threadId = 'interview_test_thread_'.uniqid('', true);
        $utterance = 'Parlami dei tuoi progetti recenti in Laravel.';

        // Creiamo una history con ultimo messaggio utente molto simile all’utterance corrente
        Quoter::factory()->create([
            'thread_id' => $threadId,
            'role' => 'user',
            'content' => $utterance,
        ]);

        // Act
        $response = $this->postJson('/api/chatbot/interview-suggestion', [
            'cv_text' => 'CV di test con esperienza Laravel.',
            'utterance' => $utterance,
            'locale' => 'it',
            'lang_a' => 'it',
            'lang_b' => 'en',
            'thread_id' => $threadId,
        ]);

        $json = $response->json();

        // Assert (stato + contenuto/logica)
        $response->assertStatus(200);

        $this->assertArrayHasKey('topic_changed', $json);
        $this->assertFalse($json['topic_changed']);

        $this->assertSame('', $json['suggestion_lang_a']);
        $this->assertSame('', $json['suggestion_lang_b']);
    }
}



