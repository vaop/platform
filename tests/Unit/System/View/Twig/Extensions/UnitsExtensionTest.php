<?php

declare(strict_types=1);

namespace Tests\Unit\System\View\Twig\Extensions;

use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Support\UnitsOfMeasure\Enums\AltitudeUnit;
use Support\UnitsOfMeasure\Enums\DistanceUnit;
use Support\UnitsOfMeasure\Enums\SpeedUnit;
use Support\UnitsOfMeasure\Enums\WeightUnit;
use Support\UnitsOfMeasure\ValueObjects\Altitude;
use Support\UnitsOfMeasure\ValueObjects\Distance;
use Support\UnitsOfMeasure\ValueObjects\Duration;
use Support\UnitsOfMeasure\ValueObjects\Speed;
use Support\UnitsOfMeasure\ValueObjects\Weight;
use System\Settings\UnitsSettings;
use System\View\Twig\Extensions\UnitsExtension;
use Tests\TestCase;

class UnitsExtensionTest extends TestCase
{
    use RefreshDatabase;

    private UnitsExtension $extension;

    private UnitsSettings $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settings = app(UnitsSettings::class);
        $this->extension = new UnitsExtension($this->settings);
    }

    // -------------------------------------------------------------------------
    // Distance Filter
    // -------------------------------------------------------------------------

    #[Test]
    public function it_formats_distance_from_value_object(): void
    {
        $distance = Distance::fromNauticalMiles(100);

        $result = $this->extension->formatDistance($distance);

        $this->assertSame('100 nm', $result);
    }

    #[Test]
    public function it_formats_distance_from_raw_value(): void
    {
        $result = $this->extension->formatDistance(100);

        $this->assertSame('100 nm', $result);
    }

    #[Test]
    public function it_formats_distance_with_decimals(): void
    {
        $distance = Distance::fromNauticalMiles(123.456);

        $result = $this->extension->formatDistance($distance, 2);

        $this->assertSame('123.46 nm', $result);
    }

    #[Test]
    public function it_formats_distance_without_unit(): void
    {
        $result = $this->extension->formatDistance(100, 0, false);

        $this->assertSame('100', $result);
    }

    #[Test]
    public function it_handles_null_distance(): void
    {
        $result = $this->extension->formatDistance(null);

        $this->assertSame('', $result);
    }

    #[Test]
    public function it_formats_distance_in_user_preferred_unit(): void
    {
        $user = User::factory()->create(['distance_unit' => DistanceUnit::KILOMETERS]);
        $this->actingAs($user);

        $distance = Distance::fromNauticalMiles(100); // 185.2 km

        $result = $this->extension->formatDistance($distance, 1);

        $this->assertStringContainsString('km', $result);
        $this->assertStringContainsString('185', $result);
    }

    // -------------------------------------------------------------------------
    // Altitude Filter
    // -------------------------------------------------------------------------

    #[Test]
    public function it_formats_altitude_from_value_object(): void
    {
        $altitude = Altitude::fromFeet(35000);

        $result = $this->extension->formatAltitude($altitude);

        $this->assertSame('35,000 ft', $result);
    }

    #[Test]
    public function it_formats_altitude_from_raw_value(): void
    {
        $result = $this->extension->formatAltitude(35000);

        $this->assertSame('35,000 ft', $result);
    }

    #[Test]
    public function it_handles_null_altitude(): void
    {
        $result = $this->extension->formatAltitude(null);

        $this->assertSame('', $result);
    }

    #[Test]
    public function it_formats_altitude_in_user_preferred_unit(): void
    {
        $user = User::factory()->create(['altitude_unit' => AltitudeUnit::METERS]);
        $this->actingAs($user);

        $altitude = Altitude::fromFeet(10000); // ~3048 m

        $result = $this->extension->formatAltitude($altitude, 0);

        $this->assertStringContainsString('m', $result);
        $this->assertStringContainsString('3,048', $result);
    }

    // -------------------------------------------------------------------------
    // Speed Filter
    // -------------------------------------------------------------------------

    #[Test]
    public function it_formats_speed_from_value_object(): void
    {
        $speed = Speed::fromKnots(450);

        $result = $this->extension->formatSpeed($speed);

        $this->assertSame('450 kts', $result);
    }

    #[Test]
    public function it_formats_speed_from_raw_value(): void
    {
        $result = $this->extension->formatSpeed(450);

        $this->assertSame('450 kts', $result);
    }

    #[Test]
    public function it_handles_null_speed(): void
    {
        $result = $this->extension->formatSpeed(null);

        $this->assertSame('', $result);
    }

    #[Test]
    public function it_formats_speed_in_user_preferred_unit(): void
    {
        $user = User::factory()->create(['speed_unit' => SpeedUnit::KILOMETERS_PER_HOUR]);
        $this->actingAs($user);

        $speed = Speed::fromKnots(100); // ~185.2 km/h

        $result = $this->extension->formatSpeed($speed, 0);

        $this->assertStringContainsString('km/h', $result);
        $this->assertStringContainsString('185', $result);
    }

    // -------------------------------------------------------------------------
    // Weight Filter
    // -------------------------------------------------------------------------

    #[Test]
    public function it_formats_weight_from_value_object(): void
    {
        $weight = Weight::fromKilograms(75000);

        $result = $this->extension->formatWeight($weight);

        $this->assertSame('75,000 kg', $result);
    }

    #[Test]
    public function it_formats_weight_from_raw_value(): void
    {
        $result = $this->extension->formatWeight(75000);

        $this->assertSame('75,000 kg', $result);
    }

    #[Test]
    public function it_handles_null_weight(): void
    {
        $result = $this->extension->formatWeight(null);

        $this->assertSame('', $result);
    }

    #[Test]
    public function it_formats_weight_in_user_preferred_unit(): void
    {
        $user = User::factory()->create(['weight_unit' => WeightUnit::POUNDS]);
        $this->actingAs($user);

        $weight = Weight::fromKilograms(1000); // ~2204.62 lbs

        $result = $this->extension->formatWeight($weight, 0);

        $this->assertStringContainsString('lbs', $result);
        $this->assertStringContainsString('2,205', $result);
    }

    // -------------------------------------------------------------------------
    // Duration Filters
    // -------------------------------------------------------------------------

    #[Test]
    public function it_formats_duration_from_value_object(): void
    {
        $duration = Duration::fromSeconds(9045); // 2:30:45

        $result = $this->extension->formatDuration($duration);

        $this->assertSame('2:30', $result);
    }

    #[Test]
    public function it_formats_duration_from_raw_seconds(): void
    {
        $result = $this->extension->formatDuration(9045);

        $this->assertSame('2:30', $result);
    }

    #[Test]
    public function it_handles_null_duration(): void
    {
        $result = $this->extension->formatDuration(null);

        $this->assertSame('', $result);
    }

    #[Test]
    public function it_formats_duration_with_seconds(): void
    {
        $duration = Duration::fromSeconds(9045); // 2:30:45

        $result = $this->extension->formatDurationHMS($duration);

        $this->assertSame('2:30:45', $result);
    }

    #[Test]
    public function it_handles_null_duration_hms(): void
    {
        $result = $this->extension->formatDurationHMS(null);

        $this->assertSame('', $result);
    }

    // -------------------------------------------------------------------------
    // User Customization Toggle
    // -------------------------------------------------------------------------

    #[Test]
    public function it_ignores_user_preference_when_customization_disabled(): void
    {
        // Disable user customization
        $this->settings->allowUserCustomization = false;
        $this->settings->save();

        // Refresh extension with updated settings
        $this->extension = new UnitsExtension(app(UnitsSettings::class));

        $user = User::factory()->create(['distance_unit' => DistanceUnit::KILOMETERS]);
        $this->actingAs($user);

        $distance = Distance::fromNauticalMiles(100);

        $result = $this->extension->formatDistance($distance);

        // Should use airline default (nm), not user preference (km)
        $this->assertSame('100 nm', $result);
    }

    // -------------------------------------------------------------------------
    // Filter Registration
    // -------------------------------------------------------------------------

    #[Test]
    public function it_registers_all_expected_filters(): void
    {
        $filters = $this->extension->getFilters();
        $filterNames = array_map(fn ($f) => $f->getName(), $filters);

        $expectedFilters = [
            'distance',
            'altitude',
            'speed',
            'weight',
            'duration',
            'duration_hms',
        ];

        foreach ($expectedFilters as $expected) {
            $this->assertContains($expected, $filterNames, "Filter '{$expected}' should be registered");
        }
    }
}
