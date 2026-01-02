<?php

declare(strict_types=1);

namespace Domain\User\Listeners;

use Illuminate\Auth\Events\Registered;
use System\Settings\RegistrationSettings;

class SendEmailVerificationNotification
{
    public function __construct(
        private readonly RegistrationSettings $settings
    ) {}

    public function handle(Registered $event): void
    {
        if (! $this->settings->requireEmailVerification) {
            return;
        }

        if (! $event->user->hasVerifiedEmail()) {
            $event->user->sendEmailVerificationNotification();
        }
    }
}
