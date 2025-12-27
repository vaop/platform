<?php

declare(strict_types=1);

namespace Tests\Unit\Install\Services;

use App\Install\Services\DatabaseValidator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DatabaseValidatorTest extends TestCase
{
    private DatabaseValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new DatabaseValidator();
    }

    #[Test]
    public function it_returns_success_for_valid_connection(): void
    {
        $config = [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'vaop_test'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
        ];

        $result = $this->validator->test($config);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('version', $result);

        if ($result['success']) {
            $this->assertNotNull($result['version']);
            $this->assertStringContainsString(__('install.database.connection_success', ['version' => '']), $result['message']);
        }
    }

    #[Test]
    public function it_returns_failure_for_invalid_host(): void
    {
        $config = [
            'driver' => 'mysql',
            'host' => 'invalid-host-that-does-not-exist.local',
            'port' => 3306,
            'database' => 'test',
            'username' => 'test',
            'password' => 'test',
        ];

        $result = $this->validator->test($config);

        $this->assertFalse($result['success']);
        $this->assertNull($result['version']);
        $this->assertNotEmpty($result['message']);
    }

    #[Test]
    public function it_returns_failure_for_invalid_credentials(): void
    {
        $config = [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => 'test',
            'username' => 'invalid_user_' . uniqid(),
            'password' => 'invalid_password',
        ];

        $result = $this->validator->test($config);

        $this->assertFalse($result['success']);
        $this->assertNull($result['version']);
    }

    #[Test]
    public function it_returns_failure_for_unknown_database(): void
    {
        $config = [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => 'nonexistent_database_' . uniqid(),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
        ];

        $result = $this->validator->test($config);

        $this->assertFalse($result['success']);
        $this->assertNull($result['version']);
    }
}
