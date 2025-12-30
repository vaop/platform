<?php

declare(strict_types=1);

namespace Tests\Browser\Pages\Install;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class FinalizePage extends Page
{
    public function url(): string
    {
        return '/install/finalize';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs('/install/finalize')
            ->assertSee(__('install.finalize.title'));
    }

    /**
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@heading' => 'h1',
            '@finalize-button' => 'button[type="submit"]',
            '@summary' => '.summary, .card',
        ];
    }
}
