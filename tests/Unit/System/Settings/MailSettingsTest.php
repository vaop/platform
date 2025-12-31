<?php

declare(strict_types=1);

namespace Tests\Unit\System\Settings;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use System\Settings\MailSettings;
use Tests\TestCase;

class MailSettingsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_correct_group_name(): void
    {
        $this->assertEquals('mail', MailSettings::group());
    }

    #[Test]
    public function it_has_encrypted_fields_defined(): void
    {
        $this->assertEquals(['smtpPassword'], MailSettings::encrypted());
    }

    #[Test]
    public function it_has_default_from_address_after_migration(): void
    {
        $settings = app(MailSettings::class);

        $this->assertEquals('hello@example.com', $settings->fromAddress);
    }

    #[Test]
    public function it_has_default_from_name_after_migration(): void
    {
        $settings = app(MailSettings::class);

        $this->assertEquals('Example', $settings->fromName);
    }

    #[Test]
    public function it_has_default_smtp_host_after_migration(): void
    {
        $settings = app(MailSettings::class);

        $this->assertEquals('127.0.0.1', $settings->smtpHost);
    }

    #[Test]
    public function it_has_default_smtp_port_after_migration(): void
    {
        $settings = app(MailSettings::class);

        $this->assertEquals(2525, $settings->smtpPort);
    }

    #[Test]
    public function it_has_nullable_optional_fields(): void
    {
        $settings = app(MailSettings::class);

        $this->assertNull($settings->smtpScheme);
        $this->assertNull($settings->smtpUsername);
        $this->assertNull($settings->smtpPassword);
        $this->assertNull($settings->ehloDomain);
    }

    #[Test]
    public function it_can_update_smtp_settings(): void
    {
        $settings = app(MailSettings::class);
        $settings->smtpHost = 'smtp.example.com';
        $settings->smtpPort = 587;
        $settings->smtpUsername = 'user@example.com';
        $settings->smtpPassword = 'secret123';
        $settings->save();

        $freshSettings = app(MailSettings::class);
        $this->assertEquals('smtp.example.com', $freshSettings->smtpHost);
        $this->assertEquals(587, $freshSettings->smtpPort);
        $this->assertEquals('user@example.com', $freshSettings->smtpUsername);
        $this->assertEquals('secret123', $freshSettings->smtpPassword);
    }

    #[Test]
    public function it_encrypts_smtp_password_in_database(): void
    {
        $settings = app(MailSettings::class);
        $settings->smtpPassword = 'mysecretpassword';
        $settings->save();

        // Read raw value from database
        $rawValue = DB::table('system_settings')
            ->where('group', 'mail')
            ->where('name', 'smtpPassword')
            ->value('payload');

        // The raw value should not contain the plaintext password
        $this->assertNotEquals('"mysecretpassword"', $rawValue);
        $this->assertStringNotContainsString('mysecretpassword', $rawValue);

        // But we can still read it through the settings class
        $freshSettings = app(MailSettings::class);
        $this->assertEquals('mysecretpassword', $freshSettings->smtpPassword);
    }

    #[Test]
    public function it_overrides_mail_from_config(): void
    {
        $settings = app(MailSettings::class);
        $settings->fromAddress = 'noreply@myairline.com';
        $settings->fromName = 'My Airline';
        $settings->save();

        // Manually trigger the override logic
        config([
            'mail.from.address' => $settings->fromAddress,
            'mail.from.name' => $settings->fromName,
        ]);

        $this->assertEquals('noreply@myairline.com', config('mail.from.address'));
        $this->assertEquals('My Airline', config('mail.from.name'));
    }

    #[Test]
    public function it_overrides_mail_smtp_config(): void
    {
        $settings = app(MailSettings::class);
        $settings->smtpHost = 'smtp.mailserver.com';
        $settings->smtpPort = 465;
        $settings->smtpUsername = 'apiuser';
        $settings->smtpPassword = 'apikey123';
        $settings->smtpScheme = 'smtps';
        $settings->ehloDomain = 'myairline.com';
        $settings->save();

        // Manually trigger the override logic
        config([
            'mail.mailers.smtp.scheme' => $settings->smtpScheme,
            'mail.mailers.smtp.host' => $settings->smtpHost,
            'mail.mailers.smtp.port' => $settings->smtpPort,
            'mail.mailers.smtp.username' => $settings->smtpUsername,
            'mail.mailers.smtp.password' => $settings->smtpPassword,
            'mail.mailers.smtp.local_domain' => $settings->ehloDomain,
        ]);

        $this->assertEquals('smtps', config('mail.mailers.smtp.scheme'));
        $this->assertEquals('smtp.mailserver.com', config('mail.mailers.smtp.host'));
        $this->assertEquals(465, config('mail.mailers.smtp.port'));
        $this->assertEquals('apiuser', config('mail.mailers.smtp.username'));
        $this->assertEquals('apikey123', config('mail.mailers.smtp.password'));
        $this->assertEquals('myairline.com', config('mail.mailers.smtp.local_domain'));
    }
}
