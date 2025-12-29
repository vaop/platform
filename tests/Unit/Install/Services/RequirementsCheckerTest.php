<?php

declare(strict_types=1);

namespace Tests\Unit\Install\Services;

use App\Install\Services\RequirementsChecker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RequirementsCheckerTest extends TestCase
{
    private RequirementsChecker $checker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = new RequirementsChecker;
    }

    #[Test]
    public function it_checks_php_version(): void
    {
        $result = $this->checker->check();

        $this->assertArrayHasKey('php', $result);
        $this->assertArrayHasKey('required', $result['php']);
        $this->assertArrayHasKey('current', $result['php']);
        $this->assertArrayHasKey('passed', $result['php']);
        $this->assertEquals(PHP_VERSION, $result['php']['current']);
    }

    #[Test]
    public function it_passes_php_version_check_on_supported_version(): void
    {
        $result = $this->checker->check();

        // Since we're running tests on a supported PHP version, this should pass
        $this->assertTrue($result['php']['passed']);
    }

    #[Test]
    public function it_checks_required_extensions(): void
    {
        $result = $this->checker->check();

        $this->assertArrayHasKey('extensions', $result);
        $this->assertIsArray($result['extensions']);

        $expectedExtensions = [
            'pdo_mysql',
            'openssl',
            'json',
            'curl',
            'zip',
            'gd',
            'bcmath',
            'intl',
            'xml',
            'fileinfo',
        ];

        foreach ($expectedExtensions as $extension) {
            $this->assertArrayHasKey($extension, $result['extensions']);
            $this->assertArrayHasKey('name', $result['extensions'][$extension]);
            $this->assertArrayHasKey('passed', $result['extensions'][$extension]);
            $this->assertEquals($extension, $result['extensions'][$extension]['name']);
        }
    }

    #[Test]
    public function it_checks_required_directories(): void
    {
        $result = $this->checker->check();

        $this->assertArrayHasKey('directories', $result);
        $this->assertIsArray($result['directories']);

        $expectedDirectories = [
            'storage/app',
            'storage/framework',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
            'bootstrap/cache',
        ];

        foreach ($expectedDirectories as $directory) {
            $this->assertArrayHasKey($directory, $result['directories']);
            $this->assertArrayHasKey('path', $result['directories'][$directory]);
            $this->assertArrayHasKey('exists', $result['directories'][$directory]);
            $this->assertArrayHasKey('writable', $result['directories'][$directory]);
            $this->assertArrayHasKey('passed', $result['directories'][$directory]);
        }
    }

    #[Test]
    public function it_returns_passed_status(): void
    {
        $result = $this->checker->check();

        $this->assertArrayHasKey('passed', $result);
        $this->assertIsBool($result['passed']);
    }

    #[Test]
    public function all_passed_returns_true_when_all_requirements_met(): void
    {
        // In test environment, all requirements should be met
        $this->assertTrue($this->checker->allPassed());
    }
}
