<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Version
    |--------------------------------------------------------------------------
    |
    | The current version of VAOP, read from the VERSION file in the root.
    |
    */

    'version' => trim(@file_get_contents(base_path('VERSION')) ?: '') ?: 'unknown',

    /*
    |--------------------------------------------------------------------------
    | Installer
    |--------------------------------------------------------------------------
    |
    | Configuration for the web-based installer. When disabled, all installer
    | routes return 404. This is automatically disabled in Docker environments.
    |
    */

    'installer' => [
        'enabled' => env('INSTALLER_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    |
    | Configuration for the CLI update system. The repository setting determines
    | where to fetch releases from (GitHub owner/repo format).
    |
    */

    'update' => [
        'repository' => env('VAOP_UPDATE_REPOSITORY', 'vaop/platform'),
    ],

];
