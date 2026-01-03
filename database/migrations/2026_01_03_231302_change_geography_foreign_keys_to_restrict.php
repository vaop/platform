<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Change geography foreign keys to restrict delete.
     *
     * Prevents accidental deletion of continents/countries that have dependent records.
     */
    public function up(): void
    {
        // Countries: continent_id should restrict (can't delete continent with countries)
        Schema::table('geography_countries', function (Blueprint $table) {
            $table->dropForeign(['continent_id']);
            $table->foreign('continent_id')
                ->references('id')
                ->on('geography_continents')
                ->restrictOnDelete();
        });

        // Metro areas: country_id should restrict (can't delete country with metro areas)
        Schema::table('geography_metro_areas', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->foreign('country_id')
                ->references('id')
                ->on('geography_countries')
                ->restrictOnDelete();
        });

        // Users: country_id should restrict (can't delete country with users)
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->foreign('country_id')
                ->references('id')
                ->on('geography_countries')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // Revert to original cascade/nullOnDelete behavior
        Schema::table('geography_countries', function (Blueprint $table) {
            $table->dropForeign(['continent_id']);
            $table->foreign('continent_id')
                ->references('id')
                ->on('geography_continents')
                ->cascadeOnDelete();
        });

        Schema::table('geography_metro_areas', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->foreign('country_id')
                ->references('id')
                ->on('geography_countries')
                ->cascadeOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->foreign('country_id')
                ->references('id')
                ->on('geography_countries')
                ->nullOnDelete();
        });
    }
};
