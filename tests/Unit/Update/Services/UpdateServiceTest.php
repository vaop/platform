<?php

declare(strict_types=1);

namespace Tests\Unit\Update\Services;

use Illuminate\Support\Facades\Http;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Services\GitHub\GitHubReleaseService;
use Services\Update\Exceptions\UpdateException;
use Services\Update\UpdateService;
use System\Filesystem\BackupService;
use Tests\TestCase;

class UpdateServiceTest extends TestCase
{
    private UpdateService $service;

    private MockInterface $githubMock;

    private MockInterface $backupMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->githubMock = Mockery::mock(GitHubReleaseService::class);
        $this->backupMock = Mockery::mock(BackupService::class);

        $this->service = new UpdateService(
            $this->githubMock,
            $this->backupMock,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_returns_current_version_from_version_file(): void
    {
        $version = $this->service->getCurrentVersion();

        $this->assertIsString($version);
        $this->assertNotEmpty($version);
    }

    #[Test]
    public function it_returns_dev_version_when_no_version_file(): void
    {
        // Temporarily rename VERSION file
        $versionFile = base_path('VERSION');
        $tempFile = base_path('VERSION.bak');
        $exists = file_exists($versionFile);

        if ($exists) {
            rename($versionFile, $tempFile);
        }

        try {
            $version = $this->service->getCurrentVersion();
            $this->assertEquals('0.0.0-dev', $version);
        } finally {
            if ($exists) {
                rename($tempFile, $versionFile);
            }
        }
    }

    #[Test]
    public function it_checks_for_available_update(): void
    {
        $this->githubMock->shouldReceive('getLatestRelease')
            ->once()
            ->andReturn([
                'tag_name' => 'v99.0.0',
                'name' => 'Version 99.0.0',
                'body' => 'Release notes',
                'assets' => [],
            ]);

        $this->githubMock->shouldReceive('parseVersion')
            ->with('v99.0.0')
            ->andReturn('99.0.0');

        $this->githubMock->shouldReceive('compareVersions')
            ->with('99.0.0', Mockery::any())
            ->andReturn(1); // 99.0.0 > current

        $result = $this->service->checkForUpdate();

        $this->assertTrue($result['available']);
        $this->assertEquals('99.0.0', $result['latest']);
        $this->assertNotNull($result['release']);
    }

    #[Test]
    public function it_detects_no_update_available(): void
    {
        $currentVersion = $this->service->getCurrentVersion();

        $this->githubMock->shouldReceive('getLatestRelease')
            ->once()
            ->andReturn([
                'tag_name' => 'v0.0.1',
                'name' => 'Version 0.0.1',
                'body' => 'Old release',
                'assets' => [],
            ]);

        $this->githubMock->shouldReceive('parseVersion')
            ->with('v0.0.1')
            ->andReturn('0.0.1');

        $this->githubMock->shouldReceive('compareVersions')
            ->with('0.0.1', $currentVersion)
            ->andReturn(-1); // 0.0.1 < current (or equal)

        $result = $this->service->checkForUpdate();

        $this->assertFalse($result['available']);
        $this->assertNull($result['release']);
    }

    #[Test]
    public function it_throws_exception_when_already_up_to_date(): void
    {
        $currentVersion = $this->service->getCurrentVersion();

        $this->githubMock->shouldReceive('parseVersion')
            ->with('v0.0.1')
            ->andReturn('0.0.1');

        $this->githubMock->shouldReceive('compareVersions')
            ->with('0.0.1', $currentVersion)
            ->andReturn(0); // Same version

        $this->expectException(UpdateException::class);
        $this->expectExceptionMessage(__('update.errors.already_up_to_date', ['version' => $currentVersion]));

        $release = [
            'tag_name' => 'v0.0.1',
            'assets' => [],
        ];

        $this->service->update($release);
    }

    #[Test]
    public function it_propagates_github_exceptions(): void
    {
        $this->githubMock->shouldReceive('getLatestRelease')
            ->once()
            ->andThrow(UpdateException::noReleasesFound());

        $this->expectException(UpdateException::class);
        $this->expectExceptionMessage(__('update.errors.no_releases'));

        $this->service->checkForUpdate();
    }

    #[Test]
    public function it_calls_progress_callback_during_update(): void
    {
        $progressMessages = [];

        $this->githubMock->shouldReceive('parseVersion')
            ->andReturn('99.0.0');

        $this->githubMock->shouldReceive('compareVersions')
            ->andReturn(1);

        $this->githubMock->shouldReceive('getDownloadUrl')
            ->andReturn('https://example.com/release.tar.gz');

        $this->backupMock->shouldReceive('create')
            ->andReturn(storage_path('app/backups/test-backup.zip'));

        $this->backupMock->shouldReceive('restore')
            ->andReturnNull();

        $this->backupMock->shouldReceive('cleanup')
            ->andReturn(0);

        Http::fake([
            'example.com/*' => Http::response('fake tar content', 200),
        ]);

        $release = [
            'tag_name' => 'v99.0.0',
            'assets' => [],
        ];

        $exceptionThrown = false;
        try {
            $this->service->update($release, function ($message) use (&$progressMessages) {
                $progressMessages[] = $message;
            });
        } catch (\Exception $e) {
            $exceptionThrown = true;
            // Expected to fail at extraction stage since we're providing fake tar content
        }

        // The update should have progressed to at least the fetching stage
        $this->assertNotEmpty($progressMessages, 'Progress callback should have been called at least once');
        $this->assertContains(__('update.progress.fetching'), $progressMessages, 'Should include fetching progress message');

        // It's expected to fail at extraction because we're not providing a real archive
        $this->assertTrue($exceptionThrown, 'Update should fail at extraction stage with fake content');
    }

    #[Test]
    public function it_creates_backup_before_update(): void
    {
        $backupCreated = false;

        $this->githubMock->shouldReceive('parseVersion')
            ->andReturn('99.0.0');

        $this->githubMock->shouldReceive('compareVersions')
            ->andReturn(1);

        $this->githubMock->shouldReceive('getDownloadUrl')
            ->andReturn('https://example.com/release.tar.gz');

        // Verify backup is created with current version
        $this->backupMock->shouldReceive('create')
            ->once()
            ->withArgs(function ($version) use (&$backupCreated) {
                $backupCreated = is_string($version);

                return $backupCreated;
            })
            ->andReturn(storage_path('app/backups/test-backup.zip'));

        $this->backupMock->shouldReceive('restore')->andReturnNull();
        $this->backupMock->shouldReceive('cleanup')->andReturn(0);

        Http::fake([
            'example.com/*' => Http::response('fake tar content', 200),
        ]);

        $release = [
            'tag_name' => 'v99.0.0',
            'assets' => [],
        ];

        try {
            $this->service->update($release);
        } catch (\Exception $e) {
            // Expected to fail at extraction stage
        }

        // Verify backup was created
        $this->assertTrue($backupCreated, 'Backup should have been created with a version string');
    }

    #[Test]
    public function check_for_update_returns_required_structure(): void
    {
        $this->githubMock->shouldReceive('getLatestRelease')
            ->once()
            ->andReturn([
                'tag_name' => 'v1.0.0',
                'name' => 'Version 1.0.0',
                'body' => 'Release notes',
                'assets' => [],
            ]);

        $this->githubMock->shouldReceive('parseVersion')
            ->with('v1.0.0')
            ->andReturn('1.0.0');

        $this->githubMock->shouldReceive('compareVersions')
            ->andReturn(0); // Same version

        $result = $this->service->checkForUpdate();

        // Verify all required keys exist
        $this->assertArrayHasKey('available', $result);
        $this->assertArrayHasKey('current', $result);
        $this->assertArrayHasKey('latest', $result);
        $this->assertArrayHasKey('release', $result);

        // Verify types
        $this->assertIsBool($result['available']);
        $this->assertIsString($result['current']);
        $this->assertIsString($result['latest']);
    }
}
