<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\Casts;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use Support\UnitsOfMeasure\Casts\WeightCast;
use Support\UnitsOfMeasure\ValueObjects\Weight;
use Tests\TestCase;

class WeightCastTest extends TestCase
{
    private WeightCast $cast;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast = new WeightCast;
        $this->model = new class extends Model {};
    }

    #[Test]
    public function it_casts_null_to_null(): void
    {
        $result = $this->cast->get($this->model, 'weight', null, []);

        $this->assertNull($result);
    }

    #[Test]
    public function it_casts_float_to_weight(): void
    {
        $result = $this->cast->get($this->model, 'weight', 75000.5, []);

        $this->assertInstanceOf(Weight::class, $result);
        $this->assertEqualsWithDelta(75000.5, $result->toKilograms(), 0.01);
    }

    #[Test]
    public function it_casts_string_to_weight(): void
    {
        $result = $this->cast->get($this->model, 'weight', '50000', []);

        $this->assertInstanceOf(Weight::class, $result);
        $this->assertEqualsWithDelta(50000, $result->toKilograms(), 0.01);
    }

    #[Test]
    public function it_sets_null_to_null(): void
    {
        $result = $this->cast->set($this->model, 'weight', null, []);

        $this->assertNull($result);
    }

    #[Test]
    public function it_sets_weight_to_kilograms(): void
    {
        $weight = Weight::fromKilograms(75000);

        $result = $this->cast->set($this->model, 'weight', $weight, []);

        $this->assertEqualsWithDelta(75000, $result, 0.01);
    }

    #[Test]
    public function it_converts_pounds_to_kilograms(): void
    {
        $weight = Weight::fromPounds(165347); // ~75000 kg

        $result = $this->cast->set($this->model, 'weight', $weight, []);

        $this->assertEqualsWithDelta(75000, $result, 10);
    }

    #[Test]
    public function it_allows_raw_numeric_values(): void
    {
        $result = $this->cast->set($this->model, 'weight', 80000, []);

        $this->assertEqualsWithDelta(80000, $result, 0.01);
    }
}
