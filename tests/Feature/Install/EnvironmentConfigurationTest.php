<?php

declare(strict_types=1);

namespace Tests\Feature\Install;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnvironmentConfigurationTest extends TestCase
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
    public function environment_form_displays_correctly(): void
    {
        $response = $this->get(route('install.environment'));

        $response->assertStatus(200);
        $response->assertSee(__('install.environment.airline_name'));
        $response->assertSee(__('install.environment.app_url'));
    }

    #[Test]
    public function environment_form_contains_required_input_fields(): void
    {
        $response = $this->get(route('install.environment'));

        $response->assertStatus(200);
        // Verify form structure
        $response->assertSee('name="app_name"', false);
        $response->assertSee('name="app_url"', false);
    }

    #[Test]
    public function environment_configuration_requires_app_name(): void
    {
        $response = $this->post(route('install.environment.store'), [
            'app_name' => '',
            'app_url' => 'https://example.com',
        ]);

        $response->assertSessionHasErrors('app_name');
    }

    #[Test]
    public function environment_configuration_requires_valid_url(): void
    {
        $response = $this->post(route('install.environment.store'), [
            'app_name' => 'My Airline',
            'app_url' => 'not-a-valid-url',
        ]);

        $response->assertSessionHasErrors('app_url');
    }

    #[Test]
    public function environment_configuration_can_be_saved(): void
    {
        $response = $this->post(route('install.environment.store'), [
            'app_name' => 'My Virtual Airline',
            'app_url' => 'https://example.com',
        ]);

        $response->assertRedirect(route('install.admin'));
    }

    #[Test]
    public function environment_configuration_stores_values_in_session(): void
    {
        $this->post(route('install.environment.store'), [
            'app_name' => 'Test VA',
            'app_url' => 'https://test.example.com',
        ]);

        $this->assertEquals('Test VA', session('va_name'));
        $this->assertEquals('https://test.example.com', session('site_url'));
    }

    #[Test]
    public function environment_configuration_accepts_url_with_port(): void
    {
        $response = $this->post(route('install.environment.store'), [
            'app_name' => 'My Virtual Airline',
            'app_url' => 'https://example.com:8080',
        ]);

        $response->assertRedirect(route('install.admin'));
    }

    #[Test]
    public function environment_configuration_accepts_http_url(): void
    {
        $response = $this->post(route('install.environment.store'), [
            'app_name' => 'My Virtual Airline',
            'app_url' => 'http://localhost',
        ]);

        $response->assertRedirect(route('install.admin'));
    }

    #[Test]
    public function environment_configuration_strips_trailing_slash_from_url(): void
    {
        $this->post(route('install.environment.store'), [
            'app_name' => 'My Virtual Airline',
            'app_url' => 'https://example.com/',
        ]);

        $this->assertEquals('https://example.com', session('site_url'));
    }
}
