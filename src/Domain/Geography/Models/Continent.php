<?php

namespace Domain\Geography\Models;

use Domain\Geography\Factories\ContinentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Continent extends Model
{
    /** @use HasFactory<ContinentFactory> */
    use HasFactory;

    protected $table = 'geography_continents';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * Get the countries on this continent.
     *
     * @return HasMany<Country, $this>
     */
    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }

    protected static function newFactory(): ContinentFactory
    {
        return ContinentFactory::new();
    }
}
