<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Quantities;

use PhpUnitsOfMeasure\PhysicalQuantity\Pressure as BasePressure;
use Support\UnitsOfMeasure\Enums\PressureUnit;
use Support\UnitsOfMeasure\Traits\HasUserPreferences;

/**
 * Represents atmospheric pressure measurements with user preference awareness.
 *
 * Extends the PhpUnitsOfMeasure Pressure class to provide user preference-aware
 * conversions between different pressure units. Critical for altimeter settings,
 * weather reporting (QNH, QFE), and atmospheric conditions throughout the platform.
 */
class Pressure extends BasePressure
{
    use HasUserPreferences;

    /**
     * Creates a new Pressure measurement from a value and unit enum.
     */
    public static function fromPressure(float $value, PressureUnit $unit): self
    {
        return new self($value, $unit->getUnitName());
    }

    /**
     * Creates a Pressure from hectopascals (canonical pressure unit).
     */
    public static function fromHectopascals(float $value): self
    {
        return new self($value, PressureUnit::canonical()->getUnitName());
    }

    /**
     * Creates a Pressure from inches of mercury.
     */
    public static function fromInchesOfMercury(float $value): self
    {
        return new self($value, PressureUnit::INCHES_OF_MERCURY->getUnitName());
    }

    /**
     * Converts to the user's preferred pressure unit.
     */
    public function toPreferredPressureUnit(): float
    {
        return $this->toUnit($this->getPreferredPressureUnit()->getUnitName());
    }

    /**
     * Converts to hectopascals (canonical pressure unit).
     */
    public function toHectopascals(): float
    {
        return $this->toUnit(PressureUnit::canonical()->getUnitName());
    }

    /**
     * Converts to inches of mercury.
     */
    public function toInchesOfMercury(): float
    {
        return $this->toUnit(PressureUnit::INCHES_OF_MERCURY->getUnitName());
    }

    /**
     * Converts to millibars (equivalent to hectopascals).
     */
    public function toMillibars(): float
    {
        return $this->toUnit(PressureUnit::MILLIBARS->getUnitName());
    }
}
