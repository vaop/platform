<?php

declare(strict_types=1);

namespace Tests\Unit\System\View\Twig\Extensions;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use System\View\Twig\Extensions\CoreFunctionsExtension;
use Tests\TestCase;

class CoreFunctionsExtensionTest extends TestCase
{
    private CoreFunctionsExtension $extension;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new CoreFunctionsExtension;
    }

    #[Test]
    public function it_generates_csrf_field(): void
    {
        $result = (string) $this->extension->csrfField();

        $this->assertStringContainsString('_token', $result);
        $this->assertStringContainsString('hidden', $result);
    }

    #[Test]
    public function it_generates_csrf_token(): void
    {
        // Start a session for CSRF token generation
        session()->start();

        $token = $this->extension->csrfToken();

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    #[Test]
    public function it_generates_method_field(): void
    {
        $result = (string) $this->extension->methodField('PUT');

        $this->assertStringContainsString('_method', $result);
        $this->assertStringContainsString('PUT', $result);
        $this->assertStringContainsString('hidden', $result);
    }

    #[Test]
    public function it_generates_route_url(): void
    {
        $result = $this->extension->route('install.welcome');

        $this->assertStringContainsString('install', $result);
    }

    #[Test]
    public function it_generates_url(): void
    {
        $result = $this->extension->url('/test-path');

        $this->assertStringContainsString('test-path', $result);
    }

    #[Test]
    public function it_generates_asset_url(): void
    {
        $result = $this->extension->asset('images/logo.png');

        $this->assertStringContainsString('images/logo.png', $result);
    }

    #[Test]
    public function it_checks_authentication_returns_false_for_guests(): void
    {
        $this->assertFalse($this->extension->isAuthenticated());
    }

    #[Test]
    public function it_checks_guest_returns_true_for_guests(): void
    {
        $this->assertTrue($this->extension->isGuest());
    }

    #[Test]
    public function it_translates_strings(): void
    {
        $result = $this->extension->trans('validation.required');

        $this->assertIsString($result);
    }

    #[Test]
    public function it_returns_current_time(): void
    {
        $result = $this->extension->now();

        $this->assertInstanceOf(Carbon::class, $result);
    }

    #[Test]
    public function it_formats_current_time(): void
    {
        $result = $this->extension->now('Y-m-d');

        $this->assertIsString($result);
        $this->assertEquals(date('Y-m-d'), $result);
    }

    #[Test]
    public function config_returns_default_for_disallowed_keys(): void
    {
        // app.key is not in the allowed list
        $result = $this->extension->config('app.key');
        $this->assertNull($result);

        $result = $this->extension->config('app.key', 'default');
        $this->assertEquals('default', $result);
    }

    #[Test]
    public function config_returns_value_for_allowed_keys(): void
    {
        $result = $this->extension->config('app.name');

        $this->assertEquals(config('app.name'), $result);
    }

    #[Test]
    public function it_gets_old_input_value(): void
    {
        // Without any old input, should return default
        $result = $this->extension->old('test_field', 'default_value');

        $this->assertEquals('default_value', $result);
    }

    #[Test]
    public function it_registers_all_expected_functions(): void
    {
        $functions = $this->extension->getFunctions();
        $functionNames = array_map(fn ($f) => $f->getName(), $functions);

        $expectedFunctions = [
            'route',
            'url',
            'asset',
            'vite',
            'csrf_field',
            'csrf_token',
            'method_field',
            'old',
            '__',
            'trans_choice',
            'session',
            'has_flash',
            'flash',
            'flash_all',
            'is_authenticated',
            'is_guest',
            'now',
            'dump',
            'config',
        ];

        foreach ($expectedFunctions as $expected) {
            $this->assertContains($expected, $functionNames, "Function '{$expected}' should be registered");
        }
    }
}
