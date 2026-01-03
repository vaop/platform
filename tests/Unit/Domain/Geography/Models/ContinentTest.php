<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Geography\Models;

use Domain\Geography\Models\Continent;
use Domain\Geography\Models\Country;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContinentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $continent = new Continent;

        $this->assertEquals(['code', 'name'], $continent->getFillable());
    }

    #[Test]
    public function it_can_be_created_via_factory(): void
    {
        $continent = Continent::factory()->create();

        $this->assertNotNull($continent->id);
        $this->assertNotNull($continent->code);
        $this->assertNotNull($continent->name);
    }

    #[Test]
    public function it_can_be_created_with_specific_attributes(): void
    {
        $continent = Continent::factory()->create([
            'code' => 'EU',
            'name' => 'Europe',
        ]);

        $this->assertEquals('EU', $continent->code);
        $this->assertEquals('Europe', $continent->name);
    }

    #[Test]
    public function it_has_countries_relationship(): void
    {
        $continent = Continent::factory()->create();

        $this->assertInstanceOf(Collection::class, $continent->countries);
    }

    #[Test]
    public function it_can_have_multiple_countries(): void
    {
        $continent = Continent::factory()->create();
        Country::factory()->count(3)->create(['continent_id' => $continent->id]);

        $this->assertCount(3, $continent->countries);
    }

    #[Test]
    public function it_uses_geography_continents_table(): void
    {
        $continent = new Continent;

        $this->assertEquals('geography_continents', $continent->getTable());
    }

    #[Test]
    public function it_can_use_factory_continent_states(): void
    {
        $europe = Continent::factory()->europe()->create();
        $asia = Continent::factory()->asia()->create();
        $northAmerica = Continent::factory()->northAmerica()->create();

        $this->assertEquals('EU', $europe->code);
        $this->assertEquals('Europe', $europe->name);

        $this->assertEquals('AS', $asia->code);
        $this->assertEquals('Asia', $asia->name);

        $this->assertEquals('NA', $northAmerica->code);
        $this->assertEquals('North America', $northAmerica->name);
    }

    #[Test]
    public function code_must_be_unique(): void
    {
        Continent::factory()->create(['code' => 'EU']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Continent::factory()->create(['code' => 'EU']);
    }

    #[Test]
    public function it_cannot_be_deleted_with_countries(): void
    {
        $continent = Continent::factory()->create();
        Country::factory()->create(['continent_id' => $continent->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $continent->delete();
    }
}
