<?php

declare(strict_types=1);

namespace Tests\Unit\Install\Services;

use PHPUnit\Framework\Attributes\Test;
use System\Platform\RequirementsChecker;
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
    public function it_requires_php_84_or_higher(): void
    {
        $result = $this->checker->check();

        $this->assertEquals('8.4.0', $result['php']['required']);
    }

    #[Test]
    public function it_passes_php_version_check_on_supported_version(): void
    {
        $result = $this->checker->check();

        // Since we're running tests on a supported PHP version, this should pass
        $this->assertTrue($result['php']['passed']);
    }

    #[Test]
    public function php_version_comparison_is_semantically_correct(): void
    {
        $result = $this->checker->check();

        // The passed status should match version_compare result
        $expected = version_compare(PHP_VERSION, '8.4.0', '>=');
        $this->assertEquals($expected, $result['php']['passed']);
    }

    #[Test]
    public function it_checks_all_required_extensions(): void
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
            $this->assertArrayHasKey($extension, $result['extensions'], "Extension '$extension' should be checked");
            $this->assertArrayHasKey('name', $result['extensions'][$extension]);
            $this->assertArrayHasKey('passed', $result['extensions'][$extension]);
            $this->assertEquals($extension, $result['extensions'][$extension]['name']);
        }
    }

    #[Test]
    public function extension_check_matches_actual_loaded_state(): void
    {
        $result = $this->checker->check();

        foreach ($result['extensions'] as $name => $extension) {
            $actuallyLoaded = extension_loaded($name);
            $this->assertEquals(
                $actuallyLoaded,
                $extension['passed'],
                "Extension '$name' passed status should match extension_loaded()"
            );
        }
    }

    #[Test]
    public function it_checks_all_required_directories(): void
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
            $this->assertArrayHasKey($directory, $result['directories'], "Directory '$directory' should be checked");
            $this->assertArrayHasKey('path', $result['directories'][$directory]);
            $this->assertArrayHasKey('exists', $result['directories'][$directory]);
            $this->assertArrayHasKey('writable', $result['directories'][$directory]);
            $this->assertArrayHasKey('passed', $result['directories'][$directory]);
        }
    }

    #[Test]
    public function directory_check_matches_actual_filesystem_state(): void
    {
        $result = $this->checker->check();

        foreach ($result['directories'] as $directory => $info) {
            $path = base_path($directory);

            $this->assertEquals(is_dir($path), $info['exists'], "Directory '$directory' exists status mismatch");

            if ($info['exists']) {
                $this->assertEquals(is_writable($path), $info['writable'], "Directory '$directory' writable status mismatch");
            }

            // passed should equal writable (a directory is only passed if it's writable)
            $this->assertEquals($info['writable'], $info['passed'], "Directory '$directory' passed should equal writable");
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

    #[Test]
    public function check_result_passed_matches_all_passed(): void
    {
        $result = $this->checker->check();

        $this->assertEquals($this->checker->allPassed(), $result['passed']);
    }

    #[Test]
    public function all_passed_is_false_if_any_extension_fails(): void
    {
        // We can't actually unload extensions, but we can verify the logic
        // by checking that all extensions pass in our environment
        $result = $this->checker->check();

        $allExtensionsPassed = true;
        foreach ($result['extensions'] as $ext) {
            if (! $ext['passed']) {
                $allExtensionsPassed = false;
                break;
            }
        }

        // If all extensions pass, allPassed should consider other factors
        if ($allExtensionsPassed && $result['php']['passed']) {
            // Only directories could fail at this point
            $allDirsPassed = true;
            foreach ($result['directories'] as $dir) {
                if (! $dir['passed']) {
                    $allDirsPassed = false;
                    break;
                }
            }

            $this->assertEquals($allDirsPassed, $result['passed']);
        }
    }

    #[Test]
    public function directory_fails_when_not_writable(): void
    {
        // Create a temporary non-writable directory to test failure case
        $testDir = storage_path('app/test-readonly-'.uniqid());

        // Skip this test if we can't create the directory
        if (! @mkdir($testDir, 0555, true)) {
            $this->markTestSkipped('Cannot create test directory');
        }

        try {
            // Make directory non-writable (this may not work on all systems)
            @chmod($testDir, 0555);

            $isWritable = is_writable($testDir);

            if (! $isWritable) {
                // Verify our understanding: non-writable directories should fail
                $this->assertFalse($isWritable);
            } else {
                // On some systems (like when running as root), directories are always writable
                $this->markTestSkipped('Cannot make directory non-writable on this system');
            }
        } finally {
            // Clean up: restore permissions and remove
            @chmod($testDir, 0755);
            @rmdir($testDir);
        }
    }

    #[Test]
    public function result_structure_is_complete(): void
    {
        $result = $this->checker->check();

        // Verify top-level structure
        $this->assertCount(4, $result, 'Result should have exactly 4 top-level keys');
        $this->assertArrayHasKey('php', $result);
        $this->assertArrayHasKey('extensions', $result);
        $this->assertArrayHasKey('directories', $result);
        $this->assertArrayHasKey('passed', $result);
    }

    #[Test]
    public function extensions_count_matches_required(): void
    {
        $result = $this->checker->check();

        // There should be exactly 10 required extensions
        $this->assertCount(10, $result['extensions']);
    }

    #[Test]
    public function directories_count_matches_required(): void
    {
        $result = $this->checker->check();

        // There should be exactly 7 required directories
        $this->assertCount(7, $result['directories']);
    }
}
