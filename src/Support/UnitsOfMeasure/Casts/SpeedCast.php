<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Support\UnitsOfMeasure\ValueObjects\Speed;

/**
 * Cast for Speed value objects.
 *
 * Stores speed in knots (canonical unit) in the database.
 */
class SpeedCast implements CastsAttributes
{
    /**
     * Cast the given value from the database.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Speed
    {
        if ($value === null) {
            return null;
        }

        return Speed::fromKnots((float) $value);
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

        if ($value instanceof Speed) {
            return $value->toKnots();
        }

        // Allow setting raw numeric values (assumed to be in knots)
        return (float) $value;
    }
}
