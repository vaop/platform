<?php

namespace Domain\User\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Domain\User\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Support\UnitsOfMeasure\Enums\AltitudeUnit;
use Support\UnitsOfMeasure\Enums\DistanceUnit;
use Support\UnitsOfMeasure\Enums\FuelUnit;
use Support\UnitsOfMeasure\Enums\HeightUnit;
use Support\UnitsOfMeasure\Enums\LengthUnit;
use Support\UnitsOfMeasure\Enums\PressureUnit;
use Support\UnitsOfMeasure\Enums\SpeedUnit;
use Support\UnitsOfMeasure\Enums\TemperatureUnit;
use Support\UnitsOfMeasure\Enums\VolumeUnit;
use Support\UnitsOfMeasure\Enums\WeightUnit;

class User extends Authenticatable
{
    /** @use HasFactory<\Domain\User\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'distance_unit',
        'altitude_unit',
        'height_unit',
        'length_unit',
        'pressure_unit',
        'speed_unit',
        'weight_unit',
        'fuel_unit',
        'volume_unit',
        'temperature_unit',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'distance_unit' => DistanceUnit::class,
            'altitude_unit' => AltitudeUnit::class,
            'height_unit' => HeightUnit::class,
            'length_unit' => LengthUnit::class,
            'pressure_unit' => PressureUnit::class,
            'speed_unit' => SpeedUnit::class,
            'weight_unit' => WeightUnit::class,
            'fuel_unit' => FuelUnit::class,
            'volume_unit' => VolumeUnit::class,
            'temperature_unit' => TemperatureUnit::class,
        ];
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
