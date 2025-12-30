<?php

declare(strict_types=1);

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class HomePage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs('/')
            ->assertSee('Welcome to');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@heading' => 'h1',
            '@login-button' => 'a[href*="login"]',
            '@register-button' => 'a[href*="register"]',
            '@dashboard-button' => 'a[href*="dashboard"]',
        ];
    }
}
