<?php

declare(strict_types=1);

namespace App\Admin\Resources\MetroAreaResource\Importers;

use Domain\Geography\Models\Country;
use Domain\Geography\Models\MetroArea;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Illuminate\Database\Eloquent\Model;
use System\Filament\Concerns\HasContextualImportNotifications;
use System\Filament\Concerns\HasImportOptions;
use System\Filament\Concerns\SynchronizesSmallImports;

class MetroAreaImporter extends Importer
{
    use HasContextualImportNotifications;
    use HasImportOptions;
    use SynchronizesSmallImports;

    protected static ?string $model = MetroArea::class;

    protected static function getImportedEntityName(): string
    {
        return 'metro areas';
    }

    public static function getMatchFields(): array
    {
        return [
            'id' => 'ID',
            'code_country' => 'Code + Country (composite)',
        ];
    }

    public static function getDefaultMatchField(): string
    {
        return 'code_country';
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
                ->rules(['required', 'string', 'max:10'])
                ->examples(['NYC', 'LAX', 'LHR']),
            ImportColumn::make('country')
                ->label('Country')
                ->requiredMapping()
                ->relationship(resolveUsing: 'iso_alpha2')
                ->rules(['required'])
                ->examples(['US', 'US', 'GB']),
            ImportColumn::make('name')
                ->label('Name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:100'])
                ->examples(['New York City', 'Los Angeles', 'London']),
        ];
    }

    protected function findRecordByMatchField(string $matchField, mixed $value): ?Model
    {
        if ($matchField === 'id') {
            return MetroArea::find($value);
        }

        if ($matchField === 'code_country') {
            $code = strtoupper($this->data['code'] ?? '');
            $countryCode = strtoupper($this->data['country'] ?? '');

            if (blank($code) || blank($countryCode)) {
                return null;
            }

            $country = Country::where('iso_alpha2', $countryCode)->first();

            if (! $country) {
                return null;
            }

            return MetroArea::where('code', $code)
                ->where('country_id', $country->id)
                ->first();
        }

        return null;
    }

    protected function getMatchFieldValue(string $matchField): mixed
    {
        if ($matchField === 'code_country') {
            // For composite key, return a truthy value if both parts exist
            $code = $this->data['code'] ?? '';
            $country = $this->data['country'] ?? '';

            return (filled($code) && filled($country)) ? "{$code}/{$country}" : null;
        }

        return $this->data[$matchField] ?? null;
    }

    protected function beforeValidate(): void
    {
        $this->data['code'] = strtoupper($this->data['code'] ?? '');
    }
}
