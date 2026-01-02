<?php

declare(strict_types=1);

namespace App\Admin\Pages\Settings;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Spatie\LaravelSettings\Settings;

/**
 * Base class for Spatie Settings pages in Filament.
 *
 * @property-read Schema $form
 */
abstract class SettingsPage extends Page
{
    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 100;

    /**
     * Get the settings class to manage.
     *
     * @return class-string<Settings>
     */
    abstract protected static function getSettingsClass(): string;

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $settings = app(static::getSettingsClass());

        $data = [];
        foreach ($settings->toArray() as $key => $value) {
            $data[$key] = $value;
        }

        $this->form->fill($data);
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
    }

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment(static::$formActionsAlignment)
                    ->sticky(static::$formActionsAreSticky),
            ]);
    }
}
