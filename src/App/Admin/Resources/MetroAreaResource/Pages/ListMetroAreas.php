<?php

declare(strict_types=1);

namespace App\Admin\Resources\MetroAreaResource\Pages;

use App\Admin\Resources\MetroAreaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMetroAreas extends ListRecords
{
    protected static string $resource = MetroAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
