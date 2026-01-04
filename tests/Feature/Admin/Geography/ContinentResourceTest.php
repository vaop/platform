<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Geography;

use App\Admin\Resources\ContinentResource;
use App\Admin\Resources\ContinentResource\Pages\CreateContinent;
use App\Admin\Resources\ContinentResource\Pages\EditContinent;
use App\Admin\Resources\ContinentResource\Pages\ListContinents;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\Geography\Models\Continent;
use Domain\Geography\Models\Country;
use Domain\User\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContinentResourceTest extends TestCase
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
    public function unauthenticated_user_cannot_access_continents_list(): void
    {
        $response = $this->get(ContinentResource::getUrl('index'));

        $response->assertRedirect();
    }

    #[Test]
    public function non_admin_user_cannot_access_continents_list(): void
    {
        $user = User::factory()->active()->create();
        $this->actingAs($user);

        $response = $this->get(ContinentResource::getUrl('index'));

        $response->assertForbidden();
    }

    #[Test]
    public function admin_can_view_continents_list(): void
    {
        $this->authenticatedAdmin();
        $continents = Continent::factory()->count(3)->create();

        Livewire::test(ListContinents::class)
            ->assertCanSeeTableRecords($continents);
    }

    #[Test]
    public function admin_can_search_continents_by_name(): void
    {
        $this->authenticatedAdmin();
        $europe = Continent::factory()->europe()->create();
        $asia = Continent::factory()->asia()->create();

        Livewire::test(ListContinents::class)
            ->searchTable('Europe')
            ->assertCanSeeTableRecords([$europe])
            ->assertCanNotSeeTableRecords([$asia]);
    }

    #[Test]
    public function admin_can_search_continents_by_code(): void
    {
        $this->authenticatedAdmin();
        $europe = Continent::factory()->europe()->create();
        $asia = Continent::factory()->asia()->create();

        Livewire::test(ListContinents::class)
            ->searchTable('EU')
            ->assertCanSeeTableRecords([$europe])
            ->assertCanNotSeeTableRecords([$asia]);
    }

    #[Test]
    public function admin_can_create_continent(): void
    {
        $this->authenticatedAdmin();

        Livewire::test(CreateContinent::class)
            ->fillForm([
                'code' => 'XX',
                'name' => 'Test Continent',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('geography_continents', [
            'code' => 'XX',
            'name' => 'Test Continent',
        ]);
    }

    #[Test]
    public function admin_cannot_create_duplicate_continent_code(): void
    {
        $this->authenticatedAdmin();
        Continent::factory()->create(['code' => 'EU']);

        Livewire::test(CreateContinent::class)
            ->fillForm([
                'code' => 'EU',
                'name' => 'Duplicate',
            ])
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);
    }

    #[Test]
    public function admin_can_edit_continent(): void
    {
        $this->authenticatedAdmin();
        $continent = Continent::factory()->create([
            'code' => 'EU',
            'name' => 'Europe',
        ]);

        Livewire::test(EditContinent::class, ['record' => $continent->id])
            ->fillForm([
                'name' => 'Updated Europe',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('geography_continents', [
            'id' => $continent->id,
            'name' => 'Updated Europe',
        ]);
    }

    #[Test]
    public function admin_can_delete_continent(): void
    {
        $this->authenticatedAdmin();
        $continent = Continent::factory()->create();

        Livewire::test(EditContinent::class, ['record' => $continent->id])
            ->callAction('delete');

        $this->assertDatabaseMissing('geography_continents', [
            'id' => $continent->id,
        ]);
    }

    #[Test]
    public function admin_cannot_delete_continent_with_countries(): void
    {
        $this->authenticatedAdmin();
        $continent = Continent::factory()->create();
        Country::factory()->count(3)->create(['continent_id' => $continent->id]);

        Livewire::test(EditContinent::class, ['record' => $continent->id])
            ->assertActionHidden('delete')
            ->assertActionVisible('cannotDelete');

        $this->assertDatabaseHas('geography_continents', [
            'id' => $continent->id,
        ]);
    }
}
