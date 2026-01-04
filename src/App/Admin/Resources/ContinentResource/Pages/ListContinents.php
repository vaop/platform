<?php

declare(strict_types=1);

namespace App\Admin\Resources\ContinentResource\Pages;

use App\Admin\Resources\ContinentResource;
use App\Admin\Resources\ContinentResource\Exporters\ContinentExporter;
use App\Admin\Resources\ContinentResource\Importers\ContinentImporter;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListContinents extends ListRecords
{
    protected static string $resource = ContinentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ActionGroup::make([
                ImportAction::make()
                    ->importer(ContinentImporter::class),
                ExportAction::make()
                    ->exporter(ContinentExporter::class),
            ])
                ->label('')
                ->icon('heroicon-o-ellipsis-vertical')
                ->button()
                ->color('gray'),
        ];
    }
}
