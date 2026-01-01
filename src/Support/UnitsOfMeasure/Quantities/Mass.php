<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Quantities;

use PhpUnitsOfMeasure\PhysicalQuantity\Mass as BaseMass;
use Support\UnitsOfMeasure\Enums\FuelUnit;
use Support\UnitsOfMeasure\Enums\WeightUnit;
use Support\UnitsOfMeasure\Traits\HasUserPreferences;

/**
 * Represents mass/weight measurements with user preference awareness.
 *
 * Extends the PhpUnitsOfMeasure Mass class to provide user preference-aware
 * conversions between different mass units. Critical for aircraft weight
 * calculations, payload management, fuel planning, and weight and balance
 * computations.
 */
class Mass extends BaseMass
{
    use HasUserPreferences;

    /**
     * Creates a new Mass measurement from weight value and unit enum.
     */
    public static function fromWeight(float $value, WeightUnit $unit): self
    {
        return new self($value, $unit->getUnitName());
    }

    /**
     * Creates a new Mass measurement from fuel value and unit enum.
     * Note: Fuel volume units (liters, gallons) are handled by the Volume class.
     */
    public static function fromFuel(float $value, FuelUnit $unit): self
    {
        return new self($value, $unit->getUnitName());
    }

    /**
     * Creates a Mass from kilograms (canonical weight unit).
     */
    public static function fromKilograms(float $value): self
    {
        return new self($value, WeightUnit::canonical()->getUnitName());
    }

    /**
     * Creates a Mass from pounds.
     */
    public static function fromPounds(float $value): self
    {
        return new self($value, WeightUnit::POUNDS->getUnitName());
    }

    /**
     * Converts to the user's preferred weight unit.
     */
    public function toPreferredWeightUnit(): float
    {
        return $this->toUnit($this->getPreferredWeightUnit()->getUnitName());
    }

    /**
     * Converts to the user's preferred fuel unit (mass-based only).
     */
    public function toPreferredFuelUnit(): float
    {
        $preferredUnit = $this->getPreferredFuelUnit();

        // Only convert if it's a mass-based fuel unit
        if (in_array($preferredUnit, [FuelUnit::KILOGRAMS, FuelUnit::POUNDS], true)) {
            return $this->toUnit($preferredUnit->getUnitName());
        }

        // Fall back to canonical mass unit for volume-based preferences
        return $this->toKilograms();
    }

    /**
     * Converts to kilograms (canonical weight unit).
     */
    public function toKilograms(): float
    {
        return $this->toUnit(WeightUnit::canonical()->getUnitName());
    }

    /**
     * Converts to pounds.
     */
    public function toPounds(): float
    {
        return $this->toUnit(WeightUnit::POUNDS->getUnitName());
    }
}
