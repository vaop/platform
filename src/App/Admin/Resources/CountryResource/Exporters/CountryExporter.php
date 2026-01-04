<?php

declare(strict_types=1);

namespace App\Admin\Resources\CountryResource\Exporters;

use Domain\Geography\Models\Country;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use System\Filament\Concerns\SynchronizesSmallExports;

class CountryExporter extends Exporter
{
    use SynchronizesSmallExports;

    protected static ?string $model = Country::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('iso_alpha2')
                ->label('ISO Alpha-2'),
            ExportColumn::make('iso_alpha3')
                ->label('ISO Alpha-3'),
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('continent.code')
                ->label('Continent'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $count = number_format($export->successful_rows);

        return "Exported {$count} countries.";
    }
}
