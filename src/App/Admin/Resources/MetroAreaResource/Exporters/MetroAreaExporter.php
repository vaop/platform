<?php

declare(strict_types=1);

namespace App\Admin\Resources\MetroAreaResource\Exporters;

use Domain\Geography\Models\MetroArea;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use System\Filament\Concerns\SynchronizesSmallExports;

class MetroAreaExporter extends Exporter
{
    use SynchronizesSmallExports;

    protected static ?string $model = MetroArea::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('code')
                ->label('Code'),
            ExportColumn::make('country.iso_alpha2')
                ->label('Country'),
            ExportColumn::make('name')
                ->label('Name'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $count = number_format($export->successful_rows);

        return "Exported {$count} metro areas.";
    }
}
