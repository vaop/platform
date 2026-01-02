<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Domain\User\Enums\UserStatus;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use System\Settings\RegistrationSettings;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markAsInstalled();
    }

    protected function tearDown(): void
    {
        RateLimiter::clear('test@example.com|127.0.0.1');
        $this->markAsNotInstalled();
        parent::tearDown();
    }

    #[Test]
    public function login_page_is_displayed(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    #[Test]
    public function authenticated_user_is_redirected_from_login_page(): void
    {
        $user = User::factory()->active()->create();

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect(route('home'));
    }

    #[Test]
    public function user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->active()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home'));
    }

    #[Test]
    public function user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->active()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function user_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function pending_user_cannot_login(): void
    {
        // Ensure approval is required so user stays pending
        $settings = app(RegistrationSettings::class);
        $settings->requireApproval = true;
        $settings->save();

        $user = User::factory()->pending()->create([
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function pending_user_is_auto_activated_when_settings_become_less_restrictive(): void
    {
        // User registered when email verification was required
        $user = User::factory()->pending()->unverified()->create([
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        // Settings changed: no longer require email verification or approval
        $settings = app(RegistrationSettings::class);
        $settings->requireApproval = false;
        $settings->requireEmailVerification = false;
        $settings->save();

        // User should be able to login and get auto-activated
        $response = $this->post(route('login'), [
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home'));
        $this->assertEquals(UserStatus::Active, $user->fresh()->status);
    }

    #[Test]
    public function pending_user_with_verified_email_is_auto_activated_when_approval_disabled(): void
    {
        // User registered when approval was required, but verified their email
        $user = User::factory()->pending()->create([
            'email' => 'pending@example.com',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);

        // Settings changed: no longer require approval
        $settings = app(RegistrationSettings::class);
        $settings->requireApproval = false;
        $settings->requireEmailVerification = true;
        $settings->save();

        // User should be able to login and get auto-activated
        $response = $this->post(route('login'), [
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $this->assertEquals(UserStatus::Active, $user->fresh()->status);
    }

    #[Test]
    public function pending_user_stays_pending_when_approval_still_required(): void
    {
        $user = User::factory()->pending()->create([
            'email' => 'pending@example.com',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);

        // Approval still required
        $settings = app(RegistrationSettings::class);
        $settings->requireApproval = true;
        $settings->requireEmailVerification = false;
        $settings->save();

        $response = $this->post(route('login'), [
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $this->assertEquals(UserStatus::Pending, $user->fresh()->status);
    }

    #[Test]
    public function pending_user_stays_pending_when_email_not_verified(): void
    {
        $user = User::factory()->pending()->unverified()->create([
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        // Email verification still required
        $settings = app(RegistrationSettings::class);
        $settings->requireApproval = false;
        $settings->requireEmailVerification = true;
        $settings->save();

        $response = $this->post(route('login'), [
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $this->assertEquals(UserStatus::Pending, $user->fresh()->status);
    }

    #[Test]
    public function inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create([
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function suspended_user_cannot_login(): void
    {
        $user = User::factory()->suspended()->create([
            'email' => 'suspended@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'suspended@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function last_login_at_is_updated_on_successful_login(): void
    {
        $user = User::factory()->active()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
            'last_login_at' => null,
        ]);

        $this->assertNull($user->last_login_at);

        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
    }

    #[Test]
    public function remember_me_creates_remember_token(): void
    {
        $user = User::factory()->active()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $user->refresh();
        $this->assertNotNull($user->remember_token);
    }

    #[Test]
    public function login_is_rate_limited_after_too_many_attempts(): void
    {
        $user = User::factory()->active()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function rate_limiter_is_cleared_on_successful_login(): void
    {
        $user = User::factory()->active()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Make 3 failed attempts
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('login'), [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // Successful login
        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();

        // Logout and verify rate limiter was cleared
        $this->post(route('logout'));

        // Should be able to make 5 more failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
