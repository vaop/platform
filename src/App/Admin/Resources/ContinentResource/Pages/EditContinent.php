<?php

declare(strict_types=1);

namespace App\Admin\Resources\ContinentResource\Pages;

use App\Admin\Resources\ContinentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContinent extends EditRecord
{
    protected static string $resource = ContinentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
