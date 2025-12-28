<?php

declare(strict_types=1);

namespace Tests\Feature\Update;

use App\Update\Exceptions\UpdateException;
use App\Update\Services\BackupService;
use App\Update\Services\UpdateService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_shows_update_available_with_check_option(): void
    {
        // Use the real GitHub API - this tests actual update check functionality
        // Since there's a real release (v0.0.2), the update should be available
        $this->artisan('vaop:update', ['--check' => true])
            ->expectsOutputToContain('Current version:')
            ->expectsOutputToContain('Latest version:')
            ->expectsOutputToContain('An update is available!')
            ->assertSuccessful();
    }

    #[Test]
    public function it_shows_up_to_date_message_when_no_update(): void
    {
        $this->mock(UpdateService::class, function (MockInterface $mock) {
            $mock->shouldReceive('checkForUpdate')
                ->once()
                ->andReturn([
                    'available' => false,
                    'current' => '1.0.0',
                    'latest' => '1.0.0',
                    'release' => null,
                ]);
        });

        $this->artisan('vaop:update', ['--check' => true])
            ->expectsOutputToContain('You are running the latest version.')
            ->assertSuccessful();
    }

    #[Test]
    public function it_shows_error_when_no_releases_found(): void
    {
        $this->mock(UpdateService::class, function (MockInterface $mock) {
            $mock->shouldReceive('checkForUpdate')
                ->once()
                ->andThrow(UpdateException::noReleasesFound());
        });

        $this->artisan('vaop:update', ['--check' => true])
            ->expectsOutputToContain('No releases found')
            ->assertFailed();
    }

    #[Test]
    public function it_lists_available_backups(): void
    {
        $this->mock(BackupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('list')
                ->once()
                ->andReturn([
                    [
                        'filename' => 'backup-1.0.0-2024-01-15-120000.zip',
                        'path' => '/storage/app/backups/backup-1.0.0-2024-01-15-120000.zip',
                        'size' => 1024 * 1024 * 5,
                        'created' => '2024-01-15 12:00:00',
                    ],
                    [
                        'filename' => 'backup-0.9.0-2024-01-10-100000.zip',
                        'path' => '/storage/app/backups/backup-0.9.0-2024-01-10-100000.zip',
                        'size' => 1024 * 1024 * 4,
                        'created' => '2024-01-10 10:00:00',
                    ],
                ]);
        });

        $this->artisan('vaop:update', ['--list-backups' => true])
            ->expectsOutputToContain('Available backups:')
            ->expectsOutputToContain('backup-1.0.0')
            ->expectsOutputToContain('backup-0.9.0')
            ->assertSuccessful();
    }

    #[Test]
    public function it_shows_no_backups_message(): void
    {
        $this->mock(BackupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('list')
                ->once()
                ->andReturn([]);
        });

        $this->artisan('vaop:update', ['--list-backups' => true])
            ->expectsOutputToContain('No backups found.')
            ->assertSuccessful();
    }

    #[Test]
    public function it_shows_error_for_nonexistent_backup_restore(): void
    {
        $this->mock(BackupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getBackupPath')
                ->andReturn(storage_path('app/backups'));
        });

        $this->artisan('vaop:update', ['--restore' => 'nonexistent.zip'])
            ->expectsOutputToContain('Backup file not found')
            ->assertFailed();
    }

    #[Test]
    public function it_prompts_for_confirmation_before_update(): void
    {
        $this->mock(UpdateService::class, function (MockInterface $mock) {
            $mock->shouldReceive('checkForUpdate')
                ->once()
                ->andReturn([
                    'available' => true,
                    'current' => '0.0.0-dev',
                    'latest' => '99.0.0',
                    'release' => [
                        'tag_name' => 'v99.0.0',
                        'name' => 'Version 99.0.0',
                        'body' => 'Test release notes',
                        'published_at' => '2024-01-15T10:00:00Z',
                        'assets' => [],
                    ],
                ]);
        });

        $this->artisan('vaop:update')
            ->expectsOutputToContain('An update is available!')
            ->expectsConfirmation('Do you want to update now?', 'no')
            ->expectsOutputToContain('Update cancelled.')
            ->assertSuccessful();
    }

    #[Test]
    public function it_skips_confirmation_with_force_option(): void
    {
        $this->mock(UpdateService::class, function (MockInterface $mock) {
            $mock->shouldReceive('checkForUpdate')
                ->once()
                ->andReturn([
                    'available' => true,
                    'current' => '0.0.0-dev',
                    'latest' => '99.0.0',
                    'release' => [
                        'tag_name' => 'v99.0.0',
                        'name' => 'Version 99.0.0',
                        'body' => 'Test release notes',
                        'published_at' => '2024-01-15T10:00:00Z',
                        'assets' => [],
                    ],
                ]);

            $mock->shouldReceive('update')
                ->once()
                ->andReturnNull();
        });

        // With --force, it skips confirmation and calls update
        $this->artisan('vaop:update', ['--force' => true])
            ->expectsOutputToContain('An update is available!')
            ->expectsOutputToContain('Update completed successfully!')
            ->assertSuccessful();
    }

    #[Test]
    public function it_displays_release_notes(): void
    {
        $this->mock(UpdateService::class, function (MockInterface $mock) {
            $mock->shouldReceive('checkForUpdate')
                ->once()
                ->andReturn([
                    'available' => true,
                    'current' => '0.0.0-dev',
                    'latest' => '99.0.0',
                    'release' => [
                        'tag_name' => 'v99.0.0',
                        'name' => 'Version 99.0.0',
                        'body' => "## What's New\n- Feature 1\n- Feature 2\n\n## Bug Fixes\n- Fix 1",
                        'published_at' => '2024-01-15T10:00:00Z',
                        'assets' => [],
                    ],
                ]);
        });

        $this->artisan('vaop:update', ['--check' => true])
            ->expectsOutputToContain('Release notes:')
            ->expectsOutputToContain("What's New")
            ->expectsOutputToContain('Feature 1')
            ->assertSuccessful();
    }

    #[Test]
    public function it_prompts_for_restore_confirmation(): void
    {
        // Create a temporary backup file
        $backupPath = storage_path('app/backups');
        if (! is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $testBackup = $backupPath.'/test-restore-backup.zip';
        $zip = new \ZipArchive;
        $zip->open($testBackup, \ZipArchive::CREATE);
        $zip->addFromString('test.txt', 'test content');
        $zip->close();

        try {
            $this->mock(BackupService::class, function (MockInterface $mock) {
                $mock->shouldReceive('getBackupPath')
                    ->andReturn(storage_path('app/backups'));
            });

            $this->artisan('vaop:update', ['--restore' => 'test-restore-backup.zip'])
                ->expectsOutputToContain('This will restore your application')
                ->expectsConfirmation('Are you sure you want to restore this backup?', 'no')
                ->expectsOutputToContain('Restore cancelled.')
                ->assertSuccessful();
        } finally {
            @unlink($testBackup);
        }
    }
}
