<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Domain\User\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordResetTest extends TestCase
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
    public function forgot_password_page_is_displayed(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
    }

    #[Test]
    public function reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->active()->create();

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    #[Test]
    public function reset_password_link_is_not_sent_for_nonexistent_email(): void
    {
        Notification::fake();

        $response = $this->post(route('password.email'), [
            'email' => 'nonexistent@example.com',
        ]);

        Notification::assertNothingSent();
    }

    #[Test]
    public function reset_password_page_is_displayed(): void
    {
        $user = User::factory()->active()->create();

        $token = Password::createToken($user);

        $response = $this->get(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]));

        $response->assertStatus(200);
    }

    #[Test]
    public function password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->active()->create();

        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('new-password123', $user->password));
    }

    #[Test]
    public function password_cannot_be_reset_with_invalid_token(): void
    {
        $user = User::factory()->active()->create();

        $response = $this->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function password_reset_requires_valid_email(): void
    {
        $response = $this->post(route('password.email'), [
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function password_reset_requires_password_confirmation(): void
    {
        $user = User::factory()->active()->create();

        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function password_reset_requires_matching_confirmation(): void
    {
        $user = User::factory()->active()->create();

        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
