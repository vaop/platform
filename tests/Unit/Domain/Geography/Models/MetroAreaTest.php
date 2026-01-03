<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Geography\Models;

use Domain\Geography\Models\Country;
use Domain\Geography\Models\MetroArea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MetroAreaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $metroArea = new MetroArea;

        $this->assertEquals(['code', 'name', 'country_id'], $metroArea->getFillable());
    }

    #[Test]
    public function it_can_be_created_via_factory(): void
    {
        $metroArea = MetroArea::factory()->create();

        $this->assertNotNull($metroArea->id);
        $this->assertNotNull($metroArea->code);
        $this->assertNotNull($metroArea->name);
        $this->assertNotNull($metroArea->country_id);
    }

    #[Test]
    public function it_can_be_created_with_specific_attributes(): void
    {
        $country = Country::factory()->create();
        $metroArea = MetroArea::factory()->create([
            'code' => 'NYC',
            'name' => 'New York City',
            'country_id' => $country->id,
        ]);

        $this->assertEquals('NYC', $metroArea->code);
        $this->assertEquals('New York City', $metroArea->name);
        $this->assertEquals($country->id, $metroArea->country_id);
    }

    #[Test]
    public function it_belongs_to_country(): void
    {
        $country = Country::factory()->unitedStates()->create();
        $metroArea = MetroArea::factory()->create(['country_id' => $country->id]);

        $this->assertInstanceOf(Country::class, $metroArea->country);
        $this->assertEquals($country->id, $metroArea->country->id);
    }

    #[Test]
    public function it_uses_geography_metro_areas_table(): void
    {
        $metroArea = new MetroArea;

        $this->assertEquals('geography_metro_areas', $metroArea->getTable());
    }

    #[Test]
    public function it_can_use_factory_metro_area_states(): void
    {
        $nyc = MetroArea::factory()->newYorkCity()->create();
        $london = MetroArea::factory()->london()->create();
        $la = MetroArea::factory()->losAngeles()->create();

        $this->assertEquals('NYC', $nyc->code);
        $this->assertEquals('New York City', $nyc->name);
        $this->assertEquals('LON', $london->code);
        $this->assertEquals('London', $london->name);
        $this->assertEquals('LAX', $la->code);
        $this->assertEquals('Los Angeles', $la->name);
    }

    #[Test]
    public function it_can_use_for_country_factory_state(): void
    {
        $country = Country::factory()->create();
        $metroArea = MetroArea::factory()->forCountry($country)->create();

        $this->assertEquals($country->id, $metroArea->country_id);
    }

    #[Test]
    public function it_can_access_continent_through_country(): void
    {
        $metroArea = MetroArea::factory()->create();

        $this->assertNotNull($metroArea->country->continent);
    }

    #[Test]
    public function it_enforces_unique_code_per_country(): void
    {
        $country = Country::factory()->create();
        MetroArea::factory()->create([
            'code' => 'NYC',
            'country_id' => $country->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        MetroArea::factory()->create([
            'code' => 'NYC',
            'country_id' => $country->id,
        ]);
    }

    #[Test]
    public function it_allows_same_code_in_different_countries(): void
    {
        $us = Country::factory()->unitedStates()->create();
        $uk = Country::factory()->unitedKingdom()->create();

        $usMetro = MetroArea::factory()->create([
            'code' => 'ABC',
            'country_id' => $us->id,
        ]);

        $ukMetro = MetroArea::factory()->create([
            'code' => 'ABC',
            'country_id' => $uk->id,
        ]);

        $this->assertEquals('ABC', $usMetro->code);
        $this->assertEquals('ABC', $ukMetro->code);
        $this->assertNotEquals($usMetro->country_id, $ukMetro->country_id);
    }
}
