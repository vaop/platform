<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Enums;

/**
 * Units for measuring distance/length.
 *
 * The canonical storage unit is nautical miles (nm).
 */
enum DistanceUnit: int
{
    case NAUTICAL_MILES = 0;
    case KILOMETERS = 1;
    case STATUTE_MILES = 2;

    /**
     * Get the unit name for the php-units-of-measure library.
     */
    public function getUnitName(): string
    {
        return match ($this) {
            self::NAUTICAL_MILES => 'nmi',
            self::KILOMETERS => 'km',
            self::STATUTE_MILES => 'mi',
        };
    }

    /**
     * Get the human-readable label.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::NAUTICAL_MILES => 'Nautical Miles',
            self::KILOMETERS => 'Kilometers',
            self::STATUTE_MILES => 'Statute Miles',
        };
    }

    /**
     * Get the short acronym for display.
     */
    public function getAcronym(): string
    {
        return match ($this) {
            self::NAUTICAL_MILES => 'nm',
            self::KILOMETERS => 'km',
            self::STATUTE_MILES => 'mi',
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
        return self::NAUTICAL_MILES;
    }
}
