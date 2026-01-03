<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Geography\Models;

use Domain\Geography\Models\Continent;
use Domain\Geography\Models\Country;
use Domain\Geography\Models\MetroArea;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CountryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $country = new Country;

        $this->assertEquals(['iso_alpha2', 'iso_alpha3', 'name', 'continent_id'], $country->getFillable());
    }

    #[Test]
    public function it_can_be_created_via_factory(): void
    {
        $country = Country::factory()->create();

        $this->assertNotNull($country->id);
        $this->assertNotNull($country->iso_alpha2);
        $this->assertNotNull($country->iso_alpha3);
        $this->assertNotNull($country->name);
        $this->assertNotNull($country->continent_id);
    }

    #[Test]
    public function it_can_be_created_with_specific_attributes(): void
    {
        $continent = Continent::factory()->europe()->create();
        $country = Country::factory()->create([
            'iso_alpha2' => 'DE',
            'iso_alpha3' => 'DEU',
            'name' => 'Germany',
            'continent_id' => $continent->id,
        ]);

        $this->assertEquals('DE', $country->iso_alpha2);
        $this->assertEquals('DEU', $country->iso_alpha3);
        $this->assertEquals('Germany', $country->name);
        $this->assertEquals($continent->id, $country->continent_id);
    }

    #[Test]
    public function it_belongs_to_continent(): void
    {
        $continent = Continent::factory()->europe()->create();
        $country = Country::factory()->create(['continent_id' => $continent->id]);

        $this->assertInstanceOf(Continent::class, $country->continent);
        $this->assertEquals($continent->id, $country->continent->id);
    }

    #[Test]
    public function it_has_metro_areas_relationship(): void
    {
        $country = Country::factory()->create();

        $this->assertInstanceOf(Collection::class, $country->metroAreas);
    }

    #[Test]
    public function it_can_have_multiple_metro_areas(): void
    {
        $country = Country::factory()->create();
        MetroArea::factory()->count(3)->create(['country_id' => $country->id]);

        $this->assertCount(3, $country->metroAreas);
    }

    #[Test]
    public function it_has_users_relationship(): void
    {
        $country = Country::factory()->create();

        $this->assertInstanceOf(Collection::class, $country->users);
    }

    #[Test]
    public function it_can_have_multiple_users(): void
    {
        $country = Country::factory()->create();
        User::factory()->count(3)->create(['country_id' => $country->id]);

        $this->assertCount(3, $country->users);
    }

    #[Test]
    public function it_uses_geography_countries_table(): void
    {
        $country = new Country;

        $this->assertEquals('geography_countries', $country->getTable());
    }

    #[Test]
    public function it_can_use_factory_country_states(): void
    {
        $us = Country::factory()->unitedStates()->create();
        $uk = Country::factory()->unitedKingdom()->create();
        $germany = Country::factory()->germany()->create();

        $this->assertEquals('US', $us->iso_alpha2);
        $this->assertEquals('USA', $us->iso_alpha3);
        $this->assertEquals('United States', $us->name);

        $this->assertEquals('GB', $uk->iso_alpha2);
        $this->assertEquals('GBR', $uk->iso_alpha3);
        $this->assertEquals('United Kingdom', $uk->name);

        $this->assertEquals('DE', $germany->iso_alpha2);
        $this->assertEquals('DEU', $germany->iso_alpha3);
        $this->assertEquals('Germany', $germany->name);
    }

    #[Test]
    public function it_can_use_for_continent_factory_state(): void
    {
        $continent = Continent::factory()->create();
        $country = Country::factory()->forContinent($continent)->create();

        $this->assertEquals($continent->id, $country->continent_id);
    }

    #[Test]
    public function iso_alpha2_must_be_unique(): void
    {
        Country::factory()->create(['iso_alpha2' => 'US']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Country::factory()->create(['iso_alpha2' => 'US']);
    }

    #[Test]
    public function iso_alpha3_must_be_unique(): void
    {
        Country::factory()->create(['iso_alpha3' => 'USA']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Country::factory()->create(['iso_alpha3' => 'USA']);
    }

    #[Test]
    public function it_has_flag_emoji_attribute(): void
    {
        $us = Country::factory()->unitedStates()->create();
        $uk = Country::factory()->unitedKingdom()->create();
        $germany = Country::factory()->germany()->create();

        $this->assertEquals('🇺🇸', $us->flag);
        $this->assertEquals('🇬🇧', $uk->flag);
        $this->assertEquals('🇩🇪', $germany->flag);
    }

    #[Test]
    public function it_cannot_be_deleted_with_metro_areas(): void
    {
        $country = Country::factory()->create();
        MetroArea::factory()->create(['country_id' => $country->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $country->delete();
    }

    #[Test]
    public function it_cannot_be_deleted_with_users(): void
    {
        $country = Country::factory()->create();
        User::factory()->create(['country_id' => $country->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $country->delete();
    }
}
