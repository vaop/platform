<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\ValueObjects;

use Support\Contracts\ValueObject;
use Support\Exceptions\InvalidValueException;
use Support\UnitsOfMeasure\Enums\WeightUnit;
use Support\UnitsOfMeasure\Quantities\Mass;

/**
 * Immutable value object representing a weight measurement.
 *
 * Weights are stored internally in kilograms (canonical unit).
 * Provides conversion methods for display in user-preferred units.
 */
final readonly class Weight implements ValueObject
{
    private function __construct(
        private Mass $mass,
    ) {}

    /**
     * Create a Weight from kilograms.
     */
    public static function fromKilograms(float $value): self
    {
        self::validateNonNegative($value, 'Weight');

        return new self(Mass::fromKilograms($value));
    }

    /**
     * Create a Weight from pounds.
     */
    public static function fromPounds(float $value): self
    {
        self::validateNonNegative($value, 'Weight');

        return new self(Mass::fromPounds($value));
    }

    /**
     * Create a Weight from a value and unit.
     */
    public static function from(float $value, WeightUnit $unit): self
    {
        self::validateNonNegative($value, 'Weight');

        return new self(Mass::fromWeight($value, $unit));
    }

    /**
     * Create a zero weight.
     */
    public static function zero(): self
    {
        return new self(Mass::fromKilograms(0));
    }

    /**
     * Get the weight in kilograms (canonical unit).
     */
    public function toKilograms(): float
    {
        return $this->mass->toKilograms();
    }

    /**
     * Get the weight in pounds.
     */
    public function toPounds(): float
    {
        return $this->mass->toPounds();
    }

    /**
     * Get the weight in the user's preferred unit.
     */
    public function toPreferredUnit(): float
    {
        return $this->mass->toPreferredWeightUnit();
    }

    /**
     * Add another weight, returning a new Weight.
     */
    public function add(self $other): self
    {
        return self::fromKilograms($this->toKilograms() + $other->toKilograms());
    }

    /**
     * Subtract another weight, returning a new Weight.
     *
     * @throws InvalidValueException If the result would be negative
     */
    public function subtract(self $other): self
    {
        $result = $this->toKilograms() - $other->toKilograms();

        if ($result < 0) {
            throw InvalidValueException::cannotBeNegative('Weight', $result);
        }

        return self::fromKilograms($result);
    }

    /**
     * Check if this weight is zero.
     */
    public function isZero(): bool
    {
        return abs($this->toKilograms()) < 0.01;
    }

    /**
     * Check if this weight is greater than another.
     */
    public function isGreaterThan(self $other): bool
    {
        return $this->toKilograms() > $other->toKilograms();
    }

    /**
     * Check if this weight is less than another.
     */
    public function isLessThan(self $other): bool
    {
        return $this->toKilograms() < $other->toKilograms();
    }

    public function equals(ValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return abs($this->toKilograms() - $other->toKilograms()) < 0.01;
    }

    public function __toString(): string
    {
        return sprintf('%.2f kg', $this->toKilograms());
    }

    private static function validateNonNegative(float $value, string $field): void
    {
        if ($value < 0) {
            throw InvalidValueException::cannotBeNegative($field, $value);
        }
    }
}
