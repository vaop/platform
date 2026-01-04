<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Domain\User\Enums\UserStatus;
use Domain\User\Models\User;
use Domain\User\Notifications\VerifyEmailNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use System\Settings\RegistrationSettings;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
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
    public function verification_notice_page_is_displayed(): void
    {
        $user = User::factory()->active()->unverified()->create();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
    }

    #[Test]
    public function verified_user_is_redirected_from_notice_page(): void
    {
        $user = User::factory()->active()->create();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertRedirect(route('home'));
    }

    #[Test]
    public function email_can_be_verified(): void
    {
        Event::fake([Verified::class]);

        $user = User::factory()->active()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('home'));
    }

    #[Test]
    public function email_cannot_be_verified_with_invalid_hash(): void
    {
        $user = User::factory()->active()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email@example.com')]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    #[Test]
    public function already_verified_user_is_redirected(): void
    {
        $user = User::factory()->active()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('home'));
    }

    #[Test]
    public function verification_email_can_be_resent(): void
    {
        Notification::fake();

        $user = User::factory()->active()->unverified()->create();

        $response = $this->actingAs($user)->post(route('verification.send'));

        Notification::assertSentToTimes($user, VerifyEmailNotification::class, 1);
        $response->assertSessionHas('status');
    }

    #[Test]
    public function guest_cannot_access_verification_routes(): void
    {
        $response = $this->get(route('verification.notice'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function pending_user_is_activated_after_verification_when_approval_not_required(): void
    {
        $settings = app(RegistrationSettings::class);
        $settings->requireApproval = false;
        $settings->save();

        $user = User::factory()->pending()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertEquals(UserStatus::Active, $user->fresh()->status);
    }

    #[Test]
    public function pending_user_stays_pending_after_verification_when_approval_required(): void
    {
        $settings = app(RegistrationSettings::class);
        $settings->requireApproval = true;
        $settings->save();

        $user = User::factory()->pending()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertEquals(UserStatus::Pending, $user->fresh()->status);
    }
}
