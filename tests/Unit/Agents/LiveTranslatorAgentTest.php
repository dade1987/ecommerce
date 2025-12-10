<?php

namespace Tests\Unit\Agents;

use App\Neuron\LiveTranslatorAgent;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LiveTranslatorAgentTest extends TestCase
{
    public function test_instructions_force_output_language_to_target_lang(): void
    {
        // Arrange
        $agent = LiveTranslatorAgent::make()->withLocale('it')->withTargetLang('en');
        $ref = new ReflectionClass($agent);
        $method = $ref->getMethod('instructions');
        $method->setAccessible(true);

        // Act
        $instructions = $method->invoke($agent);

        // Assert
        $this->assertIsString($instructions);
        $this->assertStringContainsString('The OUTPUT language is fixed to: "en"', $instructions);
        $this->assertStringContainsString('You are a real-time voice translator for short sentences.', $instructions);
    }
}



