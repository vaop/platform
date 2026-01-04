<?php

declare(strict_types=1);

namespace App\Admin\Resources\ContinentResource\Importers;

use Domain\Geography\Models\Continent;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Illuminate\Database\Eloquent\Model;
use System\Filament\Concerns\HasContextualImportNotifications;
use System\Filament\Concerns\HasImportOptions;
use System\Filament\Concerns\SynchronizesSmallImports;

class ContinentImporter extends Importer
{
    use HasContextualImportNotifications;
    use HasImportOptions;
    use SynchronizesSmallImports;

    protected static ?string $model = Continent::class;

    protected static function getImportedEntityName(): string
    {
        return 'continents';
    }

    public static function getMatchFields(): array
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
        ];
    }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->label('ID')
                ->rules(['nullable', 'integer']),
            ImportColumn::make('code')
                ->label('Code')
                ->requiredMapping()
                ->rules(['required', 'string', 'size:2'])
                ->examples(['NA', 'EU', 'AS']),
            ImportColumn::make('name')
                ->label('Name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:50'])
                ->examples(['North America', 'Europe', 'Asia']),
        ];
    }

    protected function findRecordByMatchField(string $matchField, mixed $value): ?Model
    {
        if ($matchField === 'id') {
            return Continent::find($value);
        }

        if ($matchField === 'code') {
            return Continent::where('code', strtoupper($value))->first();
        }

        return null;
    }

    protected function beforeValidate(): void
    {
        $this->data['code'] = strtoupper($this->data['code'] ?? '');
    }
}
