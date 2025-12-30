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

        // Should return the actual translation, not just a string
        $this->assertNotEmpty($result);
        $this->assertEquals(__('validation.required'), $result);
    }

    #[Test]
    public function it_uses_translation_function_with_replacements(): void
    {
        $template = Twig::createTemplate('{{ __("validation.min.string", {"attribute": "name", "min": "3"}) }}');
        $result = $template->render([]);

        $this->assertNotEmpty($result);
        $this->assertStringContainsString('3', $result);
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

        // Request method should be a valid HTTP method
        $validMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];
        $this->assertContains($result, $validMethods, "Request method should be a valid HTTP method, got: {$result}");
    }

    #[Test]
    public function it_provides_request_path(): void
    {
        $template = Twig::createTemplate('{{ request.path }}');
        $result = $template->render([]);

        $this->assertIsString($result);
        // Path should start with / or be empty
        $this->assertTrue($result === '' || str_starts_with($result, '/') || $result === '/', 'Path should be empty or start with /');
    }

    #[Test]
    public function it_provides_request_url(): void
    {
        $template = Twig::createTemplate('{{ request.url }}');
        $result = $template->render([]);

        $this->assertNotEmpty($result);
        // URL should be a valid URL format
        $this->assertStringStartsWith('http', $result);
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

    #[Test]
    public function it_renders_include_tag(): void
    {
        // Test include using inline template
        $template = Twig::createTemplate('{% include "components/button.twig" with {label: "Click me", variant: "primary"} %}');
        $result = $template->render([]);

        $this->assertStringContainsString('Click me', $result);
    }

    #[Test]
    public function it_renders_embed_tag(): void
    {
        // Embed allows overriding blocks from included template
        $template = Twig::createTemplate('{% embed "layouts/guest.twig" %}{% block content %}Custom content{% endblock %}{% endembed %}');
        $result = $template->render([]);

        $this->assertStringContainsString('Custom content', $result);
    }

    #[Test]
    public function it_uses_dump_function_in_debug_mode(): void
    {
        // dump() should work (outputs debug info)
        $template = Twig::createTemplate('{{ dump("test") }}');
        $result = $template->render([]);

        // dump returns a string representation
        $this->assertIsString($result);
    }

    #[Test]
    public function it_uses_date_function(): void
    {
        $template = Twig::createTemplate('{{ date("2025-01-15")|date("Y-m-d") }}');
        $result = $template->render([]);

        $this->assertEquals('2025-01-15', $result);
    }

    #[Test]
    public function it_uses_cycle_function(): void
    {
        $template = Twig::createTemplate('{% for i in range(0, 3) %}{{ cycle(["odd", "even"], i) }}{% endfor %}');
        $result = $template->render([]);

        $this->assertEquals('oddevenoddeven', $result);
    }

    #[Test]
    public function it_uses_random_function(): void
    {
        $template = Twig::createTemplate('{{ random([1, 2, 3]) }}');
        $result = $template->render([]);

        $this->assertContains((int) $result, [1, 2, 3]);
    }
}
