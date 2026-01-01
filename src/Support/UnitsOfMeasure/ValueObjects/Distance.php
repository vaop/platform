<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\ValueObjects;

use Support\Contracts\ValueObject;
use Support\Exceptions\InvalidValueException;
use Support\UnitsOfMeasure\Enums\DistanceUnit;
use Support\UnitsOfMeasure\Quantities\Length;

/**
 * Immutable value object representing a distance measurement.
 *
 * Distances are stored internally in nautical miles (canonical unit).
 * Provides conversion methods for display in user-preferred units.
 */
final readonly class Distance implements ValueObject
{
    private function __construct(
        private Length $length,
    ) {}

    /**
     * Create a Distance from nautical miles.
     */
    public static function fromNauticalMiles(float $value): self
    {
        self::validateNonNegative($value, 'Distance');

        return new self(Length::fromNauticalMiles($value));
    }

    /**
     * Create a Distance from kilometers.
     */
    public static function fromKilometers(float $value): self
    {
        self::validateNonNegative($value, 'Distance');

        return new self(Length::fromDistance($value, DistanceUnit::KILOMETERS));
    }

    /**
     * Create a Distance from statute miles.
     */
    public static function fromStatuteMiles(float $value): self
    {
        self::validateNonNegative($value, 'Distance');

        return new self(Length::fromDistance($value, DistanceUnit::STATUTE_MILES));
    }

    /**
     * Create a Distance from a value and unit.
     */
    public static function from(float $value, DistanceUnit $unit): self
    {
        self::validateNonNegative($value, 'Distance');

        return new self(Length::fromDistance($value, $unit));
    }

    /**
     * Create a zero distance.
     */
    public static function zero(): self
    {
        return new self(Length::fromNauticalMiles(0));
    }

    /**
     * Get the distance in nautical miles (canonical unit).
     */
    public function toNauticalMiles(): float
    {
        return $this->length->toNauticalMiles();
    }

    /**
     * Get the distance in kilometers.
     */
    public function toKilometers(): float
    {
        return $this->length->toKilometers();
    }

    /**
     * Get the distance in statute miles.
     */
    public function toStatuteMiles(): float
    {
        return $this->length->toUnit(DistanceUnit::STATUTE_MILES->getUnitName());
    }

    /**
     * Get the distance in the user's preferred unit.
     */
    public function toPreferredUnit(): float
    {
        return $this->length->toPreferredDistanceUnit();
    }

    /**
     * Add another distance, returning a new Distance.
     */
    public function add(self $other): self
    {
        return self::fromNauticalMiles(
            $this->toNauticalMiles() + $other->toNauticalMiles()
        );
    }

    /**
     * Subtract another distance, returning a new Distance.
     *
     * @throws InvalidValueException If the result would be negative
     */
    public function subtract(self $other): self
    {
        $result = $this->toNauticalMiles() - $other->toNauticalMiles();

        if ($result < 0) {
            throw InvalidValueException::cannotBeNegative('Distance', $result);
        }

        return self::fromNauticalMiles($result);
    }

    /**
     * Check if this distance is zero.
     */
    public function isZero(): bool
    {
        return abs($this->toNauticalMiles()) < 0.0001;
    }

    /**
     * Check if this distance is greater than another.
     */
    public function isGreaterThan(self $other): bool
    {
        return $this->toNauticalMiles() > $other->toNauticalMiles();
    }

    /**
     * Check if this distance is less than another.
     */
    public function isLessThan(self $other): bool
    {
        return $this->toNauticalMiles() < $other->toNauticalMiles();
    }

    public function equals(ValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return abs($this->toNauticalMiles() - $other->toNauticalMiles()) < 0.0001;
    }

    public function __toString(): string
    {
        return sprintf('%.2f nm', $this->toNauticalMiles());
    }

    private static function validateNonNegative(float $value, string $field): void
    {
        if ($value < 0) {
            throw InvalidValueException::cannotBeNegative($field, $value);
        }
    }
}
