<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    | Storage mode determines which disk set to use:
    | - "local" (default): Uses local filesystem (local-private/local-public disks)
    | - "s3": Uses S3-compatible storage (s3-private/s3-public disks)
    |
    */

    'default' => match (env('STORAGE_MODE', 'local')) {
        's3' => 's3-private',
        default => 'local-private',
    },

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        /*
        |--------------------------------------------------------------------------
        | Local Storage Disks
        |--------------------------------------------------------------------------
        */

        'local-private' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'local-public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | S3-Compatible Storage Disks
        |--------------------------------------------------------------------------
        |
        | These disks support AWS S3 and S3-compatible services like
        | DigitalOcean Spaces, Cloudflare R2, and MinIO.
        |
        | Each bucket has its own credentials, allowing different IAM users
        | or even different providers for private vs public storage.
        |
        */

        's3-private' => [
            'driver' => 's3',
            'key' => env('S3_PRIVATE_ACCESS_KEY_ID'),
            'secret' => env('S3_PRIVATE_SECRET_ACCESS_KEY'),
            'region' => env('S3_PRIVATE_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('S3_PRIVATE_BUCKET'),
            'url' => env('S3_PRIVATE_URL'),
            'endpoint' => env('S3_PRIVATE_ENDPOINT'),
            'use_path_style_endpoint' => env('S3_PRIVATE_USE_PATH_STYLE_ENDPOINT', false),
            'visibility' => 'private',
            'throw' => false,
            'report' => false,
        ],

        's3-public' => [
            'driver' => 's3',
            'key' => env('S3_PUBLIC_ACCESS_KEY_ID'),
            'secret' => env('S3_PUBLIC_SECRET_ACCESS_KEY'),
            'region' => env('S3_PUBLIC_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('S3_PUBLIC_BUCKET'),
            'url' => env('S3_PUBLIC_URL'),
            'endpoint' => env('S3_PUBLIC_ENDPOINT'),
            'use_path_style_endpoint' => env('S3_PUBLIC_USE_PATH_STYLE_ENDPOINT', false),
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

    ],

];
