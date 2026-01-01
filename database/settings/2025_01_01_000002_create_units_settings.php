<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;
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

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Allow user customization by default
        $this->migrator->add('units.allowUserCustomization', true);

        // Default units per ADR 0009
        $this->migrator->add('units.distanceUnit', DistanceUnit::canonical()->value);
        $this->migrator->add('units.altitudeUnit', AltitudeUnit::canonical()->value);
        $this->migrator->add('units.heightUnit', HeightUnit::canonical()->value);
        $this->migrator->add('units.lengthUnit', LengthUnit::canonical()->value);
        $this->migrator->add('units.pressureUnit', PressureUnit::canonical()->value);
        $this->migrator->add('units.speedUnit', SpeedUnit::canonical()->value);
        $this->migrator->add('units.weightUnit', WeightUnit::canonical()->value);
        $this->migrator->add('units.fuelUnit', FuelUnit::canonical()->value);
        $this->migrator->add('units.volumeUnit', VolumeUnit::canonical()->value);
        $this->migrator->add('units.temperatureUnit', TemperatureUnit::canonical()->value);
    }

    public function down(): void
    {
        $this->migrator->delete('units.allowUserCustomization');
        $this->migrator->delete('units.distanceUnit');
        $this->migrator->delete('units.altitudeUnit');
        $this->migrator->delete('units.heightUnit');
        $this->migrator->delete('units.lengthUnit');
        $this->migrator->delete('units.pressureUnit');
        $this->migrator->delete('units.speedUnit');
        $this->migrator->delete('units.weightUnit');
        $this->migrator->delete('units.fuelUnit');
        $this->migrator->delete('units.volumeUnit');
        $this->migrator->delete('units.temperatureUnit');
    }
};
