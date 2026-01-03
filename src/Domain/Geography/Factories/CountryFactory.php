<?php

namespace Domain\Geography\Factories;

use Domain\Geography\Models\Continent;
use Domain\Geography\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'iso_alpha2' => fake()->unique()->countryCode(),
            'iso_alpha3' => fake()->unique()->countryISOAlpha3(),
            'name' => fake()->country(),
            'continent_id' => Continent::factory(),
        ];
    }

    /**
     * Set the country's continent.
     */
    public function forContinent(Continent $continent): static
    {
        return $this->state(fn (array $attributes) => [
            'continent_id' => $continent->id,
        ]);
    }

    /**
     * Create a United States country.
     */
    public function unitedStates(): static
    {
        return $this->state(fn (array $attributes) => [
            'iso_alpha2' => 'US',
            'iso_alpha3' => 'USA',
            'name' => 'United States',
        ]);
    }

    /**
     * Create a United Kingdom country.
     */
    public function unitedKingdom(): static
    {
        return $this->state(fn (array $attributes) => [
            'iso_alpha2' => 'GB',
            'iso_alpha3' => 'GBR',
            'name' => 'United Kingdom',
        ]);
    }

    /**
     * Create a Germany country.
     */
    public function germany(): static
    {
        return $this->state(fn (array $attributes) => [
            'iso_alpha2' => 'DE',
            'iso_alpha3' => 'DEU',
            'name' => 'Germany',
        ]);
    }

    /**
     * Create a France country.
     */
    public function france(): static
    {
        return $this->state(fn (array $attributes) => [
            'iso_alpha2' => 'FR',
            'iso_alpha3' => 'FRA',
            'name' => 'France',
        ]);
    }

    /**
     * Create a Japan country.
     */
    public function japan(): static
    {
        return $this->state(fn (array $attributes) => [
            'iso_alpha2' => 'JP',
            'iso_alpha3' => 'JPN',
            'name' => 'Japan',
        ]);
    }
}
