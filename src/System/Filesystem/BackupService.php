<?php

declare(strict_types=1);

namespace System\Filesystem;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Services\Update\Exceptions\UpdateException;
use ZipArchive;

class BackupService
{
    private string $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
    }

    /**
     * Create a backup of the application.
     *
     * @throws UpdateException
     */
    public function create(string $version): string
    {
        $this->ensureBackupDirectory();

        $filename = "backup-{$version}-".date('Y-m-d-His').'.zip';
        $filepath = $this->backupPath.'/'.$filename;

        $zip = new ZipArchive;
        if ($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw UpdateException::backupFailed('Could not create backup archive');
        }

        $basePath = base_path();
        $directories = $this->getBackupDirectories();

        foreach ($directories as $dir) {
            $fullPath = $basePath.'/'.$dir;
            if (! is_dir($fullPath)) {
                continue;
            }

            $this->addDirectoryToZip($zip, $fullPath, $dir);
        }

        // Add root files
        $rootFiles = $this->getBackupRootFiles();
        foreach ($rootFiles as $file) {
            $fullPath = $basePath.'/'.$file;
            if (file_exists($fullPath)) {
                $zip->addFile($fullPath, $file);
            }
        }

        $zip->close();

        return $filepath;
    }

    /**
     * Restore from a backup file.
     *
     * @throws UpdateException
     */
    public function restore(string $backupFile): void
    {
        if (! file_exists($backupFile)) {
            throw UpdateException::restoreFailed("Backup file not found: {$backupFile}");
        }

        $zip = new ZipArchive;
        if ($zip->open($backupFile) !== true) {
            throw UpdateException::restoreFailed('Could not open backup archive');
        }

        $basePath = base_path();

        // Extract to a temporary location first
        $tempDir = sys_get_temp_dir().'/vaop-restore-'.uniqid();
        if (! $zip->extractTo($tempDir)) {
            $zip->close();
            throw UpdateException::restoreFailed('Could not extract backup archive');
        }

        $zip->close();

        // Copy files back to their original locations
        $this->copyDirectory($tempDir, $basePath);

        // Clean up temp directory
        $this->removeDirectory($tempDir);
    }

    /**
     * List available backups.
     *
     * @return array<int, array{filename: string, path: string, size: int, created: string}>
     */
    public function list(): array
    {
        $this->ensureBackupDirectory();

        $backups = [];
        $files = glob($this->backupPath.'/backup-*.zip');

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'path' => $file,
                'size' => filesize($file),
                'created' => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }

        // Sort by creation time, newest first
        usort($backups, fn ($a, $b) => strtotime($b['created']) - strtotime($a['created']));

        return $backups;
    }

    /**
     * Delete old backups, keeping only the specified number.
     */
    public function cleanup(int $keep = 5): int
    {
        $backups = $this->list();
        $deleted = 0;

        // Keep the newest backups
        $toDelete = array_slice($backups, $keep);

        foreach ($toDelete as $backup) {
            if (unlink($backup['path'])) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Get the backup directory path.
     */
    public function getBackupPath(): string
    {
        return $this->backupPath;
    }

    private function ensureBackupDirectory(): void
    {
        if (! is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    private function addDirectoryToZip(ZipArchive $zip, string $path, string $relativePath): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $itemPath = $item->getPathname();
            $relPath = $relativePath.'/'.substr($itemPath, strlen($path) + 1);

            // Skip certain patterns
            if ($this->shouldSkip($relPath)) {
                continue;
            }

            if ($item->isDir()) {
                $zip->addEmptyDir($relPath);
            } else {
                // Check file still exists before adding (handles race conditions)
                if (! file_exists($itemPath) || ! is_readable($itemPath)) {
                    logger()->warning("Backup: Skipping missing or unreadable file: {$relPath}");

                    continue;
                }

                $zip->addFile($itemPath, $relPath);
            }
        }
    }

    private function copyDirectory(string $source, string $destination): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = substr($item->getPathname(), strlen($source) + 1);
            $destPath = $destination.'/'.$relativePath;

            if ($item->isDir()) {
                if (! is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                copy($item->getPathname(), $destPath);
            }
        }
    }

    private function removeDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($path);
    }

    private function shouldSkip(string $path): bool
    {
        $skipPatterns = [
            'vendor',
            'node_modules',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/app/backups',
            'bootstrap/cache',
            '.git',
        ];

        foreach ($skipPatterns as $pattern) {
            // Match exact directory name or path containing the pattern with separator
            if ($path === $pattern || str_contains($path, $pattern.'/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Directories to include in backup.
     *
     * @return array<string>
     */
    private function getBackupDirectories(): array
    {
        return [
            'src',
            'config',
            'database',
            'public',
            'resources',
            'routes',
            'bootstrap',
            'lang',
        ];
    }

    /**
     * Root files to include in backup.
     *
     * @return array<string>
     */
    private function getBackupRootFiles(): array
    {
        return [
            'composer.json',
            'composer.lock',
            'package.json',
            'package-lock.json',
            'VERSION',
            'artisan',
            'vite.config.js',
            'tailwind.config.js',
            'postcss.config.js',
        ];
    }
}
