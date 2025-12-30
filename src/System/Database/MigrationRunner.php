<?php

declare(strict_types=1);

namespace System\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrationRunner
{
    public function getPendingMigrations(): array
    {
        $files = glob(database_path('migrations/*.php'));
        $migrations = [];

        foreach ($files as $file) {
            $migrations[] = pathinfo($file, PATHINFO_FILENAME);
        }

        sort($migrations);

        if (! $this->migrationTableExists()) {
            return $migrations;
        }

        $ran = DB::table('migrations')->pluck('migration')->toArray();

        return array_values(array_diff($migrations, $ran));
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

        return $migration;
    }

    public function hasPending(): bool
    {
        return count($this->getPendingMigrations()) > 0;
    }

    public function getProgress(): array
    {
        $files = glob(database_path('migrations/*.php'));
        $total = count($files);
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
            return Schema::hasTable('migrations');
        } catch (\Exception $e) {
            return false;
        }
    }

    private function ensureMigrationTableExists(): void
    {
        if ($this->migrationTableExists()) {
            return;
        }

        Schema::create('migrations', function ($table) {
            $table->id();
            $table->string('migration');
            $table->integer('batch');
        });
    }

    private function runMigration(string $migration): void
    {
        $file = database_path("migrations/{$migration}.php");
        $class = require $file;

        if (is_object($class)) {
            $class->up();
        }

        $batch = DB::table('migrations')->max('batch') ?? 0;

        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => $batch + 1,
        ]);
    }
}
