<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\Listeners;

use Domain\User\Events\EmailChanged;
use Domain\User\Listeners\SendEmailVerificationOnEmailChange;
use Domain\User\Models\User;
use Domain\User\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use System\Settings\RegistrationSettings;
use Tests\TestCase;

class SendEmailVerificationOnEmailChangeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markAsInstalled();
    }

    protected function tearDown(): void
    {
        $this->markAsNotInstalled();
        parent::tearDown();
    }

    #[Test]
    public function it_sends_verification_email_when_required(): void
    {
        Notification::fake();

        $settings = app(RegistrationSettings::class);
        $settings->requireEmailVerification = true;
        $settings->save();

        $user = User::factory()->active()->unverified()->create([
            'email' => 'new@example.com',
        ]);

        $event = new EmailChanged($user);

        $listener = app(SendEmailVerificationOnEmailChange::class);
        $listener->handle($event);

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    #[Test]
    public function it_does_not_send_email_when_not_required(): void
    {
        Notification::fake();

        $settings = app(RegistrationSettings::class);
        $settings->requireEmailVerification = false;
        $settings->save();

        $user = User::factory()->active()->unverified()->create([
            'email' => 'new@example.com',
        ]);

        $event = new EmailChanged($user);

        $listener = app(SendEmailVerificationOnEmailChange::class);
        $listener->handle($event);

        Notification::assertNotSentTo($user, VerifyEmailNotification::class);
    }

    #[Test]
    public function it_does_not_send_email_when_already_verified(): void
    {
        Notification::fake();

        $settings = app(RegistrationSettings::class);
        $settings->requireEmailVerification = true;
        $settings->save();

        // Note: Factory creates verified users by default, and observer doesn't
        // reset verification for test factory since it's create() not update()
        $user = User::factory()->active()->create([
            'email' => 'new@example.com',
            'email_verified_at' => now(),
        ]);

        $event = new EmailChanged($user);

        $listener = app(SendEmailVerificationOnEmailChange::class);
        $listener->handle($event);

        Notification::assertNotSentTo($user, VerifyEmailNotification::class);
    }
}
