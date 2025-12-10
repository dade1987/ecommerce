<?php

namespace Tests\Unit\Agents;

use App\Neuron\InterviewMindMapAgent;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class InterviewMindMapAgentTest extends TestCase
{
    public function test_instructions_enforce_json_nodes_and_edges_structure(): void
    {
        // Arrange
        $agent = InterviewMindMapAgent::make()->withLocale('it')->withLanguages('it', 'en');
        $ref = new ReflectionClass($agent);
        $method = $ref->getMethod('instructions');
        $method->setAccessible(true);

        // Act
        $instructions = $method->invoke($agent);

        // Assert
        $this->assertIsString($instructions);
        $this->assertStringContainsString('"nodes": [', $instructions);
        $this->assertStringContainsString('"edges": [', $instructions);
        $this->assertStringContainsString('OUTPUT SOLO JSON', $instructions);
    }
}



