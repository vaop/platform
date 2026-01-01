<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Traits;

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

/**
 * Provides user preference awareness to physical quantity classes.
 *
 * This trait enables any physical quantity class to respect the preference hierarchy:
 * 1. Authenticated user's preference (if set AND user customization is allowed)
 * 2. Airline-wide default from UnitsSettings
 *
 * The trait centralizes preference resolution to ensure consistent unit
 * presentation throughout the application.
 */
trait HasUserPreferences
{
    /**
     * Check if user customization is allowed by the airline settings.
     */
    protected function isUserCustomizationAllowed(): bool
    {
        return app(UnitsSettings::class)->allowsUserCustomization();
    }

    /**
     * Get the user's preferred distance unit.
     */
    protected function getPreferredDistanceUnit(): DistanceUnit
    {
        if ($this->isUserCustomizationAllowed() && auth()->user()?->distance_unit) {
            return auth()->user()->distance_unit;
        }

        return app(UnitsSettings::class)->getDistanceUnit();
    }

    /**
     * Get the user's preferred altitude unit.
     */
    protected function getPreferredAltitudeUnit(): AltitudeUnit
    {
        if ($this->isUserCustomizationAllowed() && auth()->user()?->altitude_unit) {
            return auth()->user()->altitude_unit;
        }

        return app(UnitsSettings::class)->getAltitudeUnit();
    }

    /**
     * Get the user's preferred height unit.
     */
    protected function getPreferredHeightUnit(): HeightUnit
    {
        if ($this->isUserCustomizationAllowed() && auth()->user()?->height_unit) {
            return auth()->user()->height_unit;
        }

        return app(UnitsSettings::class)->getHeightUnit();
    }

    /**
     * Get the user's preferred length unit.
     */
    protected function getPreferredLengthUnit(): LengthUnit
    {
        if ($this->isUserCustomizationAllowed() && auth()->user()?->length_unit) {
            return auth()->user()->length_unit;
        }

        return app(UnitsSettings::class)->getLengthUnit();
    }

    /**
     * Get the user's preferred pressure unit.
     */
    protected function getPreferredPressureUnit(): PressureUnit
    {
        if ($this->isUserCustomizationAllowed() && auth()->user()?->pressure_unit) {
            return auth()->user()->pressure_unit;
        }

        return app(UnitsSettings::class)->getPressureUnit();
    }

    /**
     * Get the user's preferred speed unit.
     */
    protected function getPreferredSpeedUnit(): SpeedUnit
    {
        if ($this->isUserCustomizationAllowed() && auth()->user()?->speed_unit) {
            return auth()->user()->speed_unit;
        }

        return app(UnitsSettings::class)->getSpeedUnit();
    }

    /**
     * Get the user's preferred weight unit.
     */
    protected function getPreferredWeightUnit(): WeightUnit
    {
        if ($this->isUserCustomizationAllowed() && auth()->user()?->weight_unit) {
            return auth()->user()->weight_unit;
        }

        return app(UnitsSettings::class)->getWeightUnit();
    }

    /**
     * Get the user's preferred fuel unit.
     */
    protected function getPreferredFuelUnit(): FuelUnit
    {
        if ($this->isUserCustomizationAllowed() && auth()->user()?->fuel_unit) {
            return auth()->user()->fuel_unit;
        }

        return app(UnitsSettings::class)->getFuelUnit();
    }

    /**
     * Get the user's preferred volume unit.
     */
    protected function getPreferredVolumeUnit(): VolumeUnit
    {
        if ($this->isUserCustomizationAllowed() && auth()->user()?->volume_unit) {
            return auth()->user()->volume_unit;
        }

        return app(UnitsSettings::class)->getVolumeUnit();
    }

    /**
     * Get the user's preferred temperature unit.
     */
    protected function getPreferredTemperatureUnit(): TemperatureUnit
    {
        if ($this->isUserCustomizationAllowed() && auth()->user()?->temperature_unit) {
            return auth()->user()->temperature_unit;
        }

        return app(UnitsSettings::class)->getTemperatureUnit();
    }
}
