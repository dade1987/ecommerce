<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\WhisperTranscriptionController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class WhisperTranscriptionControllerSegmentsTest extends TestCase
{
    public function test_extract_high_quality_text_discards_low_confidence_and_silence_segments(): void
    {
        // Arrange
        $controller = new WhisperTranscriptionController();
        $ref = new ReflectionClass($controller);
        $method = $ref->getMethod('extractHighQualityTextFromWhisperResponse');
        $method->setAccessible(true);

        $response = [
            'text' => 'fallback text',
            'segments' => [
                [
                    'text' => 'rumore di fondo',
                    'avg_logprob' => -2.0,
                    'no_speech_prob' => 0.1,
                ],
                [
                    'text' => '   silenzio   ',
                    'avg_logprob' => 0.0,
                    'no_speech_prob' => 0.9,
                ],
                [
                    'text' => 'questa è una frase valida',
                    'avg_logprob' => -0.2,
                    'no_speech_prob' => 0.1,
                ],
            ],
        ];

        // Act
        $text = $method->invoke($controller, $response);

        // Assert
        $this->assertIsString($text);
        $this->assertSame('questa è una frase valida', $text);
    }

    public function test_extract_high_quality_text_falls_back_to_text_field_when_all_segments_discarded(): void
    {
        // Arrange
        $controller = new WhisperTranscriptionController();
        $ref = new ReflectionClass($controller);
        $method = $ref->getMethod('extractHighQualityTextFromWhisperResponse');
        $method->setAccessible(true);

        $response = [
            'text' => 'testo di fallback',
            'segments' => [
                [
                    'text' => 'segmento scartato per avg_logprob',
                    'avg_logprob' => -5.0,
                    'no_speech_prob' => 0.1,
                ],
                [
                    'text' => 'segmento scartato per no_speech_prob',
                    'avg_logprob' => 0.0,
                    'no_speech_prob' => 0.9,
                ],
            ],
        ];

        // Act
        $text = $method->invoke($controller, $response);

        // Assert
        $this->assertIsString($text);
        $this->assertSame('testo di fallback', $text);
    }
}



