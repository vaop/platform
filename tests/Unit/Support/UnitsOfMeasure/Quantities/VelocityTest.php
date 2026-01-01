<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\Quantities;

use PHPUnit\Framework\Attributes\Test;
use Support\UnitsOfMeasure\Enums\SpeedUnit;
use Support\UnitsOfMeasure\Quantities\Velocity;
use Tests\TestCase;

class VelocityTest extends TestCase
{
    #[Test]
    public function it_creates_velocity_from_knots(): void
    {
        $velocity = Velocity::fromKnots(450);

        $this->assertEqualsWithDelta(450, $velocity->toKnots(), 0.01);
    }

    #[Test]
    public function it_creates_velocity_from_speed_enum(): void
    {
        $velocity = Velocity::fromSpeed(800, SpeedUnit::KILOMETERS_PER_HOUR);

        $this->assertEqualsWithDelta(800, $velocity->toKilometersPerHour(), 0.01);
    }

    #[Test]
    public function it_converts_knots_to_kilometers_per_hour(): void
    {
        $velocity = Velocity::fromKnots(100);

        // 1 knot = 1.852 km/h
        $this->assertEqualsWithDelta(185.2, $velocity->toKilometersPerHour(), 0.1);
    }

    #[Test]
    public function it_converts_knots_to_miles_per_hour(): void
    {
        $velocity = Velocity::fromKnots(100);

        // 1 knot = 1.15078 mph
        $this->assertEqualsWithDelta(115.078, $velocity->toMilesPerHour(), 0.1);
    }

    #[Test]
    public function it_creates_velocity_from_feet_per_minute(): void
    {
        $velocity = Velocity::fromFeetPerMinute(2000);

        $this->assertEqualsWithDelta(2000, $velocity->toFeetPerMinute(), 0.1);
    }

    #[Test]
    public function it_converts_to_vertical_speed_unit(): void
    {
        $velocity = Velocity::fromFeetPerMinute(2500);

        // Vertical speed is expressed in hundreds of feet per minute
        $this->assertEqualsWithDelta(25, $velocity->toVerticalSpeedUnit(), 0.01);
    }
}
