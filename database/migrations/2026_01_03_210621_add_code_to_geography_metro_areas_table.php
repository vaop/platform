<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('geography_metro_areas', function (Blueprint $table) {
            $table->string('code', 3)->after('id');
            $table->unique(['code', 'country_id'], 'metro_areas_code_country_unique');
        });
    }

    public function down(): void
    {
        Schema::table('geography_metro_areas', function (Blueprint $table) {
            $table->dropUnique('metro_areas_code_country_unique');
            $table->dropColumn('code');
        });
    }
};
