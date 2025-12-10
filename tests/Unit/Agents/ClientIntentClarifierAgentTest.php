<?php

namespace Tests\Unit\Agents;

use App\Neuron\ClientIntentClarifierAgent;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ClientIntentClarifierAgentTest extends TestCase
{
    public function test_instructions_focus_on_interlocutor_role(): void
    {
        // Arrange
        $agent = ClientIntentClarifierAgent::make()
            ->withLocale('it')
            ->withFocusText('Il recruiter chiede quali progetti recenti hai seguito.')
            ->withInterlocutorRole('recruiter')
            ->withLanguages('it', 'en');

        $ref = new ReflectionClass($agent);
        $method = $ref->getMethod('instructions');
        $method->setAccessible(true);

        // Act
        $instructions = $method->invoke($agent);

        // Assert
        $this->assertIsString($instructions);
        $this->assertStringContainsString('RUOLO DELL\'INTERLOCUTORE: recruiter', $instructions);
        $this->assertStringContainsString('LE INTENZIONI DELL\'INTERLOCUTORE DESCRITTO DAL RUOLO', $instructions);
    }
}



