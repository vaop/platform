<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Enums;

/**
 * Units for measuring speed/velocity.
 *
 * The canonical storage unit is knots (kts).
 */
enum SpeedUnit: int
{
    case KNOTS = 0;
    case KILOMETERS_PER_HOUR = 1;
    case MILES_PER_HOUR = 2;

    /**
     * Get the unit name for the php-units-of-measure library.
     */
    public function getUnitName(): string
    {
        return match ($this) {
            self::KNOTS => 'knots',
            self::KILOMETERS_PER_HOUR => 'km/h',
            self::MILES_PER_HOUR => 'mph',
        };
    }

    /**
     * Get the human-readable label.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::KNOTS => 'Knots',
            self::KILOMETERS_PER_HOUR => 'Kilometers per Hour',
            self::MILES_PER_HOUR => 'Miles per Hour',
        };
    }

    /**
     * Get the short acronym for display.
     */
    public function getAcronym(): string
    {
        return match ($this) {
            self::KNOTS => 'kts',
            self::KILOMETERS_PER_HOUR => 'km/h',
            self::MILES_PER_HOUR => 'mph',
        };
    }

    /**
     * Get options array for forms/dropdowns.
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }

    /**
     * Get the canonical (default storage) unit.
     */
    public static function canonical(): self
    {
        return self::KNOTS;
    }
}
