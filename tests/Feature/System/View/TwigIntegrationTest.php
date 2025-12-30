<?php

declare(strict_types=1);

namespace Tests\Feature\System\View;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use TwigBridge\Facade\Twig;

class TwigIntegrationTest extends TestCase
{
    #[Test]
    public function it_renders_basic_twig_template(): void
    {
        $template = Twig::createTemplate('Hello {{ name }}!');
        $result = $template->render(['name' => 'World']);

        $this->assertEquals('Hello World!', $result);
    }

    #[Test]
    public function it_renders_if_conditions(): void
    {
        $template = Twig::createTemplate('{% if show %}Visible{% endif %}');

        $result = $template->render(['show' => true]);
        $this->assertEquals('Visible', $result);

        $result = $template->render(['show' => false]);
        $this->assertEquals('', $result);
    }

    #[Test]
    public function it_renders_for_loops(): void
    {
        $template = Twig::createTemplate('{% for item in items %}{{ item }}{% endfor %}');
        $result = $template->render(['items' => ['a', 'b', 'c']]);

        $this->assertEquals('abc', $result);
    }

    #[Test]
    public function it_uses_route_function(): void
    {
        $template = Twig::createTemplate('{{ route("install.welcome") }}');
        $result = $template->render([]);

        $this->assertStringContainsString('install', $result);
    }

    #[Test]
    public function it_uses_url_function(): void
    {
        $template = Twig::createTemplate('{{ url("/test") }}');
        $result = $template->render([]);

        $this->assertStringContainsString('test', $result);
    }

    #[Test]
    public function it_uses_csrf_functions(): void
    {
        // Start session for CSRF token generation
        session()->start();

        $template = Twig::createTemplate('{{ csrf_token() }}');
        $result = $template->render([]);

        $this->assertNotEmpty($result);
    }

    #[Test]
    public function it_uses_csrf_field_function(): void
    {
        $template = Twig::createTemplate('{{ csrf_field() }}');
        $result = $template->render([]);

        $this->assertStringContainsString('_token', $result);
        $this->assertStringContainsString('hidden', $result);
    }

    #[Test]
    public function it_uses_translation_function(): void
    {
        $template = Twig::createTemplate('{{ __("validation.required") }}');
        $result = $template->render([]);

        $this->assertIsString($result);
    }

    #[Test]
    public function it_uses_is_guest_function(): void
    {
        $template = Twig::createTemplate('{% if is_guest() %}Guest{% endif %}');
        $result = $template->render([]);

        $this->assertEquals('Guest', $result);
    }

    #[Test]
    public function it_uses_now_function(): void
    {
        $template = Twig::createTemplate('{{ now("Y") }}');
        $result = $template->render([]);

        $this->assertEquals(date('Y'), $result);
    }

    #[Test]
    public function it_uses_truncate_filter(): void
    {
        $template = Twig::createTemplate('{{ text|truncate(10) }}');
        $result = $template->render(['text' => 'This is a long text']);

        // Str::limit includes the limit chars + ellipsis
        $this->assertEquals('This is a...', $result);
    }

    #[Test]
    public function it_uses_datetime_filter(): void
    {
        $template = Twig::createTemplate('{{ date|datetime("Y-m-d") }}');
        $result = $template->render(['date' => '2025-12-25']);

        $this->assertEquals('2025-12-25', $result);
    }

    #[Test]
    public function it_provides_app_global_variables(): void
    {
        $template = Twig::createTemplate('{{ app.name }}');
        $result = $template->render([]);

        $this->assertEquals(config('app.name'), $result);
    }

    #[Test]
    public function it_provides_request_global_variables(): void
    {
        $template = Twig::createTemplate('{{ request.method }}');
        $result = $template->render([]);

        // In test context, method might vary
        $this->assertIsString($result);
    }

    #[Test]
    public function it_autoescapes_html_by_default(): void
    {
        $template = Twig::createTemplate('{{ html }}');
        $result = $template->render(['html' => '<script>alert("xss")</script>']);

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    #[Test]
    public function it_allows_raw_output_when_marked_safe(): void
    {
        $template = Twig::createTemplate('{{ html|raw }}');
        $result = $template->render(['html' => '<strong>Bold</strong>']);

        $this->assertStringContainsString('<strong>Bold</strong>', $result);
    }

    #[Test]
    public function config_function_restricts_sensitive_keys(): void
    {
        $template = Twig::createTemplate('{{ config("app.key", "fallback") }}');
        $result = $template->render([]);

        // app.key is not in the allowed list, should return fallback
        $this->assertEquals('fallback', $result);
    }

    #[Test]
    public function config_function_allows_safe_keys(): void
    {
        $template = Twig::createTemplate('{{ config("app.name") }}');
        $result = $template->render([]);

        $this->assertEquals(config('app.name'), $result);
    }
}
