<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Settings;

use App\Admin\Pages\Settings\ModulesSettingsPage;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\User\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use System\Settings\ModulesSettings;
use Tests\TestCase;

class ModulesSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markAsInstalled();
        app(RolesAndPermissionsSeeder::class)->run();
        Filament::setCurrentPanel('admin');
    }

    protected function tearDown(): void
    {
        $this->markAsNotInstalled();
        parent::tearDown();
    }

    private function authenticatedAdmin(): User
    {
        $user = User::factory()->active()->create();
        $user->givePermissionTo('admin.access');
        $this->actingAs($user);

        return $user;
    }

    #[Test]
    public function unauthenticated_user_cannot_access_modules_settings(): void
    {
        $response = $this->get('/admin/settings/modules');

        $response->assertRedirect();
    }

    #[Test]
    public function non_admin_user_cannot_access_modules_settings(): void
    {
        $user = User::factory()->active()->create();
        $this->actingAs($user);

        $response = $this->get('/admin/settings/modules');

        $response->assertForbidden();
    }

    #[Test]
    public function admin_can_view_modules_settings_page(): void
    {
        $this->authenticatedAdmin();

        $response = $this->get('/admin/settings/modules');

        $response->assertOk();
        $response->assertSee('Module Settings');
    }

    #[Test]
    public function admin_can_enable_metro_areas(): void
    {
        $this->authenticatedAdmin();

        Livewire::test(ModulesSettingsPage::class)
            ->fillForm([
                'enableMetroAreas' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(ModulesSettings::class);
        $this->assertTrue($settings->enableMetroAreas);
    }

    #[Test]
    public function admin_can_disable_metro_areas(): void
    {
        $this->authenticatedAdmin();

        $settings = app(ModulesSettings::class);
        $settings->enableMetroAreas = true;
        $settings->save();

        Livewire::test(ModulesSettingsPage::class)
            ->fillForm([
                'enableMetroAreas' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $freshSettings = app(ModulesSettings::class);
        $this->assertFalse($freshSettings->enableMetroAreas);
    }

    #[Test]
    public function modules_settings_page_shows_current_values(): void
    {
        $this->authenticatedAdmin();

        $settings = app(ModulesSettings::class);
        $settings->enableMetroAreas = true;
        $settings->save();

        Livewire::test(ModulesSettingsPage::class)
            ->assertFormSet([
                'enableMetroAreas' => true,
            ]);
    }
}
