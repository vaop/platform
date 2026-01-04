<?php

declare(strict_types=1);

namespace App\Admin\Resources\CountryResource\Pages;

use App\Admin\Resources\CountryResource;
use App\Admin\Resources\CountryResource\Exporters\CountryExporter;
use App\Admin\Resources\CountryResource\Importers\CountryImporter;
use Domain\Geography\Seeders\CountrySeeder;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Notifications\Notification;
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
                    ->importer(CountryImporter::class)
                    ->label('Import Countries'),
                ExportAction::make()
                    ->exporter(CountryExporter::class)
                    ->label('Export Countries')
                    ->modalDescription('Only the currently filtered records will be exported.'),
                ActionGroup::make([
                    Action::make('sync')
                        ->label('Sync Dataset')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-exclamation-triangle')
                        ->modalIconColor('warning')
                        ->modalHeading('Sync Countries Dataset')
                        ->modalDescription('This will sync the countries data with the standard dataset. New countries may be added and existing records may be updated. This action cannot be undone.')
                        ->action(function (): void {
                            app(CountrySeeder::class)->run();

                            Notification::make()
                                ->title('Countries synced successfully')
                                ->success()
                                ->send();
                        }),
                ])->dropdown(false),
            ])
                ->label('')
                ->icon('heroicon-o-ellipsis-vertical')
                ->button()
                ->color('gray'),
        ];
    }
}
