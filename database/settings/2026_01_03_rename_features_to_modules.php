<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Rename the group from 'features' to 'modules'
        DB::table('system_settings')
            ->where('group', 'features')
            ->update(['group' => 'modules']);
    }

    public function down(): void
    {
        DB::table('system_settings')
            ->where('group', 'modules')
            ->update(['group' => 'features']);
    }
};
