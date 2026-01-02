<?php

declare(strict_types=1);

namespace Domain\User\Observers;

use Domain\User\Events\EmailChanged;
use Domain\User\Models\User;

class UserObserver
{
    /**
     * Handle the User "updating" event.
     *
     * Reset email verification when email address changes.
     * User status is NOT changed - active users remain active.
     */
    public function updating(User $user): void
    {
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
    }

    /**
     * Handle the User "updated" event.
     *
     * Dispatch EmailChanged event if email was changed.
     */
    public function updated(User $user): void
    {
        if ($user->wasChanged('email')) {
            event(new EmailChanged($user));
        }
    }
}
