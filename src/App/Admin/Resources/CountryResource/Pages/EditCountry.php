<?php

declare(strict_types=1);

namespace App\Admin\Resources\CountryResource\Pages;

use App\Admin\Actions\CannotDeleteAction;
use App\Admin\Resources\CountryResource;
use App\Admin\Resources\MetroAreaResource;
use Domain\Geography\Models\Country;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCountry extends EditRecord
{
    protected static string $resource = CountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (Country $record): bool => ! $this->hasRelatedRecords($record)),

            CannotDeleteAction::make()
                ->recordLabel('Country')
                ->checkRelationship('metroAreas', 'metro area', 'metro areas', MetroAreaResource::class, 'country')
                ->checkRelationship('users', 'user'),
        ];
    }

    private function hasRelatedRecords(Country $record): bool
    {
        return $record->metroAreas()->exists() || $record->users()->exists();
    }
}
