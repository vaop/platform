<?php

declare(strict_types=1);

namespace System\Settings;

use Spatie\LaravelSettings\Settings;

class ModulesSettings extends Settings
{
    public bool $enableMetroAreas;

    public static function group(): string
    {
        return 'modules';
    }
}
