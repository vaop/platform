<?php

declare(strict_types=1);

namespace Tests\Feature\Install;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstallMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->tearDownInstaller();
        parent::tearDown();
    }

    #[Test]
    public function redirect_if_not_installed_middleware_redirects_when_not_installed(): void
    {
        config(['vaop.installer.enabled' => true]);
        $this->markAsNotInstalled();

        $response = $this->get('/');

        $response->assertRedirect(route('install.welcome'));
    }

    #[Test]
    public function redirect_if_not_installed_middleware_allows_when_installed(): void
    {
        config(['vaop.installer.enabled' => true]);
        $this->markAsInstalled();

        $response = $this->get('/');

        $response->assertStatus(200);
    }

    #[Test]
    public function ensure_not_installed_middleware_redirects_when_installed(): void
    {
        config(['vaop.installer.enabled' => true]);
        $this->markAsInstalled();

        $response = $this->get(route('install.welcome'));

        $response->assertRedirect('/');
    }

    #[Test]
    public function ensure_installer_enabled_middleware_returns_404_when_disabled(): void
    {
        config(['vaop.installer.enabled' => false]);
        $this->markAsNotInstalled();

        $response = $this->get(route('install.welcome'));

        $response->assertStatus(404);
    }

    #[Test]
    public function installer_routes_accessible_when_not_installed(): void
    {
        config(['vaop.installer.enabled' => true]);
        $this->markAsNotInstalled();

        $response = $this->get(route('install.welcome'));

        $response->assertStatus(200);
    }
}
