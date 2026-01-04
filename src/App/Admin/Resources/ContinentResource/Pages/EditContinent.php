<?php

declare(strict_types=1);

namespace App\Admin\Resources\ContinentResource\Pages;

use App\Admin\Actions\CannotDeleteAction;
use App\Admin\Resources\ContinentResource;
use App\Admin\Resources\CountryResource;
use Domain\Geography\Models\Continent;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContinent extends EditRecord
{
    protected static string $resource = ContinentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (Continent $record): bool => ! $record->countries()->exists()),

            CannotDeleteAction::make()
                ->recordLabel('Continent')
                ->checkRelationship('countries', 'country', 'countries', CountryResource::class, 'continent'),
        ];
    }
}
