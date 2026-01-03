<?php

namespace Domain\Geography\Models;

use Domain\Geography\Factories\CountryFactory;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    /** @use HasFactory<CountryFactory> */
    use HasFactory;

    protected $table = 'geography_countries';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'iso_alpha2',
        'iso_alpha3',
        'name',
        'continent_id',
    ];

    /**
     * Get the continent this country belongs to.
     *
     * @return BelongsTo<Continent, $this>
     */
    public function continent(): BelongsTo
    {
        return $this->belongsTo(Continent::class);
    }

    /**
     * Get the metro areas in this country.
     *
     * @return HasMany<MetroArea, $this>
     */
    public function metroAreas(): HasMany
    {
        return $this->hasMany(MetroArea::class);
    }

    /**
     * Get the users in this country.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the emoji flag for this country.
     *
     * Converts the ISO alpha-2 code to regional indicator symbols.
     * For example, "US" becomes 🇺🇸
     */
    public function getFlagAttribute(): string
    {
        $code = strtoupper($this->iso_alpha2);

        // Regional indicator symbols start at U+1F1E6 for 'A'
        $base = 0x1F1E6 - ord('A');

        return mb_chr($base + ord($code[0])).mb_chr($base + ord($code[1]));
    }

    protected static function newFactory(): CountryFactory
    {
        return CountryFactory::new();
    }
}
