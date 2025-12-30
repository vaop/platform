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
        $this->setUpInstaller();
    }

    protected function tearDown(): void
    {
        $this->tearDownInstaller();
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
}
