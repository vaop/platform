<?php

declare(strict_types=1);

namespace App\Admin\Resources;

use App\Admin\Resources\CountryResource\Pages;
use Domain\Geography\Models\Country;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static string|\BackedEnum|null $navigationIcon = 'fas-flag';

    protected static string|\UnitEnum|null $navigationGroup = 'Reference';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Reference Data')
                    ->description('This is reference data used throughout the system. Changes may have unintended consequences.')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('warning')
                    ->extraAttributes(['class' => 'fi-section-warning'])
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('iso_alpha2')
                            ->label('ISO Alpha-2')
                            ->required()
                            ->maxLength(2)
                            ->unique(ignoreRecord: true),
                        TextInput::make('iso_alpha3')
                            ->label('ISO Alpha-3')
                            ->required()
                            ->maxLength(3)
                            ->unique(ignoreRecord: true),
                        Select::make('continent_id')
                            ->label('Continent')
                            ->relationship('continent', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('flag')
                    ->label('')
                    ->sortable(false)
                    ->searchable(false),
                TextColumn::make('iso_alpha2')
                    ->label('Alpha-2')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('iso_alpha3')
                    ->label('Alpha-3')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('continent.name')
                    ->label('Continent')
                    ->sortable()
                    ->color('primary')
                    ->url(fn (Country $record): ?string => $record->continent_id
                        ? ContinentResource::getUrl('edit', ['record' => $record->continent_id])
                        : null),
                TextColumn::make('metro_areas_count')
                    ->label('Metro Areas')
                    ->counts('metroAreas')
                    ->sortable()
                    ->color('primary')
                    ->url(fn (Country $record): string => MetroAreaResource::getUrl('index', [
                        'filters' => [
                            'country' => ['value' => $record->id],
                        ],
                    ])),
            ])
            ->filters([
                SelectFilter::make('continent')
                    ->relationship('continent', 'name'),
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
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}
