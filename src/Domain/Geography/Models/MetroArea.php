<?php

namespace Domain\Geography\Models;

use Domain\Geography\Factories\MetroAreaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetroArea extends Model
{
    /** @use HasFactory<MetroAreaFactory> */
    use HasFactory;

    protected $table = 'geography_metro_areas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'country_id',
    ];

    /**
     * Get the country this metro area is in.
     *
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    protected static function newFactory(): MetroAreaFactory
    {
        return MetroAreaFactory::new();
    }
}
