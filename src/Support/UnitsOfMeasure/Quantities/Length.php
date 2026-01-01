<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Quantities;

use PhpUnitsOfMeasure\PhysicalQuantity\Length as BaseLength;
use Support\UnitsOfMeasure\Enums\AltitudeUnit;
use Support\UnitsOfMeasure\Enums\DistanceUnit;
use Support\UnitsOfMeasure\Enums\HeightUnit;
use Support\UnitsOfMeasure\Enums\LengthUnit;
use Support\UnitsOfMeasure\Traits\HasUserPreferences;

/**
 * Represents length measurements with user preference awareness.
 *
 * Extends the PhpUnitsOfMeasure Length class to provide context-specific
 * conversion methods that respect user preferences for different contexts
 * (distance, altitude). This class serves as the base for all length-based
 * measurements across the platform.
 */
class Length extends BaseLength
{
    use HasUserPreferences;

    /**
     * Creates a new Length measurement from a value and unit enum.
     */
    public static function fromDistance(float $value, DistanceUnit $unit): self
    {
        return new self($value, $unit->value);
    }

    /**
     * Creates a new Length measurement from altitude value and unit enum.
     */
    public static function fromAltitude(float $value, AltitudeUnit $unit): self
    {
        return new self($value, $unit->value);
    }

    /**
     * Creates a Length from nautical miles (canonical distance unit).
     */
    public static function fromNauticalMiles(float $value): self
    {
        return new self($value, DistanceUnit::canonical()->value);
    }

    /**
     * Creates a Length from feet (canonical altitude unit).
     */
    public static function fromFeet(float $value): self
    {
        return new self($value, AltitudeUnit::canonical()->value);
    }

    /**
     * Creates a new Length measurement from height value and unit enum.
     */
    public static function fromHeight(float $value, HeightUnit $unit): self
    {
        return new self($value, $unit->value);
    }

    /**
     * Creates a new Length measurement from length value and unit enum.
     */
    public static function fromLength(float $value, LengthUnit $unit): self
    {
        return new self($value, $unit->value);
    }

    /**
     * Creates a Length from meters (canonical length unit).
     */
    public static function fromMeters(float $value): self
    {
        return new self($value, LengthUnit::canonical()->value);
    }

    /**
     * Converts to the user's preferred distance unit.
     */
    public function toPreferredDistanceUnit(): float
    {
        return $this->toUnit($this->getPreferredDistanceUnit()->value);
    }

    /**
     * Converts to the user's preferred altitude unit.
     */
    public function toPreferredAltitudeUnit(): float
    {
        return $this->toUnit($this->getPreferredAltitudeUnit()->value);
    }

    /**
     * Converts to the user's preferred height unit.
     */
    public function toPreferredHeightUnit(): float
    {
        return $this->toUnit($this->getPreferredHeightUnit()->value);
    }

    /**
     * Converts to the user's preferred length unit.
     */
    public function toPreferredLengthUnit(): float
    {
        return $this->toUnit($this->getPreferredLengthUnit()->value);
    }

    /**
     * Converts to nautical miles (canonical distance unit).
     */
    public function toNauticalMiles(): float
    {
        return $this->toUnit(DistanceUnit::canonical()->value);
    }

    /**
     * Converts to feet (canonical altitude unit).
     */
    public function toFeet(): float
    {
        return $this->toUnit(AltitudeUnit::canonical()->value);
    }

    /**
     * Converts to meters.
     */
    public function toMeters(): float
    {
        return $this->toUnit(AltitudeUnit::METERS->value);
    }

    /**
     * Converts to kilometers.
     */
    public function toKilometers(): float
    {
        return $this->toUnit(DistanceUnit::KILOMETERS->value);
    }

    /**
     * Converts altitude to flight level (hundreds of feet).
     */
    public function toFlightLevel(): float
    {
        return $this->toFeet() / 100;
    }
}
