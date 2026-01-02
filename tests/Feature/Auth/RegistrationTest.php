<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Domain\User\Enums\UserStatus;
use Domain\User\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use System\Settings\RegistrationSettings;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markAsInstalled();
        $this->seedRolesAndPermissions();
    }

    protected function tearDown(): void
    {
        $this->markAsNotInstalled();
        parent::tearDown();
    }

    private function seedRolesAndPermissions(): void
    {
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    #[Test]
    public function registration_page_is_displayed(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
    }

    #[Test]
    public function authenticated_user_is_redirected_from_registration_page(): void
    {
        $user = User::factory()->active()->create();

        $response = $this->actingAs($user)->get(route('register'));

        $response->assertRedirect(route('home'));
    }

    #[Test]
    public function user_can_register_with_valid_data(): void
    {
        Event::fake([Registered::class]);

        // Disable email verification for direct activation
        $settings = app(RegistrationSettings::class);
        $settings->requireEmailVerification = false;
        $settings->requireApproval = false;
        $settings->save();

        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'status' => UserStatus::Active->value,
        ]);

        Event::assertDispatched(Registered::class);
    }

    #[Test]
    public function user_can_register_with_optional_fields(): void
    {
        // Disable email verification for direct activation
        $settings = app(RegistrationSettings::class);
        $settings->requireEmailVerification = false;
        $settings->requireApproval = false;
        $settings->save();

        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country' => 'US',
            'timezone' => 'America/New_York',
            'terms' => true,
        ]);

        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'country' => 'US',
            'timezone' => 'America/New_York',
        ]);
    }

    #[Test]
    public function registration_requires_name(): void
    {
        $response = $this->post(route('register'), [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function registration_requires_email(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function registration_requires_valid_email(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function registration_requires_password(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'terms' => true,
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function registration_requires_password_confirmation(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'terms' => true,
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function registration_requires_matching_password_confirmation(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
            'terms' => true,
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function registration_requires_terms_acceptance_when_terms_url_set(): void
    {
        $settings = app(RegistrationSettings::class);
        $settings->termsUrl = 'https://example.com/terms';
        $settings->save();

        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('terms');
    }

    #[Test]
    public function registration_does_not_require_terms_when_no_legal_urls(): void
    {
        // Ensure no legal URLs are set and no email verification
        $settings = app(RegistrationSettings::class);
        $settings->termsUrl = null;
        $settings->privacyPolicyUrl = null;
        $settings->requireEmailVerification = false;
        $settings->requireApproval = false;
        $settings->save();

        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home'));
    }

    #[Test]
    public function registration_validates_country_code_length(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country' => 'USA',
            'terms' => true,
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('country');
    }

    #[Test]
    public function registration_validates_timezone(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'timezone' => 'Invalid/Timezone',
            'terms' => true,
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('timezone');
    }

    #[Test]
    public function registration_is_blocked_when_closed(): void
    {
        $settings = app(RegistrationSettings::class);
        $settings->registrationOpen = false;
        $settings->save();

        $response = $this->get(route('register'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function user_is_pending_when_approval_required(): void
    {
        $settings = app(RegistrationSettings::class);
        $settings->requireApproval = true;
        $settings->save();

        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'status' => UserStatus::Pending->value,
        ]);
    }

    #[Test]
    public function verification_email_is_sent_when_required(): void
    {
        Notification::fake();

        $settings = app(RegistrationSettings::class);
        $settings->requireEmailVerification = true;
        $settings->requireApproval = false;
        $settings->save();

        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $user = User::where('email', 'test@example.com')->first();
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);

        // User is logged in but pending until email verified
        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'status' => UserStatus::Pending->value,
        ]);
    }

    #[Test]
    public function user_is_pending_when_email_verification_required(): void
    {
        $settings = app(RegistrationSettings::class);
        $settings->requireEmailVerification = true;
        $settings->requireApproval = false;
        $settings->save();

        $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'status' => UserStatus::Pending->value,
        ]);
    }

    #[Test]
    public function verification_email_is_not_sent_when_not_required(): void
    {
        Notification::fake();

        $settings = app(RegistrationSettings::class);
        $settings->requireEmailVerification = false;
        $settings->save();

        $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $user = User::where('email', 'test@example.com')->first();
        Notification::assertNotSentTo($user, VerifyEmail::class);
    }
}
