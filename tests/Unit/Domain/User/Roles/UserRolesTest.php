<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\Roles;

use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions for each test
        app(RolesAndPermissionsSeeder::class)->run();
    }

    #[Test]
    public function seeder_creates_superadmin_role(): void
    {
        $this->assertDatabaseHas('user_roles', ['name' => 'superadmin']);
    }

    #[Test]
    public function seeder_creates_admin_access_permission(): void
    {
        $this->assertDatabaseHas('user_permissions', ['name' => 'admin.access']);
    }

    #[Test]
    public function user_can_be_assigned_role(): void
    {
        $user = User::factory()->create();

        $user->assignRole('superadmin');

        $this->assertTrue($user->hasRole('superadmin'));
    }

    #[Test]
    public function user_can_have_multiple_roles(): void
    {
        Role::findOrCreate('admin');
        $user = User::factory()->create();

        $user->assignRole('superadmin');
        $user->assignRole('admin');

        $this->assertTrue($user->hasRole('superadmin'));
        $this->assertTrue($user->hasRole('admin'));
    }

    #[Test]
    public function user_can_be_removed_from_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $user->removeRole('superadmin');

        $this->assertFalse($user->hasRole('superadmin'));
    }

    #[Test]
    public function user_can_be_assigned_permission(): void
    {
        $user = User::factory()->create();

        $user->givePermissionTo('admin.access');

        $this->assertTrue($user->hasPermissionTo('admin.access'));
    }

    #[Test]
    public function seeder_is_idempotent(): void
    {
        // Run seeder multiple times
        app(RolesAndPermissionsSeeder::class)->run();
        app(RolesAndPermissionsSeeder::class)->run();

        // Should still only have 1 role
        $this->assertCount(1, Role::all());

        // Should still only have 1 permission
        $this->assertCount(1, Permission::all());
    }
}
