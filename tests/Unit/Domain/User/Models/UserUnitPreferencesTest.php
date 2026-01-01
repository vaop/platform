<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\Models;

use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Support\UnitsOfMeasure\Enums\AltitudeUnit;
use Support\UnitsOfMeasure\Enums\DistanceUnit;
use Support\UnitsOfMeasure\Enums\FuelUnit;
use Support\UnitsOfMeasure\Enums\SpeedUnit;
use Support\UnitsOfMeasure\Enums\TemperatureUnit;
use Support\UnitsOfMeasure\Enums\WeightUnit;
use Tests\TestCase;

class UserUnitPreferencesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_nullable_unit_preferences_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->distance_unit);
        $this->assertNull($user->altitude_unit);
        $this->assertNull($user->speed_unit);
        $this->assertNull($user->weight_unit);
        $this->assertNull($user->fuel_unit);
        $this->assertNull($user->temperature_unit);
    }

    #[Test]
    public function it_casts_distance_unit_to_enum(): void
    {
        $user = User::factory()->create([
            'distance_unit' => DistanceUnit::KILOMETERS,
        ]);

        $this->assertInstanceOf(DistanceUnit::class, $user->distance_unit);
        $this->assertEquals(DistanceUnit::KILOMETERS, $user->distance_unit);
    }

    #[Test]
    public function it_casts_altitude_unit_to_enum(): void
    {
        $user = User::factory()->create([
            'altitude_unit' => AltitudeUnit::METERS,
        ]);

        $this->assertInstanceOf(AltitudeUnit::class, $user->altitude_unit);
        $this->assertEquals(AltitudeUnit::METERS, $user->altitude_unit);
    }

    #[Test]
    public function it_casts_speed_unit_to_enum(): void
    {
        $user = User::factory()->create([
            'speed_unit' => SpeedUnit::MILES_PER_HOUR,
        ]);

        $this->assertInstanceOf(SpeedUnit::class, $user->speed_unit);
        $this->assertEquals(SpeedUnit::MILES_PER_HOUR, $user->speed_unit);
    }

    #[Test]
    public function it_casts_weight_unit_to_enum(): void
    {
        $user = User::factory()->create([
            'weight_unit' => WeightUnit::POUNDS,
        ]);

        $this->assertInstanceOf(WeightUnit::class, $user->weight_unit);
        $this->assertEquals(WeightUnit::POUNDS, $user->weight_unit);
    }

    #[Test]
    public function it_casts_fuel_unit_to_enum(): void
    {
        $user = User::factory()->create([
            'fuel_unit' => FuelUnit::GALLONS,
        ]);

        $this->assertInstanceOf(FuelUnit::class, $user->fuel_unit);
        $this->assertEquals(FuelUnit::GALLONS, $user->fuel_unit);
    }

    #[Test]
    public function it_casts_temperature_unit_to_enum(): void
    {
        $user = User::factory()->create([
            'temperature_unit' => TemperatureUnit::FAHRENHEIT,
        ]);

        $this->assertInstanceOf(TemperatureUnit::class, $user->temperature_unit);
        $this->assertEquals(TemperatureUnit::FAHRENHEIT, $user->temperature_unit);
    }

    #[Test]
    public function it_can_update_all_unit_preferences(): void
    {
        $user = User::factory()->create();

        $user->update([
            'distance_unit' => DistanceUnit::STATUTE_MILES,
            'altitude_unit' => AltitudeUnit::METERS,
            'speed_unit' => SpeedUnit::KILOMETERS_PER_HOUR,
            'weight_unit' => WeightUnit::POUNDS,
            'fuel_unit' => FuelUnit::LITERS,
            'temperature_unit' => TemperatureUnit::FAHRENHEIT,
        ]);

        $user->refresh();

        $this->assertEquals(DistanceUnit::STATUTE_MILES, $user->distance_unit);
        $this->assertEquals(AltitudeUnit::METERS, $user->altitude_unit);
        $this->assertEquals(SpeedUnit::KILOMETERS_PER_HOUR, $user->speed_unit);
        $this->assertEquals(WeightUnit::POUNDS, $user->weight_unit);
        $this->assertEquals(FuelUnit::LITERS, $user->fuel_unit);
        $this->assertEquals(TemperatureUnit::FAHRENHEIT, $user->temperature_unit);
    }
}
