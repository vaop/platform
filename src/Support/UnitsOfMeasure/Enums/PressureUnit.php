<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Enums;

/**
 * Units for measuring atmospheric pressure.
 *
 * The canonical storage unit is hectopascals (hPa).
 */
enum PressureUnit: int
{
    case HECTOPASCALS = 0;
    case INCHES_OF_MERCURY = 1;
    case MILLIBARS = 2;

    /**
     * Get the unit name for the php-units-of-measure library.
     */
    public function getUnitName(): string
    {
        return match ($this) {
            self::HECTOPASCALS => 'hPa',
            self::INCHES_OF_MERCURY => 'inHg',
            self::MILLIBARS => 'mbar',
        };
    }

    /**
     * Get the human-readable label.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::HECTOPASCALS => 'Hectopascals',
            self::INCHES_OF_MERCURY => 'Inches of Mercury',
            self::MILLIBARS => 'Millibars',
        };
    }

    /**
     * Get the short acronym for display.
     */
    public function getAcronym(): string
    {
        return match ($this) {
            self::HECTOPASCALS => 'hPa',
            self::INCHES_OF_MERCURY => 'inHg',
            self::MILLIBARS => 'mb',
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
        return self::HECTOPASCALS;
    }
}
