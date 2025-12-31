<?php

declare(strict_types=1);

namespace Tests\Unit\Install\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use System\Database\MigrationRunner;
use Tests\TestCase;

class MigrationRunnerTest extends TestCase
{
    use RefreshDatabase;

    private MigrationRunner $runner;

    private array $allMigrationFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->runner = new MigrationRunner;

        // Get all migration files for reference
        $files = glob(database_path('migrations/*.php'));
        foreach ($files as $file) {
            $this->allMigrationFiles[] = pathinfo($file, PATHINFO_FILENAME);
        }
        sort($this->allMigrationFiles);
    }

    #[Test]
    public function migration_files_exist(): void
    {
        $this->assertNotEmpty($this->allMigrationFiles, 'Migration files should exist');
    }

    #[Test]
    public function it_returns_empty_array_when_all_migrations_complete(): void
    {
        // RefreshDatabase ensures all migrations are run
        $pending = $this->runner->getPendingMigrations();

        $this->assertIsArray($pending);
        $this->assertEmpty($pending);
    }

    #[Test]
    public function has_pending_returns_false_when_no_migrations_pending(): void
    {
        $this->assertFalse($this->runner->hasPending());
    }

    #[Test]
    public function progress_contains_all_required_keys(): void
    {
        $progress = $this->runner->getProgress();

        $this->assertArrayHasKey('total', $progress);
        $this->assertArrayHasKey('completed', $progress);
        $this->assertArrayHasKey('pending', $progress);
        $this->assertArrayHasKey('percent', $progress);
    }

    #[Test]
    public function progress_values_are_correct_types(): void
    {
        $progress = $this->runner->getProgress();

        $this->assertIsInt($progress['total']);
        $this->assertIsInt($progress['completed']);
        $this->assertIsInt($progress['pending']);
        $this->assertIsFloat($progress['percent']);
    }

    #[Test]
    public function progress_math_is_correct(): void
    {
        $progress = $this->runner->getProgress();

        // Verify the math: total = completed + pending
        $this->assertEquals(
            $progress['total'],
            $progress['completed'] + $progress['pending'],
            'Total should equal completed + pending'
        );

        // Verify percent is within valid range
        $this->assertGreaterThanOrEqual(0, $progress['percent']);
        $this->assertLessThanOrEqual(100, $progress['percent']);
    }

    #[Test]
    public function run_next_returns_null_when_no_pending_migrations(): void
    {
        $result = $this->runner->runNext();

        $this->assertNull($result);
    }

    #[Test]
    public function progress_shows_100_percent_when_all_migrations_complete(): void
    {
        $progress = $this->runner->getProgress();

        $this->assertEquals(100, $progress['percent']);
        $this->assertEquals(0, $progress['pending']);
        $this->assertEquals($progress['total'], $progress['completed']);
    }

    #[Test]
    public function completed_migrations_are_less_than_or_equal_to_database_count(): void
    {
        $progress = $this->runner->getProgress();
        $dbCount = DB::table('migrations')->count();

        // MigrationRunner tracks only database/migrations/, but the migrations table
        // may include additional migrations (e.g., Spatie settings migrations from database/settings/)
        $this->assertLessThanOrEqual($dbCount, $progress['completed']);
    }

    #[Test]
    public function total_migrations_match_files_count(): void
    {
        $progress = $this->runner->getProgress();
        $fileCount = count($this->allMigrationFiles);

        $this->assertEquals($fileCount, $progress['total']);
    }

    #[Test]
    public function pending_migrations_returns_array(): void
    {
        $pending = $this->runner->getPendingMigrations();

        $this->assertIsArray($pending);
    }

    #[Test]
    public function get_pending_migrations_returns_sorted_array(): void
    {
        $pending = $this->runner->getPendingMigrations();

        // Even if empty, verify it's sorted
        $sorted = $pending;
        sort($sorted);
        $this->assertEquals($sorted, $pending, 'Pending migrations should be sorted');
    }
}
