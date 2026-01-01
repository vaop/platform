<?php

declare(strict_types=1);

namespace Domain\User\Enums;

enum UserStatus: int
{
    case Pending = 0;
    case Active = 1;
    case Inactive = 2;
    case Suspended = 3;

    /**
     * Get a human-readable label for the status.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending Approval',
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Suspended => 'Suspended',
        };
    }

    /**
     * Get the color associated with this status (for UI).
     */
    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Active => 'success',
            self::Inactive => 'gray',
            self::Suspended => 'danger',
        };
    }

    /**
     * Check if the user can log in with this status.
     */
    public function canLogin(): bool
    {
        return $this === self::Active;
    }

    /**
     * Get all statuses as options for forms.
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $status) => $status->getLabel(), self::cases()),
        );
    }
}
