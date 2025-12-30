<?php

declare(strict_types=1);

namespace Tests\Feature\Update;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Services\Update\Exceptions\UpdateException;
use Services\Update\UpdateService;
use System\Filesystem\BackupService;
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
            ->expectsOutputToContain(__('update.current_version', ['version' => '']))
            ->expectsOutputToContain(__('update.latest_version', ['version' => '']))
            ->expectsOutputToContain(__('update.update_available'))
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
            ->expectsOutputToContain(__('update.up_to_date'))
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
            ->expectsOutputToContain(__('update.errors.no_releases'))
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
            ->expectsOutputToContain(__('update.available_backups'))
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
            ->expectsOutputToContain(__('update.no_backups'))
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
            ->expectsOutputToContain(__('update.backup_file_not_found', ['file' => '']))
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
            ->expectsOutputToContain(__('update.update_available'))
            ->expectsConfirmation(__('update.confirm_update'), 'no')
            ->expectsOutputToContain(__('update.update_cancelled'))
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
            ->expectsOutputToContain(__('update.update_available'))
            ->expectsOutputToContain(__('update.update_completed'))
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
            ->expectsOutputToContain(__('update.release_notes'))
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
                ->expectsOutputToContain(__('update.restore_warning'))
                ->expectsConfirmation(__('update.confirm_restore'), 'no')
                ->expectsOutputToContain(__('update.restore_cancelled'))
                ->assertSuccessful();
        } finally {
            @unlink($testBackup);
        }
    }
}
