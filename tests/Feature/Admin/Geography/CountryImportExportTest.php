<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Geography;

use App\Admin\Resources\CountryResource\Exporters\CountryExporter;
use App\Admin\Resources\CountryResource\Importers\CountryImporter;
use App\Admin\Resources\CountryResource\Pages\ListCountries;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\Geography\Models\Country;
use Domain\User\Models\User;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Imports\Models\Import;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CountryImportExportTest extends TestCase
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
    public function exporter_has_correct_columns(): void
    {
        $columns = CountryExporter::getColumns();

        $this->assertCount(5, $columns);
        $this->assertEquals('id', $columns[0]->getName());
        $this->assertEquals('iso_alpha2', $columns[1]->getName());
        $this->assertEquals('iso_alpha3', $columns[2]->getName());
        $this->assertEquals('name', $columns[3]->getName());
        $this->assertEquals('continent.code', $columns[4]->getName());
    }

    #[Test]
    public function exporter_has_correct_model(): void
    {
        $this->assertEquals(Country::class, CountryExporter::getModel());
    }

    #[Test]
    public function exporter_generates_completed_notification_body(): void
    {
        $export = new Export;
        $export->successful_rows = 10;

        $body = CountryExporter::getCompletedNotificationBody($export);

        $this->assertEquals('Exported 10 countries.', $body);
    }

    #[Test]
    public function importer_has_correct_columns(): void
    {
        $columns = CountryImporter::getColumns();

        $this->assertCount(5, $columns);
        $this->assertEquals('id', $columns[0]->getName());
        $this->assertEquals('iso_alpha2', $columns[1]->getName());
        $this->assertEquals('iso_alpha3', $columns[2]->getName());
        $this->assertEquals('name', $columns[3]->getName());
        $this->assertEquals('continent', $columns[4]->getName());
    }

    #[Test]
    public function importer_has_correct_model(): void
    {
        $this->assertEquals(Country::class, CountryImporter::getModel());
    }

    #[Test]
    public function importer_has_import_options(): void
    {
        $options = CountryImporter::getOptionsFormComponents();

        $this->assertCount(4, $options);
        $this->assertEquals('matchField', $options[0]->getName());
        $this->assertEquals('updateExisting', $options[1]->getName());
        $this->assertEquals('createNew', $options[2]->getName());
        $this->assertEquals('validateOnly', $options[3]->getName());
    }

    #[Test]
    public function importer_has_match_fields(): void
    {
        $matchFields = CountryImporter::getMatchFields();

        $this->assertArrayHasKey('id', $matchFields);
        $this->assertArrayHasKey('iso_alpha2', $matchFields);
        $this->assertArrayHasKey('iso_alpha3', $matchFields);
    }

    #[Test]
    public function importer_generates_completed_notification_body(): void
    {
        $import = new Import;
        $import->successful_rows = 10;
        $import->importer_data = ['options' => ['validateOnly' => false]];

        $body = CountryImporter::getCompletedNotificationBody($import);

        $this->assertEquals('Imported 10 countries.', $body);
    }

    #[Test]
    public function importer_generates_validate_only_notification_body(): void
    {
        $import = new Import;
        $import->successful_rows = 10;
        $import->importer_data = ['options' => ['validateOnly' => true]];

        $body = CountryImporter::getCompletedNotificationBody($import);

        $this->assertStringContainsString('Validated 10 rows', $body);
        $this->assertStringContainsString('No data was imported', $body);
    }

    #[Test]
    public function admin_can_see_import_export_action_group_on_list_page(): void
    {
        $this->authenticatedAdmin();

        Livewire::test(ListCountries::class)
            ->assertActionExists('import')
            ->assertActionExists('export');
    }
}
