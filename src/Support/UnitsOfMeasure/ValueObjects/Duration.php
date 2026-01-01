<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\ValueObjects;

use DateInterval;
use Support\Contracts\ValueObject;
use Support\Exceptions\InvalidValueException;

/**
 * Immutable value object representing a time duration.
 *
 * Durations are stored internally in seconds.
 * Provides conversion methods for hours, minutes, and formatted output.
 */
final readonly class Duration implements ValueObject
{
    private function __construct(
        private int $seconds,
    ) {}

    /**
     * Create a Duration from seconds.
     */
    public static function fromSeconds(int $seconds): self
    {
        self::validateNonNegative($seconds, 'Duration');

        return new self($seconds);
    }

    /**
     * Create a Duration from minutes.
     */
    public static function fromMinutes(int|float $minutes): self
    {
        $seconds = (int) round($minutes * 60);
        self::validateNonNegative($seconds, 'Duration');

        return new self($seconds);
    }

    /**
     * Create a Duration from hours.
     */
    public static function fromHours(int|float $hours): self
    {
        $seconds = (int) round($hours * 3600);
        self::validateNonNegative($seconds, 'Duration');

        return new self($seconds);
    }

    /**
     * Create a Duration from hours and minutes.
     */
    public static function fromHoursAndMinutes(int $hours, int $minutes): self
    {
        if ($hours < 0 || $minutes < 0) {
            throw InvalidValueException::cannotBeNegative('Duration components', min($hours, $minutes));
        }

        return new self(($hours * 3600) + ($minutes * 60));
    }

    /**
     * Create a Duration from a DateInterval.
     */
    public static function fromDateInterval(DateInterval $interval): self
    {
        $seconds = ($interval->h * 3600) + ($interval->i * 60) + $interval->s;

        // Add days if present
        if ($interval->d > 0) {
            $seconds += $interval->d * 86400;
        }

        return new self($seconds);
    }

    /**
     * Create a zero duration.
     */
    public static function zero(): self
    {
        return new self(0);
    }

    /**
     * Get the duration in seconds.
     */
    public function toSeconds(): int
    {
        return $this->seconds;
    }

    /**
     * Get the duration in minutes (as a float).
     */
    public function toMinutes(): float
    {
        return $this->seconds / 60;
    }

    /**
     * Get the duration in hours (as a float).
     */
    public function toHours(): float
    {
        return $this->seconds / 3600;
    }

    /**
     * Get the hours component (whole hours).
     */
    public function getHours(): int
    {
        return (int) floor($this->seconds / 3600);
    }

    /**
     * Get the minutes component (remaining after hours).
     */
    public function getMinutes(): int
    {
        return (int) floor(($this->seconds % 3600) / 60);
    }

    /**
     * Get the seconds component (remaining after minutes).
     */
    public function getRemainingSeconds(): int
    {
        return $this->seconds % 60;
    }

    /**
     * Convert to a DateInterval.
     */
    public function toDateInterval(): DateInterval
    {
        return new DateInterval(sprintf('PT%dS', $this->seconds));
    }

    /**
     * Format as HH:MM.
     */
    public function toHoursMinutes(): string
    {
        return sprintf('%d:%02d', $this->getHours(), $this->getMinutes());
    }

    /**
     * Format as HH:MM:SS.
     */
    public function toHoursMinutesSeconds(): string
    {
        return sprintf(
            '%d:%02d:%02d',
            $this->getHours(),
            $this->getMinutes(),
            $this->getRemainingSeconds()
        );
    }

    /**
     * Add another duration, returning a new Duration.
     */
    public function add(self $other): self
    {
        return new self($this->seconds + $other->seconds);
    }

    /**
     * Subtract another duration, returning a new Duration.
     *
     * @throws InvalidValueException If the result would be negative
     */
    public function subtract(self $other): self
    {
        $result = $this->seconds - $other->seconds;

        if ($result < 0) {
            throw InvalidValueException::cannotBeNegative('Duration', $result);
        }

        return new self($result);
    }

    /**
     * Check if this duration is zero.
     */
    public function isZero(): bool
    {
        return $this->seconds === 0;
    }

    /**
     * Check if this duration is greater than another.
     */
    public function isGreaterThan(self $other): bool
    {
        return $this->seconds > $other->seconds;
    }

    /**
     * Check if this duration is less than another.
     */
    public function isLessThan(self $other): bool
    {
        return $this->seconds < $other->seconds;
    }

    public function equals(ValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return $this->seconds === $other->seconds;
    }

    public function __toString(): string
    {
        return $this->toHoursMinutes();
    }

    private static function validateNonNegative(int $value, string $field): void
    {
        if ($value < 0) {
            throw InvalidValueException::cannotBeNegative($field, $value);
        }
    }
}
