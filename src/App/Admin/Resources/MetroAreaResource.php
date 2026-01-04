<?php

declare(strict_types=1);

namespace App\Admin\Resources;

use App\Admin\Resources\MetroAreaResource\Pages;
use Domain\Geography\Models\MetroArea;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MetroAreaResource extends Resource
{
    protected static ?string $model = MetroArea::class;

    protected static string|\BackedEnum|null $navigationIcon = 'fas-city';

    protected static string|\UnitEnum|null $navigationGroup = 'Scheduling';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('code')
                    ->required()
                    ->maxLength(3)
                    ->alpha()
                    ->formatStateUsing(fn (?string $state) => $state ? strtoupper($state) : null)
                    ->dehydrateStateUsing(fn (string $state) => strtoupper($state)),
                TextInput::make('name')
                    ->required()
                    ->maxLength(100),
                Select::make('country_id')
                    ->label('Country')
                    ->relationship('country', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('country.name')
                    ->label('Country')
                    ->sortable()
                    ->color('primary')
                    ->url(fn (MetroArea $record): ?string => $record->country_id
                        ? CountryResource::getUrl('edit', ['record' => $record->country_id])
                        : null),
                TextColumn::make('country.continent.name')
                    ->label('Continent')
                    ->sortable()
                    ->color('primary')
                    ->url(fn (MetroArea $record): ?string => $record->country?->continent_id
                        ? ContinentResource::getUrl('edit', ['record' => $record->country->continent_id])
                        : null),
            ])
            ->filters([
                SelectFilter::make('country')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMetroAreas::route('/'),
            'create' => Pages\CreateMetroArea::route('/create'),
            'edit' => Pages\EditMetroArea::route('/{record}/edit'),
        ];
    }
}
