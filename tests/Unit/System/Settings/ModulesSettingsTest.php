<?php

declare(strict_types=1);

namespace Tests\Unit\System\Settings;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use System\Settings\ModulesSettings;
use Tests\TestCase;

class ModulesSettingsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_correct_group_name(): void
    {
        $this->assertEquals('modules', ModulesSettings::group());
    }

    #[Test]
    public function it_has_default_enable_metro_areas_disabled_after_migration(): void
    {
        $settings = app(ModulesSettings::class);

        $this->assertFalse($settings->enableMetroAreas);
    }

    #[Test]
    public function it_can_enable_metro_areas(): void
    {
        $settings = app(ModulesSettings::class);
        $settings->enableMetroAreas = true;
        $settings->save();

        $freshSettings = app(ModulesSettings::class);
        $this->assertTrue($freshSettings->enableMetroAreas);
    }

    #[Test]
    public function it_can_disable_metro_areas(): void
    {
        $settings = app(ModulesSettings::class);
        $settings->enableMetroAreas = true;
        $settings->save();

        $settings->enableMetroAreas = false;
        $settings->save();

        $freshSettings = app(ModulesSettings::class);
        $this->assertFalse($freshSettings->enableMetroAreas);
    }
}
