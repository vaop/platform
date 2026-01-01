<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\Casts;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Support\UnitsOfMeasure\Casts\GeoCoordinateCast;
use Support\UnitsOfMeasure\ValueObjects\GeoCoordinate;
use Tests\TestCase;

class GeoCoordinateCastTest extends TestCase
{
    private GeoCoordinateCast $cast;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast = new GeoCoordinateCast;
        $this->model = new class extends Model {};
    }

    #[Test]
    public function it_casts_null_to_null(): void
    {
        $result = $this->cast->get($this->model, 'location', null, [
            'location_lat' => null,
            'location_lon' => null,
        ]);

        $this->assertNull($result);
    }

    #[Test]
    public function it_casts_lat_lon_to_coordinate(): void
    {
        $result = $this->cast->get($this->model, 'location', null, [
            'location_lat' => 40.7128,
            'location_lon' => -74.0060,
        ]);

        $this->assertInstanceOf(GeoCoordinate::class, $result);
        $this->assertEqualsWithDelta(40.7128, $result->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(-74.0060, $result->getLongitude(), 0.0001);
    }

    #[Test]
    public function it_returns_null_if_lat_missing(): void
    {
        $result = $this->cast->get($this->model, 'location', null, [
            'location_lon' => -74.0060,
        ]);

        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_null_if_lon_missing(): void
    {
        $result = $this->cast->get($this->model, 'location', null, [
            'location_lat' => 40.7128,
        ]);

        $this->assertNull($result);
    }

    #[Test]
    public function it_sets_null_to_null_columns(): void
    {
        $result = $this->cast->set($this->model, 'location', null, []);

        $this->assertSame([
            'location_lat' => null,
            'location_lon' => null,
        ], $result);
    }

    #[Test]
    public function it_sets_coordinate_to_lat_lon_columns(): void
    {
        $coord = GeoCoordinate::fromDecimalDegrees(40.7128, -74.0060);

        $result = $this->cast->set($this->model, 'location', $coord, []);

        $this->assertEqualsWithDelta(40.7128, $result['location_lat'], 0.0001);
        $this->assertEqualsWithDelta(-74.0060, $result['location_lon'], 0.0001);
    }

    #[Test]
    public function it_sets_from_array_with_lat_lon_keys(): void
    {
        $result = $this->cast->set($this->model, 'location', [
            'lat' => 40.7128,
            'lon' => -74.0060,
        ], []);

        $this->assertEqualsWithDelta(40.7128, $result['location_lat'], 0.0001);
        $this->assertEqualsWithDelta(-74.0060, $result['location_lon'], 0.0001);
    }

    #[Test]
    public function it_sets_from_array_with_latitude_longitude_keys(): void
    {
        $result = $this->cast->set($this->model, 'location', [
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ], []);

        $this->assertEqualsWithDelta(40.7128, $result['location_lat'], 0.0001);
        $this->assertEqualsWithDelta(-74.0060, $result['location_lon'], 0.0001);
    }

    #[Test]
    public function it_sets_from_indexed_array(): void
    {
        $result = $this->cast->set($this->model, 'location', [40.7128, -74.0060], []);

        $this->assertEqualsWithDelta(40.7128, $result['location_lat'], 0.0001);
        $this->assertEqualsWithDelta(-74.0060, $result['location_lon'], 0.0001);
    }

    #[Test]
    public function it_throws_on_invalid_array(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cast->set($this->model, 'location', ['invalid' => 'data'], []);
    }

    #[Test]
    public function it_throws_on_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cast->set($this->model, 'location', 'invalid', []);
    }
}
