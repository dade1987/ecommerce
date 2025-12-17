<?php

namespace Tests\Unit\Speech;

use App\Services\Speech\OpenAiWhisperTranscriptionProvider;
use PHPUnit\Framework\TestCase;

class WhisperTranscriptionQualityGuardTest extends TestCase
{
    public function test_accepts_high_confidence_speech_segment(): void
    {
        // Arrange
        $json = [
            'text' => 'Hello world',
            'segments' => [
                [
                    'id' => 8,
                    'seek' => 3000,
                    'start' => 43.92,
                    'end' => 50.16,
                    'text' => ' document that the functional specification',
                    'avg_logprob' => -0.097569615,
                    'compression_ratio' => 1.6637554,
                    'no_speech_prob' => 0.012814695,
                ],
            ],
        ];

        // Act
        $rejected = OpenAiWhisperTranscriptionProvider::shouldRejectTranscriptionQuality($json);

        // Assert
        $this->assertFalse($rejected);
        $this->assertSame('Hello world', $json['text']);
    }

    public function test_rejects_when_avg_logprob_is_too_low(): void
    {
        // Arrange
        $json = [
            'text' => 'test',
            'segments' => [
                [
                    'text' => 'some text',
                    'avg_logprob' => -0.9,
                    'compression_ratio' => 1.2,
                    'no_speech_prob' => 0.01,
                ],
            ],
        ];

        // Act
        $rejected = OpenAiWhisperTranscriptionProvider::shouldRejectTranscriptionQuality($json);

        // Assert
        $this->assertTrue($rejected);
        $this->assertNotEmpty($json['segments']);
    }

    public function test_rejects_when_no_speech_prob_is_high(): void
    {
        // Arrange
        $json = [
            'text' => 'test',
            'segments' => [
                [
                    'text' => 'some text',
                    'avg_logprob' => -0.1,
                    'compression_ratio' => 1.2,
                    'no_speech_prob' => 0.95,
                ],
            ],
        ];

        // Act
        $rejected = OpenAiWhisperTranscriptionProvider::shouldRejectTranscriptionQuality($json);

        // Assert
        $this->assertTrue($rejected);
        $this->assertGreaterThanOrEqual(0.0, $json['segments'][0]['no_speech_prob']);
    }
}
