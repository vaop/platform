<?php

declare(strict_types=1);

namespace Domain\User\Listeners;

use Domain\User\Events\EmailChanged;
use System\Settings\RegistrationSettings;

readonly class SendEmailVerificationOnEmailChange
{
    public function __construct(
        private RegistrationSettings $settings
    ) {}

    public function handle(EmailChanged $event): void
    {
        if (! $this->settings->requireEmailVerification) {
            return;
        }

        $user = $event->user;

        // Use duck typing - check for methods instead of interface
        if (method_exists($user, 'hasVerifiedEmail')
            && method_exists($user, 'sendEmailVerificationNotification')
            && ! $user->hasVerifiedEmail()
        ) {
            $user->sendEmailVerificationNotification();
        }
    }
}
