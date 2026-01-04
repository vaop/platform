<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Geography;

use App\Admin\Resources\MetroAreaResource\Exporters\MetroAreaExporter;
use App\Admin\Resources\MetroAreaResource\Importers\MetroAreaImporter;
use App\Admin\Resources\MetroAreaResource\Pages\ListMetroAreas;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\Geography\Models\MetroArea;
use Domain\User\Models\User;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Imports\Models\Import;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use System\Settings\ModulesSettings;
use Tests\TestCase;

class MetroAreaImportExportTest extends TestCase
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
    public function exporter_has_correct_columns(): void
    {
        $columns = MetroAreaExporter::getColumns();

        $this->assertCount(4, $columns);
        $this->assertEquals('id', $columns[0]->getName());
        $this->assertEquals('code', $columns[1]->getName());
        $this->assertEquals('country.iso_alpha2', $columns[2]->getName());
        $this->assertEquals('name', $columns[3]->getName());
    }

    #[Test]
    public function exporter_has_correct_model(): void
    {
        $this->assertEquals(MetroArea::class, MetroAreaExporter::getModel());
    }

    #[Test]
    public function exporter_generates_completed_notification_body(): void
    {
        $export = new Export;
        $export->successful_rows = 5;

        $body = MetroAreaExporter::getCompletedNotificationBody($export);

        $this->assertEquals('Exported 5 metro areas.', $body);
    }

    #[Test]
    public function importer_has_correct_columns(): void
    {
        $columns = MetroAreaImporter::getColumns();

        $this->assertCount(4, $columns);
        $this->assertEquals('id', $columns[0]->getName());
        $this->assertEquals('code', $columns[1]->getName());
        $this->assertEquals('country', $columns[2]->getName());
        $this->assertEquals('name', $columns[3]->getName());
    }

    #[Test]
    public function importer_has_correct_model(): void
    {
        $this->assertEquals(MetroArea::class, MetroAreaImporter::getModel());
    }

    #[Test]
    public function importer_has_import_options(): void
    {
        $options = MetroAreaImporter::getOptionsFormComponents();

        $this->assertCount(4, $options);
        $this->assertEquals('matchField', $options[0]->getName());
        $this->assertEquals('updateExisting', $options[1]->getName());
        $this->assertEquals('createNew', $options[2]->getName());
        $this->assertEquals('validateOnly', $options[3]->getName());
    }

    #[Test]
    public function importer_has_match_fields(): void
    {
        $matchFields = MetroAreaImporter::getMatchFields();

        $this->assertArrayHasKey('id', $matchFields);
        $this->assertArrayHasKey('code_country', $matchFields);
    }

    #[Test]
    public function importer_generates_completed_notification_body(): void
    {
        $import = new Import;
        $import->successful_rows = 5;
        $import->importer_data = ['options' => ['validateOnly' => false]];

        $body = MetroAreaImporter::getCompletedNotificationBody($import);

        $this->assertEquals('Imported 5 metro areas.', $body);
    }

    #[Test]
    public function importer_generates_validate_only_notification_body(): void
    {
        $import = new Import;
        $import->successful_rows = 5;
        $import->importer_data = ['options' => ['validateOnly' => true]];

        $body = MetroAreaImporter::getCompletedNotificationBody($import);

        $this->assertStringContainsString('Validated 5 rows', $body);
        $this->assertStringContainsString('No data was imported', $body);
    }

    #[Test]
    public function admin_can_see_import_export_action_group_on_list_page(): void
    {
        $this->authenticatedAdmin();

        Livewire::test(ListMetroAreas::class)
            ->assertActionExists('import')
            ->assertActionExists('export');
    }
}
