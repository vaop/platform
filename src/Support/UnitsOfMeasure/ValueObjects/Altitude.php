<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\ValueObjects;

use Support\Contracts\ValueObject;
use Support\Exceptions\InvalidValueException;
use Support\UnitsOfMeasure\Enums\AltitudeUnit;
use Support\UnitsOfMeasure\Quantities\Length;

/**
 * Immutable value object representing an altitude measurement.
 *
 * Altitudes are stored internally in feet (canonical unit).
 * Provides conversion methods for display in user-preferred units.
 * Note: Altitude can be negative (below sea level).
 */
final readonly class Altitude implements ValueObject
{
    private function __construct(
        private Length $length,
    ) {}

    /**
     * Create an Altitude from feet.
     */
    public static function fromFeet(float $value): self
    {
        return new self(Length::fromFeet($value));
    }

    /**
     * Create an Altitude from meters.
     */
    public static function fromMeters(float $value): self
    {
        return new self(Length::fromAltitude($value, AltitudeUnit::METERS));
    }

    /**
     * Create an Altitude from a value and unit.
     */
    public static function from(float $value, AltitudeUnit $unit): self
    {
        return new self(Length::fromAltitude($value, $unit));
    }

    /**
     * Create an Altitude from a flight level (e.g., FL350 = 35000 feet).
     */
    public static function fromFlightLevel(int $flightLevel): self
    {
        if ($flightLevel < 0) {
            throw InvalidValueException::cannotBeNegative('Flight level', $flightLevel);
        }

        return self::fromFeet($flightLevel * 100);
    }

    /**
     * Create a sea level altitude (0 feet).
     */
    public static function seaLevel(): self
    {
        return new self(Length::fromFeet(0));
    }

    /**
     * Get the altitude in feet (canonical unit).
     */
    public function toFeet(): float
    {
        return $this->length->toFeet();
    }

    /**
     * Get the altitude in meters.
     */
    public function toMeters(): float
    {
        return $this->length->toMeters();
    }

    /**
     * Get the altitude as a flight level.
     */
    public function toFlightLevel(): int
    {
        return (int) round($this->length->toFlightLevel());
    }

    /**
     * Get the altitude in the user's preferred unit.
     */
    public function toPreferredUnit(): float
    {
        return $this->length->toPreferredAltitudeUnit();
    }

    /**
     * Add another altitude, returning a new Altitude.
     */
    public function add(self $other): self
    {
        return self::fromFeet($this->toFeet() + $other->toFeet());
    }

    /**
     * Subtract another altitude, returning a new Altitude.
     */
    public function subtract(self $other): self
    {
        return self::fromFeet($this->toFeet() - $other->toFeet());
    }

    /**
     * Check if this altitude is at or above another.
     */
    public function isAtOrAbove(self $other): bool
    {
        return $this->toFeet() >= $other->toFeet();
    }

    /**
     * Check if this altitude is above another.
     */
    public function isAbove(self $other): bool
    {
        return $this->toFeet() > $other->toFeet();
    }

    /**
     * Check if this altitude is below another.
     */
    public function isBelow(self $other): bool
    {
        return $this->toFeet() < $other->toFeet();
    }

    /**
     * Check if this altitude is at sea level.
     */
    public function isSeaLevel(): bool
    {
        return abs($this->toFeet()) < 1;
    }

    public function equals(ValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return abs($this->toFeet() - $other->toFeet()) < 1;
    }

    public function __toString(): string
    {
        return sprintf('%.0f ft', $this->toFeet());
    }
}
