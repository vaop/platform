<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.vaName', 'My Virtual Airline');
        $this->migrator->add('general.siteUrl', 'http://localhost');
    }

    public function down(): void
    {
        $this->migrator->delete('general.vaName');
        $this->migrator->delete('general.siteUrl');
    }
};
