<?php

declare(strict_types=1);

namespace Services\Update;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Services\GitHub\GitHubReleaseService;
use Services\Update\Exceptions\UpdateException;
use System\Filesystem\BackupService;
use ZipArchive;

class UpdateService
{
    private string $tempPath;

    public function __construct(
        private readonly GitHubReleaseService $github,
        private readonly BackupService $backup,
    ) {
        $this->tempPath = storage_path('app/updates');
    }

    /**
     * Get the current installed version.
     */
    public function getCurrentVersion(): string
    {
        $versionFile = base_path('VERSION');

        if (! file_exists($versionFile)) {
            return '0.0.0-dev';
        }

        return trim(file_get_contents($versionFile));
    }

    /**
     * Check if an update is available.
     *
     * @return array{available: bool, current: string, latest: string, release: ?array}
     *
     * @throws UpdateException
     */
    public function checkForUpdate(): array
    {
        $current = $this->getCurrentVersion();
        $release = $this->github->getLatestRelease();
        $latest = $this->github->parseVersion($release['tag_name']);

        $available = $this->github->compareVersions($latest, $current) > 0;

        return [
            'available' => $available,
            'current' => $current,
            'latest' => $latest,
            'release' => $available ? $release : null,
        ];
    }

    /**
     * Perform the update.
     *
     * @param  callable|null  $progress  Callback for progress updates
     *
     * @throws UpdateException
     */
    public function update(?array $release = null, ?callable $progress = null): void
    {
        $progress ??= fn () => null;

        // Step 1: Get release info
        $progress(__('update.progress.fetching'));
        $release ??= $this->github->getLatestRelease();
        $targetVersion = $this->github->parseVersion($release['tag_name']);
        $currentVersion = $this->getCurrentVersion();

        if ($this->github->compareVersions($targetVersion, $currentVersion) <= 0) {
            throw UpdateException::alreadyUpToDate($currentVersion);
        }

        // Step 2: Check disk space
        $progress(__('update.progress.checking_disk'));
        $this->checkDiskSpace();

        // Step 3: Create backup
        $progress(__('update.progress.creating_backup'));
        $backupFile = $this->backup->create($currentVersion);

        try {
            // Step 4: Download release
            $progress(__('update.progress.downloading'));
            $archivePath = $this->downloadRelease($release);

            // Step 5: Extract and apply update
            $progress(__('update.progress.extracting'));
            $this->extractAndApply($archivePath, $release['tag_name']);

            // Step 6: Run composer install
            $progress(__('update.progress.dependencies'));
            $this->runComposer();

            // Step 7: Run migrations
            $progress(__('update.progress.migrations'));
            $this->runMigrations();

            // Step 8: Clear caches
            $progress(__('update.progress.caches'));
            $this->clearCaches();

            // Step 9: Clean up
            $progress(__('update.progress.cleanup'));
            $this->cleanup($archivePath);

            $progress(__('update.progress.completed', ['version' => $targetVersion]));

        } catch (\Exception $e) {
            // Attempt to restore from backup
            $progress(__('update.progress.failed'));

            try {
                $this->backup->restore($backupFile);
                $progress(__('update.progress.restored'));
            } catch (\Exception $restoreError) {
                throw UpdateException::restoreFailed(
                    "Original error: {$e->getMessage()}. Restore error: {$restoreError->getMessage()}"
                );
            }

            throw $e;
        }
    }

    /**
     * Check available disk space.
     *
     * @throws UpdateException
     */
    private function checkDiskSpace(): void
    {
        $required = 100 * 1024 * 1024; // 100 MB minimum
        $available = disk_free_space(base_path());

        if ($available < $required) {
            throw UpdateException::insufficientDiskSpace($required, (int) $available);
        }
    }

    /**
     * Download the release archive.
     *
     * @throws UpdateException
     */
    private function downloadRelease(array $release): string
    {
        $this->ensureTempDirectory();

        $url = $this->github->getDownloadUrl($release, 'zip');

        $filename = "release-{$release['tag_name']}.zip";
        $filepath = $this->tempPath.'/'.$filename;

        $response = Http::withOptions([
            'sink' => $filepath,
            'timeout' => 300,
            'allow_redirects' => true,
        ])->get($url);

        if ($response->failed()) {
            throw UpdateException::downloadFailed($url, "HTTP {$response->status()}");
        }

        if (! file_exists($filepath)) {
            throw UpdateException::downloadFailed($url, 'Downloaded file does not exist');
        }

        $size = filesize($filepath);
        if ($size === 0) {
            throw UpdateException::downloadFailed($url, 'Downloaded file is empty');
        }

        // Sanity check - tar.gz files should be at least a few KB
        if ($size < 1024) {
            $content = file_get_contents($filepath);
            throw UpdateException::downloadFailed($url, "Downloaded file too small ({$size} bytes): ".substr($content, 0, 100));
        }

        return $filepath;
    }

    /**
     * Extract the archive and apply the update.
     *
     * @throws UpdateException
     */
    private function extractAndApply(string $archivePath, string $tag): void
    {
        $extractPath = $this->tempPath.'/extracted-'.uniqid();

        $this->extractZip($archivePath, $extractPath);

        // Find the extracted directory
        $contents = array_diff(scandir($extractPath), ['.', '..']);

        // Determine the source directory for copying
        // Check if this looks like a flat extraction (files directly in root)
        $hasRootFiles = in_array('composer.json', $contents) || in_array('artisan', $contents) || in_array('VERSION', $contents);

        if ($hasRootFiles) {
            // Flat structure - files extracted directly, no parent directory
            $sourceDir = $extractPath;
        } else {
            // GitHub archive format - look for single subdirectory
            $directories = [];
            foreach ($contents as $item) {
                if (is_dir($extractPath.'/'.$item)) {
                    $directories[] = $item;
                }
            }

            if (count($directories) === 1) {
                $sourceDir = $extractPath.'/'.$directories[0];
            } else {
                // Look for a directory matching GitHub's pattern: reponame-version
                $sourceDir = null;
                foreach ($directories as $dir) {
                    if (preg_match('/^[a-zA-Z0-9_-]+-v?\d+/', $dir)) {
                        $sourceDir = $extractPath.'/'.$dir;
                        break;
                    }
                }

                if (! $sourceDir) {
                    throw UpdateException::extractionFailed('Could not find GitHub archive root');
                }
            }
        }

        // Copy files to base path
        $this->copyUpdateFiles($sourceDir, base_path());

        // Clean up extracted files
        $this->removeDirectory($extractPath);
    }

    private function extractZip(string $archivePath, string $extractPath): void
    {
        if (! mkdir($extractPath, 0755, true)) {
            throw UpdateException::extractionFailed('Could not create extraction directory');
        }

        $zip = new ZipArchive;
        $result = $zip->open($archivePath);

        if ($result !== true) {
            throw UpdateException::extractionFailed("Could not open zip archive (error code: {$result})");
        }

        if (! $zip->extractTo($extractPath)) {
            $zip->close();
            throw UpdateException::extractionFailed('Could not extract zip archive');
        }

        $zip->close();
    }

    private function copyUpdateFiles(string $source, string $destination): void
    {
        // Normalize paths using realpath
        $sourceReal = realpath($source);
        $destReal = realpath($destination);

        if ($sourceReal === false) {
            throw UpdateException::extractionFailed("Source path does not exist: {$source}");
        }

        if ($destReal === false) {
            throw UpdateException::extractionFailed("Destination path does not exist: {$destination}");
        }

        $sourceLen = strlen($sourceReal);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceReal, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        // Files and directories to preserve (not overwrite)
        $preserve = [
            '.env',
            'storage/app',
            'storage/logs',
            'storage/installed',
        ];

        foreach ($iterator as $item) {
            // Get absolute path and compute relative path from source
            $itemPath = $item->getPathname();
            $relativePath = substr($itemPath, $sourceLen + 1);

            // Skip if relative path is empty or invalid
            if (empty($relativePath)) {
                continue;
            }

            $destPath = $destReal.DIRECTORY_SEPARATOR.$relativePath;

            // Skip preserved paths
            $skip = false;
            foreach ($preserve as $preserved) {
                if (str_starts_with($relativePath, $preserved)) {
                    $skip = true;
                    break;
                }
            }

            if ($skip) {
                continue;
            }

            if ($item->isDir()) {
                if (! is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                $dir = dirname($destPath);
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                copy($itemPath, $destPath);
            }
        }
    }

    private function runComposer(): void
    {
        $composerPath = $this->findComposer();
        $basePath = base_path();

        $command = "{$composerPath} install --no-dev --optimize-autoloader --no-interaction 2>&1";
        $output = [];
        $returnCode = 0;

        exec("cd {$basePath} && {$command}", $output, $returnCode);

        if ($returnCode !== 0) {
            throw UpdateException::extractionFailed('Composer install failed: '.implode("\n", $output));
        }
    }

    private function findComposer(): string
    {
        // Check common locations
        $locations = [
            'composer',
            'composer.phar',
            '/usr/local/bin/composer',
            '/usr/bin/composer',
            base_path('composer.phar'),
        ];

        foreach ($locations as $location) {
            $output = [];
            $returnCode = 0;
            exec("which {$location} 2>/dev/null || command -v {$location} 2>/dev/null", $output, $returnCode);

            if ($returnCode === 0 && ! empty($output)) {
                return trim($output[0]);
            }

            if (file_exists($location)) {
                return "php {$location}";
            }
        }

        return 'composer';
    }

    private function runMigrations(): void
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (\Exception $e) {
            throw UpdateException::migrationFailed($e->getMessage());
        }
    }

    private function clearCaches(): void
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        // Optionally optimize
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
    }

    private function cleanup(string $archivePath): void
    {
        if (file_exists($archivePath)) {
            unlink($archivePath);
        }

        // Clean up old backups
        $this->backup->cleanup(5);
    }

    private function ensureTempDirectory(): void
    {
        if (! is_dir($this->tempPath)) {
            mkdir($this->tempPath, 0755, true);
        }
    }

    private function removeDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
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
}
