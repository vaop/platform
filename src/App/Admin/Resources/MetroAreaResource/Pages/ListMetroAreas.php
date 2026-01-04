<?php

declare(strict_types=1);

namespace App\Admin\Resources\MetroAreaResource\Pages;

use App\Admin\Resources\MetroAreaResource;
use App\Admin\Resources\MetroAreaResource\Exporters\MetroAreaExporter;
use App\Admin\Resources\MetroAreaResource\Importers\MetroAreaImporter;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListMetroAreas extends ListRecords
{
    protected static string $resource = MetroAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ActionGroup::make([
                ImportAction::make()
                    ->importer(MetroAreaImporter::class)
                    ->label('Import Metro Areas'),
                ExportAction::make()
                    ->exporter(MetroAreaExporter::class)
                    ->label('Export Metro Areas')
                    ->modalDescription('Only the currently filtered records will be exported.'),
            ])
                ->label('')
                ->icon('heroicon-o-ellipsis-vertical')
                ->button()
                ->color('gray'),
        ];
    }
}
