<?php

declare(strict_types=1);

namespace Tests\Feature\Install;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminCreationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['vaop.installer.enabled' => true]);

        $installedPath = storage_path('installed');
        if (file_exists($installedPath)) {
            unlink($installedPath);
        }
    }

    protected function tearDown(): void
    {
        $installedPath = storage_path('installed');
        if (file_exists($installedPath)) {
            unlink($installedPath);
        }

        parent::tearDown();
    }

    #[Test]
    public function admin_form_displays_correctly(): void
    {
        $response = $this->get(route('install.admin'));

        $response->assertStatus(200);
        $response->assertSee(__('install.admin.name'));
        $response->assertSee(__('install.admin.email'));
        $response->assertSee(__('install.admin.password'));
        $response->assertSee(__('install.admin.password_confirm'));
    }

    #[Test]
    public function admin_creation_requires_name(): void
    {
        $response = $this->post(route('install.admin.store'), [
            'name' => '',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function admin_creation_requires_valid_email(): void
    {
        $response = $this->post(route('install.admin.store'), [
            'name' => 'Admin User',
            'email' => 'not-a-valid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function admin_creation_requires_password_confirmation(): void
    {
        $response = $this->post(route('install.admin.store'), [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ]);

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function admin_creation_requires_minimum_password_length(): void
    {
        $response = $this->post(route('install.admin.store'), [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function admin_creation_stores_data_in_session(): void
    {
        $response = $this->post(route('install.admin.store'), [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('install.finalize'));
        $response->assertSessionHas('admin_user');
    }
}
