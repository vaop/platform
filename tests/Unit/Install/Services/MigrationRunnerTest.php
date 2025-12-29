<?php

declare(strict_types=1);

namespace Tests\Unit\Install\Services;

use App\Install\Services\MigrationRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MigrationRunnerTest extends TestCase
{
    use RefreshDatabase;

    private MigrationRunner $runner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->runner = new MigrationRunner;
    }

    #[Test]
    public function it_can_get_pending_migrations(): void
    {
        $pending = $this->runner->getPendingMigrations();

        $this->assertIsArray($pending);
    }

    #[Test]
    public function it_can_check_if_migrations_are_pending(): void
    {
        $hasPending = $this->runner->hasPending();

        $this->assertIsBool($hasPending);
    }

    #[Test]
    public function it_can_get_progress_information(): void
    {
        $progress = $this->runner->getProgress();

        $this->assertArrayHasKey('total', $progress);
        $this->assertArrayHasKey('completed', $progress);
        $this->assertArrayHasKey('pending', $progress);
        $this->assertArrayHasKey('percent', $progress);

        $this->assertIsInt($progress['total']);
        $this->assertIsInt($progress['completed']);
        $this->assertIsInt($progress['pending']);
        $this->assertIsNumeric($progress['percent']);

        $this->assertEquals($progress['total'], $progress['completed'] + $progress['pending']);
        $this->assertGreaterThanOrEqual(0, $progress['percent']);
        $this->assertLessThanOrEqual(100, $progress['percent']);
    }

    #[Test]
    public function it_returns_null_when_no_pending_migrations(): void
    {
        // After RefreshDatabase runs all migrations, there should be none pending
        $result = $this->runner->runNext();

        $this->assertNull($result);
    }

    #[Test]
    public function progress_shows_100_percent_when_all_migrations_complete(): void
    {
        // After RefreshDatabase, all migrations should be complete
        $progress = $this->runner->getProgress();

        $this->assertEquals(100, $progress['percent']);
        $this->assertEquals(0, $progress['pending']);
    }
}
