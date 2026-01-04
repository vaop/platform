<?php

declare(strict_types=1);

namespace System\Filament\Models;

use Filament\Actions\Exports\Models\Export as BaseExport;

class Export extends BaseExport
{
    protected $table = 'system_exports';
}
