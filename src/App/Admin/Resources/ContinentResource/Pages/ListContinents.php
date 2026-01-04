<?php

declare(strict_types=1);

namespace App\Admin\Resources\ContinentResource\Pages;

use App\Admin\Resources\ContinentResource;
use App\Admin\Resources\ContinentResource\Exporters\ContinentExporter;
use App\Admin\Resources\ContinentResource\Importers\ContinentImporter;
use Domain\Geography\Seeders\ContinentSeeder;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Notifications\Notification;
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
                ActionGroup::make([
                    Action::make('sync')
                        ->label('Sync Dataset')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-exclamation-triangle')
                        ->modalIconColor('warning')
                        ->modalHeading('Sync Continents Dataset')
                        ->modalDescription('This will sync the continents data with the standard dataset. New continents may be added and existing records may be updated. This action cannot be undone.')
                        ->action(function (): void {
                            app(ContinentSeeder::class)->run();

                            Notification::make()
                                ->title('Continents synced successfully')
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
