<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\Quantities;

use PHPUnit\Framework\Attributes\Test;
use Support\UnitsOfMeasure\Enums\TemperatureUnit;
use Support\UnitsOfMeasure\Quantities\Temperature;
use Tests\TestCase;

class TemperatureTest extends TestCase
{
    #[Test]
    public function it_creates_temperature_from_celsius(): void
    {
        $temp = Temperature::fromCelsius(25);

        $this->assertEqualsWithDelta(25, $temp->toCelsius(), 0.01);
    }

    #[Test]
    public function it_creates_temperature_from_fahrenheit(): void
    {
        $temp = Temperature::fromFahrenheit(77);

        $this->assertEqualsWithDelta(77, $temp->toFahrenheit(), 0.01);
    }

    #[Test]
    public function it_creates_temperature_from_enum(): void
    {
        $temp = Temperature::fromTemperature(20, TemperatureUnit::CELSIUS);

        $this->assertEqualsWithDelta(20, $temp->toCelsius(), 0.01);
    }

    #[Test]
    public function it_converts_celsius_to_fahrenheit(): void
    {
        $temp = Temperature::fromCelsius(0);

        $this->assertEqualsWithDelta(32, $temp->toFahrenheit(), 0.01);
    }

    #[Test]
    public function it_converts_celsius_to_fahrenheit_at_100(): void
    {
        $temp = Temperature::fromCelsius(100);

        $this->assertEqualsWithDelta(212, $temp->toFahrenheit(), 0.01);
    }

    #[Test]
    public function it_converts_fahrenheit_to_celsius(): void
    {
        $temp = Temperature::fromFahrenheit(32);

        $this->assertEqualsWithDelta(0, $temp->toCelsius(), 0.01);
    }

    #[Test]
    public function it_converts_celsius_to_kelvin(): void
    {
        $temp = Temperature::fromCelsius(0);

        $this->assertEqualsWithDelta(273.15, $temp->toKelvin(), 0.01);
    }
}
