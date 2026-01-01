<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\ValueObjects;

use PHPUnit\Framework\Attributes\Test;
use Support\Exceptions\InvalidValueException;
use Support\UnitsOfMeasure\Enums\WeightUnit;
use Support\UnitsOfMeasure\ValueObjects\Weight;
use Tests\TestCase;

class WeightTest extends TestCase
{
    #[Test]
    public function it_creates_weight_from_kilograms(): void
    {
        $weight = Weight::fromKilograms(1000);

        $this->assertEqualsWithDelta(1000, $weight->toKilograms(), 0.01);
    }

    #[Test]
    public function it_creates_weight_from_pounds(): void
    {
        $weight = Weight::fromPounds(2204.62);

        // 2204.62 lbs ≈ 1000 kg
        $this->assertEqualsWithDelta(1000, $weight->toKilograms(), 1);
    }

    #[Test]
    public function it_creates_weight_from_unit_enum(): void
    {
        $weight = Weight::from(1000, WeightUnit::KILOGRAMS);

        $this->assertEqualsWithDelta(1000, $weight->toKilograms(), 0.01);
    }

    #[Test]
    public function it_creates_zero_weight(): void
    {
        $weight = Weight::zero();

        $this->assertTrue($weight->isZero());
        $this->assertEqualsWithDelta(0, $weight->toKilograms(), 0.01);
    }

    #[Test]
    public function it_throws_exception_for_negative_weight(): void
    {
        $this->expectException(InvalidValueException::class);

        Weight::fromKilograms(-10);
    }

    #[Test]
    public function it_converts_to_pounds(): void
    {
        $weight = Weight::fromKilograms(1000);

        // 1000 kg ≈ 2204.62 lbs
        $this->assertEqualsWithDelta(2204.62, $weight->toPounds(), 1);
    }

    #[Test]
    public function it_adds_weights(): void
    {
        $w1 = Weight::fromKilograms(500);
        $w2 = Weight::fromKilograms(300);

        $result = $w1->add($w2);

        $this->assertEqualsWithDelta(800, $result->toKilograms(), 0.01);
    }

    #[Test]
    public function it_subtracts_weights(): void
    {
        $w1 = Weight::fromKilograms(1000);
        $w2 = Weight::fromKilograms(400);

        $result = $w1->subtract($w2);

        $this->assertEqualsWithDelta(600, $result->toKilograms(), 0.01);
    }

    #[Test]
    public function it_throws_exception_when_subtraction_would_be_negative(): void
    {
        $w1 = Weight::fromKilograms(100);
        $w2 = Weight::fromKilograms(200);

        $this->expectException(InvalidValueException::class);

        $w1->subtract($w2);
    }

    #[Test]
    public function it_compares_weights(): void
    {
        $w1 = Weight::fromKilograms(1000);
        $w2 = Weight::fromKilograms(500);

        $this->assertTrue($w1->isGreaterThan($w2));
        $this->assertFalse($w1->isLessThan($w2));
        $this->assertTrue($w2->isLessThan($w1));
    }

    #[Test]
    public function it_checks_equality(): void
    {
        $w1 = Weight::fromKilograms(1000);
        $w2 = Weight::fromKilograms(1000);
        $w3 = Weight::fromKilograms(500);

        $this->assertTrue($w1->equals($w2));
        $this->assertFalse($w1->equals($w3));
    }

    #[Test]
    public function it_converts_to_string(): void
    {
        $weight = Weight::fromKilograms(1234.56);

        $this->assertSame('1234.56 kg', (string) $weight);
    }
}
