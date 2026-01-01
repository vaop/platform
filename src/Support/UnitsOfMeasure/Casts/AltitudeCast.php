<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Support\UnitsOfMeasure\ValueObjects\Altitude;

/**
 * Cast for Altitude value objects.
 *
 * Stores altitude in feet (canonical unit) in the database.
 */
class AltitudeCast implements CastsAttributes
{
    /**
     * Cast the given value from the database.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Altitude
    {
        if ($value === null) {
            return null;
        }

        return Altitude::fromFeet((float) $value);
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

        if ($value instanceof Altitude) {
            return $value->toFeet();
        }

        // Allow setting raw numeric values (assumed to be in feet)
        return (float) $value;
    }
}
