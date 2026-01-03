<?php

namespace Domain\Geography\Factories;

use Domain\Geography\Models\Continent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Continent>
 */
class ContinentFactory extends Factory
{
    protected $model = Continent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->randomElement(['AF', 'AN', 'AS', 'EU', 'NA', 'OC', 'SA']),
            'name' => fake()->word(),
        ];
    }

    /**
     * Create a continent with a specific code.
     */
    public function withCode(string $code): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => $code,
        ]);
    }

    /**
     * Create an Africa continent.
     */
    public function africa(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'AF',
            'name' => 'Africa',
        ]);
    }

    /**
     * Create an Asia continent.
     */
    public function asia(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'AS',
            'name' => 'Asia',
        ]);
    }

    /**
     * Create a Europe continent.
     */
    public function europe(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'EU',
            'name' => 'Europe',
        ]);
    }

    /**
     * Create a North America continent.
     */
    public function northAmerica(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'NA',
            'name' => 'North America',
        ]);
    }

    /**
     * Create a South America continent.
     */
    public function southAmerica(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'SA',
            'name' => 'South America',
        ]);
    }

    /**
     * Create an Oceania continent.
     */
    public function oceania(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'OC',
            'name' => 'Oceania',
        ]);
    }

    /**
     * Create an Antarctica continent.
     */
    public function antarctica(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'AN',
            'name' => 'Antarctica',
        ]);
    }
}
