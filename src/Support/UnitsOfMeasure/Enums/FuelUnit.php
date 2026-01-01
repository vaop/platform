<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Enums;

/**
 * Units for measuring fuel quantity.
 *
 * Fuel can be measured by weight or volume. The canonical storage unit
 * is kilograms (kg) to ensure consistent weight-based calculations.
 *
 * Note: Volume-to-weight conversion requires fuel density, which varies
 * by fuel type (Jet-A, AvGas, etc.). Conversions are handled at the
 * application layer.
 */
enum FuelUnit: int
{
    case KILOGRAMS = 0;
    case POUNDS = 1;
    case LITERS = 2;
    case GALLONS = 3;

    /**
     * Get the unit name for the php-units-of-measure library.
     */
    public function getUnitName(): string
    {
        return match ($this) {
            self::KILOGRAMS => 'kg',
            self::POUNDS => 'lbs',
            self::LITERS => 'l',
            self::GALLONS => 'gal',
        };
    }

    /**
     * Get the human-readable label.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::KILOGRAMS => 'Kilograms',
            self::POUNDS => 'Pounds',
            self::LITERS => 'Liters',
            self::GALLONS => 'Gallons (US)',
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
            self::LITERS => 'L',
            self::GALLONS => 'gal',
        };
    }

    /**
     * Check if this is a weight-based unit.
     */
    public function isWeight(): bool
    {
        return match ($this) {
            self::KILOGRAMS, self::POUNDS => true,
            self::LITERS, self::GALLONS => false,
        };
    }

    /**
     * Check if this is a volume-based unit.
     */
    public function isVolume(): bool
    {
        return ! $this->isWeight();
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
     * Get weight-based options only.
     *
     * @return array<int, string>
     */
    public static function weightOptions(): array
    {
        return [
            self::KILOGRAMS->value => self::KILOGRAMS->getLabel(),
            self::POUNDS->value => self::POUNDS->getLabel(),
        ];
    }

    /**
     * Get volume-based options only.
     *
     * @return array<int, string>
     */
    public static function volumeOptions(): array
    {
        return [
            self::LITERS->value => self::LITERS->getLabel(),
            self::GALLONS->value => self::GALLONS->getLabel(),
        ];
    }

    /**
     * Get the canonical (default storage) unit.
     */
    public static function canonical(): self
    {
        return self::KILOGRAMS;
    }
}
