<?php

declare(strict_types=1);

namespace System\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrationRunner
{
    /**
     * Directories containing migrations to run.
     */
    private array $migrationPaths = [
        'migrations',
        'settings',
    ];

    /**
     * Get all migration files from all configured paths.
     *
     * @return array<array{name: string, path: string}>
     */
    private function getAllMigrations(): array
    {
        $migrations = [];

        foreach ($this->migrationPaths as $dir) {
            $files = glob(database_path("{$dir}/*.php")) ?: [];

            foreach ($files as $file) {
                $migrations[] = [
                    'name' => pathinfo($file, PATHINFO_FILENAME),
                    'path' => $file,
                ];
            }
        }

        // Sort by name (timestamp-based naming ensures correct order)
        usort($migrations, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $migrations;
    }

    /**
     * @return array<array{name: string, path: string}>
     */
    public function getPendingMigrations(): array
    {
        $migrations = $this->getAllMigrations();

        if (! $this->migrationTableExists()) {
            return $migrations;
        }

        $ran = DB::table('system_migrations')->pluck('migration')->toArray();

        return array_values(array_filter(
            $migrations,
            fn ($m) => ! in_array($m['name'], $ran, true)
        ));
    }

    public function runNext(): ?string
    {
        $pending = $this->getPendingMigrations();

        if (empty($pending)) {
            return null;
        }

        $migration = $pending[0];

        $this->ensureMigrationTableExists();
        $this->runMigration($migration);

        return $migration['name'];
    }

    public function hasPending(): bool
    {
        return count($this->getPendingMigrations()) > 0;
    }

    public function getProgress(): array
    {
        $total = count($this->getAllMigrations());
        $pending = count($this->getPendingMigrations());
        $completed = $total - $pending;

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'percent' => $total > 0 ? round(($completed / $total) * 100) : 100,
        ];
    }

    private function migrationTableExists(): bool
    {
        try {
            return Schema::hasTable('system_migrations');
        } catch (\Exception $e) {
            return false;
        }
    }

    private function ensureMigrationTableExists(): void
    {
        if ($this->migrationTableExists()) {
            return;
        }

        Schema::create('system_migrations', function ($table) {
            $table->id();
            $table->string('migration');
            $table->integer('batch');
        });
    }

    /**
     * @param  array{name: string, path: string}  $migration
     */
    private function runMigration(array $migration): void
    {
        $class = require $migration['path'];

        if (is_object($class)) {
            $class->up();
        }

        $batch = DB::table('system_migrations')->max('batch') ?? 0;

        DB::table('system_migrations')->insert([
            'migration' => $migration['name'],
            'batch' => $batch + 1,
        ]);
    }
}
