<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Geography;

use App\Admin\Resources\MetroAreaResource;
use App\Admin\Resources\MetroAreaResource\Pages\CreateMetroArea;
use App\Admin\Resources\MetroAreaResource\Pages\EditMetroArea;
use App\Admin\Resources\MetroAreaResource\Pages\ListMetroAreas;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\Geography\Models\Country;
use Domain\Geography\Models\MetroArea;
use Domain\User\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use System\Settings\ModulesSettings;
use Tests\TestCase;

class MetroAreaResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markAsInstalled();
        app(RolesAndPermissionsSeeder::class)->run();
        Filament::setCurrentPanel('admin');

        // Enable metro areas module for these tests
        $settings = app(ModulesSettings::class);
        $settings->enableMetroAreas = true;
        $settings->save();
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
    public function unauthenticated_user_cannot_access_metro_areas_list(): void
    {
        $response = $this->get(MetroAreaResource::getUrl('index'));

        $response->assertRedirect();
    }

    #[Test]
    public function non_admin_user_cannot_access_metro_areas_list(): void
    {
        $user = User::factory()->active()->create();
        $this->actingAs($user);

        $response = $this->get(MetroAreaResource::getUrl('index'));

        $response->assertForbidden();
    }

    #[Test]
    public function admin_can_view_metro_areas_list(): void
    {
        $this->authenticatedAdmin();
        $metroAreas = MetroArea::factory()->count(3)->create();

        Livewire::test(ListMetroAreas::class)
            ->assertCanSeeTableRecords($metroAreas);
    }

    #[Test]
    public function admin_can_search_metro_areas_by_name(): void
    {
        $this->authenticatedAdmin();
        $nyc = MetroArea::factory()->newYorkCity()->create();
        $london = MetroArea::factory()->london()->create();

        Livewire::test(ListMetroAreas::class)
            ->searchTable('New York')
            ->assertCanSeeTableRecords([$nyc])
            ->assertCanNotSeeTableRecords([$london]);
    }

    #[Test]
    public function admin_can_filter_metro_areas_by_country(): void
    {
        $this->authenticatedAdmin();
        $us = Country::factory()->unitedStates()->create();
        $uk = Country::factory()->unitedKingdom()->create();

        $nyc = MetroArea::factory()->newYorkCity()->create(['country_id' => $us->id]);
        $london = MetroArea::factory()->london()->create(['country_id' => $uk->id]);

        Livewire::test(ListMetroAreas::class)
            ->filterTable('country', $us->id)
            ->assertCanSeeTableRecords([$nyc])
            ->assertCanNotSeeTableRecords([$london]);
    }

    #[Test]
    public function admin_can_create_metro_area(): void
    {
        $this->authenticatedAdmin();
        $country = Country::factory()->unitedStates()->create();

        Livewire::test(CreateMetroArea::class)
            ->fillForm([
                'code' => 'TMA',
                'name' => 'Test Metro Area',
                'country_id' => $country->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('geography_metro_areas', [
            'code' => 'TMA',
            'name' => 'Test Metro Area',
            'country_id' => $country->id,
        ]);
    }

    #[Test]
    public function admin_can_edit_metro_area(): void
    {
        $this->authenticatedAdmin();
        $metroArea = MetroArea::factory()->create([
            'code' => 'OLD',
            'name' => 'Original Name',
        ]);

        Livewire::test(EditMetroArea::class, ['record' => $metroArea->id])
            ->fillForm([
                'code' => 'NEW',
                'name' => 'Updated Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('geography_metro_areas', [
            'id' => $metroArea->id,
            'code' => 'NEW',
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function admin_can_delete_metro_area(): void
    {
        $this->authenticatedAdmin();
        $metroArea = MetroArea::factory()->create();

        Livewire::test(EditMetroArea::class, ['record' => $metroArea->id])
            ->callAction('delete');

        $this->assertDatabaseMissing('geography_metro_areas', [
            'id' => $metroArea->id,
        ]);
    }

    #[Test]
    public function admin_can_change_metro_area_country(): void
    {
        $this->authenticatedAdmin();
        $originalCountry = Country::factory()->unitedStates()->create();
        $newCountry = Country::factory()->unitedKingdom()->create();

        $metroArea = MetroArea::factory()->create(['country_id' => $originalCountry->id]);

        Livewire::test(EditMetroArea::class, ['record' => $metroArea->id])
            ->fillForm([
                'country_id' => $newCountry->id,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('geography_metro_areas', [
            'id' => $metroArea->id,
            'country_id' => $newCountry->id,
        ]);
    }

    #[Test]
    public function code_is_required_when_creating_metro_area(): void
    {
        $this->authenticatedAdmin();
        $country = Country::factory()->create();

        Livewire::test(CreateMetroArea::class)
            ->fillForm([
                'code' => '',
                'name' => 'Test Metro Area',
                'country_id' => $country->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'required']);
    }

    #[Test]
    public function code_is_converted_to_uppercase(): void
    {
        $this->authenticatedAdmin();
        $country = Country::factory()->create();

        Livewire::test(CreateMetroArea::class)
            ->fillForm([
                'code' => 'abc',
                'name' => 'Test Metro Area',
                'country_id' => $country->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('geography_metro_areas', [
            'code' => 'ABC',
            'name' => 'Test Metro Area',
        ]);
    }

    #[Test]
    public function admin_can_search_metro_areas_by_code(): void
    {
        $this->authenticatedAdmin();
        $nyc = MetroArea::factory()->newYorkCity()->create();
        $london = MetroArea::factory()->london()->create();

        Livewire::test(ListMetroAreas::class)
            ->searchTable('NYC')
            ->assertCanSeeTableRecords([$nyc])
            ->assertCanNotSeeTableRecords([$london]);
    }

    #[Test]
    public function admin_cannot_access_metro_areas_when_module_disabled(): void
    {
        $this->authenticatedAdmin();

        // Disable the module
        $settings = app(ModulesSettings::class);
        $settings->enableMetroAreas = false;
        $settings->save();

        $response = $this->get(MetroAreaResource::getUrl('index'));

        $response->assertForbidden();
    }
}
