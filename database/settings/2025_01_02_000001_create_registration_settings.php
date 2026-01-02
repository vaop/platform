<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('registration.registrationOpen', true);
        $this->migrator->add('registration.requireApproval', false);
        $this->migrator->add('registration.requireEmailVerification', true);
        $this->migrator->add('registration.termsUrl', null);
        $this->migrator->add('registration.privacyPolicyUrl', null);
    }

    public function down(): void
    {
        $this->migrator->delete('registration.termsUrl');
        $this->migrator->delete('registration.privacyPolicyUrl');
        $this->migrator->delete('registration.registrationOpen');
        $this->migrator->delete('registration.requireApproval');
        $this->migrator->delete('registration.requireEmailVerification');
    }
};
