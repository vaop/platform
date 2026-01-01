<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\Casts;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use Support\UnitsOfMeasure\Casts\SpeedCast;
use Support\UnitsOfMeasure\ValueObjects\Speed;
use Tests\TestCase;

class SpeedCastTest extends TestCase
{
    private SpeedCast $cast;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast = new SpeedCast;
        $this->model = new class extends Model {};
    }

    #[Test]
    public function it_casts_null_to_null(): void
    {
        $result = $this->cast->get($this->model, 'speed', null, []);

        $this->assertNull($result);
    }

    #[Test]
    public function it_casts_float_to_speed(): void
    {
        $result = $this->cast->get($this->model, 'speed', 450.5, []);

        $this->assertInstanceOf(Speed::class, $result);
        $this->assertEqualsWithDelta(450.5, $result->toKnots(), 0.01);
    }

    #[Test]
    public function it_casts_string_to_speed(): void
    {
        $result = $this->cast->get($this->model, 'speed', '250', []);

        $this->assertInstanceOf(Speed::class, $result);
        $this->assertEqualsWithDelta(250, $result->toKnots(), 0.01);
    }

    #[Test]
    public function it_sets_null_to_null(): void
    {
        $result = $this->cast->set($this->model, 'speed', null, []);

        $this->assertNull($result);
    }

    #[Test]
    public function it_sets_speed_to_knots(): void
    {
        $speed = Speed::fromKnots(450);

        $result = $this->cast->set($this->model, 'speed', $speed, []);

        $this->assertEqualsWithDelta(450, $result, 0.01);
    }

    #[Test]
    public function it_converts_kmh_to_knots(): void
    {
        $speed = Speed::fromKilometersPerHour(833.4); // ~450 kts

        $result = $this->cast->set($this->model, 'speed', $speed, []);

        $this->assertEqualsWithDelta(450, $result, 1);
    }

    #[Test]
    public function it_allows_raw_numeric_values(): void
    {
        $result = $this->cast->set($this->model, 'speed', 300, []);

        $this->assertEqualsWithDelta(300, $result, 0.01);
    }
}
