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
    public function environment_form_displays_correctly(): void
    {
        $response = $this->get(route('install.environment'));

        $response->assertStatus(200);
        $response->assertSee(__('install.environment.airline_name'));
        $response->assertSee(__('install.environment.app_url'));
        $response->assertSee(__('install.environment.timezone'));
    }

    #[Test]
    public function environment_form_shows_timezone_options(): void
    {
        $response = $this->get(route('install.environment'));

        $response->assertStatus(200);
        $response->assertSee('UTC');
        $response->assertSee('America/New_York');
        $response->assertSee('Europe/London');
    }

    #[Test]
    public function environment_configuration_requires_app_name(): void
    {
        $response = $this->post(route('install.environment.store'), [
            'app_name' => '',
            'app_url' => 'https://example.com',
            'timezone' => 'UTC',
        ]);

        $response->assertSessionHasErrors('app_name');
    }

    #[Test]
    public function environment_configuration_requires_valid_url(): void
    {
        $response = $this->post(route('install.environment.store'), [
            'app_name' => 'My Airline',
            'app_url' => 'not-a-valid-url',
            'timezone' => 'UTC',
        ]);

        $response->assertSessionHasErrors('app_url');
    }

    #[Test]
    public function environment_configuration_can_be_saved(): void
    {
        $response = $this->post(route('install.environment.store'), [
            'app_name' => 'My Virtual Airline',
            'app_url' => 'https://example.com',
            'timezone' => 'UTC',
        ]);

        $response->assertRedirect(route('install.admin'));
    }
}
