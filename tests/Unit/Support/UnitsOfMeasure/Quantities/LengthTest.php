<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\Quantities;

use PHPUnit\Framework\Attributes\Test;
use Support\UnitsOfMeasure\Enums\AltitudeUnit;
use Support\UnitsOfMeasure\Enums\DistanceUnit;
use Support\UnitsOfMeasure\Quantities\Length;
use Tests\TestCase;

class LengthTest extends TestCase
{
    #[Test]
    public function it_creates_length_from_nautical_miles(): void
    {
        $length = Length::fromNauticalMiles(100);

        $this->assertEqualsWithDelta(100, $length->toNauticalMiles(), 0.01);
    }

    #[Test]
    public function it_creates_length_from_distance_enum(): void
    {
        $length = Length::fromDistance(100, DistanceUnit::KILOMETERS);

        $this->assertEqualsWithDelta(100, $length->toKilometers(), 0.01);
    }

    #[Test]
    public function it_creates_length_from_feet(): void
    {
        $length = Length::fromFeet(35000);

        $this->assertEqualsWithDelta(35000, $length->toFeet(), 0.01);
    }

    #[Test]
    public function it_creates_length_from_altitude_enum(): void
    {
        $length = Length::fromAltitude(10000, AltitudeUnit::METERS);

        $this->assertEqualsWithDelta(10000, $length->toMeters(), 0.01);
    }

    #[Test]
    public function it_converts_nautical_miles_to_kilometers(): void
    {
        $length = Length::fromNauticalMiles(100);

        // 1 nautical mile = 1.852 km
        $this->assertEqualsWithDelta(185.2, $length->toKilometers(), 0.1);
    }

    #[Test]
    public function it_converts_feet_to_meters(): void
    {
        $length = Length::fromFeet(10000);

        // 1 foot = 0.3048 meters
        $this->assertEqualsWithDelta(3048, $length->toMeters(), 0.1);
    }

    #[Test]
    public function it_converts_to_flight_level(): void
    {
        $length = Length::fromFeet(35000);

        $this->assertEqualsWithDelta(350, $length->toFlightLevel(), 0.01);
    }

    #[Test]
    public function it_converts_meters_to_feet(): void
    {
        $length = Length::fromAltitude(1000, AltitudeUnit::METERS);

        // 1 meter = 3.28084 feet
        $this->assertEqualsWithDelta(3280.84, $length->toFeet(), 0.1);
    }
}
