<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Enums;

/**
 * Units for measuring general length (e.g., wingspan, runway length).
 *
 * The canonical storage unit is meters (m).
 */
enum LengthUnit: int
{
    case METERS = 0;
    case FEET = 1;
    case INCHES = 2;

    /**
     * Get the unit name for the php-units-of-measure library.
     */
    public function getUnitName(): string
    {
        return match ($this) {
            self::METERS => 'm',
            self::FEET => 'ft',
            self::INCHES => 'in',
        };
    }

    /**
     * Get the human-readable label.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::METERS => 'Meters',
            self::FEET => 'Feet',
            self::INCHES => 'Inches',
        };
    }

    /**
     * Get the short acronym for display.
     */
    public function getAcronym(): string
    {
        return match ($this) {
            self::METERS => 'm',
            self::FEET => 'ft',
            self::INCHES => 'in',
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
        return self::METERS;
    }
}
