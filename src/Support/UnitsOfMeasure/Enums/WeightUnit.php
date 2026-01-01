<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Enums;

/**
 * Units for measuring weight/mass.
 *
 * The canonical storage unit is kilograms (kg).
 */
enum WeightUnit: string
{
    case KILOGRAMS = 'kg';
    case POUNDS = 'lbs';

    /**
     * Get the human-readable label.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::KILOGRAMS => 'Kilograms',
            self::POUNDS => 'Pounds',
        };
    }

    /**
     * Get the short acronym for display.
     */
    public function getAcronym(): string
    {
        return match ($this) {
            self::KILOGRAMS => 'kg',
            self::POUNDS => 'lbs',
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
        return self::KILOGRAMS;
    }
}
