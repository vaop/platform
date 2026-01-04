<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Temporary File Upload
    |--------------------------------------------------------------------------
    |
    | When using Livewire's file upload features, uploaded files are stored
    | temporarily before being moved to their final location. This config
    | controls which disk is used for temporary storage.
    |
    | The disk is determined by STORAGE_MODE:
    | - "local" (default): Uses the local-private disk
    | - "s3": Uses the s3-private disk for temporary uploads
    |
    */

    'temporary_file_upload' => [
        'disk' => match (env('STORAGE_MODE', 'local')) {
            's3' => 's3-private',
            default => 'local-private',
        },
    ],

];
