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
        Schema::rename('jobs', 'system_jobs');
        Schema::rename('job_batches', 'system_job_batches');
        Schema::rename('failed_jobs', 'system_failed_jobs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('system_jobs', 'jobs');
        Schema::rename('system_job_batches', 'job_batches');
        Schema::rename('system_failed_jobs', 'failed_jobs');
    }
};
