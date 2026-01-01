<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\ValueObjects;

use PHPUnit\Framework\Attributes\Test;
use Support\Exceptions\InvalidValueException;
use Support\UnitsOfMeasure\ValueObjects\Distance;
use Support\UnitsOfMeasure\ValueObjects\GeoCoordinate;
use Tests\TestCase;

class GeoCoordinateTest extends TestCase
{
    #[Test]
    public function it_creates_coordinate_from_decimal_degrees(): void
    {
        $coord = GeoCoordinate::fromDecimalDegrees(40.7128, -74.0060);

        $this->assertEqualsWithDelta(40.7128, $coord->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(-74.0060, $coord->getLongitude(), 0.0001);
    }

    #[Test]
    public function it_creates_coordinate_from_dms(): void
    {
        // 40°42'46.08"N 74°0'21.6"W (New York City)
        $coord = GeoCoordinate::fromDMS(40, 42, 46.08, 'N', 74, 0, 21.6, 'W');

        $this->assertEqualsWithDelta(40.7128, $coord->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(-74.0060, $coord->getLongitude(), 0.001);
    }

    #[Test]
    public function it_throws_exception_for_invalid_latitude(): void
    {
        $this->expectException(InvalidValueException::class);

        GeoCoordinate::fromDecimalDegrees(91, 0);
    }

    #[Test]
    public function it_throws_exception_for_invalid_latitude_negative(): void
    {
        $this->expectException(InvalidValueException::class);

        GeoCoordinate::fromDecimalDegrees(-91, 0);
    }

    #[Test]
    public function it_throws_exception_for_invalid_longitude(): void
    {
        $this->expectException(InvalidValueException::class);

        GeoCoordinate::fromDecimalDegrees(0, 181);
    }

    #[Test]
    public function it_throws_exception_for_invalid_longitude_negative(): void
    {
        $this->expectException(InvalidValueException::class);

        GeoCoordinate::fromDecimalDegrees(0, -181);
    }

    #[Test]
    public function it_gets_latitude_direction(): void
    {
        $north = GeoCoordinate::fromDecimalDegrees(40, 0);
        $south = GeoCoordinate::fromDecimalDegrees(-40, 0);

        $this->assertSame('N', $north->getLatitudeDirection());
        $this->assertSame('S', $south->getLatitudeDirection());
    }

    #[Test]
    public function it_gets_longitude_direction(): void
    {
        $east = GeoCoordinate::fromDecimalDegrees(0, 74);
        $west = GeoCoordinate::fromDecimalDegrees(0, -74);

        $this->assertSame('E', $east->getLongitudeDirection());
        $this->assertSame('W', $west->getLongitudeDirection());
    }

    #[Test]
    public function it_converts_to_dms(): void
    {
        $coord = GeoCoordinate::fromDecimalDegrees(40.7128, -74.0060);

        $latDMS = $coord->getLatitudeDMS();
        $lonDMS = $coord->getLongitudeDMS();

        $this->assertSame(40, $latDMS['degrees']);
        $this->assertSame(42, $latDMS['minutes']);
        $this->assertEqualsWithDelta(46.08, $latDMS['seconds'], 0.1);
        $this->assertSame('N', $latDMS['direction']);

        $this->assertSame(74, $lonDMS['degrees']);
        $this->assertSame(0, $lonDMS['minutes']);
        $this->assertEqualsWithDelta(21.6, $lonDMS['seconds'], 0.1);
        $this->assertSame('W', $lonDMS['direction']);
    }

    #[Test]
    public function it_calculates_distance_to_another_coordinate(): void
    {
        // JFK Airport (40.6413° N, 73.7781° W)
        $jfk = GeoCoordinate::fromDecimalDegrees(40.6413, -73.7781);

        // LAX Airport (33.9425° N, 118.4081° W)
        $lax = GeoCoordinate::fromDecimalDegrees(33.9425, -118.4081);

        $distance = $jfk->distanceTo($lax);

        // JFK to LAX is approximately 2145 nm
        $this->assertInstanceOf(Distance::class, $distance);
        $this->assertEqualsWithDelta(2145, $distance->toNauticalMiles(), 10);
    }

    #[Test]
    public function it_calculates_bearing_to_another_coordinate(): void
    {
        // JFK Airport
        $jfk = GeoCoordinate::fromDecimalDegrees(40.6413, -73.7781);

        // LAX Airport
        $lax = GeoCoordinate::fromDecimalDegrees(33.9425, -118.4081);

        $bearing = $jfk->bearingTo($lax);

        // Bearing from JFK to LAX is approximately 275° (westward)
        $this->assertEqualsWithDelta(275, $bearing, 5);
    }

    #[Test]
    public function it_formats_as_iso6709(): void
    {
        $coord = GeoCoordinate::fromDecimalDegrees(40.7128, -74.0060);

        $iso = $coord->toIso6709();

        $this->assertStringContainsString('+40.7128', $iso);
        $this->assertStringContainsString('-74.0060', $iso);
        $this->assertStringEndsWith('/', $iso);
    }

    #[Test]
    public function it_formats_as_dms_string(): void
    {
        $coord = GeoCoordinate::fromDecimalDegrees(40.7128, -74.0060);

        $dms = $coord->toDMSString();

        $this->assertStringContainsString('40°', $dms);
        $this->assertStringContainsString('N', $dms);
        $this->assertStringContainsString('74°', $dms);
        $this->assertStringContainsString('W', $dms);
    }

    #[Test]
    public function it_checks_equality(): void
    {
        $c1 = GeoCoordinate::fromDecimalDegrees(40.7128, -74.0060);
        $c2 = GeoCoordinate::fromDecimalDegrees(40.7128, -74.0060);
        $c3 = GeoCoordinate::fromDecimalDegrees(33.9425, -118.4081);

        $this->assertTrue($c1->equals($c2));
        $this->assertFalse($c1->equals($c3));
    }

    #[Test]
    public function it_converts_to_string(): void
    {
        $coord = GeoCoordinate::fromDecimalDegrees(40.7128, -74.0060);

        $this->assertSame('40.712800, -74.006000', (string) $coord);
    }
}
