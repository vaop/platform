<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\Observers;

use Domain\User\Enums\UserStatus;
use Domain\User\Events\EmailChanged;
use Domain\User\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use System\Settings\RegistrationSettings;
use Tests\TestCase;

class UserObserverTest extends TestCase
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
    public function email_change_resets_verification_status(): void
    {
        $user = User::factory()->active()->create([
            'email' => 'original@example.com',
            'email_verified_at' => now(),
        ]);

        $this->assertNotNull($user->email_verified_at);

        $user->update(['email' => 'new@example.com']);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    #[Test]
    public function email_change_dispatches_email_changed_event(): void
    {
        Event::fake([EmailChanged::class]);

        $user = User::factory()->active()->create([
            'email' => 'original@example.com',
            'email_verified_at' => now(),
        ]);

        $user->update(['email' => 'new@example.com']);

        Event::assertDispatched(EmailChanged::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    #[Test]
    public function email_change_sends_verification_email_when_required(): void
    {
        Notification::fake();

        // Ensure the listener is registered for this test
        Event::listen(
            EmailChanged::class,
            \Domain\User\Listeners\SendEmailVerificationOnEmailChange::class
        );

        $settings = app(RegistrationSettings::class);
        $settings->requireEmailVerification = true;
        $settings->save();

        $user = User::factory()->active()->create([
            'email' => 'original@example.com',
            'email_verified_at' => now(),
        ]);

        $user->update(['email' => 'new@example.com']);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    #[Test]
    public function email_change_does_not_send_verification_when_not_required(): void
    {
        Notification::fake();

        $settings = app(RegistrationSettings::class);
        $settings->requireEmailVerification = false;
        $settings->save();

        $user = User::factory()->active()->create([
            'email' => 'original@example.com',
            'email_verified_at' => now(),
        ]);

        $user->update(['email' => 'new@example.com']);

        Notification::assertNotSentTo($user, VerifyEmail::class);
    }

    #[Test]
    public function non_email_changes_do_not_affect_verification(): void
    {
        $verifiedAt = now();

        $user = User::factory()->active()->create([
            'email' => 'test@example.com',
            'email_verified_at' => $verifiedAt,
            'name' => 'Original Name',
        ]);

        $user->update(['name' => 'New Name']);

        $this->assertEquals(
            $verifiedAt->toDateTimeString(),
            $user->fresh()->email_verified_at->toDateTimeString()
        );
    }

    #[Test]
    public function non_email_changes_do_not_dispatch_email_changed_event(): void
    {
        Event::fake([EmailChanged::class]);

        $user = User::factory()->active()->create([
            'email' => 'test@example.com',
            'name' => 'Original Name',
        ]);

        $user->update(['name' => 'New Name']);

        Event::assertNotDispatched(EmailChanged::class);
    }

    #[Test]
    public function verification_still_reset_even_when_verification_not_required(): void
    {
        $settings = app(RegistrationSettings::class);
        $settings->requireEmailVerification = false;
        $settings->save();

        $user = User::factory()->active()->create([
            'email' => 'original@example.com',
            'email_verified_at' => now(),
        ]);

        $user->update(['email' => 'new@example.com']);

        // Verification status is always reset, even if not required
        // This ensures consistency if the setting is later enabled
        $this->assertNull($user->fresh()->email_verified_at);
    }

    #[Test]
    public function active_user_stays_active_when_email_changes(): void
    {
        $settings = app(RegistrationSettings::class);
        $settings->requireEmailVerification = true;
        $settings->save();

        $user = User::factory()->active()->create([
            'email' => 'original@example.com',
            'email_verified_at' => now(),
        ]);

        $this->assertEquals(UserStatus::Active, $user->status);

        $user->update(['email' => 'new@example.com']);

        $user->refresh();
        // Active users stay active - they've already been approved
        $this->assertEquals(UserStatus::Active, $user->status);
        $this->assertNull($user->email_verified_at);
    }

    #[Test]
    public function pending_user_stays_pending_when_email_changes(): void
    {
        $user = User::factory()->pending()->unverified()->create([
            'email' => 'original@example.com',
        ]);

        $user->update(['email' => 'new@example.com']);

        $user->refresh();
        $this->assertEquals(UserStatus::Pending, $user->status);
    }
}
