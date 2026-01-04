<?php

declare(strict_types=1);

namespace System\Filament\Models;

use Filament\Actions\Imports\Models\FailedImportRow as BaseFailedImportRow;

class FailedImportRow extends BaseFailedImportRow
{
    protected $table = 'system_failed_import_rows';
}
