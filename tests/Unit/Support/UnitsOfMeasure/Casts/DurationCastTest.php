<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\Casts;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use Support\UnitsOfMeasure\Casts\DurationCast;
use Support\UnitsOfMeasure\ValueObjects\Duration;
use Tests\TestCase;

class DurationCastTest extends TestCase
{
    private DurationCast $cast;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast = new DurationCast;
        $this->model = new class extends Model {};
    }

    #[Test]
    public function it_casts_null_to_null(): void
    {
        $result = $this->cast->get($this->model, 'duration', null, []);

        $this->assertNull($result);
    }

    #[Test]
    public function it_casts_integer_to_duration(): void
    {
        $result = $this->cast->get($this->model, 'duration', 9045, []);

        $this->assertInstanceOf(Duration::class, $result);
        $this->assertSame(9045, $result->toSeconds());
    }

    #[Test]
    public function it_casts_string_to_duration(): void
    {
        $result = $this->cast->get($this->model, 'duration', '3600', []);

        $this->assertInstanceOf(Duration::class, $result);
        $this->assertSame(3600, $result->toSeconds());
    }

    #[Test]
    public function it_casts_float_to_duration_truncating(): void
    {
        $result = $this->cast->get($this->model, 'duration', 3600.9, []);

        $this->assertInstanceOf(Duration::class, $result);
        $this->assertSame(3600, $result->toSeconds());
    }

    #[Test]
    public function it_sets_null_to_null(): void
    {
        $result = $this->cast->set($this->model, 'duration', null, []);

        $this->assertNull($result);
    }

    #[Test]
    public function it_sets_duration_to_seconds(): void
    {
        $duration = Duration::fromSeconds(9045);

        $result = $this->cast->set($this->model, 'duration', $duration, []);

        $this->assertSame(9045, $result);
    }

    #[Test]
    public function it_converts_hours_to_seconds(): void
    {
        $duration = Duration::fromHours(2.5); // 9000 seconds

        $result = $this->cast->set($this->model, 'duration', $duration, []);

        $this->assertSame(9000, $result);
    }

    #[Test]
    public function it_allows_raw_numeric_values(): void
    {
        $result = $this->cast->set($this->model, 'duration', 7200, []);

        $this->assertSame(7200, $result);
    }
}
