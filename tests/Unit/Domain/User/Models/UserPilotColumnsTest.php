<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\Models;

use Carbon\Carbon;
use Domain\Geography\Models\Country;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserPilotColumnsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_nullable_vanity_id_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->vanity_id);
    }

    #[Test]
    public function it_can_set_vanity_id(): void
    {
        $user = User::factory()->create([
            'vanity_id' => 'VMS042',
        ]);

        $this->assertEquals('VMS042', $user->vanity_id);
    }

    #[Test]
    public function vanity_id_must_be_unique(): void
    {
        User::factory()->create(['vanity_id' => 'UNIQUE123']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create(['vanity_id' => 'UNIQUE123']);
    }

    #[Test]
    public function it_has_nullable_avatar(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->avatar);
    }

    #[Test]
    public function it_can_set_avatar(): void
    {
        $user = User::factory()->create([
            'avatar' => 'avatars/user123.jpg',
        ]);

        $this->assertEquals('avatars/user123.jpg', $user->avatar);
    }

    #[Test]
    public function it_has_nullable_country(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->country_id);
    }

    #[Test]
    public function it_can_set_country(): void
    {
        $country = Country::factory()->create();

        $user = User::factory()->create([
            'country_id' => $country->id,
        ]);

        $this->assertEquals($country->id, $user->country_id);
        $this->assertTrue($user->country->is($country));
    }

    #[Test]
    public function it_has_nullable_timezone(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->timezone);
    }

    #[Test]
    public function it_can_set_timezone(): void
    {
        $user = User::factory()->create([
            'timezone' => 'America/New_York',
        ]);

        $this->assertEquals('America/New_York', $user->timezone);
    }

    #[Test]
    public function it_has_nullable_last_login_at(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->last_login_at);
    }

    #[Test]
    public function it_casts_last_login_at_to_datetime(): void
    {
        $now = Carbon::parse('2025-01-15 10:30:00');

        $user = User::factory()->create([
            'last_login_at' => $now,
        ]);

        $this->assertInstanceOf(Carbon::class, $user->last_login_at);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $user->last_login_at->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_can_update_pilot_columns(): void
    {
        $country = Country::factory()->create();
        $user = User::factory()->create();

        $user->update([
            'vanity_id' => 'NEWID',
            'avatar' => 'avatars/new.jpg',
            'country_id' => $country->id,
            'timezone' => 'Europe/London',
        ]);

        $user->refresh();

        $this->assertEquals('NEWID', $user->vanity_id);
        $this->assertEquals('avatars/new.jpg', $user->avatar);
        $this->assertEquals($country->id, $user->country_id);
        $this->assertEquals('Europe/London', $user->timezone);
    }
}
