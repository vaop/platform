<?php

declare(strict_types=1);

namespace App\Admin\Pages\Settings;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Support\UnitsOfMeasure\Enums\AltitudeUnit;
use Support\UnitsOfMeasure\Enums\DistanceUnit;
use Support\UnitsOfMeasure\Enums\FuelUnit;
use Support\UnitsOfMeasure\Enums\HeightUnit;
use Support\UnitsOfMeasure\Enums\LengthUnit;
use Support\UnitsOfMeasure\Enums\PressureUnit;
use Support\UnitsOfMeasure\Enums\SpeedUnit;
use Support\UnitsOfMeasure\Enums\TemperatureUnit;
use Support\UnitsOfMeasure\Enums\VolumeUnit;
use Support\UnitsOfMeasure\Enums\WeightUnit;
use System\Settings\UnitsSettings;

class UnitsSettingsPage extends SettingsPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationLabel = 'Units';

    protected static ?string $title = 'Unit Preferences';

    protected static ?string $slug = 'settings/units';

    protected static ?int $navigationSort = 3;

    protected static function getSettingsClass(): string
    {
        return UnitsSettings::class;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('allowUserCustomization')
                    ->label('Allow User Customization')
                    ->helperText('When enabled, users can set their own unit preferences in their profile'),
                Select::make('distanceUnit')
                    ->label('Distance Unit')
                    ->options(DistanceUnit::options())
                    ->required(),
                Select::make('altitudeUnit')
                    ->label('Altitude Unit')
                    ->options(AltitudeUnit::options())
                    ->required(),
                Select::make('heightUnit')
                    ->label('Height Unit')
                    ->options(HeightUnit::options())
                    ->required(),
                Select::make('lengthUnit')
                    ->label('Length Unit')
                    ->options(LengthUnit::options())
                    ->required(),
                Select::make('pressureUnit')
                    ->label('Pressure Unit')
                    ->options(PressureUnit::options())
                    ->required(),
                Select::make('speedUnit')
                    ->label('Speed Unit')
                    ->options(SpeedUnit::options())
                    ->required(),
                Select::make('weightUnit')
                    ->label('Weight Unit')
                    ->options(WeightUnit::options())
                    ->required(),
                Select::make('fuelUnit')
                    ->label('Fuel Unit')
                    ->options(FuelUnit::options())
                    ->required(),
                Select::make('volumeUnit')
                    ->label('Volume Unit')
                    ->options(VolumeUnit::options())
                    ->required(),
                Select::make('temperatureUnit')
                    ->label('Temperature Unit')
                    ->options(TemperatureUnit::options())
                    ->required(),
            ]);
    }
}
