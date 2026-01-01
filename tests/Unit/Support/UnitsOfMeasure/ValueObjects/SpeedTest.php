<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\ValueObjects;

use PHPUnit\Framework\Attributes\Test;
use Support\Exceptions\InvalidValueException;
use Support\UnitsOfMeasure\Enums\SpeedUnit;
use Support\UnitsOfMeasure\ValueObjects\Speed;
use Tests\TestCase;

class SpeedTest extends TestCase
{
    #[Test]
    public function it_creates_speed_from_knots(): void
    {
        $speed = Speed::fromKnots(450);

        $this->assertEqualsWithDelta(450, $speed->toKnots(), 0.01);
    }

    #[Test]
    public function it_creates_speed_from_kilometers_per_hour(): void
    {
        $speed = Speed::fromKilometersPerHour(833.4);

        // 833.4 km/h ≈ 450 kts
        $this->assertEqualsWithDelta(450, $speed->toKnots(), 1);
    }

    #[Test]
    public function it_creates_speed_from_miles_per_hour(): void
    {
        $speed = Speed::fromMilesPerHour(518);

        // 518 mph ≈ 450 kts
        $this->assertEqualsWithDelta(450, $speed->toKnots(), 1);
    }

    #[Test]
    public function it_creates_speed_from_unit_enum(): void
    {
        $speed = Speed::from(450, SpeedUnit::KNOTS);

        $this->assertEqualsWithDelta(450, $speed->toKnots(), 0.01);
    }

    #[Test]
    public function it_creates_zero_speed(): void
    {
        $speed = Speed::zero();

        $this->assertTrue($speed->isZero());
        $this->assertEqualsWithDelta(0, $speed->toKnots(), 0.01);
    }

    #[Test]
    public function it_throws_exception_for_negative_speed(): void
    {
        $this->expectException(InvalidValueException::class);

        Speed::fromKnots(-10);
    }

    #[Test]
    public function it_converts_to_kilometers_per_hour(): void
    {
        $speed = Speed::fromKnots(100);

        // 100 kts ≈ 185.2 km/h
        $this->assertEqualsWithDelta(185.2, $speed->toKilometersPerHour(), 0.1);
    }

    #[Test]
    public function it_converts_to_miles_per_hour(): void
    {
        $speed = Speed::fromKnots(100);

        // 100 kts ≈ 115.08 mph
        $this->assertEqualsWithDelta(115.08, $speed->toMilesPerHour(), 0.1);
    }

    #[Test]
    public function it_adds_speeds(): void
    {
        $s1 = Speed::fromKnots(300);
        $s2 = Speed::fromKnots(150);

        $result = $s1->add($s2);

        $this->assertEqualsWithDelta(450, $result->toKnots(), 0.01);
    }

    #[Test]
    public function it_subtracts_speeds(): void
    {
        $s1 = Speed::fromKnots(450);
        $s2 = Speed::fromKnots(150);

        $result = $s1->subtract($s2);

        $this->assertEqualsWithDelta(300, $result->toKnots(), 0.01);
    }

    #[Test]
    public function it_throws_exception_when_subtraction_would_be_negative(): void
    {
        $s1 = Speed::fromKnots(100);
        $s2 = Speed::fromKnots(200);

        $this->expectException(InvalidValueException::class);

        $s1->subtract($s2);
    }

    #[Test]
    public function it_compares_speeds(): void
    {
        $s1 = Speed::fromKnots(500);
        $s2 = Speed::fromKnots(300);

        $this->assertTrue($s1->isGreaterThan($s2));
        $this->assertFalse($s1->isLessThan($s2));
        $this->assertTrue($s2->isLessThan($s1));
    }

    #[Test]
    public function it_checks_equality(): void
    {
        $s1 = Speed::fromKnots(450);
        $s2 = Speed::fromKnots(450);
        $s3 = Speed::fromKnots(300);

        $this->assertTrue($s1->equals($s2));
        $this->assertFalse($s1->equals($s3));
    }

    #[Test]
    public function it_converts_to_string(): void
    {
        $speed = Speed::fromKnots(450);

        $this->assertSame('450 kts', (string) $speed);
    }
}
