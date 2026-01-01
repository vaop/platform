<?php

declare(strict_types=1);

namespace System\View\Twig\Extensions;

use Support\UnitsOfMeasure\Enums\AltitudeUnit;
use Support\UnitsOfMeasure\Enums\DistanceUnit;
use Support\UnitsOfMeasure\Enums\SpeedUnit;
use Support\UnitsOfMeasure\Enums\WeightUnit;
use Support\UnitsOfMeasure\ValueObjects\Altitude;
use Support\UnitsOfMeasure\ValueObjects\Distance;
use Support\UnitsOfMeasure\ValueObjects\Duration;
use Support\UnitsOfMeasure\ValueObjects\Speed;
use Support\UnitsOfMeasure\ValueObjects\Weight;
use System\Settings\UnitsSettings;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig filters for formatting units of measure.
 *
 * Displays values in the user's preferred unit with appropriate formatting.
 * Falls back to airline defaults when user preferences are not set.
 */
class UnitsExtension extends AbstractExtension
{
    public function __construct(
        private readonly UnitsSettings $settings,
    ) {}

    /**
     * @return array<TwigFilter>
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('distance', [$this, 'formatDistance']),
            new TwigFilter('altitude', [$this, 'formatAltitude']),
            new TwigFilter('speed', [$this, 'formatSpeed']),
            new TwigFilter('weight', [$this, 'formatWeight']),
            new TwigFilter('duration', [$this, 'formatDuration']),
            new TwigFilter('duration_hms', [$this, 'formatDurationHMS']),
        ];
    }

    /**
     * Format a distance value in the user's preferred unit.
     *
     * @param  Distance|float|int|null  $value  Distance object or raw nautical miles
     * @param  int  $decimals  Number of decimal places
     * @param  bool  $showUnit  Whether to append the unit label
     */
    public function formatDistance(Distance|float|int|null $value, int $decimals = 0, bool $showUnit = true): string
    {
        if ($value === null) {
            return '';
        }

        $distance = $value instanceof Distance ? $value : Distance::fromNauticalMiles((float) $value);
        $unit = $this->getPreferredDistanceUnit();

        $converted = match ($unit) {
            DistanceUnit::NAUTICAL_MILES => $distance->toNauticalMiles(),
            DistanceUnit::KILOMETERS => $distance->toKilometers(),
            DistanceUnit::STATUTE_MILES => $distance->toStatuteMiles(),
        };

        $formatted = number_format($converted, $decimals);

        return $showUnit ? "{$formatted} {$unit->getAcronym()}" : $formatted;
    }

    /**
     * Format an altitude value in the user's preferred unit.
     *
     * @param  Altitude|float|int|null  $value  Altitude object or raw feet
     * @param  int  $decimals  Number of decimal places
     * @param  bool  $showUnit  Whether to append the unit label
     */
    public function formatAltitude(Altitude|float|int|null $value, int $decimals = 0, bool $showUnit = true): string
    {
        if ($value === null) {
            return '';
        }

        $altitude = $value instanceof Altitude ? $value : Altitude::fromFeet((float) $value);
        $unit = $this->getPreferredAltitudeUnit();

        $converted = match ($unit) {
            AltitudeUnit::FEET => $altitude->toFeet(),
            AltitudeUnit::METERS => $altitude->toMeters(),
        };

        $formatted = number_format($converted, $decimals);

        return $showUnit ? "{$formatted} {$unit->getAcronym()}" : $formatted;
    }

    /**
     * Format a speed value in the user's preferred unit.
     *
     * @param  Speed|float|int|null  $value  Speed object or raw knots
     * @param  int  $decimals  Number of decimal places
     * @param  bool  $showUnit  Whether to append the unit label
     */
    public function formatSpeed(Speed|float|int|null $value, int $decimals = 0, bool $showUnit = true): string
    {
        if ($value === null) {
            return '';
        }

        $speed = $value instanceof Speed ? $value : Speed::fromKnots((float) $value);
        $unit = $this->getPreferredSpeedUnit();

        $converted = match ($unit) {
            SpeedUnit::KNOTS => $speed->toKnots(),
            SpeedUnit::KILOMETERS_PER_HOUR => $speed->toKilometersPerHour(),
            SpeedUnit::MILES_PER_HOUR => $speed->toMilesPerHour(),
        };

        $formatted = number_format($converted, $decimals);

        return $showUnit ? "{$formatted} {$unit->getAcronym()}" : $formatted;
    }

    /**
     * Format a weight value in the user's preferred unit.
     *
     * @param  Weight|float|int|null  $value  Weight object or raw kilograms
     * @param  int  $decimals  Number of decimal places
     * @param  bool  $showUnit  Whether to append the unit label
     */
    public function formatWeight(Weight|float|int|null $value, int $decimals = 0, bool $showUnit = true): string
    {
        if ($value === null) {
            return '';
        }

        $weight = $value instanceof Weight ? $value : Weight::fromKilograms((float) $value);
        $unit = $this->getPreferredWeightUnit();

        $converted = match ($unit) {
            WeightUnit::KILOGRAMS => $weight->toKilograms(),
            WeightUnit::POUNDS => $weight->toPounds(),
        };

        $formatted = number_format($converted, $decimals);

        return $showUnit ? "{$formatted} {$unit->getAcronym()}" : $formatted;
    }

    /**
     * Format a duration as HH:MM.
     *
     * @param  Duration|int|null  $value  Duration object or raw seconds
     */
    public function formatDuration(Duration|int|null $value): string
    {
        if ($value === null) {
            return '';
        }

        $duration = $value instanceof Duration ? $value : Duration::fromSeconds($value);

        return $duration->toHoursMinutes();
    }

    /**
     * Format a duration as HH:MM:SS.
     *
     * @param  Duration|int|null  $value  Duration object or raw seconds
     */
    public function formatDurationHMS(Duration|int|null $value): string
    {
        if ($value === null) {
            return '';
        }

        $duration = $value instanceof Duration ? $value : Duration::fromSeconds($value);

        return $duration->toHoursMinutesSeconds();
    }

    /**
     * Get the user's preferred distance unit.
     */
    private function getPreferredDistanceUnit(): DistanceUnit
    {
        if ($this->settings->allowsUserCustomization() && auth()->user()?->distance_unit) {
            return auth()->user()->distance_unit;
        }

        return $this->settings->getDistanceUnit();
    }

    /**
     * Get the user's preferred altitude unit.
     */
    private function getPreferredAltitudeUnit(): AltitudeUnit
    {
        if ($this->settings->allowsUserCustomization() && auth()->user()?->altitude_unit) {
            return auth()->user()->altitude_unit;
        }

        return $this->settings->getAltitudeUnit();
    }

    /**
     * Get the user's preferred speed unit.
     */
    private function getPreferredSpeedUnit(): SpeedUnit
    {
        if ($this->settings->allowsUserCustomization() && auth()->user()?->speed_unit) {
            return auth()->user()->speed_unit;
        }

        return $this->settings->getSpeedUnit();
    }

    /**
     * Get the user's preferred weight unit.
     */
    private function getPreferredWeightUnit(): WeightUnit
    {
        if ($this->settings->allowsUserCustomization() && auth()->user()?->weight_unit) {
            return auth()->user()->weight_unit;
        }

        return $this->settings->getWeightUnit();
    }
}
