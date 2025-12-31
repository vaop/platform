<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('cache', 'system_cache');
        Schema::rename('cache_locks', 'system_cache_locks');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('system_cache', 'cache');
        Schema::rename('system_cache_locks', 'cache_locks');
    }
};
