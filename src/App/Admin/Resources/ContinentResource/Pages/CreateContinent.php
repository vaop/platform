<?php

declare(strict_types=1);

namespace App\Admin\Resources\ContinentResource\Pages;

use App\Admin\Resources\ContinentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContinent extends CreateRecord
{
    protected static string $resource = ContinentResource::class;
}
