<?php

declare(strict_types=1);

namespace App\Admin\Pages\Settings;

use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use System\Settings\ModulesSettings;

class ModulesSettingsPage extends SettingsPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'fas-puzzle-piece';

    protected static ?string $navigationLabel = 'Modules';

    protected static ?string $title = 'Module Settings';

    protected static ?string $slug = 'settings/modules';

    protected static ?int $navigationSort = 5;

    protected static function getSettingsClass(): string
    {
        return ModulesSettings::class;
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = app(static::getSettingsClass());

        foreach ($data as $key => $value) {
            if (property_exists($settings, $key)) {
                $settings->{$key} = $value;
            }
        }

        $settings->save();

        Notification::make()
            ->success()
            ->title('Settings saved')
            ->send();

        // Redirect to refresh the page and rebuild navigation
        $this->redirect(static::getUrl());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('enableMetroAreas')
                    ->label('Enable Metro Areas')
                    ->helperText('Enable the metro areas module for geographic organization of airports and crew bases'),
            ]);
    }
}
