<?php

declare(strict_types=1);

namespace App\Console\Commands\Vaop;

use App\Update\Exceptions\UpdateException;
use App\Update\Services\BackupService;
use App\Update\Services\GitHubReleaseService;
use App\Update\Services\UpdateService;
use Illuminate\Console\Command;

class UpdateCommand extends Command
{
    protected $signature = 'vaop:update
        {--check : Only check for updates without installing}
        {--force : Skip confirmation prompts}
        {--no-backup : Skip creating a backup before updating}
        {--list-backups : List available backups}
        {--restore= : Restore from a specific backup file}';

    protected $description = 'Update VAOP to the latest version';

    public function handle(
        UpdateService $updateService,
        GitHubReleaseService $github,
        BackupService $backup,
    ): int {
        // Handle backup listing
        if ($this->option('list-backups')) {
            return $this->listBackups($backup);
        }

        // Handle backup restoration
        if ($restoreFile = $this->option('restore')) {
            return $this->restoreBackup($backup, $restoreFile);
        }

        // Check for updates
        try {
            $this->info(__('update.checking'));
            $result = $updateService->checkForUpdate();
        } catch (UpdateException $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        $this->newLine();
        $this->line(__('update.current_version', ['version' => "<info>{$result['current']}</info>"]));
        $this->line(__('update.latest_version', ['version' => "<info>{$result['latest']}</info>"]));
        $this->newLine();

        if (! $result['available']) {
            $this->info(__('update.up_to_date'));

            return Command::SUCCESS;
        }

        // If only checking, stop here
        if ($this->option('check')) {
            $this->warn(__('update.update_available'));
            $this->displayReleaseNotes($result['release']);

            return Command::SUCCESS;
        }

        // Display release notes
        $this->warn(__('update.update_available'));
        $this->displayReleaseNotes($result['release']);

        // Confirm update
        if (! $this->option('force')) {
            if (! $this->confirm(__('update.confirm_update'), true)) {
                $this->info(__('update.update_cancelled'));

                return Command::SUCCESS;
            }
        }

        // Perform update
        return $this->performUpdate($updateService, $result['release']);
    }

    private function performUpdate(UpdateService $updateService, array $release): int
    {
        $this->newLine();

        try {
            $updateService->update($release, function (string $message) {
                $this->line("  <comment>→</comment> {$message}");
            });

            $this->newLine();
            $this->info(__('update.update_completed'));

            return Command::SUCCESS;

        } catch (UpdateException $e) {
            $this->newLine();
            $this->error(__('update.update_failed', ['error' => $e->getMessage()]));

            return Command::FAILURE;
        }
    }

    private function displayReleaseNotes(array $release): void
    {
        $notes = $release['body'] ?? '';
        $publishedAt = $release['published_at'] ?? '';

        if ($publishedAt) {
            $date = date('F j, Y', strtotime($publishedAt));
            $this->line(__('update.released', ['date' => "<comment>{$date}</comment>"]));
        }

        if ($notes) {
            $this->newLine();
            $this->line('<comment>'.__('update.release_notes').'</comment>');
            $this->line('─────────────────────────────────────');

            // Truncate very long release notes
            $lines = explode("\n", $notes);
            $maxLines = 20;

            if (count($lines) > $maxLines) {
                $lines = array_slice($lines, 0, $maxLines);
                $lines[] = '';
                $lines[] = '<comment>'.__('update.truncated').'</comment>';
            }

            foreach ($lines as $line) {
                $this->line("  {$line}");
            }

            $this->line('─────────────────────────────────────');
        }

        $this->newLine();
    }

    private function listBackups(BackupService $backup): int
    {
        $backups = $backup->list();

        if (empty($backups)) {
            $this->info(__('update.no_backups'));

            return Command::SUCCESS;
        }

        $this->info(__('update.available_backups'));
        $this->newLine();

        $headers = [
            __('update.table.filename'),
            __('update.table.size'),
            __('update.table.created'),
        ];
        $rows = [];

        foreach ($backups as $b) {
            $rows[] = [
                $b['filename'],
                $this->formatBytes($b['size']),
                $b['created'],
            ];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->line(__('update.restore_usage'));
        $this->line('  <comment>php artisan vaop:update --restore=backup-x.x.x-YYYY-MM-DD-HHMMSS.zip</comment>');

        return Command::SUCCESS;
    }

    private function restoreBackup(BackupService $backup, string $file): int
    {
        // If just filename, prepend the backup path
        if (! str_contains($file, '/')) {
            $file = $backup->getBackupPath().'/'.$file;
        }

        if (! file_exists($file)) {
            $this->error(__('update.backup_file_not_found', ['file' => $file]));

            return Command::FAILURE;
        }

        $this->warn(__('update.restore_warning'));
        $this->line("  {$file}");
        $this->newLine();
        $this->warn(__('update.restore_overwrite'));

        if (! $this->option('force')) {
            if (! $this->confirm(__('update.confirm_restore'), false)) {
                $this->info(__('update.restore_cancelled'));

                return Command::SUCCESS;
            }
        }

        try {
            $this->info(__('update.restoring'));
            $backup->restore($file);

            $this->newLine();
            $this->info(__('update.restore_completed'));
            $this->line(__('update.restore_hint', [
                'composer' => '<comment>composer install</comment>',
                'migrate' => '<comment>php artisan migrate</comment>',
            ]));

            return Command::SUCCESS;

        } catch (UpdateException $e) {
            $this->error(__('update.update_failed', ['error' => $e->getMessage()]));

            return Command::FAILURE;
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
