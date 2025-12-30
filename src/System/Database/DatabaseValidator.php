<?php

declare(strict_types=1);

namespace System\Database;

use PDO;
use PDOException;

class DatabaseValidator
{
    public function test(array $config): array
    {
        try {
            $dsn = $this->buildDsn($config);
            $pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                ]
            );

            $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);

            return [
                'success' => true,
                'message' => __('install.database.connection_success', ['version' => $version]),
                'version' => $version,
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => $this->parseError($e->getMessage()),
                'version' => null,
            ];
        }
    }

    private function buildDsn(array $config): string
    {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 3306;
        $database = $config['database'] ?? '';

        return match ($driver) {
            'mysql', 'mariadb' => "mysql:host={$host};port={$port};dbname={$database}",
            default => "mysql:host={$host};port={$port};dbname={$database}",
        };
    }

    private function parseError(string $message): string
    {
        if (str_contains($message, 'Access denied')) {
            return __('install.database.errors.access_denied');
        }

        if (str_contains($message, 'Unknown database')) {
            return __('install.database.errors.unknown_database');
        }

        if (str_contains($message, 'Connection refused') || str_contains($message, 'No such file or directory')) {
            return __('install.database.errors.connection_refused');
        }

        if (str_contains($message, 'Name or service not known')) {
            return __('install.database.errors.host_not_found');
        }

        return $message;
    }
}
