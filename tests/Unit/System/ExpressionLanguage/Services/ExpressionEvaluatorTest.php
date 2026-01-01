<?php

declare(strict_types=1);

namespace Tests\Unit\System\ExpressionLanguage\Services;

use PHPUnit\Framework\Attributes\Test;
use System\ExpressionLanguage\Contracts\ExpressionContextInterface;
use System\ExpressionLanguage\Exceptions\ExpressionEvaluationException;
use System\ExpressionLanguage\Exceptions\ExpressionSecurityException;
use System\ExpressionLanguage\Exceptions\ExpressionSyntaxException;
use System\ExpressionLanguage\Services\ExpressionEvaluator;
use Tests\TestCase;

/**
 * Test context for expression evaluation.
 */
class TestScoringContext implements ExpressionContextInterface
{
    public function __construct(
        private readonly float $distance = 100,
        private readonly float $duration = 3600,
        private readonly int $bonus = 50,
    ) {}

    public function getVariables(): array
    {
        return [
            'distance' => $this->distance,
            'duration' => $this->duration,
            'bonus' => $this->bonus,
        ];
    }

    public function getName(): string
    {
        return 'scoring';
    }

    public function getAllowedVariables(): array
    {
        return ['distance', 'duration', 'bonus'];
    }
}

class ExpressionEvaluatorTest extends TestCase
{
    private ExpressionEvaluator $evaluator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = app(ExpressionEvaluator::class);
    }

    #[Test]
    public function it_evaluates_simple_expression(): void
    {
        $context = new TestScoringContext(distance: 100);

        $result = $this->evaluator->evaluate('distance * 2', $context);

        $this->assertEquals(200, $result);
    }

    #[Test]
    public function it_evaluates_expression_with_multiple_variables(): void
    {
        $context = new TestScoringContext(distance: 100, bonus: 50);

        $result = $this->evaluator->evaluate('distance + bonus', $context);

        $this->assertEquals(150, $result);
    }

    #[Test]
    public function it_evaluates_boolean_expression(): void
    {
        $context = new TestScoringContext(distance: 100);

        $result = $this->evaluator->evaluateBoolean('distance > 50', $context);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_evaluates_numeric_expression(): void
    {
        $context = new TestScoringContext(distance: 100);

        $result = $this->evaluator->evaluateNumeric('distance / 4', $context);

        $this->assertEquals(25, $result);
    }

    #[Test]
    public function it_throws_on_type_mismatch_for_numeric(): void
    {
        $context = new TestScoringContext(distance: 100);

        $this->expectException(ExpressionEvaluationException::class);
        $this->expectExceptionMessage('Type mismatch');

        $this->evaluator->evaluateNumeric('distance > 50', $context);
    }

    #[Test]
    public function it_evaluates_with_math_functions(): void
    {
        $context = new TestScoringContext(distance: 100.7);

        $result = $this->evaluator->evaluate('round(distance)', $context);

        $this->assertEquals(101, $result);
    }

    #[Test]
    public function it_evaluates_custom_clamp_function(): void
    {
        $context = new TestScoringContext(distance: 150);

        $result = $this->evaluator->evaluate('clamp(distance, 0, 100)', $context);

        $this->assertEquals(100, $result);
    }

    #[Test]
    public function it_evaluates_custom_between_function(): void
    {
        $context = new TestScoringContext(distance: 75);

        $result = $this->evaluator->evaluate('between(distance, 50, 100)', $context);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_evaluates_custom_percentage_function(): void
    {
        $context = new TestScoringContext(distance: 25);

        $result = $this->evaluator->evaluate('percentage(distance, 100)', $context);

        $this->assertEquals(25, $result);
    }

    #[Test]
    public function it_evaluates_many_expressions(): void
    {
        $context = new TestScoringContext(distance: 100, bonus: 50);

        $results = $this->evaluator->evaluateMany([
            'base_score' => 'distance * 2',
            'total_score' => 'distance * 2 + bonus',
            'is_long_haul' => 'distance > 500',
        ], $context);

        $this->assertEquals(200, $results['base_score']);
        $this->assertEquals(250, $results['total_score']);
        $this->assertFalse($results['is_long_haul']);
    }

    #[Test]
    public function it_safely_evaluates_with_default(): void
    {
        $context = new TestScoringContext;

        $result = $this->evaluator->evaluateSafe('invalid syntax !!!', $context, 0);

        $this->assertEquals(0, $result);
    }

    #[Test]
    public function it_throws_on_syntax_error(): void
    {
        $context = new TestScoringContext;

        $this->expectException(ExpressionSyntaxException::class);

        $this->evaluator->evaluate('distance ( invalid', $context);
    }

    #[Test]
    public function it_throws_on_security_violation(): void
    {
        $context = new TestScoringContext;

        $this->expectException(ExpressionSecurityException::class);

        $this->evaluator->evaluate('eval("bad")', $context);
    }

    #[Test]
    public function it_caches_parsed_expressions(): void
    {
        $context = new TestScoringContext(distance: 100);

        // Evaluate twice - second should use cache
        $result1 = $this->evaluator->evaluate('distance * 2', $context);
        $result2 = $this->evaluator->evaluate('distance * 2', $context);

        $this->assertEquals($result1, $result2);
    }
}
