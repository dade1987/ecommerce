<?php

namespace Tests\Unit\Services\OpenAi;

use App\Services\OpenAi\OpenAiLanguageDetector;
use Tests\TestCase;

class OpenAiLanguageDetectorTest extends TestCase
{
    public function test_detects_english_language_for_simple_english_text(): void
    {
        // Arrange
        $apiKey = config('services.openai.key');
        $apiKey = is_string($apiKey) ? trim($apiKey) : '';
        $fallback = config('openapi.key');
        $fallback = is_string($fallback) ? trim($fallback) : '';
        if ($apiKey === '' && $fallback === '') {
            $this->markTestSkipped('OpenAI API key mancante (services.openai.key o openapi.key).');
        }

        $detector = app(OpenAiLanguageDetector::class);

        // Act
        $result = $detector->detect('This is a simple test sentence.');

        // Assert
        $this->assertSame('en', $result['language_code']);
        $this->assertGreaterThan(0.5, $result['confidence']);
    }

    public function test_detects_italian_language_for_simple_italian_text(): void
    {
        // Arrange
        $apiKey = config('services.openai.key');
        $apiKey = is_string($apiKey) ? trim($apiKey) : '';
        $fallback = config('openapi.key');
        $fallback = is_string($fallback) ? trim($fallback) : '';
        if ($apiKey === '' && $fallback === '') {
            $this->markTestSkipped('OpenAI API key mancante (services.openai.key o openapi.key).');
        }

        $detector = app(OpenAiLanguageDetector::class);

        // Act
        $result = $detector->detect('Questa è una frase di test in italiano.');

        // Assert
        $this->assertSame('it', $result['language_code']);
        $this->assertGreaterThan(0.5, $result['confidence']);
    }
}
