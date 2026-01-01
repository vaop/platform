<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\Models;

use Domain\User\Enums\UserStatus;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserStatusTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_defaults_to_pending_status(): void
    {
        $user = User::factory()->create();

        $this->assertEquals(UserStatus::Pending, $user->status);
    }

    #[Test]
    public function it_casts_status_to_enum(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::Active,
        ]);

        $this->assertInstanceOf(UserStatus::class, $user->status);
        $this->assertEquals(UserStatus::Active, $user->status);
    }

    #[Test]
    public function it_can_check_if_user_can_login(): void
    {
        $activeUser = User::factory()->create(['status' => UserStatus::Active]);
        $pendingUser = User::factory()->create(['status' => UserStatus::Pending]);
        $inactiveUser = User::factory()->create(['status' => UserStatus::Inactive]);
        $suspendedUser = User::factory()->create(['status' => UserStatus::Suspended]);

        $this->assertTrue($activeUser->canLogin());
        $this->assertFalse($pendingUser->canLogin());
        $this->assertFalse($inactiveUser->canLogin());
        $this->assertFalse($suspendedUser->canLogin());
    }

    #[Test]
    public function it_can_check_if_user_is_active(): void
    {
        $activeUser = User::factory()->create(['status' => UserStatus::Active]);
        $pendingUser = User::factory()->create(['status' => UserStatus::Pending]);

        $this->assertTrue($activeUser->isActive());
        $this->assertFalse($pendingUser->isActive());
    }

    #[Test]
    public function it_can_check_if_user_is_pending(): void
    {
        $activeUser = User::factory()->create(['status' => UserStatus::Active]);
        $pendingUser = User::factory()->create(['status' => UserStatus::Pending]);

        $this->assertFalse($activeUser->isPending());
        $this->assertTrue($pendingUser->isPending());
    }

    #[Test]
    public function it_can_check_if_user_is_suspended(): void
    {
        $activeUser = User::factory()->create(['status' => UserStatus::Active]);
        $suspendedUser = User::factory()->create(['status' => UserStatus::Suspended]);

        $this->assertFalse($activeUser->isSuspended());
        $this->assertTrue($suspendedUser->isSuspended());
    }

    #[Test]
    public function it_can_update_status(): void
    {
        $user = User::factory()->create(['status' => UserStatus::Pending]);

        $user->update(['status' => UserStatus::Active]);
        $user->refresh();

        $this->assertEquals(UserStatus::Active, $user->status);
    }
}
