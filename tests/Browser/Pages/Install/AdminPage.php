<?php

declare(strict_types=1);

namespace Tests\Browser\Pages\Install;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class AdminPage extends Page
{
    public function url(): string
    {
        return '/install/admin';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs('/install/admin');
    }

    /**
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@heading' => 'h2',
            '@name-input' => '#name',
            '@email-input' => '#email',
            '@password-input' => '#password',
            '@password-confirm-input' => '#password_confirmation',
            '@submit-button' => 'form button[type="submit"]',
            '@back-link' => 'a[href*="environment"]',
        ];
    }

    public function fillAdminForm(Browser $browser, array $config): void
    {
        $browser->type('@name-input', $config['name'] ?? 'Admin User')
            ->type('@email-input', $config['email'] ?? 'admin@example.com')
            ->type('@password-input', $config['password'] ?? 'password123')
            ->type('@password-confirm-input', $config['password_confirmation'] ?? 'password123');
    }
}
