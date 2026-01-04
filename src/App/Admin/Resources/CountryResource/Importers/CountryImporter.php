<?php

declare(strict_types=1);

namespace App\Admin\Resources\CountryResource\Importers;

use Domain\Geography\Models\Country;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Illuminate\Database\Eloquent\Model;
use System\Filament\Concerns\HasContextualImportNotifications;
use System\Filament\Concerns\HasImportOptions;
use System\Filament\Concerns\SynchronizesSmallImports;

class CountryImporter extends Importer
{
    use HasContextualImportNotifications;
    use HasImportOptions;
    use SynchronizesSmallImports;

    protected static ?string $model = Country::class;

    protected static function getImportedEntityName(): string
    {
        return 'countries';
    }

    public static function getMatchFields(): array
    {
        return [
            'id' => 'ID',
            'iso_alpha2' => 'ISO Alpha-2',
            'iso_alpha3' => 'ISO Alpha-3',
        ];
    }

    public static function getDefaultMatchField(): string
    {
        return 'iso_alpha2';
    }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->label('ID')
                ->rules(['nullable', 'integer']),
            ImportColumn::make('iso_alpha2')
                ->label('ISO Alpha-2')
                ->requiredMapping()
                ->rules(['required', 'string', 'size:2'])
                ->examples(['US', 'GB', 'DE']),
            ImportColumn::make('iso_alpha3')
                ->label('ISO Alpha-3')
                ->requiredMapping()
                ->rules(['required', 'string', 'size:3'])
                ->examples(['USA', 'GBR', 'DEU']),
            ImportColumn::make('name')
                ->label('Name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:100'])
                ->examples(['United States', 'United Kingdom', 'Germany']),
            ImportColumn::make('continent')
                ->label('Continent Code')
                ->requiredMapping()
                ->relationship(resolveUsing: 'code')
                ->rules(['required'])
                ->examples(['NA', 'EU', 'AS']),
        ];
    }

    protected function findRecordByMatchField(string $matchField, mixed $value): ?Model
    {
        if ($matchField === 'id') {
            return Country::find($value);
        }

        if ($matchField === 'iso_alpha2') {
            return Country::where('iso_alpha2', strtoupper($value))->first();
        }

        if ($matchField === 'iso_alpha3') {
            return Country::where('iso_alpha3', strtoupper($value))->first();
        }

        return null;
    }

    protected function beforeValidate(): void
    {
        $this->data['iso_alpha2'] = strtoupper($this->data['iso_alpha2'] ?? '');
        $this->data['iso_alpha3'] = strtoupper($this->data['iso_alpha3'] ?? '');
    }
}
