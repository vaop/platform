<?php

namespace Domain\Geography\Factories;

use Domain\Geography\Models\Country;
use Domain\Geography\Models\MetroArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MetroArea>
 */
class MetroAreaFactory extends Factory
{
    protected $model = MetroArea::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->regexify('[A-Z]{3}'),
            'name' => fake()->city().' Metropolitan Area',
            'country_id' => Country::factory(),
        ];
    }

    /**
     * Set the metro area's country.
     */
    public function forCountry(Country $country): static
    {
        return $this->state(fn (array $attributes) => [
            'country_id' => $country->id,
        ]);
    }

    /**
     * Create a New York City metro area.
     */
    public function newYorkCity(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'NYC',
            'name' => 'New York City',
        ]);
    }

    /**
     * Create a London metro area.
     */
    public function london(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'LON',
            'name' => 'London',
        ]);
    }

    /**
     * Create a Tokyo metro area.
     */
    public function tokyo(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'TYO',
            'name' => 'Tokyo',
        ]);
    }

    /**
     * Create a Los Angeles metro area.
     */
    public function losAngeles(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'LAX',
            'name' => 'Los Angeles',
        ]);
    }

    /**
     * Create a San Francisco Bay Area metro area.
     */
    public function sanFranciscoBayArea(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'SFO',
            'name' => 'San Francisco Bay Area',
        ]);
    }
}
