<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Enums;

/**
 * Units for measuring atmospheric pressure.
 *
 * The canonical storage unit is hectopascals (hPa).
 */
enum PressureUnit: string
{
    case HECTOPASCALS = 'hPa';
    case INCHES_OF_MERCURY = 'inHg';
    case MILLIBARS = 'mbar';

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
     * @return array<string, string>
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
