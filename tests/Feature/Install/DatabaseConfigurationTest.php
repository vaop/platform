<?php

declare(strict_types=1);

namespace Tests\Feature\Install;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DatabaseConfigurationTest extends TestCase
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
    public function database_form_displays_correctly(): void
    {
        $response = $this->get(route('install.database'));

        $response->assertStatus(200);
        $response->assertSee(__('install.database.host'));
        $response->assertSee(__('install.database.port'));
        $response->assertSee(__('install.database.name'));
        $response->assertSee(__('install.database.username'));
        $response->assertSee(__('install.database.password'));
    }

    #[Test]
    public function database_test_endpoint_returns_json(): void
    {
        $response = $this->postJson(route('install.database.test'), [
            'driver' => 'mysql',
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
            'database' => config('database.connections.mysql.database'),
            'username' => config('database.connections.mysql.username'),
            'password' => config('database.connections.mysql.password') ?? '',
        ]);

        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'version',
        ]);
    }

    #[Test]
    public function database_test_returns_error_for_invalid_credentials(): void
    {
        $response = $this->postJson(route('install.database.test'), [
            'driver' => 'mysql',
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
            'database' => 'test',
            'username' => 'invalid_user_'.uniqid(),
            'password' => 'invalid_password',
        ]);

        $response->assertJson([
            'success' => false,
        ]);
    }

    #[Test]
    public function database_configuration_can_be_saved(): void
    {
        $response = $this->post(route('install.database.store'), [
            'driver' => 'mysql',
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
            'database' => config('database.connections.mysql.database'),
            'username' => config('database.connections.mysql.username'),
            'password' => config('database.connections.mysql.password') ?? '',
        ]);

        $response->assertRedirect(route('install.environment'));
    }
}
