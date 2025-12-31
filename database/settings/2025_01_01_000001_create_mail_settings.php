<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('mail.fromAddress', 'hello@example.com');
        $this->migrator->add('mail.fromName', 'Example');
        $this->migrator->add('mail.smtpScheme', null);
        $this->migrator->add('mail.smtpHost', '127.0.0.1');
        $this->migrator->add('mail.smtpPort', 2525);
        $this->migrator->add('mail.smtpUsername', null);
        $this->migrator->addEncrypted('mail.smtpPassword', null);
        $this->migrator->add('mail.ehloDomain', null);
    }

    public function down(): void
    {
        $this->migrator->delete('mail.fromAddress');
        $this->migrator->delete('mail.fromName');
        $this->migrator->delete('mail.smtpScheme');
        $this->migrator->delete('mail.smtpHost');
        $this->migrator->delete('mail.smtpPort');
        $this->migrator->delete('mail.smtpUsername');
        $this->migrator->delete('mail.smtpPassword');
        $this->migrator->delete('mail.ehloDomain');
    }
};
