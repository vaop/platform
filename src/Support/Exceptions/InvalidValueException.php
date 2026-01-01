<?php

declare(strict_types=1);

namespace Support\Exceptions;

/**
 * Exception thrown when a value object is constructed with invalid data.
 *
 * This exception is used for value object validation failures such as
 * negative distances, invalid coordinates, or malformed codes.
 */
class InvalidValueException extends DomainException
{
    /**
     * Create an exception for a value that is out of range.
     */
    public static function outOfRange(string $field, mixed $value, mixed $min = null, mixed $max = null): self
    {
        $range = match (true) {
            $min !== null && $max !== null => "between {$min} and {$max}",
            $min !== null => "at least {$min}",
            $max !== null => "at most {$max}",
            default => 'within valid range',
        };

        return new self(
            "{$field} must be {$range}, got {$value}",
            $field,
        );
    }

    /**
     * Create an exception for a negative value that must be positive.
     */
    public static function mustBePositive(string $field, mixed $value): self
    {
        return new self(
            "{$field} must be positive, got {$value}",
            $field,
        );
    }

    /**
     * Create an exception for a value that cannot be negative.
     */
    public static function cannotBeNegative(string $field, mixed $value): self
    {
        return new self(
            "{$field} cannot be negative, got {$value}",
            $field,
        );
    }

    /**
     * Create an exception for an invalid format.
     */
    public static function invalidFormat(string $field, mixed $value, string $expectedFormat): self
    {
        $displayValue = is_string($value) ? "'{$value}'" : (string) $value;

        return new self(
            "{$field} has invalid format: {$displayValue}. Expected: {$expectedFormat}",
            $field,
        );
    }

    /**
     * Create an exception for an empty value that cannot be empty.
     */
    public static function cannotBeEmpty(string $field): self
    {
        return new self(
            "{$field} cannot be empty",
            $field,
        );
    }
}
