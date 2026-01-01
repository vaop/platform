<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Support\UnitsOfMeasure\ValueObjects\Distance;

/**
 * Cast for Distance value objects.
 *
 * Stores distance in nautical miles (canonical unit) in the database.
 */
class DistanceCast implements CastsAttributes
{
    /**
     * Cast the given value from the database.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Distance
    {
        if ($value === null) {
            return null;
        }

        return Distance::fromNauticalMiles((float) $value);
    }

    /**
     * Prepare the given value for storage in the database.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?float
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Distance) {
            return $value->toNauticalMiles();
        }

        // Allow setting raw numeric values (assumed to be in nautical miles)
        return (float) $value;
    }
}
