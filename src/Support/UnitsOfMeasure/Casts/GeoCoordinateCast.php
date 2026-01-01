<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Support\UnitsOfMeasure\ValueObjects\GeoCoordinate;

/**
 * Cast for GeoCoordinate value objects.
 *
 * Stores coordinates as two separate columns: {key}_lat and {key}_lon.
 * Both are stored in decimal degrees.
 *
 * Usage:
 *   protected $casts = [
 *       'location' => GeoCoordinateCast::class,
 *   ];
 *
 * Requires columns: location_lat (float) and location_lon (float)
 */
class GeoCoordinateCast implements CastsAttributes
{
    /**
     * Cast the given value from the database.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?GeoCoordinate
    {
        $latKey = "{$key}_lat";
        $lonKey = "{$key}_lon";

        $lat = $attributes[$latKey] ?? null;
        $lon = $attributes[$lonKey] ?? null;

        if ($lat === null || $lon === null) {
            return null;
        }

        return GeoCoordinate::fromDecimalDegrees((float) $lat, (float) $lon);
    }

    /**
     * Prepare the given value for storage in the database.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, float|null>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        $latKey = "{$key}_lat";
        $lonKey = "{$key}_lon";

        if ($value === null) {
            return [
                $latKey => null,
                $lonKey => null,
            ];
        }

        if ($value instanceof GeoCoordinate) {
            return [
                $latKey => $value->getLatitude(),
                $lonKey => $value->getLongitude(),
            ];
        }

        // Allow setting from array ['lat' => x, 'lon' => y]
        if (is_array($value)) {
            $lat = $value['lat'] ?? $value['latitude'] ?? $value[0] ?? null;
            $lon = $value['lon'] ?? $value['longitude'] ?? $value[1] ?? null;

            if ($lat === null || $lon === null) {
                throw new InvalidArgumentException(
                    'GeoCoordinate array must contain lat/lon or latitude/longitude keys, or be [lat, lon] indexed.'
                );
            }

            return [
                $latKey => (float) $lat,
                $lonKey => (float) $lon,
            ];
        }

        throw new InvalidArgumentException(
            'GeoCoordinate value must be a GeoCoordinate instance, array, or null.'
        );
    }
}
