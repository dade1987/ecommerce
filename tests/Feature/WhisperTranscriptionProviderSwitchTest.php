<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class WhisperTranscriptionProviderSwitchTest extends TestCase
{
    private function fixtureUpload(): UploadedFile
    {
        $path = base_path('tests/Fixtures/audio.webm');

        return new UploadedFile(
            $path,
            'audio.webm',
            'audio/webm',
            null,
            true
        );
    }

    public function test_transcribe_returns_500_when_openai_key_missing_without_calling_external_service(): void
    {
        // Arrange
        Config::set('speech.transcription.provider', 'openai_whisper');
        Config::set('openapi.key', '');

        // Act
        $resp = $this->post('/api/whisper/transcribe', [
            'audio' => $this->fixtureUpload(),
            'lang' => 'it-IT',
        ]);

        // Assert
        $resp->assertStatus(500);
        $resp->assertJsonFragment([
            'error' => 'Errore trascrizione audio',
        ]);
    }

    public function test_transcribe_returns_500_when_groq_key_missing_without_calling_external_service(): void
    {
        // Arrange
        Config::set('speech.transcription.provider', 'groq_whisper');
        Config::set('services.groq.key', '');

        // Act
        $resp = $this->post('/api/whisper/transcribe', [
            'audio' => $this->fixtureUpload(),
            'lang' => 'it-IT',
        ]);

        // Assert
        $resp->assertStatus(500);
        $resp->assertJsonFragment([
            'error' => 'Errore trascrizione audio',
        ]);
    }
}
