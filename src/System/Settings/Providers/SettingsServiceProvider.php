<?php

declare(strict_types=1);

namespace System\Settings\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use System\Settings\GeneralSettings;
use System\Settings\MailSettings;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->settingsReady()) {
            $this->overrideAppConfig();
            $this->overrideMailConfig();
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

    private function overrideMailConfig(): void
    {
        try {
            $settings = app(MailSettings::class);

            config([
                'mail.from.address' => $settings->fromAddress,
                'mail.from.name' => $settings->fromName,
                'mail.mailers.smtp.scheme' => $settings->smtpScheme,
                'mail.mailers.smtp.host' => $settings->smtpHost,
                'mail.mailers.smtp.port' => $settings->smtpPort,
                'mail.mailers.smtp.username' => $settings->smtpUsername,
                'mail.mailers.smtp.password' => $settings->smtpPassword,
                'mail.mailers.smtp.local_domain' => $settings->ehloDomain,
            ]);
        } catch (\Throwable) {
            // Settings unavailable, skip override
        }
    }
}
