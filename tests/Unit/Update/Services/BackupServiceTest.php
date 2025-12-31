<?php

declare(strict_types=1);

namespace Tests\Unit\Update\Services;

use PHPUnit\Framework\Attributes\Test;
use Services\Update\Exceptions\UpdateException;
use System\Filesystem\BackupService;
use Tests\TestCase;

class BackupServiceTest extends TestCase
{
    private BackupService $service;

    private string $testBackupPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BackupService;
        $this->testBackupPath = storage_path('app/backups');
    }

    protected function tearDown(): void
    {
        // Clean up test backups
        $pattern = $this->testBackupPath.'/backup-test-*.zip';
        foreach (glob($pattern) as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }

    #[Test]
    public function it_creates_backup_directory_if_not_exists(): void
    {
        $backupPath = $this->service->getBackupPath();

        // List should create the directory
        $this->service->list();

        $this->assertDirectoryExists($backupPath);
    }

    #[Test]
    public function it_creates_a_backup(): void
    {
        $version = 'test-'.uniqid();

        $backupFile = $this->service->create($version);

        $this->assertFileExists($backupFile);
        $this->assertStringContainsString("backup-{$version}-", $backupFile);
        $this->assertStringEndsWith('.zip', $backupFile);

        // Verify it's a valid zip
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($backupFile) === true);
        $this->assertGreaterThan(0, $zip->numFiles);
        $zip->close();

        // Clean up
        unlink($backupFile);
    }

    #[Test]
    public function it_lists_available_backups(): void
    {
        // Create a test backup
        $version = 'test-'.uniqid();
        $backupFile = $this->service->create($version);

        $backups = $this->service->list();

        $this->assertIsArray($backups);
        $this->assertNotEmpty($backups);

        $found = false;
        foreach ($backups as $backup) {
            $this->assertArrayHasKey('filename', $backup);
            $this->assertArrayHasKey('path', $backup);
            $this->assertArrayHasKey('size', $backup);
            $this->assertArrayHasKey('created', $backup);

            if ($backup['path'] === $backupFile) {
                $found = true;
            }
        }

        $this->assertTrue($found, 'Created backup not found in list');

        // Clean up
        unlink($backupFile);
    }

    #[Test]
    public function it_lists_backups_sorted_by_date_newest_first(): void
    {
        // Create two backups
        $version1 = 'test-older-'.uniqid();
        $backup1 = $this->service->create($version1);

        $version2 = 'test-newer-'.uniqid();
        $backup2 = $this->service->create($version2);

        // Manipulate file timestamps to ensure deterministic ordering
        // Set backup1 to be older (1 hour ago)
        touch($backup1, time() - 3600);
        // Set backup2 to be newer (current time)
        touch($backup2, time());

        // Clear any file stat cache
        clearstatcache();

        $backups = $this->service->list();

        // Find our test backups
        $testBackups = array_values(array_filter($backups, fn ($b) => str_contains($b['filename'], 'backup-test-')));

        $this->assertGreaterThanOrEqual(2, count($testBackups));

        // Verify order (newest first) - backup2 should come before backup1
        $this->assertStringContainsString('newer', $testBackups[0]['filename']);
        $this->assertStringContainsString('older', $testBackups[1]['filename']);

        // Clean up
        unlink($backup1);
        unlink($backup2);
    }

    #[Test]
    public function it_cleans_up_old_backups(): void
    {
        // Create 3 test backups
        $backups = [];
        for ($i = 0; $i < 3; $i++) {
            $version = 'test-'.uniqid();
            $backups[] = $this->service->create($version);
            usleep(100000); // 100ms delay
        }

        // Keep only 1
        $deleted = $this->service->cleanup(1);

        // At least 2 should be deleted (we created 3, keeping 1)
        // Note: There might be other backups from previous tests
        $this->assertGreaterThanOrEqual(2, $deleted);

        // Clean up remaining
        foreach ($backups as $backup) {
            @unlink($backup);
        }
    }

    #[Test]
    public function it_throws_exception_for_nonexistent_restore_file(): void
    {
        $this->expectException(UpdateException::class);
        $this->expectExceptionMessage(__('update.errors.restore_failed', ['reason' => 'Backup file not found: /nonexistent/backup.zip']));

        $this->service->restore('/nonexistent/backup.zip');
    }

    #[Test]
    public function it_can_restore_from_backup(): void
    {
        // Use a file that's included in backups (src/Config directory)
        $testFile = base_path('src/Config/.backup-test-'.uniqid().'.php');
        $originalContent = '<?php return ["test" => true];';

        // Create test file
        file_put_contents($testFile, $originalContent);

        try {
            // Create backup (this will include our test file)
            $version = 'test-'.uniqid();
            $backupFile = $this->service->create($version);

            // Verify backup was created
            $this->assertFileExists($backupFile);

            // Modify the test file
            $modifiedContent = '<?php return ["test" => false, "modified" => true];';
            file_put_contents($testFile, $modifiedContent);

            // Verify file was modified
            $this->assertEquals($modifiedContent, file_get_contents($testFile));

            // Restore from backup
            $this->service->restore($backupFile);

            // Verify file was restored to original content
            $this->assertEquals($originalContent, file_get_contents($testFile));

            // Clean up backup
            unlink($backupFile);
        } finally {
            // Always clean up test file
            @unlink($testFile);
        }
    }

    #[Test]
    public function it_returns_correct_backup_path(): void
    {
        $path = $this->service->getBackupPath();

        $this->assertEquals(storage_path('app/backups'), $path);
    }

    #[Test]
    public function it_includes_version_file_in_backup(): void
    {
        $version = 'test-'.uniqid();
        $backupFile = $this->service->create($version);

        $zip = new \ZipArchive;
        $zip->open($backupFile);

        // Check if VERSION file is included
        $versionIncluded = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === 'VERSION') {
                $versionIncluded = true;
                break;
            }
        }

        $zip->close();

        $this->assertTrue($versionIncluded, 'VERSION file should be included in backup');

        // Clean up
        unlink($backupFile);
    }

    #[Test]
    public function it_excludes_vendor_directory_from_backup(): void
    {
        $version = 'test-'.uniqid();
        $backupFile = $this->service->create($version);

        $zip = new \ZipArchive;
        $zip->open($backupFile);

        // Check that vendor is not included
        $vendorFound = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_starts_with($name, 'vendor/')) {
                $vendorFound = true;
                break;
            }
        }

        $zip->close();

        $this->assertFalse($vendorFound, 'vendor directory should not be included in backup');

        // Clean up
        unlink($backupFile);
    }

    #[Test]
    public function it_excludes_bootstrap_cache_from_backup(): void
    {
        $version = 'test-'.uniqid();
        $backupFile = $this->service->create($version);

        $zip = new \ZipArchive;
        $zip->open($backupFile);

        // Check that bootstrap/cache is not included
        $cacheFound = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_contains($name, 'bootstrap/cache/')) {
                $cacheFound = true;
                break;
            }
        }

        $zip->close();

        $this->assertFalse($cacheFound, 'bootstrap/cache directory should not be included in backup');

        // Clean up
        unlink($backupFile);
    }
}
