<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\InterviewSuggestionController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class InterviewSuggestionSimilarityTest extends TestCase
{
    public function test_calculate_similarity_returns_one_for_identical_texts(): void
    {
        // Arrange
        $controller = new InterviewSuggestionController();
        $ref = new ReflectionClass($controller);
        $method = $ref->getMethod('calculateSimilarity');
        $method->setAccessible(true);

        // Act
        $result = $method->invoke($controller, 'Test Laravel', 'Test Laravel');

        // Assert
        $this->assertIsFloat($result);
        $this->assertSame(1.0, $result);
    }

    public function test_calculate_similarity_returns_low_value_for_different_texts(): void
    {
        // Arrange
        $controller = new InterviewSuggestionController();
        $ref = new ReflectionClass($controller);
        $method = $ref->getMethod('calculateSimilarity');
        $method->setAccessible(true);

        // Act
        $result = $method->invoke($controller, 'Laravel developer', 'I like cooking pizza');

        // Assert
        $this->assertIsFloat($result);
        $this->assertGreaterThanOrEqual(0.0, $result);
        $this->assertLessThan(0.7, $result);
    }
}
