<?php

declare(strict_types=1);

namespace Tests\Browser\Pages\Install;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class EnvironmentPage extends Page
{
    public function url(): string
    {
        return '/install/environment';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs('/install/environment')
            ->assertSee(__('install.environment.title'));
    }

    /**
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@heading' => 'h1',
            '@app-name-input' => 'input[name="app_name"]',
            '@app-url-input' => 'input[name="app_url"]',
            '@timezone-select' => 'select[name="timezone"]',
            '@submit-button' => 'button[type="submit"]',
        ];
    }

    public function fillEnvironmentForm(Browser $browser, array $config): void
    {
        $browser->type('@app-name-input', $config['app_name'] ?? 'Test Airline')
            ->type('@app-url-input', $config['app_url'] ?? 'http://localhost')
            ->select('@timezone-select', $config['timezone'] ?? 'UTC');
    }
}
