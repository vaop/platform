<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\Casts;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use Support\UnitsOfMeasure\Casts\DistanceCast;
use Support\UnitsOfMeasure\ValueObjects\Distance;
use Tests\TestCase;

class DistanceCastTest extends TestCase
{
    private DistanceCast $cast;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast = new DistanceCast;
        $this->model = new class extends Model {};
    }

    #[Test]
    public function it_casts_null_to_null(): void
    {
        $result = $this->cast->get($this->model, 'distance', null, []);

        $this->assertNull($result);
    }

    #[Test]
    public function it_casts_float_to_distance(): void
    {
        $result = $this->cast->get($this->model, 'distance', 100.5, []);

        $this->assertInstanceOf(Distance::class, $result);
        $this->assertEqualsWithDelta(100.5, $result->toNauticalMiles(), 0.001);
    }

    #[Test]
    public function it_casts_string_to_distance(): void
    {
        $result = $this->cast->get($this->model, 'distance', '250', []);

        $this->assertInstanceOf(Distance::class, $result);
        $this->assertEqualsWithDelta(250, $result->toNauticalMiles(), 0.001);
    }

    #[Test]
    public function it_sets_null_to_null(): void
    {
        $result = $this->cast->set($this->model, 'distance', null, []);

        $this->assertNull($result);
    }

    #[Test]
    public function it_sets_distance_to_nautical_miles(): void
    {
        $distance = Distance::fromNauticalMiles(100);

        $result = $this->cast->set($this->model, 'distance', $distance, []);

        $this->assertEqualsWithDelta(100, $result, 0.001);
    }

    #[Test]
    public function it_converts_other_units_to_nautical_miles(): void
    {
        $distance = Distance::fromKilometers(185.2); // ~100 nm

        $result = $this->cast->set($this->model, 'distance', $distance, []);

        $this->assertEqualsWithDelta(100, $result, 0.1);
    }

    #[Test]
    public function it_allows_raw_numeric_values(): void
    {
        $result = $this->cast->set($this->model, 'distance', 500, []);

        $this->assertEqualsWithDelta(500, $result, 0.001);
    }
}
