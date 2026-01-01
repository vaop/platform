<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Quantities;

use PhpUnitsOfMeasure\PhysicalQuantity\Temperature as BaseTemperature;
use Support\UnitsOfMeasure\Enums\TemperatureUnit;
use Support\UnitsOfMeasure\Traits\HasUserPreferences;

/**
 * Represents temperature measurements with user preference awareness.
 *
 * Extends the PhpUnitsOfMeasure Temperature class to provide user preference-aware
 * conversions between different temperature scales. Critical for weather reporting,
 * aircraft environmental systems, and atmospheric conditions throughout the platform.
 */
class Temperature extends BaseTemperature
{
    use HasUserPreferences;

    /**
     * Creates a new Temperature measurement from a value and unit enum.
     */
    public static function fromTemperature(float $value, TemperatureUnit $unit): self
    {
        return new self($value, $unit->value);
    }

    /**
     * Creates a Temperature from Celsius (canonical temperature unit).
     */
    public static function fromCelsius(float $value): self
    {
        return new self($value, TemperatureUnit::canonical()->value);
    }

    /**
     * Creates a Temperature from Fahrenheit.
     */
    public static function fromFahrenheit(float $value): self
    {
        return new self($value, TemperatureUnit::FAHRENHEIT->value);
    }

    /**
     * Converts to the user's preferred temperature unit.
     */
    public function toPreferredTemperatureUnit(): float
    {
        return $this->toUnit($this->getPreferredTemperatureUnit()->value);
    }

    /**
     * Converts to Celsius (canonical temperature unit).
     */
    public function toCelsius(): float
    {
        return $this->toUnit(TemperatureUnit::canonical()->value);
    }

    /**
     * Converts to Fahrenheit.
     */
    public function toFahrenheit(): float
    {
        return $this->toUnit(TemperatureUnit::FAHRENHEIT->value);
    }

    /**
     * Converts to Kelvin.
     */
    public function toKelvin(): float
    {
        return $this->toUnit('K');
    }
}
