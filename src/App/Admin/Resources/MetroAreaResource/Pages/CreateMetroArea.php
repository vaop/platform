<?php

declare(strict_types=1);

namespace App\Admin\Resources\MetroAreaResource\Pages;

use App\Admin\Resources\MetroAreaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMetroArea extends CreateRecord
{
    protected static string $resource = MetroAreaResource::class;
}
