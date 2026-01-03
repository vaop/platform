<?php

declare(strict_types=1);

namespace Domain\Geography\Seeders;

use Domain\Geography\Models\Continent;
use Illuminate\Database\Seeder;

class ContinentSeeder extends Seeder
{
    /**
     * Seed the continents table with all 7 continents.
     *
     * Uses standard 2-letter continent codes from UN M.49 / ISO 3166-1.
     * These are static and rarely change, so we define them directly.
     */
    public function run(): void
    {
        $continents = [
            ['code' => 'AF', 'name' => 'Africa'],
            ['code' => 'AN', 'name' => 'Antarctica'],
            ['code' => 'AS', 'name' => 'Asia'],
            ['code' => 'EU', 'name' => 'Europe'],
            ['code' => 'NA', 'name' => 'North America'],
            ['code' => 'OC', 'name' => 'Oceania'],
            ['code' => 'SA', 'name' => 'South America'],
        ];

        foreach ($continents as $continent) {
            Continent::updateOrCreate(
                ['code' => $continent['code']],
                ['name' => $continent['name']]
            );
        }
    }
}
