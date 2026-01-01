<?php

declare(strict_types=1);

namespace System\Settings;

use Spatie\LaravelSettings\Settings;
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

/**
 * Airline-wide default unit preferences.
 *
 * These defaults are used when a user hasn't set their own preferences.
 * Users can override these in their profile settings if allowUserCustomization is true.
 */
class UnitsSettings extends Settings
{
    /**
     * Whether users can customize their own unit preferences.
     * When false, all users see airline defaults regardless of their personal settings.
     */
    public bool $allowUserCustomization;

    public int $distanceUnit;

    public int $altitudeUnit;

    public int $heightUnit;

    public int $lengthUnit;

    public int $pressureUnit;

    public int $speedUnit;

    public int $weightUnit;

    public int $fuelUnit;

    public int $volumeUnit;

    public int $temperatureUnit;

    public static function group(): string
    {
        return 'units';
    }

    /**
     * Check if user customization is allowed.
     */
    public function allowsUserCustomization(): bool
    {
        return $this->allowUserCustomization;
    }

    /**
     * Get the distance unit enum.
     */
    public function getDistanceUnit(): DistanceUnit
    {
        return DistanceUnit::from($this->distanceUnit);
    }

    /**
     * Get the altitude unit enum.
     */
    public function getAltitudeUnit(): AltitudeUnit
    {
        return AltitudeUnit::from($this->altitudeUnit);
    }

    /**
     * Get the height unit enum.
     */
    public function getHeightUnit(): HeightUnit
    {
        return HeightUnit::from($this->heightUnit);
    }

    /**
     * Get the length unit enum.
     */
    public function getLengthUnit(): LengthUnit
    {
        return LengthUnit::from($this->lengthUnit);
    }

    /**
     * Get the pressure unit enum.
     */
    public function getPressureUnit(): PressureUnit
    {
        return PressureUnit::from($this->pressureUnit);
    }

    /**
     * Get the speed unit enum.
     */
    public function getSpeedUnit(): SpeedUnit
    {
        return SpeedUnit::from($this->speedUnit);
    }

    /**
     * Get the weight unit enum.
     */
    public function getWeightUnit(): WeightUnit
    {
        return WeightUnit::from($this->weightUnit);
    }

    /**
     * Get the fuel unit enum.
     */
    public function getFuelUnit(): FuelUnit
    {
        return FuelUnit::from($this->fuelUnit);
    }

    /**
     * Get the volume unit enum.
     */
    public function getVolumeUnit(): VolumeUnit
    {
        return VolumeUnit::from($this->volumeUnit);
    }

    /**
     * Get the temperature unit enum.
     */
    public function getTemperatureUnit(): TemperatureUnit
    {
        return TemperatureUnit::from($this->temperatureUnit);
    }
}
