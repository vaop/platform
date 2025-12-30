<?php

declare(strict_types=1);

namespace Tests\Unit\Install\Services;

use PHPUnit\Framework\Attributes\Test;
use System\Database\DatabaseValidator;
use Tests\TestCase;

/**
 * Note: These tests require a database connection to run properly.
 * They test the actual database validation behavior which requires real connections.
 */
class DatabaseValidatorTest extends TestCase
{
    private DatabaseValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new DatabaseValidator;
    }

    #[Test]
    public function it_returns_correct_structure_for_successful_connection(): void
    {
        $config = $this->getTestDatabaseConfig();
        $result = $this->validator->test($config);

        // Always verify structure regardless of connection success
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('version', $result);
        $this->assertIsBool($result['success']);
        $this->assertIsString($result['message']);
    }

    #[Test]
    public function successful_connection_returns_database_version(): void
    {
        $config = $this->getTestDatabaseConfig();
        $result = $this->validator->test($config);

        // Only assert version if connection was successful
        if ($result['success']) {
            $this->assertNotNull($result['version'], 'Version should be set on successful connection');
            $this->assertIsString($result['version']);
            $this->assertNotEmpty($result['version']);
        } else {
            $this->markTestSkipped('Database connection not available for this test');
        }
    }

    #[Test]
    public function it_returns_failure_for_invalid_host(): void
    {
        $config = [
            'driver' => 'mysql',
            'host' => 'invalid-host-'.uniqid().'.local',
            'port' => 3306,
            'database' => 'test',
            'username' => 'test',
            'password' => 'test',
        ];

        $result = $this->validator->test($config);

        $this->assertFalse($result['success'], 'Connection should fail for invalid host');
        $this->assertNull($result['version'], 'Version should be null on failure');
        $this->assertNotEmpty($result['message'], 'Error message should be provided');
    }

    #[Test]
    public function it_returns_failure_for_invalid_credentials(): void
    {
        $config = [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'testing'),
            'username' => 'invalid_user_'.uniqid(),
            'password' => 'invalid_password_'.uniqid(),
        ];

        $result = $this->validator->test($config);

        $this->assertFalse($result['success'], 'Connection should fail for invalid credentials');
        $this->assertNull($result['version'], 'Version should be null on failure');
    }

    #[Test]
    public function it_returns_failure_for_unknown_database(): void
    {
        $config = [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => 'nonexistent_database_'.uniqid(),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
        ];

        $result = $this->validator->test($config);

        $this->assertFalse($result['success'], 'Connection should fail for unknown database');
        $this->assertNull($result['version'], 'Version should be null on failure');
    }

    #[Test]
    public function it_handles_connection_refused_error(): void
    {
        $config = [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 65432, // Unlikely to have a service running
            'database' => 'test',
            'username' => 'test',
            'password' => 'test',
        ];

        $result = $this->validator->test($config);

        $this->assertFalse($result['success'], 'Connection should fail for refused connection');
        $this->assertNull($result['version']);
        $this->assertIsString($result['message']);
    }

    #[Test]
    public function it_uses_default_values_when_config_is_incomplete(): void
    {
        // Test with minimal config - should use defaults for missing values
        $config = [
            'username' => 'test',
            'password' => 'test',
        ];

        $result = $this->validator->test($config);

        // Should still return proper structure even if connection fails
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('version', $result);
    }

    #[Test]
    public function it_handles_mariadb_driver(): void
    {
        $config = [
            'driver' => 'mariadb',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'testing'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
        ];

        $result = $this->validator->test($config);

        // Verify it handles mariadb driver without error
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('version', $result);
    }

    /**
     * Get test database configuration from environment.
     */
    private function getTestDatabaseConfig(): array
    {
        return [
            'driver' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'testing'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
        ];
    }
}
