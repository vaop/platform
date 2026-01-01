<?php

declare(strict_types=1);

namespace Tests\Unit\System\Settings;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Support\UnitsOfMeasure\Enums\AltitudeUnit;
use Support\UnitsOfMeasure\Enums\DistanceUnit;
use Support\UnitsOfMeasure\Enums\FuelUnit;
use Support\UnitsOfMeasure\Enums\HeightUnit;
use Support\UnitsOfMeasure\Enums\LengthUnit;
use Support\UnitsOfMeasure\Enums\PressureUnit;
use Support\UnitsOfMeasure\Enums\SpeedUnit;
use Support\UnitsOfMeasure\Enums\TemperatureUnit;
use Support\UnitsOfMeasure\Enums\VolumeUnit;
use Support\UnitsOfMeasure\Enums\WeightUnit;
use System\Settings\UnitsSettings;
use Tests\TestCase;

class UnitsSettingsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_correct_group_name(): void
    {
        $this->assertEquals('units', UnitsSettings::group());
    }

    #[Test]
    public function it_allows_user_customization_by_default(): void
    {
        $settings = app(UnitsSettings::class);

        $this->assertTrue($settings->allowUserCustomization);
        $this->assertTrue($settings->allowsUserCustomization());
    }

    #[Test]
    public function it_can_disable_user_customization(): void
    {
        $settings = app(UnitsSettings::class);
        $settings->allowUserCustomization = false;
        $settings->save();

        $freshSettings = app(UnitsSettings::class);
        $this->assertFalse($freshSettings->allowsUserCustomization());
    }

    #[Test]
    public function it_has_canonical_distance_unit_as_default(): void
    {
        $settings = app(UnitsSettings::class);

        $this->assertEquals(DistanceUnit::canonical()->value, $settings->distanceUnit);
        $this->assertEquals(DistanceUnit::NAUTICAL_MILES, $settings->getDistanceUnit());
    }

    #[Test]
    public function it_has_canonical_altitude_unit_as_default(): void
    {
        $settings = app(UnitsSettings::class);

        $this->assertEquals(AltitudeUnit::canonical()->value, $settings->altitudeUnit);
        $this->assertEquals(AltitudeUnit::FEET, $settings->getAltitudeUnit());
    }

    #[Test]
    public function it_has_canonical_height_unit_as_default(): void
    {
        $settings = app(UnitsSettings::class);

        $this->assertEquals(HeightUnit::canonical()->value, $settings->heightUnit);
        $this->assertEquals(HeightUnit::FEET, $settings->getHeightUnit());
    }

    #[Test]
    public function it_has_canonical_length_unit_as_default(): void
    {
        $settings = app(UnitsSettings::class);

        $this->assertEquals(LengthUnit::canonical()->value, $settings->lengthUnit);
        $this->assertEquals(LengthUnit::METERS, $settings->getLengthUnit());
    }

    #[Test]
    public function it_has_canonical_pressure_unit_as_default(): void
    {
        $settings = app(UnitsSettings::class);

        $this->assertEquals(PressureUnit::canonical()->value, $settings->pressureUnit);
        $this->assertEquals(PressureUnit::HECTOPASCALS, $settings->getPressureUnit());
    }

    #[Test]
    public function it_has_canonical_speed_unit_as_default(): void
    {
        $settings = app(UnitsSettings::class);

        $this->assertEquals(SpeedUnit::canonical()->value, $settings->speedUnit);
        $this->assertEquals(SpeedUnit::KNOTS, $settings->getSpeedUnit());
    }

    #[Test]
    public function it_has_canonical_weight_unit_as_default(): void
    {
        $settings = app(UnitsSettings::class);

        $this->assertEquals(WeightUnit::canonical()->value, $settings->weightUnit);
        $this->assertEquals(WeightUnit::KILOGRAMS, $settings->getWeightUnit());
    }

    #[Test]
    public function it_has_canonical_fuel_unit_as_default(): void
    {
        $settings = app(UnitsSettings::class);

        $this->assertEquals(FuelUnit::canonical()->value, $settings->fuelUnit);
        $this->assertEquals(FuelUnit::KILOGRAMS, $settings->getFuelUnit());
    }

    #[Test]
    public function it_has_canonical_volume_unit_as_default(): void
    {
        $settings = app(UnitsSettings::class);

        $this->assertEquals(VolumeUnit::canonical()->value, $settings->volumeUnit);
        $this->assertEquals(VolumeUnit::LITERS, $settings->getVolumeUnit());
    }

    #[Test]
    public function it_has_canonical_temperature_unit_as_default(): void
    {
        $settings = app(UnitsSettings::class);

        $this->assertEquals(TemperatureUnit::canonical()->value, $settings->temperatureUnit);
        $this->assertEquals(TemperatureUnit::CELSIUS, $settings->getTemperatureUnit());
    }

    #[Test]
    public function it_can_update_distance_unit(): void
    {
        $settings = app(UnitsSettings::class);
        $settings->distanceUnit = DistanceUnit::KILOMETERS->value;
        $settings->save();

        $freshSettings = app(UnitsSettings::class);
        $this->assertEquals(DistanceUnit::KILOMETERS, $freshSettings->getDistanceUnit());
    }

    #[Test]
    public function it_can_update_all_unit_settings(): void
    {
        $settings = app(UnitsSettings::class);
        $settings->distanceUnit = DistanceUnit::STATUTE_MILES->value;
        $settings->altitudeUnit = AltitudeUnit::METERS->value;
        $settings->heightUnit = HeightUnit::METERS->value;
        $settings->lengthUnit = LengthUnit::FEET->value;
        $settings->pressureUnit = PressureUnit::INCHES_OF_MERCURY->value;
        $settings->speedUnit = SpeedUnit::KILOMETERS_PER_HOUR->value;
        $settings->weightUnit = WeightUnit::POUNDS->value;
        $settings->fuelUnit = FuelUnit::GALLONS->value;
        $settings->volumeUnit = VolumeUnit::GALLONS->value;
        $settings->temperatureUnit = TemperatureUnit::FAHRENHEIT->value;
        $settings->save();

        $freshSettings = app(UnitsSettings::class);
        $this->assertEquals(DistanceUnit::STATUTE_MILES, $freshSettings->getDistanceUnit());
        $this->assertEquals(AltitudeUnit::METERS, $freshSettings->getAltitudeUnit());
        $this->assertEquals(HeightUnit::METERS, $freshSettings->getHeightUnit());
        $this->assertEquals(LengthUnit::FEET, $freshSettings->getLengthUnit());
        $this->assertEquals(PressureUnit::INCHES_OF_MERCURY, $freshSettings->getPressureUnit());
        $this->assertEquals(SpeedUnit::KILOMETERS_PER_HOUR, $freshSettings->getSpeedUnit());
        $this->assertEquals(WeightUnit::POUNDS, $freshSettings->getWeightUnit());
        $this->assertEquals(FuelUnit::GALLONS, $freshSettings->getFuelUnit());
        $this->assertEquals(VolumeUnit::GALLONS, $freshSettings->getVolumeUnit());
        $this->assertEquals(TemperatureUnit::FAHRENHEIT, $freshSettings->getTemperatureUnit());
    }
}
