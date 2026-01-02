<?php

declare(strict_types=1);

namespace System\Settings;

use Spatie\LaravelSettings\Settings;

class RegistrationSettings extends Settings
{
    public bool $registrationOpen;

    public bool $requireApproval;

    public bool $requireEmailVerification;

    public ?string $termsUrl;

    public ?string $privacyPolicyUrl;

    public static function group(): string
    {
        return 'registration';
    }

    public function hasTerms(): bool
    {
        return ! empty($this->termsUrl);
    }

    public function hasPrivacyPolicy(): bool
    {
        return ! empty($this->privacyPolicyUrl);
    }

    public function requiresLegalAcceptance(): bool
    {
        return $this->hasTerms() || $this->hasPrivacyPolicy();
    }
}
