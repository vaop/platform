<?php

declare(strict_types=1);

namespace System\Filament\Models;

use Filament\Actions\Imports\Models\Import as BaseImport;

class Import extends BaseImport
{
    protected $table = 'system_imports';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'importer_data' => 'array',
        ]);
    }

    public function failedRows(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FailedImportRow::class, 'import_id');
    }
}
