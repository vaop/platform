<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogoutTest extends TestCase
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
    public function authenticated_user_can_logout(): void
    {
        $user = User::factory()->active()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('home'));
    }

    #[Test]
    public function guest_cannot_access_logout(): void
    {
        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function session_is_invalidated_on_logout(): void
    {
        $user = User::factory()->active()->create();

        $this->actingAs($user);
        session(['test_key' => 'test_value']);

        $this->post(route('logout'));

        $this->assertNull(session('test_key'));
    }
}
