<?php

declare(strict_types=1);

namespace System\Environment;

use RuntimeException;

class EnvironmentWriter
{
    private string $envPath;

    public function __construct()
    {
        $this->envPath = base_path('.env');
    }

    public function set(string $key, string $value): void
    {
        $this->setMultiple([$key => $value]);
    }

    public function setMultiple(array $values): void
    {
        $this->ensureEnvExists();

        $content = file_get_contents($this->envPath);

        foreach ($values as $key => $value) {
            $content = $this->updateOrAppend($content, $key, $value);
        }

        file_put_contents($this->envPath, $content);
    }

    public function get(string $key, ?string $default = null): ?string
    {
        if (! file_exists($this->envPath)) {
            return $default;
        }

        $content = file_get_contents($this->envPath);
        $pattern = '/^'.preg_quote($key, '/').'=(.*)$/m';

        if (preg_match($pattern, $content, $matches)) {
            $value = trim($matches[1]);

            if (preg_match('/^(["\'])(.*)\\1$/', $value, $quoted)) {
                return $quoted[2];
            }

            return $value;
        }

        return $default;
    }

    public function generateAppKey(): string
    {
        $key = 'base64:'.base64_encode(random_bytes(32));
        $this->set('APP_KEY', $key);

        return $key;
    }

    private function ensureEnvExists(): void
    {
        if (file_exists($this->envPath)) {
            return;
        }

        $examplePath = base_path('.env.example');

        if (file_exists($examplePath)) {
            copy($examplePath, $this->envPath);

            return;
        }

        throw new RuntimeException('No .env or .env.example file found');
    }

    private function updateOrAppend(string $content, string $key, string $value): string
    {
        $escapedValue = $this->escapeValue($value);
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

        if (preg_match($pattern, $content)) {
            return preg_replace($pattern, "{$key}={$escapedValue}", $content);
        }

        $content = rtrim($content);

        return $content."\n{$key}={$escapedValue}\n";
    }

    private function escapeValue(string $value): string
    {
        if ($value === '') {
            return '""';
        }

        if (preg_match('/[\s"\'#]/', $value) || str_contains($value, '${')) {
            $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], $value);

            return '"'.$escaped.'"';
        }

        return $value;
    }
}
