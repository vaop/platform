<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Quantities;

use PhpUnitsOfMeasure\PhysicalQuantity\Velocity as BaseVelocity;
use PhpUnitsOfMeasure\UnitOfMeasure;
use Support\UnitsOfMeasure\Enums\SpeedUnit;
use Support\UnitsOfMeasure\Traits\HasUserPreferences;

/**
 * Represents speed and velocity measurements with user preference awareness.
 *
 * Extends the PhpUnitsOfMeasure Velocity class to provide user preference-aware
 * conversions between different velocity units (m/s, km/h, mph, knots, fpm).
 * Essential for aircraft speed indications, wind reporting, and vertical speed
 * display throughout the platform.
 */
class Velocity extends BaseVelocity
{
    use HasUserPreferences;

    /**
     * Initializes the Velocity class with aviation-specific velocity units.
     */
    protected static function initialize(): void
    {
        parent::initialize();

        // feet per minute - essential for vertical speed indication
        $fpm = UnitOfMeasure::linearUnitFactory('ft/min', 0.00508);
        $fpm->addAlias('fpm');
        static::addUnit($fpm);
    }

    /**
     * Creates a new Velocity measurement from a value and unit enum.
     */
    public static function fromSpeed(float $value, SpeedUnit $unit): self
    {
        return new self($value, $unit->getUnitName());
    }

    /**
     * Creates a Velocity from knots (canonical speed unit).
     */
    public static function fromKnots(float $value): self
    {
        return new self($value, SpeedUnit::canonical()->getUnitName());
    }

    /**
     * Creates a Velocity from feet per minute (vertical speed).
     */
    public static function fromFeetPerMinute(float $value): self
    {
        return new self($value, 'ft/min');
    }

    /**
     * Converts to the user's preferred speed unit.
     */
    public function toPreferredSpeedUnit(): float
    {
        return $this->toUnit($this->getPreferredSpeedUnit()->getUnitName());
    }

    /**
     * Converts to knots (canonical speed unit).
     */
    public function toKnots(): float
    {
        return $this->toUnit(SpeedUnit::canonical()->getUnitName());
    }

    /**
     * Converts to kilometers per hour.
     */
    public function toKilometersPerHour(): float
    {
        return $this->toUnit(SpeedUnit::KILOMETERS_PER_HOUR->getUnitName());
    }

    /**
     * Converts to miles per hour.
     */
    public function toMilesPerHour(): float
    {
        return $this->toUnit(SpeedUnit::MILES_PER_HOUR->getUnitName());
    }

    /**
     * Converts to feet per minute (for vertical speed).
     */
    public function toFeetPerMinute(): float
    {
        return $this->toUnit('ft/min');
    }

    /**
     * Converts to scaled vertical speed (hundreds of feet per minute).
     */
    public function toVerticalSpeedUnit(): float
    {
        return $this->toFeetPerMinute() / 100;
    }
}
