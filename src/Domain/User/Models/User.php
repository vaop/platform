<?php

namespace Domain\User\Models;

use Domain\Geography\Models\Country;
use Domain\User\Enums\UserStatus;
use Domain\User\Factories\UserFactory;
use Domain\User\Notifications\ResetPasswordNotification;
use Domain\User\Notifications\VerifyEmailNotification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
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
use System\Notifications\DatabaseNotification;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, MustVerifyEmail, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'vanity_id',
        'avatar',
        'country_id',
        'timezone',
        'status',
        'last_login_at',
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
            'status' => UserStatus::class,
            'last_login_at' => 'datetime',
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
     * Check if the user can log in based on their status.
     */
    public function canLogin(): bool
    {
        return $this->status->canLogin();
    }

    /**
     * Check if the user is active.
     */
    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    /**
     * Check if the user is pending approval.
     */
    public function isPending(): bool
    {
        return $this->status === UserStatus::Pending;
    }

    /**
     * Check if a pending user is eligible for auto-activation based on current settings.
     *
     * This handles the edge case where settings become less restrictive after a user
     * registered. For example, if email verification was required when they registered
     * but has since been disabled, they should be able to activate.
     */
    public function isEligibleForAutoActivation(): bool
    {
        if (! $this->isPending()) {
            return false;
        }

        $settings = app(\System\Settings\RegistrationSettings::class);

        // If approval is required, user must wait for admin
        if ($settings->requireApproval) {
            return false;
        }

        // If email verification is required, user must have verified email
        if ($settings->requireEmailVerification && ! $this->hasVerifiedEmail()) {
            return false;
        }

        // User is pending but doesn't need to be anymore
        return true;
    }

    /**
     * Activate the user if they are eligible for auto-activation.
     *
     * @return bool Whether the user was activated
     */
    public function activateIfEligible(): bool
    {
        if (! $this->isEligibleForAutoActivation()) {
            return false;
        }

        $this->update(['status' => UserStatus::Active]);

        return true;
    }

    /**
     * Check if the user is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === UserStatus::Suspended;
    }

    /**
     * Check if the user can access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->can('admin.access');
    }

    /**
     * Get the country the user belongs to.
     *
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification(mixed $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Get the entity's notifications.
     *
     * @return MorphMany<DatabaseNotification, $this>
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')->latest();
    }
}
