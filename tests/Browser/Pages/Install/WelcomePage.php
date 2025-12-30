<?php

declare(strict_types=1);

namespace Tests\Browser\Pages\Install;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class WelcomePage extends Page
{
    public function url(): string
    {
        return '/install';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs('/install');
    }

    /**
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@heading' => 'h2',
            '@continue-button' => 'a[href*="requirements"]',
        ];
    }
}
