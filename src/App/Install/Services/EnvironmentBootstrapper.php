<?php

declare(strict_types=1);

namespace App\Install\Services;

class EnvironmentBootstrapper
{
    public static function ensure(): void
    {
        // Skip bootstrapping if installer is disabled (e.g., in Docker)
        // Docker users configure via environment variables, not .env file
        if (static::isInstallerDisabled()) {
            static::validateRequiredEnvironment();

            return;
        }

        $basePath = dirname(__DIR__, 4);
        $envPath = $basePath.'/.env';

        if (! file_exists($envPath)) {
            file_put_contents($envPath, static::minimalEnv());

            return;
        }

        // Ensure existing .env has required values for installer
        $content = file_get_contents($envPath);
        $content = static::ensureValue($content, 'APP_KEY', 'base64:'.base64_encode(random_bytes(32)));
        $content = static::ensureValue($content, 'SESSION_DRIVER', 'file');
        $content = static::ensureValue($content, 'CACHE_STORE', 'file');
        file_put_contents($envPath, $content);
    }

    private static function ensureValue(string $content, string $key, string $default): string
    {
        $pattern = "/^{$key}=.*$/m";

        // If key exists with empty value, replace it
        if (preg_match("/^{$key}=\\s*$/m", $content)) {
            return preg_replace($pattern, "{$key}={$default}", $content);
        }

        // If key doesn't exist, append it
        if (! preg_match($pattern, $content)) {
            return rtrim($content)."\n{$key}={$default}\n";
        }

        return $content;
    }

    private static function minimalEnv(): string
    {
        $key = 'base64:'.base64_encode(random_bytes(32));

        return <<<ENV
APP_KEY={$key}
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=stack
SESSION_DRIVER=file
CACHE_STORE=file
ENV;
    }

    private static function isInstallerDisabled(): bool
    {
        return ($_ENV['INSTALLER_ENABLED'] ?? $_SERVER['INSTALLER_ENABLED'] ?? null) === 'false';
    }

    private static function validateRequiredEnvironment(): void
    {
        $missing = [];

        $required = [
            'APP_KEY' => 'Application encryption key (generate with: php artisan key:generate --show)',
            'DB_HOST' => 'Database host',
            'DB_DATABASE' => 'Database name',
            'DB_USERNAME' => 'Database username',
        ];

        foreach ($required as $key => $description) {
            $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
            if ($value === false || $value === '' || $value === null) {
                $missing[$key] = $description;
            }
        }

        if (! empty($missing)) {
            static::renderMissingEnvError($missing);
        }
    }

    private static function renderMissingEnvError(array $missing): never
    {
        $isHtml = php_sapi_name() !== 'cli';

        if ($isHtml) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');

            $list = implode('', array_map(
                fn ($key, $desc) => "<li><code>{$key}</code> - {$desc}</li>",
                array_keys($missing),
                array_values($missing)
            ));

            echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Required - VAOP</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .container { background: white; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 600px; width: 100%; padding: 2rem; }
        h1 { color: #dc2626; font-size: 1.5rem; margin-bottom: 1rem; }
        p { color: #4b5563; margin-bottom: 1rem; line-height: 1.6; }
        ul { margin: 1rem 0; padding-left: 1.5rem; }
        li { color: #374151; margin-bottom: 0.5rem; }
        code { background: #f3f4f6; padding: 0.125rem 0.375rem; border-radius: 0.25rem; font-size: 0.875rem; }
        .hint { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 1rem; margin-top: 1.5rem; border-radius: 0 0.25rem 0.25rem 0; }
        .hint p { margin: 0; color: #92400e; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠️ Configuration Required</h1>
        <p>VAOP cannot start because required environment variables are missing:</p>
        <ul>{$list}</ul>
        <div class="hint">
            <p><strong>Docker users:</strong> Pass these variables via <code>-e</code> flags or in your compose file.</p>
        </div>
    </div>
</body>
</html>
HTML;
        } else {
            $list = implode("\n", array_map(
                fn ($key, $desc) => "  - {$key}: {$desc}",
                array_keys($missing),
                array_values($missing)
            ));

            echo <<<TEXT

VAOP Configuration Error
========================

Required environment variables are missing:

{$list}

Set these variables before starting the application.

TEXT;
        }

        exit(1);
    }
}
