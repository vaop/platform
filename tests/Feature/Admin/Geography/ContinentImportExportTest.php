<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Geography;

use App\Admin\Resources\ContinentResource\Exporters\ContinentExporter;
use App\Admin\Resources\ContinentResource\Importers\ContinentImporter;
use App\Admin\Resources\ContinentResource\Pages\ListContinents;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\Geography\Models\Continent;
use Domain\User\Models\User;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Imports\Models\Import;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContinentImportExportTest extends TestCase
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
        $columns = ContinentExporter::getColumns();

        $this->assertCount(3, $columns);
        $this->assertEquals('id', $columns[0]->getName());
        $this->assertEquals('code', $columns[1]->getName());
        $this->assertEquals('name', $columns[2]->getName());
    }

    #[Test]
    public function exporter_has_correct_model(): void
    {
        $this->assertEquals(Continent::class, ContinentExporter::getModel());
    }

    #[Test]
    public function exporter_generates_completed_notification_body(): void
    {
        $export = new Export;
        $export->successful_rows = 5;

        $body = ContinentExporter::getCompletedNotificationBody($export);

        $this->assertEquals('Exported 5 continents.', $body);
    }

    #[Test]
    public function importer_has_correct_columns(): void
    {
        $columns = ContinentImporter::getColumns();

        $this->assertCount(3, $columns);
        $this->assertEquals('id', $columns[0]->getName());
        $this->assertEquals('code', $columns[1]->getName());
        $this->assertEquals('name', $columns[2]->getName());
    }

    #[Test]
    public function importer_has_correct_model(): void
    {
        $this->assertEquals(Continent::class, ContinentImporter::getModel());
    }

    #[Test]
    public function importer_has_import_options(): void
    {
        $options = ContinentImporter::getOptionsFormComponents();

        $this->assertCount(4, $options);
        $this->assertEquals('matchField', $options[0]->getName());
        $this->assertEquals('updateExisting', $options[1]->getName());
        $this->assertEquals('createNew', $options[2]->getName());
        $this->assertEquals('validateOnly', $options[3]->getName());
    }

    #[Test]
    public function importer_has_match_fields(): void
    {
        $matchFields = ContinentImporter::getMatchFields();

        $this->assertArrayHasKey('id', $matchFields);
        $this->assertArrayHasKey('code', $matchFields);
    }

    #[Test]
    public function importer_generates_completed_notification_body(): void
    {
        $import = new Import;
        $import->successful_rows = 5;
        $import->importer_data = ['options' => ['validateOnly' => false]];

        $body = ContinentImporter::getCompletedNotificationBody($import);

        $this->assertEquals('Imported 5 continents.', $body);
    }

    #[Test]
    public function importer_generates_validate_only_notification_body(): void
    {
        $import = new Import;
        $import->successful_rows = 5;
        $import->importer_data = ['options' => ['validateOnly' => true]];

        $body = ContinentImporter::getCompletedNotificationBody($import);

        $this->assertStringContainsString('Validated 5 rows', $body);
        $this->assertStringContainsString('No data was imported', $body);
    }

    #[Test]
    public function admin_can_see_import_export_action_group_on_list_page(): void
    {
        $this->authenticatedAdmin();

        Livewire::test(ListContinents::class)
            ->assertActionExists('import')
            ->assertActionExists('export');
    }
}
