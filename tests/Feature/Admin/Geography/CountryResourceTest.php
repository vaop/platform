<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Geography;

use App\Admin\Resources\CountryResource;
use App\Admin\Resources\CountryResource\Pages\CreateCountry;
use App\Admin\Resources\CountryResource\Pages\EditCountry;
use App\Admin\Resources\CountryResource\Pages\ListCountries;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\Geography\Models\Continent;
use Domain\Geography\Models\Country;
use Domain\Geography\Models\MetroArea;
use Domain\User\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CountryResourceTest extends TestCase
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
    public function unauthenticated_user_cannot_access_countries_list(): void
    {
        $response = $this->get(CountryResource::getUrl('index'));

        $response->assertRedirect();
    }

    #[Test]
    public function non_admin_user_cannot_access_countries_list(): void
    {
        $user = User::factory()->active()->create();
        $this->actingAs($user);

        $response = $this->get(CountryResource::getUrl('index'));

        $response->assertForbidden();
    }

    #[Test]
    public function admin_can_view_countries_list(): void
    {
        $this->authenticatedAdmin();
        $countries = Country::factory()->count(3)->create();

        Livewire::test(ListCountries::class)
            ->assertCanSeeTableRecords($countries);
    }

    #[Test]
    public function admin_can_search_countries_by_name(): void
    {
        $this->authenticatedAdmin();
        $us = Country::factory()->unitedStates()->create();
        $uk = Country::factory()->unitedKingdom()->create();

        Livewire::test(ListCountries::class)
            ->searchTable('United States')
            ->assertCanSeeTableRecords([$us])
            ->assertCanNotSeeTableRecords([$uk]);
    }

    #[Test]
    public function admin_can_search_countries_by_iso_alpha2(): void
    {
        $this->authenticatedAdmin();
        $us = Country::factory()->unitedStates()->create();
        $uk = Country::factory()->unitedKingdom()->create();

        Livewire::test(ListCountries::class)
            ->searchTable('US')
            ->assertCanSeeTableRecords([$us])
            ->assertCanNotSeeTableRecords([$uk]);
    }

    #[Test]
    public function admin_can_filter_countries_by_continent(): void
    {
        $this->authenticatedAdmin();
        $europe = Continent::factory()->europe()->create();
        $northAmerica = Continent::factory()->northAmerica()->create();

        $germany = Country::factory()->germany()->create(['continent_id' => $europe->id]);
        $us = Country::factory()->unitedStates()->create(['continent_id' => $northAmerica->id]);

        Livewire::test(ListCountries::class)
            ->filterTable('continent', $europe->id)
            ->assertCanSeeTableRecords([$germany])
            ->assertCanNotSeeTableRecords([$us]);
    }

    #[Test]
    public function admin_can_create_country(): void
    {
        $this->authenticatedAdmin();
        $continent = Continent::factory()->europe()->create();

        Livewire::test(CreateCountry::class)
            ->fillForm([
                'name' => 'Test Country',
                'iso_alpha2' => 'TC',
                'iso_alpha3' => 'TST',
                'continent_id' => $continent->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('geography_countries', [
            'name' => 'Test Country',
            'iso_alpha2' => 'TC',
            'iso_alpha3' => 'TST',
            'continent_id' => $continent->id,
        ]);
    }

    #[Test]
    public function admin_cannot_create_duplicate_iso_alpha2(): void
    {
        $this->authenticatedAdmin();
        $continent = Continent::factory()->create();
        Country::factory()->create(['iso_alpha2' => 'US']);

        Livewire::test(CreateCountry::class)
            ->fillForm([
                'name' => 'Duplicate',
                'iso_alpha2' => 'US',
                'iso_alpha3' => 'DUP',
                'continent_id' => $continent->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['iso_alpha2' => 'unique']);
    }

    #[Test]
    public function admin_cannot_create_duplicate_iso_alpha3(): void
    {
        $this->authenticatedAdmin();
        $continent = Continent::factory()->create();
        Country::factory()->create(['iso_alpha3' => 'USA']);

        Livewire::test(CreateCountry::class)
            ->fillForm([
                'name' => 'Duplicate',
                'iso_alpha2' => 'DU',
                'iso_alpha3' => 'USA',
                'continent_id' => $continent->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['iso_alpha3' => 'unique']);
    }

    #[Test]
    public function admin_can_edit_country(): void
    {
        $this->authenticatedAdmin();
        $country = Country::factory()->create([
            'name' => 'Original Name',
        ]);

        Livewire::test(EditCountry::class, ['record' => $country->id])
            ->fillForm([
                'name' => 'Updated Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('geography_countries', [
            'id' => $country->id,
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function admin_can_delete_country(): void
    {
        $this->authenticatedAdmin();
        $country = Country::factory()->create();

        Livewire::test(EditCountry::class, ['record' => $country->id])
            ->callAction('delete');

        $this->assertDatabaseMissing('geography_countries', [
            'id' => $country->id,
        ]);
    }

    #[Test]
    public function admin_cannot_delete_country_with_metro_areas(): void
    {
        $this->authenticatedAdmin();
        $country = Country::factory()->create();
        MetroArea::factory()->count(3)->create(['country_id' => $country->id]);

        Livewire::test(EditCountry::class, ['record' => $country->id])
            ->assertActionHidden('delete')
            ->assertActionVisible('cannotDelete');

        $this->assertDatabaseHas('geography_countries', [
            'id' => $country->id,
        ]);
    }

    #[Test]
    public function admin_cannot_delete_country_with_users(): void
    {
        $this->authenticatedAdmin();
        $country = Country::factory()->create();
        User::factory()->count(2)->create(['country_id' => $country->id]);

        Livewire::test(EditCountry::class, ['record' => $country->id])
            ->assertActionHidden('delete')
            ->assertActionVisible('cannotDelete');

        $this->assertDatabaseHas('geography_countries', [
            'id' => $country->id,
        ]);
    }
}
