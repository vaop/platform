<?php

declare(strict_types=1);

namespace Tests\Feature\Install;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstallMiddlewareTest extends TestCase
{
    #[Test]
    public function redirect_if_not_installed_middleware_redirects_when_not_installed(): void
    {
        config(['vaop.installer.enabled' => true]);

        // Ensure not installed
        $installedPath = storage_path('installed');
        if (file_exists($installedPath)) {
            unlink($installedPath);
        }

        $response = $this->get('/');

        $response->assertRedirect(route('install.welcome'));
    }

    #[Test]
    public function redirect_if_not_installed_middleware_allows_when_installed(): void
    {
        config(['vaop.installer.enabled' => true]);

        // Mark as installed
        file_put_contents(storage_path('installed'), '');

        try {
            $response = $this->get('/');

            $response->assertStatus(200);
        } finally {
            unlink(storage_path('installed'));
        }
    }

    #[Test]
    public function ensure_not_installed_middleware_redirects_when_installed(): void
    {
        config(['vaop.installer.enabled' => true]);

        // Mark as installed
        file_put_contents(storage_path('installed'), '');

        try {
            $response = $this->get(route('install.welcome'));

            $response->assertRedirect('/');
        } finally {
            unlink(storage_path('installed'));
        }
    }

    #[Test]
    public function ensure_installer_enabled_middleware_returns_404_when_disabled(): void
    {
        config(['vaop.installer.enabled' => false]);

        $installedPath = storage_path('installed');
        if (file_exists($installedPath)) {
            unlink($installedPath);
        }

        $response = $this->get(route('install.welcome'));

        $response->assertStatus(404);
    }

    #[Test]
    public function installer_routes_are_not_affected_by_redirect_middleware(): void
    {
        config(['vaop.installer.enabled' => true]);

        $installedPath = storage_path('installed');
        if (file_exists($installedPath)) {
            unlink($installedPath);
        }

        // Installer routes should be accessible
        $response = $this->get(route('install.welcome'));

        $response->assertStatus(200);
    }
}
