<?php

declare(strict_types=1);

namespace Tests\Feature\Install;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstallerRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure installer is enabled and app is not installed
        config(['vaop.installer.enabled' => true]);

        // Remove installed marker if it exists
        $installedPath = storage_path('installed');
        if (file_exists($installedPath)) {
            unlink($installedPath);
        }
    }

    protected function tearDown(): void
    {
        // Clean up installed marker
        $installedPath = storage_path('installed');
        if (file_exists($installedPath)) {
            unlink($installedPath);
        }

        parent::tearDown();
    }

    #[Test]
    public function welcome_page_is_accessible(): void
    {
        $response = $this->get(route('install.welcome'));

        $response->assertStatus(200);
        $response->assertSee(__('install.welcome.title'));
    }

    #[Test]
    public function requirements_page_is_accessible(): void
    {
        $response = $this->get(route('install.requirements'));

        $response->assertStatus(200);
        $response->assertSee(__('install.requirements.title'));
    }

    #[Test]
    public function database_page_is_accessible(): void
    {
        $response = $this->get(route('install.database'));

        $response->assertStatus(200);
        $response->assertSee(__('install.database.title'));
    }

    #[Test]
    public function environment_page_is_accessible(): void
    {
        $response = $this->get(route('install.environment'));

        $response->assertStatus(200);
        $response->assertSee(__('install.environment.title'));
    }

    #[Test]
    public function admin_page_is_accessible(): void
    {
        $response = $this->get(route('install.admin'));

        $response->assertStatus(200);
        $response->assertSee(__('install.admin.title'));
    }

    #[Test]
    public function finalize_page_redirects_without_admin_session(): void
    {
        $response = $this->get(route('install.finalize'));

        // Should redirect to admin page if no admin_user in session
        $response->assertRedirect(route('install.admin'));
    }

    #[Test]
    public function finalize_page_is_accessible_with_admin_session(): void
    {
        $this->withSession([
            'admin_user' => [
                'name' => 'Test Admin',
                'email' => 'admin@test.com',
                'password' => 'password123',
            ],
        ]);

        $response = $this->get(route('install.finalize'));

        $response->assertStatus(200);
        $response->assertSee(__('install.finalize.title'));
    }

    #[Test]
    public function installer_returns_404_when_disabled(): void
    {
        config(['vaop.installer.enabled' => false]);

        $response = $this->get(route('install.welcome'));

        $response->assertStatus(404);
    }

    #[Test]
    public function installer_redirects_when_already_installed(): void
    {
        // Create installed marker
        file_put_contents(storage_path('installed'), '');

        $response = $this->get(route('install.welcome'));

        $response->assertRedirect('/');
    }

    #[Test]
    public function non_installer_routes_redirect_to_installer_when_not_installed(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('install.welcome'));
    }
}
