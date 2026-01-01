<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\Quantities;

use PHPUnit\Framework\Attributes\Test;
use Support\UnitsOfMeasure\Enums\WeightUnit;
use Support\UnitsOfMeasure\Quantities\Mass;
use Tests\TestCase;

class MassTest extends TestCase
{
    #[Test]
    public function it_creates_mass_from_kilograms(): void
    {
        $mass = Mass::fromKilograms(1000);

        $this->assertEqualsWithDelta(1000, $mass->toKilograms(), 0.01);
    }

    #[Test]
    public function it_creates_mass_from_pounds(): void
    {
        $mass = Mass::fromPounds(2000);

        $this->assertEqualsWithDelta(2000, $mass->toPounds(), 0.01);
    }

    #[Test]
    public function it_creates_mass_from_weight_enum(): void
    {
        $mass = Mass::fromWeight(500, WeightUnit::KILOGRAMS);

        $this->assertEqualsWithDelta(500, $mass->toKilograms(), 0.01);
    }

    #[Test]
    public function it_converts_kilograms_to_pounds(): void
    {
        $mass = Mass::fromKilograms(100);

        // 1 kg = 2.20462 lbs
        $this->assertEqualsWithDelta(220.462, $mass->toPounds(), 0.01);
    }

    #[Test]
    public function it_converts_pounds_to_kilograms(): void
    {
        $mass = Mass::fromPounds(100);

        // 1 lb = 0.453592 kg
        $this->assertEqualsWithDelta(45.3592, $mass->toKilograms(), 0.01);
    }
}
