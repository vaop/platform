<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Quantities;

use PhpUnitsOfMeasure\PhysicalQuantity\Volume as BaseVolume;
use Support\UnitsOfMeasure\Enums\FuelUnit;
use Support\UnitsOfMeasure\Enums\VolumeUnit;
use Support\UnitsOfMeasure\Traits\HasUserPreferences;

/**
 * Represents volume measurements with user preference awareness.
 *
 * Extends the PhpUnitsOfMeasure Volume class to provide user preference-aware
 * conversions between different volume units. Used primarily for fuel volume
 * calculations when the user prefers volume-based fuel units (liters, gallons).
 */
class Volume extends BaseVolume
{
    use HasUserPreferences;

    /**
     * Creates a new Volume measurement from a value and VolumeUnit enum.
     */
    public static function fromVolume(float $value, VolumeUnit $unit): self
    {
        return new self($value, $unit->value);
    }

    /**
     * Creates a new Volume measurement from fuel value and unit enum.
     * Note: Only valid for volume-based fuel units (liters, gallons).
     */
    public static function fromFuel(float $value, FuelUnit $unit): self
    {
        return new self($value, $unit->value);
    }

    /**
     * Creates a Volume from liters.
     */
    public static function fromLiters(float $value): self
    {
        return new self($value, FuelUnit::LITERS->value);
    }

    /**
     * Creates a Volume from gallons.
     */
    public static function fromGallons(float $value): self
    {
        return new self($value, FuelUnit::GALLONS->value);
    }

    /**
     * Converts to the user's preferred volume unit.
     */
    public function toPreferredVolumeUnit(): float
    {
        return $this->toUnit($this->getPreferredVolumeUnit()->value);
    }

    /**
     * Converts to the user's preferred fuel unit (volume-based only).
     */
    public function toPreferredFuelUnit(): float
    {
        $preferredUnit = $this->getPreferredFuelUnit();

        // Only convert if it's a volume-based fuel unit
        if (in_array($preferredUnit, [FuelUnit::LITERS, FuelUnit::GALLONS], true)) {
            return $this->toUnit($preferredUnit->value);
        }

        // Fall back to liters for mass-based preferences
        return $this->toLiters();
    }

    /**
     * Converts to liters.
     */
    public function toLiters(): float
    {
        return $this->toUnit(FuelUnit::LITERS->value);
    }

    /**
     * Converts to gallons.
     */
    public function toGallons(): float
    {
        return $this->toUnit(FuelUnit::GALLONS->value);
    }
}
