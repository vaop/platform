<?php

declare(strict_types=1);

namespace App\Admin\Pages\Settings;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use System\Settings\GeneralSettings;

class GeneralSettingsPage extends SettingsPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'fas-gear';

    protected static ?string $navigationLabel = 'General';

    protected static ?string $title = 'General Settings';

    protected static ?string $slug = 'settings/general';

    protected static ?int $navigationSort = 1;

    protected static function getSettingsClass(): string
    {
        return GeneralSettings::class;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('vaName')
                    ->label('VA Name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('The name of your virtual airline'),
                TextInput::make('siteUrl')
                    ->label('Site URL')
                    ->required()
                    ->url()
                    ->maxLength(255)
                    ->helperText('The public URL of your site'),
                Toggle::make('enableVanityIds')
                    ->label('Enable Vanity IDs')
                    ->helperText('Allow pilots to have custom vanity IDs instead of auto-generated ones'),
            ]);
    }
}
