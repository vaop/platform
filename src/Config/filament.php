<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | This is the storage disk Filament will use to store files such as
    | import/export files, file uploads in forms, etc.
    |
    | The disk is determined by STORAGE_MODE:
    | - "local" (default): Uses the local-private disk
    | - "s3": Uses the s3-private disk for Filament operations
    |
    */

    'default_filesystem_disk' => match (env('STORAGE_MODE', 'local')) {
        's3' => 's3-private',
        default => 'local-private',
    },

];
