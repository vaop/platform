<?php

declare(strict_types=1);

namespace Support\UnitsOfMeasure\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Support\UnitsOfMeasure\ValueObjects\Duration;

/**
 * Cast for Duration value objects.
 *
 * Stores duration in seconds (canonical unit) in the database.
 */
class DurationCast implements CastsAttributes
{
    /**
     * Cast the given value from the database.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Duration
    {
        if ($value === null) {
            return null;
        }

        return Duration::fromSeconds((int) $value);
    }

    /**
     * Prepare the given value for storage in the database.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Duration) {
            return $value->toSeconds();
        }

        // Allow setting raw numeric values (assumed to be in seconds)
        return (int) $value;
    }
}
