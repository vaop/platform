<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\Geography\Seeders\ContinentSeeder;
use Domain\Geography\Seeders\CountrySeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * This seeder is idempotent and can be run multiple times safely.
     * All sub-seeders use updateOrCreate to avoid duplicates.
     */
    public function run(): void
    {
        $this->call([
            // Note: Order matters!
            RolesAndPermissionsSeeder::class,
            ContinentSeeder::class,
            CountrySeeder::class,
        ]);
    }
}
