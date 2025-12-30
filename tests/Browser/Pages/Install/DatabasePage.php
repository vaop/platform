<?php

declare(strict_types=1);

namespace Tests\Browser\Pages\Install;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class DatabasePage extends Page
{
    public function url(): string
    {
        return '/install/database';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs('/install/database');
    }

    /**
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@heading' => 'h2',
            '@host-input' => '#host',
            '@port-input' => '#port',
            '@database-input' => '#database',
            '@username-input' => '#username',
            '@password-input' => '#password',
            '@test-button' => '#test-connection',
            '@submit-button' => 'form button[type="submit"]',
            '@back-link' => 'a[href*="requirements"]',
        ];
    }

    public function fillDatabaseForm(Browser $browser, array $config): void
    {
        $browser->type('@host-input', $config['host'] ?? 'localhost')
            ->type('@port-input', $config['port'] ?? '3306')
            ->type('@database-input', $config['database'] ?? 'testing')
            ->type('@username-input', $config['username'] ?? 'root')
            ->type('@password-input', $config['password'] ?? '');
    }
}
