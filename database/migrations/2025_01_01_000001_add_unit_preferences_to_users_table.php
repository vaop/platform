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
            $table->tinyInteger('distance_unit')->nullable();
            $table->tinyInteger('altitude_unit')->nullable();
            $table->tinyInteger('height_unit')->nullable();
            $table->tinyInteger('length_unit')->nullable();
            $table->tinyInteger('pressure_unit')->nullable();
            $table->tinyInteger('speed_unit')->nullable();
            $table->tinyInteger('weight_unit')->nullable();
            $table->tinyInteger('fuel_unit')->nullable();
            $table->tinyInteger('volume_unit')->nullable();
            $table->tinyInteger('temperature_unit')->nullable();
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
