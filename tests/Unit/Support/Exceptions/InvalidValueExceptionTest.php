<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Exceptions;

use PHPUnit\Framework\Attributes\Test;
use Support\Exceptions\InvalidValueException;
use Tests\TestCase;

class InvalidValueExceptionTest extends TestCase
{
    #[Test]
    public function it_creates_out_of_range_exception_with_min_and_max(): void
    {
        $exception = InvalidValueException::outOfRange('distance', 150, 0, 100);

        $this->assertStringContainsString('distance', $exception->getMessage());
        $this->assertStringContainsString('between 0 and 100', $exception->getMessage());
        $this->assertStringContainsString('150', $exception->getMessage());
        $this->assertEquals('distance', $exception->context);
    }

    #[Test]
    public function it_creates_out_of_range_exception_with_min_only(): void
    {
        $exception = InvalidValueException::outOfRange('altitude', -100, 0);

        $this->assertStringContainsString('at least 0', $exception->getMessage());
    }

    #[Test]
    public function it_creates_out_of_range_exception_with_max_only(): void
    {
        $exception = InvalidValueException::outOfRange('speed', 1000, max: 500);

        $this->assertStringContainsString('at most 500', $exception->getMessage());
    }

    #[Test]
    public function it_creates_must_be_positive_exception(): void
    {
        $exception = InvalidValueException::mustBePositive('weight', -5);

        $this->assertStringContainsString('weight', $exception->getMessage());
        $this->assertStringContainsString('must be positive', $exception->getMessage());
        $this->assertStringContainsString('-5', $exception->getMessage());
    }

    #[Test]
    public function it_creates_cannot_be_negative_exception(): void
    {
        $exception = InvalidValueException::cannotBeNegative('duration', -30);

        $this->assertStringContainsString('duration', $exception->getMessage());
        $this->assertStringContainsString('cannot be negative', $exception->getMessage());
    }

    #[Test]
    public function it_creates_invalid_format_exception(): void
    {
        $exception = InvalidValueException::invalidFormat('icao', 'ABCDE', '4 uppercase letters');

        $this->assertStringContainsString('icao', $exception->getMessage());
        $this->assertStringContainsString("'ABCDE'", $exception->getMessage());
        $this->assertStringContainsString('4 uppercase letters', $exception->getMessage());
    }

    #[Test]
    public function it_creates_cannot_be_empty_exception(): void
    {
        $exception = InvalidValueException::cannotBeEmpty('name');

        $this->assertStringContainsString('name', $exception->getMessage());
        $this->assertStringContainsString('cannot be empty', $exception->getMessage());
    }
}
