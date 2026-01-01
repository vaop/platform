<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Unit preferences - null means use airline defaults
            $table->string('distance_unit', 10)->nullable();
            $table->string('altitude_unit', 10)->nullable();
            $table->string('height_unit', 10)->nullable();
            $table->string('length_unit', 10)->nullable();
            $table->string('pressure_unit', 10)->nullable();
            $table->string('speed_unit', 10)->nullable();
            $table->string('weight_unit', 10)->nullable();
            $table->string('fuel_unit', 10)->nullable();
            $table->string('volume_unit', 10)->nullable();
            $table->string('temperature_unit', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'distance_unit',
                'altitude_unit',
                'height_unit',
                'length_unit',
                'pressure_unit',
                'speed_unit',
                'weight_unit',
                'fuel_unit',
                'volume_unit',
                'temperature_unit',
            ]);
        });
    }
};
