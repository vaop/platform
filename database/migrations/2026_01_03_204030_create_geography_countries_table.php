<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geography_countries', function (Blueprint $table) {
            $table->id();
            $table->string('iso_alpha2', 2)->unique();
            $table->string('iso_alpha3', 3)->unique();
            $table->string('name', 100);
            $table->foreignId('continent_id')
                ->constrained('geography_continents')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->index('continent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geography_countries');
    }
};
