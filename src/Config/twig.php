<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Twig Environment Options
    |--------------------------------------------------------------------------
    |
    | These options are passed directly to the Twig Environment constructor.
    | See: https://twig.symfony.com/doc/3.x/api.html#environment-options
    |
    */
    'twig' => [
        'extension' => 'twig',
        'environment' => [
            'debug' => env('APP_DEBUG', false),
            'charset' => 'UTF-8',
            'cache' => storage_path('framework/views/twig'),
            'auto_reload' => env('APP_DEBUG', false),
            'strict_variables' => true,
            'autoescape' => 'html',
            'optimizations' => -1,
        ],
        'safe_classes' => [
            \Illuminate\Contracts\Support\Htmlable::class => ['toHtml'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sandbox Security Policy
    |--------------------------------------------------------------------------
    |
    | The sandbox restricts what Twig templates can do. This is essential
    | for security when allowing user-editable templates.
    |
    */
    'sandbox' => [
        // Sandbox enabled for all templates as defense-in-depth.
        // Restricts templates to safe operations defined below.
        'enabled' => true,
        'global' => true,

        'allowed_tags' => [
            'if',
            'for',
            'set',
            'block',
            'extends',
            'include',
            'embed',
            'macro',
            'import',
            'from',
            'with',
            'spaceless',
            'verbatim',
            'autoescape',
            'apply',
            'do',
            'flush',
        ],

        'forbidden_tags' => [
            'sandbox',
        ],

        'allowed_filters' => [
            // Twig built-ins
            'escape',
            'e',
            'raw',
            'upper',
            'lower',
            'title',
            'capitalize',
            'trim',
            'nl2br',
            'striptags',
            'join',
            'split',
            'sort',
            'merge',
            'length',
            'first',
            'last',
            'reverse',
            'slice',
            'keys',
            'default',
            'json_encode',
            'abs',
            'round',
            'number_format',
            'date',
            'date_modify',
            'format',
            'replace',
            'batch',
            'column',
            'filter',
            'map',
            'reduce',
            'url_encode',
            'spaceless',

            // Custom filters (defined in FiltersExtension)
            'truncate',
            'excerpt',
            'markdown',
            'datetime',
            'relative',
        ],

        'allowed_functions' => [
            // Twig built-ins
            'range',
            'cycle',
            'constant',
            'random',
            'date',
            'min',
            'max',
            'include',
            'block',
            'parent',
            'attribute',
            'dump',

            // Custom functions (defined in CoreFunctionsExtension)
            'route',
            'url',
            'asset',
            'vite',
            'csrf_field',
            'csrf_token',
            'method_field',
            'old',
            '__',
            'trans_choice',
            'session',
            'has_flash',
            'flash',
            'flash_all',
            'is_authenticated',
            'is_guest',
            'now',
            'config',
        ],

        'allowed_methods' => [],

        'allowed_properties' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Config Keys
    |--------------------------------------------------------------------------
    |
    | The config() Twig function only allows access to these configuration
    | keys. This prevents templates from accessing sensitive configuration
    | values like API keys, database credentials, etc.
    |
    */
    'allowed_config_keys' => [
        'app.name',
        'app.url',
        'app.locale',
        'app.timezone',
        'vaop.version',
    ],
];
