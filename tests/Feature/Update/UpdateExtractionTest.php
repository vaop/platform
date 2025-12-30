<?php

declare(strict_types=1);

namespace Tests\Feature\Update;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Services\GitHub\GitHubReleaseService;
use Services\Update\UpdateService;
use System\Filesystem\BackupService;
use Tests\TestCase;
use ZipArchive;

class UpdateExtractionTest extends TestCase
{
    private string $testUpdatePath;

    private string $testArchivePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testUpdatePath = storage_path('app/test-update-'.uniqid());
        $this->testArchivePath = storage_path('app/test-archive-'.uniqid().'.zip');

        // Create test directory
        if (! is_dir($this->testUpdatePath)) {
            mkdir($this->testUpdatePath, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (file_exists($this->testArchivePath)) {
            unlink($this->testArchivePath);
        }

        if (is_dir($this->testUpdatePath)) {
            File::deleteDirectory($this->testUpdatePath);
        }

        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function backup_service_returns_correct_path(): void
    {
        $backupService = new BackupService;

        $path = $backupService->getBackupPath();

        $this->assertEquals(storage_path('app/backups'), $path);
    }

    #[Test]
    public function backup_service_creates_directory_if_missing(): void
    {
        $backupService = new BackupService;

        // List method should ensure directory exists
        $backupService->list();

        $this->assertDirectoryExists($backupService->getBackupPath());
    }

    #[Test]
    public function update_check_returns_correct_structure(): void
    {
        $githubMock = Mockery::mock(GitHubReleaseService::class);
        $backupMock = Mockery::mock(BackupService::class);

        $githubMock->shouldReceive('getLatestRelease')
            ->once()
            ->andReturn([
                'tag_name' => 'v1.0.0',
                'name' => 'Version 1.0.0',
                'body' => 'Release notes here',
                'published_at' => '2025-01-15T10:00:00Z',
                'assets' => [],
            ]);

        $githubMock->shouldReceive('parseVersion')
            ->with('v1.0.0')
            ->andReturn('1.0.0');

        $githubMock->shouldReceive('compareVersions')
            ->andReturn(1); // Update available

        $service = new UpdateService($githubMock, $backupMock);
        $result = $service->checkForUpdate();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('available', $result);
        $this->assertArrayHasKey('current', $result);
        $this->assertArrayHasKey('latest', $result);
        $this->assertArrayHasKey('release', $result);
        $this->assertTrue($result['available']);
        $this->assertEquals('1.0.0', $result['latest']);
    }

    #[Test]
    public function update_creates_backup_and_calls_progress(): void
    {
        $githubMock = Mockery::mock(GitHubReleaseService::class);
        $backupMock = Mockery::mock(BackupService::class);

        $progressMessages = [];
        $backupCreated = false;

        $githubMock->shouldReceive('parseVersion')
            ->andReturn('99.0.0');

        $githubMock->shouldReceive('compareVersions')
            ->andReturn(1);

        $githubMock->shouldReceive('getDownloadUrl')
            ->andReturn('https://example.com/release.tar.gz');

        $backupMock->shouldReceive('create')
            ->once()
            ->andReturnUsing(function () use (&$backupCreated) {
                $backupCreated = true;

                return storage_path('app/backups/test-backup.zip');
            });

        $backupMock->shouldReceive('restore')->andReturnNull();
        $backupMock->shouldReceive('cleanup')->andReturn(0);

        Http::fake([
            'example.com/*' => Http::response('fake content', 200),
        ]);

        $service = new UpdateService($githubMock, $backupMock);

        try {
            $service->update(
                ['tag_name' => 'v99.0.0', 'assets' => []],
                function ($message) use (&$progressMessages) {
                    $progressMessages[] = $message;
                }
            );
        } catch (\Exception $e) {
            // Expected to fail at extraction
        }

        $this->assertTrue($backupCreated, 'Backup should have been created');
        $this->assertNotEmpty($progressMessages, 'Progress callback should have been called');
    }

    #[Test]
    public function backup_list_returns_array(): void
    {
        $backupService = new BackupService;

        $list = $backupService->list();

        $this->assertIsArray($list);
    }

    #[Test]
    public function version_file_determines_current_version(): void
    {
        $githubMock = Mockery::mock(GitHubReleaseService::class);
        $backupMock = Mockery::mock(BackupService::class);

        $service = new UpdateService($githubMock, $backupMock);

        $version = $service->getCurrentVersion();

        $this->assertIsString($version);
        $this->assertNotEmpty($version);

        // Should match VERSION file if it exists, otherwise return dev version
        $versionFile = base_path('VERSION');
        if (file_exists($versionFile)) {
            $this->assertEquals(trim(file_get_contents($versionFile)), $version);
        } else {
            $this->assertEquals('0.0.0-dev', $version);
        }
    }
}
