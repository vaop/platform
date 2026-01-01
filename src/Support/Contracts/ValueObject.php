<?php

declare(strict_types=1);

namespace Support\Contracts;

/**
 * Interface for value objects.
 *
 * Value objects are immutable, self-validating objects defined by their
 * attributes rather than an identity. Two value objects are equal if
 * their values are equal.
 */
interface ValueObject
{
    /**
     * Check equality with another value object.
     *
     * Returns true if the other object is of the same type and has
     * identical attribute values.
     */
    public function equals(self $other): bool;

    /**
     * Get a string representation of the value object.
     */
    public function __toString(): string;
}
