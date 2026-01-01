<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\Quantities;

use PHPUnit\Framework\Attributes\Test;
use Support\UnitsOfMeasure\Enums\FuelUnit;
use Support\UnitsOfMeasure\Quantities\Volume;
use Tests\TestCase;

class VolumeTest extends TestCase
{
    #[Test]
    public function it_creates_volume_from_liters(): void
    {
        $volume = Volume::fromLiters(1000);

        $this->assertEqualsWithDelta(1000, $volume->toLiters(), 0.01);
    }

    #[Test]
    public function it_creates_volume_from_gallons(): void
    {
        $volume = Volume::fromGallons(100);

        $this->assertEqualsWithDelta(100, $volume->toGallons(), 0.01);
    }

    #[Test]
    public function it_creates_volume_from_fuel_enum(): void
    {
        $volume = Volume::fromFuel(500, FuelUnit::LITERS);

        $this->assertEqualsWithDelta(500, $volume->toLiters(), 0.01);
    }

    #[Test]
    public function it_converts_liters_to_gallons(): void
    {
        $volume = Volume::fromLiters(100);

        // 1 liter = 0.264172 US gallons
        $this->assertEqualsWithDelta(26.4172, $volume->toGallons(), 0.01);
    }

    #[Test]
    public function it_converts_gallons_to_liters(): void
    {
        $volume = Volume::fromGallons(100);

        // 1 US gallon = 3.78541 liters
        $this->assertEqualsWithDelta(378.541, $volume->toLiters(), 0.01);
    }
}
