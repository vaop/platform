<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\ValueObjects;

use Support\Contracts\ValueObject;
use Support\Exceptions\InvalidValueException;
use Support\UnitsOfMeasure\Enums\SpeedUnit;
use Support\UnitsOfMeasure\Quantities\Velocity;

/**
 * Immutable value object representing a speed measurement.
 *
 * Speeds are stored internally in knots (canonical unit).
 * Provides conversion methods for display in user-preferred units.
 */
final readonly class Speed implements ValueObject
{
    private function __construct(
        private Velocity $velocity,
    ) {}

    /**
     * Create a Speed from knots.
     */
    public static function fromKnots(float $value): self
    {
        self::validateNonNegative($value, 'Speed');

        return new self(Velocity::fromKnots($value));
    }

    /**
     * Create a Speed from kilometers per hour.
     */
    public static function fromKilometersPerHour(float $value): self
    {
        self::validateNonNegative($value, 'Speed');

        return new self(Velocity::fromSpeed($value, SpeedUnit::KILOMETERS_PER_HOUR));
    }

    /**
     * Create a Speed from miles per hour.
     */
    public static function fromMilesPerHour(float $value): self
    {
        self::validateNonNegative($value, 'Speed');

        return new self(Velocity::fromSpeed($value, SpeedUnit::MILES_PER_HOUR));
    }

    /**
     * Create a Speed from a value and unit.
     */
    public static function from(float $value, SpeedUnit $unit): self
    {
        self::validateNonNegative($value, 'Speed');

        return new self(Velocity::fromSpeed($value, $unit));
    }

    /**
     * Create a zero speed.
     */
    public static function zero(): self
    {
        return new self(Velocity::fromKnots(0));
    }

    /**
     * Get the speed in knots (canonical unit).
     */
    public function toKnots(): float
    {
        return $this->velocity->toKnots();
    }

    /**
     * Get the speed in kilometers per hour.
     */
    public function toKilometersPerHour(): float
    {
        return $this->velocity->toKilometersPerHour();
    }

    /**
     * Get the speed in miles per hour.
     */
    public function toMilesPerHour(): float
    {
        return $this->velocity->toMilesPerHour();
    }

    /**
     * Get the speed in the user's preferred unit.
     */
    public function toPreferredUnit(): float
    {
        return $this->velocity->toPreferredSpeedUnit();
    }

    /**
     * Add another speed, returning a new Speed.
     */
    public function add(self $other): self
    {
        return self::fromKnots($this->toKnots() + $other->toKnots());
    }

    /**
     * Subtract another speed, returning a new Speed.
     *
     * @throws InvalidValueException If the result would be negative
     */
    public function subtract(self $other): self
    {
        $result = $this->toKnots() - $other->toKnots();

        if ($result < 0) {
            throw InvalidValueException::cannotBeNegative('Speed', $result);
        }

        return self::fromKnots($result);
    }

    /**
     * Check if this speed is zero.
     */
    public function isZero(): bool
    {
        return abs($this->toKnots()) < 0.01;
    }

    /**
     * Check if this speed is greater than another.
     */
    public function isGreaterThan(self $other): bool
    {
        return $this->toKnots() > $other->toKnots();
    }

    /**
     * Check if this speed is less than another.
     */
    public function isLessThan(self $other): bool
    {
        return $this->toKnots() < $other->toKnots();
    }

    public function equals(ValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return abs($this->toKnots() - $other->toKnots()) < 0.01;
    }

    public function __toString(): string
    {
        return sprintf('%.0f kts', $this->toKnots());
    }

    private static function validateNonNegative(float $value, string $field): void
    {
        if ($value < 0) {
            throw InvalidValueException::cannotBeNegative($field, $value);
        }
    }
}
