<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Enums;

/**
 * Units for measuring altitude/height.
 *
 * The canonical storage unit is feet (ft).
 */
enum AltitudeUnit: string
{
    case FEET = 'ft';
    case METERS = 'm';

    /**
     * Get the human-readable label.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::FEET => 'Feet',
            self::METERS => 'Meters',
        };
    }

    /**
     * Get the short acronym for display.
     */
    public function getAcronym(): string
    {
        return match ($this) {
            self::FEET => 'ft',
            self::METERS => 'm',
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
        return self::FEET;
    }
}
