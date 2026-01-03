<?php

declare(strict_types=1);

namespace App\Admin\Resources\MetroAreaResource\Pages;

use App\Admin\Resources\MetroAreaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMetroArea extends EditRecord
{
    protected static string $resource = MetroAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
