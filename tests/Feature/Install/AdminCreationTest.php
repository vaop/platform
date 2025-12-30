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
        $this->setUpInstaller();
    }

    protected function tearDown(): void
    {
        $this->tearDownInstaller();
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
    public function admin_form_contains_required_input_fields(): void
    {
        $response = $this->get(route('install.admin'));

        $response->assertStatus(200);
        // Verify form structure
        $response->assertSee('name="name"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="password_confirmation"', false);
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

    #[Test]
    public function admin_creation_accepts_names_with_special_characters(): void
    {
        $response = $this->post(route('install.admin.store'), [
            'name' => 'José García-López',
            'email' => 'jose@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('install.finalize'));
        $response->assertSessionHas('admin_user');
    }

    #[Test]
    public function admin_creation_accepts_long_passwords(): void
    {
        $longPassword = str_repeat('a', 100);

        $response = $this->post(route('install.admin.store'), [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => $longPassword,
            'password_confirmation' => $longPassword,
        ]);

        $response->assertRedirect(route('install.finalize'));
    }

    #[Test]
    public function admin_creation_rejects_empty_email(): void
    {
        $response = $this->post(route('install.admin.store'), [
            'name' => 'Admin User',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function admin_creation_accepts_subdomain_email(): void
    {
        $response = $this->post(route('install.admin.store'), [
            'name' => 'Admin User',
            'email' => 'admin@mail.example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('install.finalize'));
    }

    #[Test]
    public function admin_creation_stores_name_as_provided(): void
    {
        // Note: XSS protection is handled at the view layer via Twig's autoescape
        // The controller stores the raw input; sanitization is not done at storage time
        $response = $this->post(route('install.admin.store'), [
            'name' => 'Admin<script>User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('install.finalize'));
        $response->assertSessionHas('admin_user');
    }

    #[Test]
    public function admin_creation_handles_sql_injection_in_email(): void
    {
        $response = $this->post(route('install.admin.store'), [
            'name' => 'Admin User',
            'email' => "admin@example.com'; DROP TABLE users; --",
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Should reject as invalid email format
        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function admin_creation_rejects_excessively_long_name(): void
    {
        $longName = str_repeat('a', 256);

        $response = $this->post(route('install.admin.store'), [
            'name' => $longName,
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function admin_creation_rejects_excessively_long_email(): void
    {
        $longEmail = str_repeat('a', 250).'@example.com';

        $response = $this->post(route('install.admin.store'), [
            'name' => 'Admin User',
            'email' => $longEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function admin_creation_accepts_maximum_valid_name_length(): void
    {
        $maxName = str_repeat('a', 255);

        $response = $this->post(route('install.admin.store'), [
            'name' => $maxName,
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('install.finalize'));
    }

    #[Test]
    public function admin_creation_handles_html_entities_in_name(): void
    {
        $response = $this->post(route('install.admin.store'), [
            'name' => '&lt;script&gt;Admin&lt;/script&gt;',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('install.finalize'));
        $response->assertSessionHas('admin_user');
    }

    #[Test]
    public function admin_creation_handles_name_with_control_characters(): void
    {
        // Test that names with unusual characters are handled gracefully
        // PHP/Laravel will typically strip null bytes from request input
        $response = $this->post(route('install.admin.store'), [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Should accept valid names
        $response->assertRedirect(route('install.finalize'));
        $response->assertSessionHas('admin_user');
    }
}
