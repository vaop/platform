<?php

declare(strict_types=1);

namespace Tests\Unit\System\ExpressionLanguage\Services;

use PHPUnit\Framework\Attributes\Test;
use System\ExpressionLanguage\Contracts\ExpressionContextInterface;
use System\ExpressionLanguage\Exceptions\ExpressionSyntaxException;
use System\ExpressionLanguage\Services\ExpressionValidator;
use Tests\TestCase;

/**
 * Test context for validation.
 */
class TestValidationContext implements ExpressionContextInterface
{
    public function getVariables(): array
    {
        return [
            'distance' => 100,
            'speed' => 450,
            'altitude' => 35000,
        ];
    }

    public function getName(): string
    {
        return 'test';
    }

    public function getAllowedVariables(): array
    {
        return ['distance', 'speed', 'altitude'];
    }
}

class ExpressionValidatorTest extends TestCase
{
    private ExpressionValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = app(ExpressionValidator::class);
    }

    #[Test]
    public function it_validates_correct_expression(): void
    {
        $context = new TestValidationContext;

        $result = $this->validator->validate('distance * 2', $context);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    #[Test]
    public function it_validates_complex_expression(): void
    {
        $context = new TestValidationContext;

        $result = $this->validator->validate(
            'distance > 100 and speed < 500 and altitude > 10000',
            $context,
        );

        $this->assertTrue($result['valid']);
    }

    #[Test]
    public function it_detects_syntax_errors(): void
    {
        $context = new TestValidationContext;

        $result = $this->validator->validate('distance ( invalid', $context);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    #[Test]
    public function it_detects_security_violations(): void
    {
        $context = new TestValidationContext;

        $result = $this->validator->validate('eval("bad code")', $context);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    #[Test]
    public function it_returns_is_valid_boolean(): void
    {
        $context = new TestValidationContext;

        $this->assertTrue($this->validator->isValid('distance * 2', $context));
        $this->assertFalse($this->validator->isValid('invalid +++', $context));
    }

    #[Test]
    public function it_validates_syntax_separately(): void
    {
        $context = new TestValidationContext;

        // Should not throw for valid syntax
        $this->validator->validateSyntax('distance * 2', $context);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_throws_on_invalid_syntax(): void
    {
        $context = new TestValidationContext;

        $this->expectException(ExpressionSyntaxException::class);

        $this->validator->validateSyntax('distance +++ invalid', $context);
    }

    #[Test]
    public function it_validates_without_context(): void
    {
        $result = $this->validator->validate('1 + 2 * 3');

        $this->assertTrue($result['valid']);
    }

    #[Test]
    public function it_validates_expressions_with_functions(): void
    {
        $context = new TestValidationContext;

        $result = $this->validator->validate('round(distance, 2)', $context);

        $this->assertTrue($result['valid']);
    }

    #[Test]
    public function it_validates_boolean_expressions(): void
    {
        $context = new TestValidationContext;

        $result = $this->validator->validate('distance > 100 or speed < 200', $context);

        $this->assertTrue($result['valid']);
    }
}
