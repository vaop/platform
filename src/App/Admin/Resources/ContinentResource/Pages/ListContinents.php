<?php

declare(strict_types=1);

namespace App\Admin\Resources\ContinentResource\Pages;

use App\Admin\Resources\ContinentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContinents extends ListRecords
{
    protected static string $resource = ContinentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
