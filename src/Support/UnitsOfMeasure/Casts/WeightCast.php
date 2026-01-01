<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Support\UnitsOfMeasure\ValueObjects\Weight;

/**
 * Cast for Weight value objects.
 *
 * Stores weight in kilograms (canonical unit) in the database.
 */
class WeightCast implements CastsAttributes
{
    /**
     * Cast the given value from the database.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Weight
    {
        if ($value === null) {
            return null;
        }

        return Weight::fromKilograms((float) $value);
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

        if ($value instanceof Weight) {
            return $value->toKilograms();
        }

        // Allow setting raw numeric values (assumed to be in kilograms)
        return (float) $value;
    }
}
