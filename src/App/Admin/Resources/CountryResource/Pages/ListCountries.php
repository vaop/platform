<?php

declare(strict_types=1);

namespace App\Admin\Resources\CountryResource\Pages;

use App\Admin\Resources\CountryResource;
use App\Admin\Resources\CountryResource\Exporters\CountryExporter;
use App\Admin\Resources\CountryResource\Importers\CountryImporter;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListCountries extends ListRecords
{
    protected static string $resource = CountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ActionGroup::make([
                ImportAction::make()
                    ->importer(CountryImporter::class),
                ExportAction::make()
                    ->exporter(CountryExporter::class),
            ])
                ->label('')
                ->icon('heroicon-o-ellipsis-vertical')
                ->button()
                ->color('gray'),
        ];
    }
}
