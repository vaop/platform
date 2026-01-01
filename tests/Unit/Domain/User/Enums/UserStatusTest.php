<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\Enums;

use Domain\User\Enums\UserStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserStatusTest extends TestCase
{
    #[Test]
    public function it_has_expected_cases(): void
    {
        $cases = UserStatus::cases();

        $this->assertCount(4, $cases);
        $this->assertContains(UserStatus::Pending, $cases);
        $this->assertContains(UserStatus::Active, $cases);
        $this->assertContains(UserStatus::Inactive, $cases);
        $this->assertContains(UserStatus::Suspended, $cases);
    }

    #[Test]
    #[DataProvider('statusLabelsProvider')]
    public function it_returns_correct_label(UserStatus $status, string $expectedLabel): void
    {
        $this->assertEquals($expectedLabel, $status->getLabel());
    }

    public static function statusLabelsProvider(): array
    {
        return [
            'pending' => [UserStatus::Pending, 'Pending Approval'],
            'active' => [UserStatus::Active, 'Active'],
            'inactive' => [UserStatus::Inactive, 'Inactive'],
            'suspended' => [UserStatus::Suspended, 'Suspended'],
        ];
    }

    #[Test]
    #[DataProvider('statusColorsProvider')]
    public function it_returns_correct_color(UserStatus $status, string $expectedColor): void
    {
        $this->assertEquals($expectedColor, $status->getColor());
    }

    public static function statusColorsProvider(): array
    {
        return [
            'pending' => [UserStatus::Pending, 'warning'],
            'active' => [UserStatus::Active, 'success'],
            'inactive' => [UserStatus::Inactive, 'gray'],
            'suspended' => [UserStatus::Suspended, 'danger'],
        ];
    }

    #[Test]
    public function only_active_status_can_login(): void
    {
        $this->assertTrue(UserStatus::Active->canLogin());
        $this->assertFalse(UserStatus::Pending->canLogin());
        $this->assertFalse(UserStatus::Inactive->canLogin());
        $this->assertFalse(UserStatus::Suspended->canLogin());
    }

    #[Test]
    public function it_returns_options_for_forms(): void
    {
        $options = UserStatus::options();

        $this->assertIsArray($options);
        $this->assertCount(4, $options);
        $this->assertEquals('Pending Approval', $options[0]);
        $this->assertEquals('Active', $options[1]);
        $this->assertEquals('Inactive', $options[2]);
        $this->assertEquals('Suspended', $options[3]);
    }

    #[Test]
    public function it_can_be_created_from_int(): void
    {
        $status = UserStatus::from(1);

        $this->assertEquals(UserStatus::Active, $status);
    }

    #[Test]
    public function it_returns_null_for_invalid_int_with_try_from(): void
    {
        $status = UserStatus::tryFrom(99);

        $this->assertNull($status);
    }
}
