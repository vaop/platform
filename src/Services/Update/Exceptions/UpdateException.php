<?php

declare(strict_types=1);

namespace Services\Update\Exceptions;

use Exception;

class UpdateException extends Exception
{
    public static function noReleasesFound(): self
    {
        return new self(__('update.errors.no_releases'));
    }

    public static function alreadyUpToDate(string $version): self
    {
        return new self(__('update.errors.already_up_to_date', ['version' => $version]));
    }

    public static function downloadFailed(string $url, string $reason): self
    {
        return new self(__('update.errors.download_failed', ['url' => $url, 'reason' => $reason]));
    }

    public static function checksumMismatch(): self
    {
        return new self(__('update.errors.checksum_mismatch'));
    }

    public static function extractionFailed(string $reason): self
    {
        return new self(__('update.errors.extraction_failed', ['reason' => $reason]));
    }

    public static function backupFailed(string $reason): self
    {
        return new self(__('update.errors.backup_failed', ['reason' => $reason]));
    }

    public static function restoreFailed(string $reason): self
    {
        return new self(__('update.errors.restore_failed', ['reason' => $reason]));
    }

    public static function insufficientPermissions(string $path): self
    {
        return new self(__('update.errors.insufficient_permissions', ['path' => $path]));
    }

    public static function insufficientDiskSpace(int $required, int $available): self
    {
        $requiredMb = round($required / 1024 / 1024, 2);
        $availableMb = round($available / 1024 / 1024, 2);

        return new self(__('update.errors.insufficient_disk_space', [
            'required' => $requiredMb,
            'available' => $availableMb,
        ]));
    }

    public static function migrationFailed(string $reason): self
    {
        return new self(__('update.errors.migration_failed', ['reason' => $reason]));
    }

    public static function networkError(string $reason): self
    {
        return new self(__('update.errors.network_error', ['reason' => $reason]));
    }
}
