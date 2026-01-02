<?php

declare(strict_types=1);

namespace App\Admin\Pages\Settings;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use System\Settings\RegistrationSettings;

class RegistrationSettingsPage extends SettingsPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationLabel = 'Registration';

    protected static ?string $title = 'Registration Settings';

    protected static ?string $slug = 'settings/registration';

    protected static ?int $navigationSort = 2;

    protected static function getSettingsClass(): string
    {
        return RegistrationSettings::class;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('registrationOpen')
                    ->label('Registration Open')
                    ->helperText('Allow new users to register'),
                Toggle::make('requireApproval')
                    ->label('Require Approval')
                    ->helperText('New registrations require admin approval before activation'),
                Toggle::make('requireEmailVerification')
                    ->label('Require Email Verification')
                    ->helperText('Users must verify their email address before logging in'),
                TextInput::make('termsUrl')
                    ->label('Terms of Service URL')
                    ->url()
                    ->maxLength(255)
                    ->helperText('URL to your terms of service page (optional)'),
                TextInput::make('privacyPolicyUrl')
                    ->label('Privacy Policy URL')
                    ->url()
                    ->maxLength(255)
                    ->helperText('URL to your privacy policy page (optional)'),
            ]);
    }
}
