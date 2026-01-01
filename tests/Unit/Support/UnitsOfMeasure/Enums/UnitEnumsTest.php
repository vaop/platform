<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\Enums;

use PHPUnit\Framework\Attributes\DataProvider;
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
use Tests\TestCase;

class UnitEnumsTest extends TestCase
{
    #[Test]
    public function distance_unit_has_expected_cases(): void
    {
        $this->assertCount(3, DistanceUnit::cases());
        $this->assertSame('nmi', DistanceUnit::NAUTICAL_MILES->value);
        $this->assertSame('km', DistanceUnit::KILOMETERS->value);
        $this->assertSame('mi', DistanceUnit::STATUTE_MILES->value);
    }

    #[Test]
    public function distance_unit_has_labels_and_acronyms(): void
    {
        $this->assertSame('Nautical Miles', DistanceUnit::NAUTICAL_MILES->getLabel());
        $this->assertSame('nm', DistanceUnit::NAUTICAL_MILES->getAcronym());
    }

    #[Test]
    public function distance_unit_canonical_is_nautical_miles(): void
    {
        $this->assertSame(DistanceUnit::NAUTICAL_MILES, DistanceUnit::canonical());
    }

    #[Test]
    public function altitude_unit_has_expected_cases(): void
    {
        $this->assertCount(2, AltitudeUnit::cases());
        $this->assertSame('ft', AltitudeUnit::FEET->value);
        $this->assertSame('m', AltitudeUnit::METERS->value);
    }

    #[Test]
    public function altitude_unit_canonical_is_feet(): void
    {
        $this->assertSame(AltitudeUnit::FEET, AltitudeUnit::canonical());
    }

    #[Test]
    public function height_unit_has_expected_cases(): void
    {
        $this->assertCount(2, HeightUnit::cases());
        $this->assertSame('ft', HeightUnit::FEET->value);
        $this->assertSame('m', HeightUnit::METERS->value);
    }

    #[Test]
    public function height_unit_canonical_is_feet(): void
    {
        $this->assertSame(HeightUnit::FEET, HeightUnit::canonical());
    }

    #[Test]
    public function length_unit_has_expected_cases(): void
    {
        $this->assertCount(3, LengthUnit::cases());
        $this->assertSame('m', LengthUnit::METERS->value);
        $this->assertSame('ft', LengthUnit::FEET->value);
        $this->assertSame('in', LengthUnit::INCHES->value);
    }

    #[Test]
    public function length_unit_canonical_is_meters(): void
    {
        $this->assertSame(LengthUnit::METERS, LengthUnit::canonical());
    }

    #[Test]
    public function pressure_unit_has_expected_cases(): void
    {
        $this->assertCount(3, PressureUnit::cases());
        $this->assertSame('hPa', PressureUnit::HECTOPASCALS->value);
        $this->assertSame('inHg', PressureUnit::INCHES_OF_MERCURY->value);
        $this->assertSame('mbar', PressureUnit::MILLIBARS->value);
    }

    #[Test]
    public function pressure_unit_canonical_is_hectopascals(): void
    {
        $this->assertSame(PressureUnit::HECTOPASCALS, PressureUnit::canonical());
    }

    #[Test]
    public function speed_unit_has_expected_cases(): void
    {
        $this->assertCount(3, SpeedUnit::cases());
        $this->assertSame('knots', SpeedUnit::KNOTS->value);
        $this->assertSame('km/h', SpeedUnit::KILOMETERS_PER_HOUR->value);
        $this->assertSame('mph', SpeedUnit::MILES_PER_HOUR->value);
    }

    #[Test]
    public function speed_unit_canonical_is_knots(): void
    {
        $this->assertSame(SpeedUnit::KNOTS, SpeedUnit::canonical());
    }

    #[Test]
    public function weight_unit_has_expected_cases(): void
    {
        $this->assertCount(2, WeightUnit::cases());
        $this->assertSame('kg', WeightUnit::KILOGRAMS->value);
        $this->assertSame('lbs', WeightUnit::POUNDS->value);
    }

    #[Test]
    public function weight_unit_canonical_is_kilograms(): void
    {
        $this->assertSame(WeightUnit::KILOGRAMS, WeightUnit::canonical());
    }

    #[Test]
    public function fuel_unit_has_expected_cases(): void
    {
        $this->assertCount(4, FuelUnit::cases());
        $this->assertSame('kg', FuelUnit::KILOGRAMS->value);
        $this->assertSame('lbs', FuelUnit::POUNDS->value);
        $this->assertSame('l', FuelUnit::LITERS->value);
        $this->assertSame('gal', FuelUnit::GALLONS->value);
    }

    #[Test]
    public function fuel_unit_identifies_weight_vs_volume(): void
    {
        $this->assertTrue(FuelUnit::KILOGRAMS->isWeight());
        $this->assertTrue(FuelUnit::POUNDS->isWeight());
        $this->assertFalse(FuelUnit::LITERS->isWeight());
        $this->assertFalse(FuelUnit::GALLONS->isWeight());

        $this->assertFalse(FuelUnit::KILOGRAMS->isVolume());
        $this->assertTrue(FuelUnit::LITERS->isVolume());
    }

    #[Test]
    public function fuel_unit_canonical_is_kilograms(): void
    {
        $this->assertSame(FuelUnit::KILOGRAMS, FuelUnit::canonical());
    }

    #[Test]
    public function volume_unit_has_expected_cases(): void
    {
        $this->assertCount(3, VolumeUnit::cases());
        $this->assertSame('l', VolumeUnit::LITERS->value);
        $this->assertSame('gal', VolumeUnit::GALLONS->value);
        $this->assertSame('m^3', VolumeUnit::CUBIC_METERS->value);
    }

    #[Test]
    public function volume_unit_canonical_is_liters(): void
    {
        $this->assertSame(VolumeUnit::LITERS, VolumeUnit::canonical());
    }

    #[Test]
    public function temperature_unit_has_expected_cases(): void
    {
        $this->assertCount(2, TemperatureUnit::cases());
        $this->assertSame('C', TemperatureUnit::CELSIUS->value);
        $this->assertSame('F', TemperatureUnit::FAHRENHEIT->value);
    }

    #[Test]
    public function temperature_unit_canonical_is_celsius(): void
    {
        $this->assertSame(TemperatureUnit::CELSIUS, TemperatureUnit::canonical());
    }

    #[Test]
    #[DataProvider('allEnumsProvider')]
    public function all_enums_have_options_method(string $enumClass): void
    {
        $options = $enumClass::options();

        $this->assertIsArray($options);
        $this->assertNotEmpty($options);

        foreach ($enumClass::cases() as $case) {
            $this->assertArrayHasKey($case->value, $options);
            $this->assertSame($case->getLabel(), $options[$case->value]);
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function allEnumsProvider(): array
    {
        return [
            'DistanceUnit' => [DistanceUnit::class],
            'AltitudeUnit' => [AltitudeUnit::class],
            'HeightUnit' => [HeightUnit::class],
            'LengthUnit' => [LengthUnit::class],
            'PressureUnit' => [PressureUnit::class],
            'SpeedUnit' => [SpeedUnit::class],
            'WeightUnit' => [WeightUnit::class],
            'FuelUnit' => [FuelUnit::class],
            'VolumeUnit' => [VolumeUnit::class],
            'TemperatureUnit' => [TemperatureUnit::class],
        ];
    }
}
