<?php

declare(strict_types=1);

namespace System\Filament\Listeners;

use Filament\Actions\Imports\Events\ImportStarted;

class StoreImportOptions
{
    public function handle(ImportStarted $event): void
    {
        $import = $event->getImport();

        $import->importer_data = [
            'columnMap' => $event->getColumnMap(),
            'options' => $event->getOptions(),
        ];

        $import->save();
    }
}
