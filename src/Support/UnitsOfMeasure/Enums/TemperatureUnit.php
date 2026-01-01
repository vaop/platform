<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Enums;

/**
 * Units for measuring temperature.
 *
 * The canonical storage unit is Celsius (C).
 */
enum TemperatureUnit: string
{
    case CELSIUS = 'C';
    case FAHRENHEIT = 'F';

    /**
     * Get the human-readable label.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::CELSIUS => 'Celsius',
            self::FAHRENHEIT => 'Fahrenheit',
        };
    }

    /**
     * Get the short acronym for display.
     */
    public function getAcronym(): string
    {
        return match ($this) {
            self::CELSIUS => '°C',
            self::FAHRENHEIT => '°F',
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
        return self::CELSIUS;
    }
}
