<?php

declare(strict_types=1);

namespace Domain\Settings\Providers;

use Domain\Settings\GeneralSettings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
                'app.url' => $settings->siteUrl,
            ]);
        } catch (\Throwable) {
            // Database connection or settings unavailable, skip override
        }
    }
}
