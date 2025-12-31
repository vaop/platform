<?php

declare(strict_types=1);

namespace System\Settings;

use Spatie\LaravelSettings\Settings;

class MailSettings extends Settings
{
    public string $fromAddress;

    public string $fromName;

    public ?string $smtpScheme;

    public string $smtpHost;

    public int $smtpPort;

    public ?string $smtpUsername;

    public ?string $smtpPassword;

    public ?string $ehloDomain;

    public static function group(): string
    {
        return 'mail';
    }

    public static function encrypted(): array
    {
        return ['smtpPassword'];
    }
}
