<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\Casts;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use Support\UnitsOfMeasure\Casts\AltitudeCast;
use Support\UnitsOfMeasure\ValueObjects\Altitude;
use Tests\TestCase;

class AltitudeCastTest extends TestCase
{
    private AltitudeCast $cast;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast = new AltitudeCast;
        $this->model = new class extends Model {};
    }

    #[Test]
    public function it_casts_null_to_null(): void
    {
        $result = $this->cast->get($this->model, 'altitude', null, []);

        $this->assertNull($result);
    }

    #[Test]
    public function it_casts_float_to_altitude(): void
    {
        $result = $this->cast->get($this->model, 'altitude', 35000, []);

        $this->assertInstanceOf(Altitude::class, $result);
        $this->assertEqualsWithDelta(35000, $result->toFeet(), 0.1);
    }

    #[Test]
    public function it_casts_string_to_altitude(): void
    {
        $result = $this->cast->get($this->model, 'altitude', '10000', []);

        $this->assertInstanceOf(Altitude::class, $result);
        $this->assertEqualsWithDelta(10000, $result->toFeet(), 0.1);
    }

    #[Test]
    public function it_casts_negative_altitude(): void
    {
        $result = $this->cast->get($this->model, 'altitude', -282, []);

        $this->assertInstanceOf(Altitude::class, $result);
        $this->assertEqualsWithDelta(-282, $result->toFeet(), 0.1);
    }

    #[Test]
    public function it_sets_null_to_null(): void
    {
        $result = $this->cast->set($this->model, 'altitude', null, []);

        $this->assertNull($result);
    }

    #[Test]
    public function it_sets_altitude_to_feet(): void
    {
        $altitude = Altitude::fromFeet(35000);

        $result = $this->cast->set($this->model, 'altitude', $altitude, []);

        $this->assertEqualsWithDelta(35000, $result, 0.1);
    }

    #[Test]
    public function it_converts_meters_to_feet(): void
    {
        $altitude = Altitude::fromMeters(10668); // ~35000 ft

        $result = $this->cast->set($this->model, 'altitude', $altitude, []);

        $this->assertEqualsWithDelta(35000, $result, 10);
    }

    #[Test]
    public function it_allows_raw_numeric_values(): void
    {
        $result = $this->cast->set($this->model, 'altitude', 25000, []);

        $this->assertEqualsWithDelta(25000, $result, 0.1);
    }
}
