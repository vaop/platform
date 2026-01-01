<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\ValueObjects;

use Support\Contracts\ValueObject;
use Support\Exceptions\InvalidValueException;

/**
 * Immutable value object representing a geographic coordinate (latitude/longitude pair).
 *
 * Coordinates are stored in decimal degrees.
 * Latitude: -90 to +90 (negative = South, positive = North)
 * Longitude: -180 to +180 (negative = West, positive = East)
 */
final readonly class GeoCoordinate implements ValueObject
{
    private function __construct(
        private float $latitude,
        private float $longitude,
    ) {}

    /**
     * Create a GeoCoordinate from decimal degrees.
     *
     * @throws InvalidValueException If coordinates are out of valid range
     */
    public static function fromDecimalDegrees(float $latitude, float $longitude): self
    {
        self::validateLatitude($latitude);
        self::validateLongitude($longitude);

        return new self($latitude, $longitude);
    }

    /**
     * Create a GeoCoordinate from degrees, minutes, seconds.
     *
     * @param  string  $latDirection  'N' or 'S'
     * @param  string  $lonDirection  'E' or 'W'
     *
     * @throws InvalidValueException If coordinates are out of valid range
     */
    public static function fromDMS(
        int $latDegrees,
        int $latMinutes,
        float $latSeconds,
        string $latDirection,
        int $lonDegrees,
        int $lonMinutes,
        float $lonSeconds,
        string $lonDirection,
    ): self {
        $latitude = self::dmsToDecimal($latDegrees, $latMinutes, $latSeconds);
        $longitude = self::dmsToDecimal($lonDegrees, $lonMinutes, $lonSeconds);

        if (strtoupper($latDirection) === 'S') {
            $latitude = -$latitude;
        }

        if (strtoupper($lonDirection) === 'W') {
            $longitude = -$longitude;
        }

        return self::fromDecimalDegrees($latitude, $longitude);
    }

    /**
     * Get the latitude in decimal degrees.
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * Get the longitude in decimal degrees.
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * Get the latitude direction (N or S).
     */
    public function getLatitudeDirection(): string
    {
        return $this->latitude >= 0 ? 'N' : 'S';
    }

    /**
     * Get the longitude direction (E or W).
     */
    public function getLongitudeDirection(): string
    {
        return $this->longitude >= 0 ? 'E' : 'W';
    }

    /**
     * Get latitude as degrees, minutes, seconds array.
     *
     * @return array{degrees: int, minutes: int, seconds: float, direction: string}
     */
    public function getLatitudeDMS(): array
    {
        return $this->decimalToDMS($this->latitude, $this->getLatitudeDirection());
    }

    /**
     * Get longitude as degrees, minutes, seconds array.
     *
     * @return array{degrees: int, minutes: int, seconds: float, direction: string}
     */
    public function getLongitudeDMS(): array
    {
        return $this->decimalToDMS($this->longitude, $this->getLongitudeDirection());
    }

    /**
     * Calculate the great-circle distance to another coordinate.
     * Uses the Haversine formula.
     *
     * @return Distance The distance to the other coordinate
     */
    public function distanceTo(self $other): Distance
    {
        $earthRadiusNm = 3440.065; // Earth radius in nautical miles

        $lat1 = deg2rad($this->latitude);
        $lat2 = deg2rad($other->latitude);
        $deltaLat = deg2rad($other->latitude - $this->latitude);
        $deltaLon = deg2rad($other->longitude - $this->longitude);

        $a = sin($deltaLat / 2) ** 2 +
             cos($lat1) * cos($lat2) * sin($deltaLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return Distance::fromNauticalMiles($earthRadiusNm * $c);
    }

    /**
     * Calculate the initial bearing (forward azimuth) to another coordinate.
     *
     * @return float Bearing in degrees (0-360)
     */
    public function bearingTo(self $other): float
    {
        $lat1 = deg2rad($this->latitude);
        $lat2 = deg2rad($other->latitude);
        $deltaLon = deg2rad($other->longitude - $this->longitude);

        $x = cos($lat2) * sin($deltaLon);
        $y = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($deltaLon);

        $bearing = rad2deg(atan2($x, $y));

        // Normalize to 0-360
        return fmod($bearing + 360, 360);
    }

    /**
     * Format as ISO 6709 string (e.g., "+40.7128-074.0060/").
     */
    public function toIso6709(): string
    {
        $lat = $this->latitude >= 0 ? '+' : '';
        $lon = $this->longitude >= 0 ? '+' : '';

        return sprintf('%s%.4f%s%.4f/', $lat, $this->latitude, $lon, $this->longitude);
    }

    /**
     * Format as DMS string (e.g., "40°42'46"N 74°00'22"W").
     */
    public function toDMSString(): string
    {
        $lat = $this->getLatitudeDMS();
        $lon = $this->getLongitudeDMS();

        return sprintf(
            '%d°%02d\'%05.2f"%s %d°%02d\'%05.2f"%s',
            $lat['degrees'],
            $lat['minutes'],
            $lat['seconds'],
            $lat['direction'],
            $lon['degrees'],
            $lon['minutes'],
            $lon['seconds'],
            $lon['direction']
        );
    }

    public function equals(ValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        // Compare with 6 decimal places precision (~0.11m accuracy)
        return abs($this->latitude - $other->latitude) < 0.000001
            && abs($this->longitude - $other->longitude) < 0.000001;
    }

    public function __toString(): string
    {
        return sprintf('%.6f, %.6f', $this->latitude, $this->longitude);
    }

    private static function validateLatitude(float $latitude): void
    {
        if ($latitude < -90 || $latitude > 90) {
            throw InvalidValueException::outOfRange('Latitude', $latitude, -90, 90);
        }
    }

    private static function validateLongitude(float $longitude): void
    {
        if ($longitude < -180 || $longitude > 180) {
            throw InvalidValueException::outOfRange('Longitude', $longitude, -180, 180);
        }
    }

    private static function dmsToDecimal(int $degrees, int $minutes, float $seconds): float
    {
        return $degrees + ($minutes / 60) + ($seconds / 3600);
    }

    /**
     * @return array{degrees: int, minutes: int, seconds: float, direction: string}
     */
    private function decimalToDMS(float $decimal, string $direction): array
    {
        $absolute = abs($decimal);
        $degrees = (int) floor($absolute);
        $minutesDecimal = ($absolute - $degrees) * 60;
        $minutes = (int) floor($minutesDecimal);
        $seconds = ($minutesDecimal - $minutes) * 60;

        return [
            'degrees' => $degrees,
            'minutes' => $minutes,
            'seconds' => round($seconds, 2),
            'direction' => $direction,
        ];
    }
}
