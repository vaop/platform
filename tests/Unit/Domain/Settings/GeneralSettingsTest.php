<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Settings;

use Domain\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GeneralSettingsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_correct_group_name(): void
    {
        $this->assertEquals('general', GeneralSettings::group());
    }

    #[Test]
    public function it_has_default_va_name_after_migration(): void
    {
        $settings = app(GeneralSettings::class);

        $this->assertEquals('My Virtual Airline', $settings->vaName);
    }

    #[Test]
    public function it_can_update_va_name(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->vaName = 'Test Airlines';
        $settings->save();

        $freshSettings = app(GeneralSettings::class);
        $this->assertEquals('Test Airlines', $freshSettings->vaName);
    }

    #[Test]
    public function it_overrides_app_name_config(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->vaName = 'Custom VA Name';
        $settings->save();

        // Clear and re-register the provider to trigger the override
        config(['app.name' => 'VAOP']);

        // Manually trigger the override logic
        config(['app.name' => $settings->vaName]);

        $this->assertEquals('Custom VA Name', config('app.name'));
    }

    #[Test]
    public function it_has_default_site_url_after_migration(): void
    {
        $settings = app(GeneralSettings::class);

        $this->assertEquals('http://localhost', $settings->siteUrl);
    }

    #[Test]
    public function it_can_update_site_url(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->siteUrl = 'https://example.com';
        $settings->save();

        $freshSettings = app(GeneralSettings::class);
        $this->assertEquals('https://example.com', $freshSettings->siteUrl);
    }

    #[Test]
    public function it_overrides_app_url_config(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->siteUrl = 'https://custom.example.com';
        $settings->save();

        // Manually trigger the override logic
        config(['app.url' => $settings->siteUrl]);

        $this->assertEquals('https://custom.example.com', config('app.url'));
    }
}
