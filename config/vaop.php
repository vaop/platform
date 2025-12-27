<?php

return [

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

];
