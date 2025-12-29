<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Update Language Lines
    |--------------------------------------------------------------------------
    */

    // Command description
    'description' => 'Update VAOP to the latest version',

    // Status messages
    'checking' => 'Checking for updates...',
    'current_version' => 'Current version: :version',
    'latest_version' => 'Latest version: :version',
    'up_to_date' => 'You are running the latest version.',
    'update_available' => 'An update is available!',
    'update_cancelled' => 'Update cancelled.',
    'update_completed' => 'Update completed successfully!',
    'update_failed' => 'Update failed: :error',

    // Release notes
    'released' => 'Released: :date',
    'release_notes' => 'Release notes:',
    'truncated' => '... (truncated)',

    // Confirmations
    'confirm_update' => 'Do you want to update now?',
    'confirm_restore' => 'Are you sure you want to restore this backup?',

    // Backup
    'no_backups' => 'No backups found.',
    'available_backups' => 'Available backups:',
    'backup_file_not_found' => 'Backup file not found: :file',
    'restore_warning' => 'This will restore your application from the backup file:',
    'restore_overwrite' => 'All current files will be overwritten!',
    'restoring' => 'Restoring backup...',
    'restore_completed' => 'Backup restored successfully!',
    'restore_cancelled' => 'Restore cancelled.',
    'restore_hint' => 'You may need to run :composer and :migrate',
    'restore_usage' => 'To restore a backup, use:',

    // Table headers
    'table' => [
        'filename' => 'Filename',
        'size' => 'Size',
        'created' => 'Created',
    ],

    // Progress messages
    'progress' => [
        'fetching' => 'Fetching release information...',
        'checking_disk' => 'Checking disk space...',
        'creating_backup' => 'Creating backup...',
        'downloading' => 'Downloading update...',
        'extracting' => 'Extracting update...',
        'dependencies' => 'Installing dependencies...',
        'migrations' => 'Running database migrations...',
        'caches' => 'Clearing caches...',
        'cleanup' => 'Cleaning up...',
        'completed' => 'Update to version :version completed successfully!',
        'failed' => 'Update failed, attempting to restore backup...',
        'restored' => 'Backup restored successfully.',
    ],

    // Errors
    'errors' => [
        'no_releases' => 'No releases found on GitHub.',
        'already_up_to_date' => 'Already running the latest version (:version).',
        'download_failed' => 'Failed to download release from :url: :reason',
        'checksum_mismatch' => 'Downloaded file checksum does not match. The file may be corrupted.',
        'extraction_failed' => 'Failed to extract update archive: :reason',
        'backup_failed' => 'Failed to create backup: :reason',
        'restore_failed' => 'Failed to restore backup: :reason',
        'insufficient_permissions' => 'Insufficient permissions to write to: :path',
        'insufficient_disk_space' => 'Insufficient disk space. Required: :required MB, Available: :available MB',
        'migration_failed' => 'Database migration failed: :reason',
        'network_error' => 'Network error: :reason',
    ],

];
