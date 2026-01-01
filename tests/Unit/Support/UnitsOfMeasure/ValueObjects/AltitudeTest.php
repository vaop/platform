<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\ValueObjects;

use PHPUnit\Framework\Attributes\Test;
use Support\Exceptions\InvalidValueException;
use Support\UnitsOfMeasure\Enums\AltitudeUnit;
use Support\UnitsOfMeasure\ValueObjects\Altitude;
use Tests\TestCase;

class AltitudeTest extends TestCase
{
    #[Test]
    public function it_creates_altitude_from_feet(): void
    {
        $altitude = Altitude::fromFeet(35000);

        $this->assertEqualsWithDelta(35000, $altitude->toFeet(), 0.1);
    }

    #[Test]
    public function it_creates_altitude_from_meters(): void
    {
        $altitude = Altitude::fromMeters(10668);

        // 10668 m ≈ 35000 ft
        $this->assertEqualsWithDelta(35000, $altitude->toFeet(), 10);
    }

    #[Test]
    public function it_creates_altitude_from_unit_enum(): void
    {
        $altitude = Altitude::from(35000, AltitudeUnit::FEET);

        $this->assertEqualsWithDelta(35000, $altitude->toFeet(), 0.1);
    }

    #[Test]
    public function it_creates_altitude_from_flight_level(): void
    {
        $altitude = Altitude::fromFlightLevel(350);

        $this->assertEqualsWithDelta(35000, $altitude->toFeet(), 0.1);
    }

    #[Test]
    public function it_throws_exception_for_negative_flight_level(): void
    {
        $this->expectException(InvalidValueException::class);

        Altitude::fromFlightLevel(-10);
    }

    #[Test]
    public function it_creates_sea_level_altitude(): void
    {
        $altitude = Altitude::seaLevel();

        $this->assertTrue($altitude->isSeaLevel());
        $this->assertEqualsWithDelta(0, $altitude->toFeet(), 0.1);
    }

    #[Test]
    public function it_allows_negative_altitude(): void
    {
        // Below sea level is valid (e.g., Death Valley, Dead Sea)
        $altitude = Altitude::fromFeet(-282);

        $this->assertEqualsWithDelta(-282, $altitude->toFeet(), 0.1);
    }

    #[Test]
    public function it_converts_to_meters(): void
    {
        $altitude = Altitude::fromFeet(10000);

        // 10000 ft ≈ 3048 m
        $this->assertEqualsWithDelta(3048, $altitude->toMeters(), 1);
    }

    #[Test]
    public function it_converts_to_flight_level(): void
    {
        $altitude = Altitude::fromFeet(35000);

        $this->assertSame(350, $altitude->toFlightLevel());
    }

    #[Test]
    public function it_adds_altitudes(): void
    {
        $a1 = Altitude::fromFeet(30000);
        $a2 = Altitude::fromFeet(5000);

        $result = $a1->add($a2);

        $this->assertEqualsWithDelta(35000, $result->toFeet(), 0.1);
    }

    #[Test]
    public function it_subtracts_altitudes(): void
    {
        $a1 = Altitude::fromFeet(35000);
        $a2 = Altitude::fromFeet(5000);

        $result = $a1->subtract($a2);

        $this->assertEqualsWithDelta(30000, $result->toFeet(), 0.1);
    }

    #[Test]
    public function it_allows_subtraction_resulting_in_negative(): void
    {
        // Altitude can be negative, so this is allowed
        $a1 = Altitude::fromFeet(1000);
        $a2 = Altitude::fromFeet(2000);

        $result = $a1->subtract($a2);

        $this->assertEqualsWithDelta(-1000, $result->toFeet(), 0.1);
    }

    #[Test]
    public function it_compares_altitudes(): void
    {
        $a1 = Altitude::fromFeet(35000);
        $a2 = Altitude::fromFeet(30000);

        $this->assertTrue($a1->isAbove($a2));
        $this->assertFalse($a1->isBelow($a2));
        $this->assertTrue($a1->isAtOrAbove($a2));
        $this->assertTrue($a2->isBelow($a1));
    }

    #[Test]
    public function it_checks_equality(): void
    {
        $a1 = Altitude::fromFeet(35000);
        $a2 = Altitude::fromFeet(35000);
        $a3 = Altitude::fromFeet(30000);

        $this->assertTrue($a1->equals($a2));
        $this->assertFalse($a1->equals($a3));
    }

    #[Test]
    public function it_converts_to_string(): void
    {
        $altitude = Altitude::fromFeet(35000);

        $this->assertSame('35000 ft', (string) $altitude);
    }
}
