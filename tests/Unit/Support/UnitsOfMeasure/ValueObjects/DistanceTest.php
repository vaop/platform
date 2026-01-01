<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\ValueObjects;

use PHPUnit\Framework\Attributes\Test;
use Support\Exceptions\InvalidValueException;
use Support\UnitsOfMeasure\Enums\DistanceUnit;
use Support\UnitsOfMeasure\ValueObjects\Distance;
use Tests\TestCase;

class DistanceTest extends TestCase
{
    #[Test]
    public function it_creates_distance_from_nautical_miles(): void
    {
        $distance = Distance::fromNauticalMiles(100);

        $this->assertEqualsWithDelta(100, $distance->toNauticalMiles(), 0.0001);
    }

    #[Test]
    public function it_creates_distance_from_kilometers(): void
    {
        $distance = Distance::fromKilometers(185.2);

        // 185.2 km ≈ 100 nm
        $this->assertEqualsWithDelta(100, $distance->toNauticalMiles(), 0.1);
    }

    #[Test]
    public function it_creates_distance_from_statute_miles(): void
    {
        $distance = Distance::fromStatuteMiles(115.08);

        // 115.08 mi ≈ 100 nm
        $this->assertEqualsWithDelta(100, $distance->toNauticalMiles(), 0.1);
    }

    #[Test]
    public function it_creates_distance_from_unit_enum(): void
    {
        $distance = Distance::from(100, DistanceUnit::NAUTICAL_MILES);

        $this->assertEqualsWithDelta(100, $distance->toNauticalMiles(), 0.0001);
    }

    #[Test]
    public function it_creates_zero_distance(): void
    {
        $distance = Distance::zero();

        $this->assertTrue($distance->isZero());
        $this->assertEqualsWithDelta(0, $distance->toNauticalMiles(), 0.0001);
    }

    #[Test]
    public function it_throws_exception_for_negative_distance(): void
    {
        $this->expectException(InvalidValueException::class);

        Distance::fromNauticalMiles(-10);
    }

    #[Test]
    public function it_converts_to_kilometers(): void
    {
        $distance = Distance::fromNauticalMiles(100);

        // 100 nm = 185.2 km
        $this->assertEqualsWithDelta(185.2, $distance->toKilometers(), 0.1);
    }

    #[Test]
    public function it_converts_to_statute_miles(): void
    {
        $distance = Distance::fromNauticalMiles(100);

        // 100 nm ≈ 115.08 statute miles
        $this->assertEqualsWithDelta(115.08, $distance->toStatuteMiles(), 0.1);
    }

    #[Test]
    public function it_adds_distances(): void
    {
        $d1 = Distance::fromNauticalMiles(50);
        $d2 = Distance::fromNauticalMiles(30);

        $result = $d1->add($d2);

        $this->assertEqualsWithDelta(80, $result->toNauticalMiles(), 0.0001);
        // Original unchanged (immutable)
        $this->assertEqualsWithDelta(50, $d1->toNauticalMiles(), 0.0001);
    }

    #[Test]
    public function it_subtracts_distances(): void
    {
        $d1 = Distance::fromNauticalMiles(50);
        $d2 = Distance::fromNauticalMiles(30);

        $result = $d1->subtract($d2);

        $this->assertEqualsWithDelta(20, $result->toNauticalMiles(), 0.0001);
    }

    #[Test]
    public function it_throws_exception_when_subtraction_would_be_negative(): void
    {
        $d1 = Distance::fromNauticalMiles(30);
        $d2 = Distance::fromNauticalMiles(50);

        $this->expectException(InvalidValueException::class);

        $d1->subtract($d2);
    }

    #[Test]
    public function it_compares_distances(): void
    {
        $d1 = Distance::fromNauticalMiles(100);
        $d2 = Distance::fromNauticalMiles(50);

        $this->assertTrue($d1->isGreaterThan($d2));
        $this->assertFalse($d1->isLessThan($d2));
        $this->assertTrue($d2->isLessThan($d1));
    }

    #[Test]
    public function it_checks_equality(): void
    {
        $d1 = Distance::fromNauticalMiles(100);
        $d2 = Distance::fromNauticalMiles(100);
        $d3 = Distance::fromNauticalMiles(50);

        $this->assertTrue($d1->equals($d2));
        $this->assertFalse($d1->equals($d3));
    }

    #[Test]
    public function it_converts_to_string(): void
    {
        $distance = Distance::fromNauticalMiles(123.45);

        $this->assertSame('123.45 nm', (string) $distance);
    }
}
