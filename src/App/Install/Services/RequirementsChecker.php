<?php

declare(strict_types=1);

namespace App\Install\Services;

class RequirementsChecker
{
    private const string MIN_PHP_VERSION = '8.4.0';

    private const array REQUIRED_EXTENSIONS = [
        'pdo_mysql',
        'openssl',
        'json',
        'curl',
        'zip',
        'gd',
        'bcmath',
        'intl',
        'xml',
        'fileinfo',
    ];

    private const array REQUIRED_DIRECTORIES = [
        'storage/app',
        'storage/framework',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'bootstrap/cache',
    ];

    public function check(): array
    {
        return [
            'php' => $this->checkPhpVersion(),
            'extensions' => $this->checkExtensions(),
            'directories' => $this->checkDirectories(),
            'passed' => $this->allPassed(),
        ];
    }

    public function allPassed(): bool
    {
        $php = $this->checkPhpVersion();
        if (! $php['passed']) {
            return false;
        }

        foreach ($this->checkExtensions() as $ext) {
            if (! $ext['passed']) {
                return false;
            }
        }

        foreach ($this->checkDirectories() as $dir) {
            if (! $dir['passed']) {
                return false;
            }
        }

        return true;
    }

    private function checkPhpVersion(): array
    {
        return [
            'required' => self::MIN_PHP_VERSION,
            'current' => PHP_VERSION,
            'passed' => version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '>='),
        ];
    }

    private function checkExtensions(): array
    {
        $results = [];

        foreach (self::REQUIRED_EXTENSIONS as $extension) {
            $results[$extension] = [
                'name' => $extension,
                'passed' => extension_loaded($extension),
            ];
        }

        return $results;
    }

    private function checkDirectories(): array
    {
        $results = [];

        foreach (self::REQUIRED_DIRECTORIES as $directory) {
            $path = base_path($directory);
            $exists = is_dir($path);
            $writable = $exists && is_writable($path);

            $results[$directory] = [
                'path' => $directory,
                'exists' => $exists,
                'writable' => $writable,
                'passed' => $writable,
            ];
        }

        return $results;
    }
}
