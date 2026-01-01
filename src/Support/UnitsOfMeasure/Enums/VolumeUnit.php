<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Enums;

/**
 * Units for measuring volume (general purpose).
 *
 * For fuel-specific volume, see FuelUnit which includes both
 * mass and volume options. The canonical storage unit is liters (L).
 */
enum VolumeUnit: int
{
    case LITERS = 0;
    case GALLONS = 1;
    case CUBIC_METERS = 2;

    /**
     * Get the unit name for the php-units-of-measure library.
     */
    public function getUnitName(): string
    {
        return match ($this) {
            self::LITERS => 'l',
            self::GALLONS => 'gal',
            self::CUBIC_METERS => 'm^3',
        };
    }

    /**
     * Get the human-readable label.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::LITERS => 'Liters',
            self::GALLONS => 'Gallons (US)',
            self::CUBIC_METERS => 'Cubic Meters',
        };
    }

    /**
     * Get the short acronym for display.
     */
    public function getAcronym(): string
    {
        return match ($this) {
            self::LITERS => 'L',
            self::GALLONS => 'gal',
            self::CUBIC_METERS => 'm³',
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
        return self::LITERS;
    }
}
