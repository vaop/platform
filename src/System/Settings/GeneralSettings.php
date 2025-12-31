<?php

declare(strict_types=1);

namespace System\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $vaName;

    public string $siteUrl;

    public static function group(): string
    {
        return 'general';
    }
}
