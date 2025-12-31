<?php

declare(strict_types=1);

namespace System\Settings\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use System\Settings\GeneralSettings;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->settingsReady()) {
            $this->overrideAppConfig();
        }
    }

    private function settingsReady(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Throwable) {
            return false;
        }
    }

    private function overrideAppConfig(): void
    {
        try {
            $settings = app(GeneralSettings::class);

            config([
                'app.name' => $settings->vaName,
                // Only override app.url if not explicitly set via environment
                'app.url' => env('APP_URL') ?: $settings->siteUrl,
            ]);
        } catch (\Throwable) {
            // Database connection or settings unavailable, skip override
        }
    }
}
