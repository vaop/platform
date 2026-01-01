<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\Roles;

use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SuperadminBypassTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions for each test
        app(RolesAndPermissionsSeeder::class)->run();
    }

    #[Test]
    public function superadmin_bypasses_all_permission_checks(): void
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        // Create a permission that the user doesn't explicitly have
        Permission::findOrCreate('some.random.permission');

        $this->actingAs($user);

        // Superadmin should pass any permission check
        $this->assertTrue(Gate::allows('some.random.permission'));
        $this->assertTrue(Gate::allows('nonexistent.permission'));
    }

    #[Test]
    public function regular_user_does_not_bypass_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('pilot');

        // Create a permission that the user doesn't have
        Permission::findOrCreate('some.restricted.permission');

        $this->actingAs($user);

        // Regular user should not pass permission checks they don't have
        $this->assertFalse(Gate::allows('some.restricted.permission'));
    }

    #[Test]
    public function user_with_explicit_permission_can_access(): void
    {
        $user = User::factory()->create();
        $user->assignRole('pilot');
        $user->givePermissionTo('admin.access');

        $this->actingAs($user);

        $this->assertTrue(Gate::allows('admin.access'));
    }
}
