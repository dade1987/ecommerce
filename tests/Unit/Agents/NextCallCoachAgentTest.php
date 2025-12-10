<?php

namespace Tests\Unit\Agents;

use App\Neuron\NextCallCoachAgent;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class NextCallCoachAgentTest extends TestCase
{
    public function test_instructions_contain_both_languages_blocks(): void
    {
        // Arrange
        $agent = NextCallCoachAgent::make()
            ->withLocale('it')
            ->withGoal('Preparare una prossima call migliore.')
            ->withTranscript('Trascrizione di esempio.')
            ->withLanguages('it', 'en');

        $ref = new ReflectionClass($agent);
        $method = $ref->getMethod('instructions');
        $method->setAccessible(true);

        // Act
        $instructions = $method->invoke($agent);

        // Assert
        $this->assertIsString($instructions);
        $this->assertStringContainsString('Sei un COACH DI COLLOQUI tecnici.', $instructions);
        $this->assertStringContainsString('ITALIANO:', $instructions);
        $this->assertStringContainsString('INGLESE:', $instructions);
    }
}



