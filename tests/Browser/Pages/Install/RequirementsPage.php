<?php

declare(strict_types=1);

namespace Tests\Browser\Pages\Install;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class RequirementsPage extends Page
{
    public function url(): string
    {
        return '/install/requirements';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs('/install/requirements');
    }

    /**
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@heading' => 'h2',
            '@continue-button' => 'form button[type="submit"]',
            '@back-link' => 'a[href*="install"]',
            '@requirements-list' => '.space-y-6',
        ];
    }
}
