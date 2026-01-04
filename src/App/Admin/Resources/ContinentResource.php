<?php

declare(strict_types=1);

namespace App\Admin\Resources;

use App\Admin\Resources\ContinentResource\Pages;
use Domain\Geography\Models\Continent;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContinentResource extends Resource
{
    protected static ?string $model = Continent::class;

    protected static string|\BackedEnum|null $navigationIcon = 'fas-globe-americas';

    protected static string|\UnitEnum|null $navigationGroup = 'Reference';

    protected static ?int $navigationSort = 1;

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
                        TextInput::make('code')
                            ->required()
                            ->maxLength(2)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(50),
                    ]),
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
                TextColumn::make('countries_count')
                    ->label('Countries')
                    ->counts('countries')
                    ->sortable()
                    ->color('primary')
                    ->url(fn (Continent $record): string => CountryResource::getUrl('index', [
                        'filters' => [
                            'continent' => ['value' => $record->id],
                        ],
                    ])),
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
            'index' => Pages\ListContinents::route('/'),
            'create' => Pages\CreateContinent::route('/create'),
            'edit' => Pages\EditContinent::route('/{record}/edit'),
        ];
    }
}
