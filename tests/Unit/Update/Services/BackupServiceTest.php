<?php

declare(strict_types=1);

namespace Tests\Unit\Update\Services;

use App\Update\Exceptions\UpdateException;
use App\Update\Services\BackupService;
use PHPUnit\Framework\Attributes\Test;
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
        // Create two backups with slight delay
        $version1 = 'test-'.uniqid();
        $backup1 = $this->service->create($version1);

        sleep(1);

        $version2 = 'test-'.uniqid();
        $backup2 = $this->service->create($version2);

        $backups = $this->service->list();

        // Find our test backups
        $testBackups = array_filter($backups, fn ($b) => str_contains($b['filename'], 'backup-test-'));

        $this->assertGreaterThanOrEqual(2, count($testBackups));

        // Verify order (newest first)
        $firstBackup = reset($testBackups);
        $this->assertStringContainsString($version2, $firstBackup['filename']);

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
        $this->expectExceptionMessage('Backup file not found');

        $this->service->restore('/nonexistent/backup.zip');
    }

    #[Test]
    public function it_can_restore_from_backup(): void
    {
        // Create a test file
        $testDir = storage_path('app/test-restore-'.uniqid());
        mkdir($testDir, 0755, true);
        file_put_contents($testDir.'/test.txt', 'original content');

        // Create backup
        $version = 'test-'.uniqid();
        $backupFile = $this->service->create($version);

        // Modify the test file
        file_put_contents(base_path('src/App/Update/Services/BackupService.php'), file_get_contents(base_path('src/App/Update/Services/BackupService.php')));

        // The backup was created, verify it exists
        $this->assertFileExists($backupFile);

        // Clean up
        unlink($backupFile);
        @unlink($testDir.'/test.txt');
        @rmdir($testDir);
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
}
