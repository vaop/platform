<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UnitsOfMeasure\ValueObjects;

use DateInterval;
use PHPUnit\Framework\Attributes\Test;
use Support\Exceptions\InvalidValueException;
use Support\UnitsOfMeasure\ValueObjects\Duration;
use Tests\TestCase;

class DurationTest extends TestCase
{
    #[Test]
    public function it_creates_duration_from_seconds(): void
    {
        $duration = Duration::fromSeconds(3600);

        $this->assertSame(3600, $duration->toSeconds());
    }

    #[Test]
    public function it_creates_duration_from_minutes(): void
    {
        $duration = Duration::fromMinutes(90);

        $this->assertSame(5400, $duration->toSeconds());
        $this->assertEqualsWithDelta(90, $duration->toMinutes(), 0.01);
    }

    #[Test]
    public function it_creates_duration_from_hours(): void
    {
        $duration = Duration::fromHours(2.5);

        $this->assertSame(9000, $duration->toSeconds());
        $this->assertEqualsWithDelta(2.5, $duration->toHours(), 0.01);
    }

    #[Test]
    public function it_creates_duration_from_hours_and_minutes(): void
    {
        $duration = Duration::fromHoursAndMinutes(2, 30);

        $this->assertSame(9000, $duration->toSeconds());
    }

    #[Test]
    public function it_creates_duration_from_date_interval(): void
    {
        $interval = new DateInterval('PT2H30M45S');
        $duration = Duration::fromDateInterval($interval);

        $this->assertSame(9045, $duration->toSeconds());
    }

    #[Test]
    public function it_creates_duration_from_date_interval_with_days(): void
    {
        $interval = new DateInterval('P1DT2H30M');
        $duration = Duration::fromDateInterval($interval);

        // 1 day + 2 hours + 30 minutes = 86400 + 7200 + 1800 = 95400 seconds
        $this->assertSame(95400, $duration->toSeconds());
    }

    #[Test]
    public function it_creates_zero_duration(): void
    {
        $duration = Duration::zero();

        $this->assertTrue($duration->isZero());
        $this->assertSame(0, $duration->toSeconds());
    }

    #[Test]
    public function it_throws_exception_for_negative_seconds(): void
    {
        $this->expectException(InvalidValueException::class);

        Duration::fromSeconds(-10);
    }

    #[Test]
    public function it_throws_exception_for_negative_hours_and_minutes(): void
    {
        $this->expectException(InvalidValueException::class);

        Duration::fromHoursAndMinutes(-1, 30);
    }

    #[Test]
    public function it_gets_hours_component(): void
    {
        $duration = Duration::fromSeconds(9045); // 2h 30m 45s

        $this->assertSame(2, $duration->getHours());
    }

    #[Test]
    public function it_gets_minutes_component(): void
    {
        $duration = Duration::fromSeconds(9045); // 2h 30m 45s

        $this->assertSame(30, $duration->getMinutes());
    }

    #[Test]
    public function it_gets_remaining_seconds_component(): void
    {
        $duration = Duration::fromSeconds(9045); // 2h 30m 45s

        $this->assertSame(45, $duration->getRemainingSeconds());
    }

    #[Test]
    public function it_converts_to_date_interval(): void
    {
        $duration = Duration::fromSeconds(3661);

        $interval = $duration->toDateInterval();

        $this->assertInstanceOf(DateInterval::class, $interval);
    }

    #[Test]
    public function it_formats_as_hours_minutes(): void
    {
        $duration = Duration::fromSeconds(9045); // 2h 30m 45s

        $this->assertSame('2:30', $duration->toHoursMinutes());
    }

    #[Test]
    public function it_formats_as_hours_minutes_seconds(): void
    {
        $duration = Duration::fromSeconds(9045); // 2h 30m 45s

        $this->assertSame('2:30:45', $duration->toHoursMinutesSeconds());
    }

    #[Test]
    public function it_adds_durations(): void
    {
        $d1 = Duration::fromMinutes(90);
        $d2 = Duration::fromMinutes(45);

        $result = $d1->add($d2);

        $this->assertSame(8100, $result->toSeconds()); // 135 minutes
    }

    #[Test]
    public function it_subtracts_durations(): void
    {
        $d1 = Duration::fromMinutes(90);
        $d2 = Duration::fromMinutes(45);

        $result = $d1->subtract($d2);

        $this->assertSame(2700, $result->toSeconds()); // 45 minutes
    }

    #[Test]
    public function it_throws_exception_when_subtraction_would_be_negative(): void
    {
        $d1 = Duration::fromMinutes(30);
        $d2 = Duration::fromMinutes(60);

        $this->expectException(InvalidValueException::class);

        $d1->subtract($d2);
    }

    #[Test]
    public function it_compares_durations(): void
    {
        $d1 = Duration::fromHours(2);
        $d2 = Duration::fromHours(1);

        $this->assertTrue($d1->isGreaterThan($d2));
        $this->assertFalse($d1->isLessThan($d2));
        $this->assertTrue($d2->isLessThan($d1));
    }

    #[Test]
    public function it_checks_equality(): void
    {
        $d1 = Duration::fromHours(2);
        $d2 = Duration::fromMinutes(120);
        $d3 = Duration::fromHours(3);

        $this->assertTrue($d1->equals($d2));
        $this->assertFalse($d1->equals($d3));
    }

    #[Test]
    public function it_converts_to_string(): void
    {
        $duration = Duration::fromSeconds(9045); // 2h 30m 45s

        $this->assertSame('2:30', (string) $duration);
    }
}
