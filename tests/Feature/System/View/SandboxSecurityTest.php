<?php

declare(strict_types=1);

namespace Tests\Feature\System\View;

use PHPUnit\Framework\Attributes\Test;
use System\View\Twig\Security\SandboxExtension;
use System\View\Twig\Security\ThemeSecurityPolicy;
use Tests\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Sandbox\SecurityError;
use TwigBridge\Facade\Twig;

class SandboxSecurityTest extends TestCase
{
    /**
     * Create a sandboxed Twig environment for testing.
     */
    private function createSandboxedEnvironment(array $config = []): Environment
    {
        $loader = new ArrayLoader([]);
        $env = new Environment($loader, [
            'strict_variables' => true,
            'autoescape' => 'html',
        ]);

        $defaultConfig = config('twig.sandbox');
        $mergedConfig = array_merge($defaultConfig, $config);

        $policy = new ThemeSecurityPolicy(
            allowedTags: $mergedConfig['allowed_tags'],
            allowedFilters: $mergedConfig['allowed_filters'],
            allowedMethods: $mergedConfig['allowed_methods'],
            allowedProperties: $mergedConfig['allowed_properties'],
            allowedFunctions: $mergedConfig['allowed_functions'],
            forbiddenTags: $mergedConfig['forbidden_tags']
        );

        $sandbox = SandboxExtension::create($policy, true);
        $env->addExtension($sandbox);

        return $env;
    }

    #[Test]
    public function it_allows_if_tag(): void
    {
        $template = Twig::createTemplate('{% if true %}OK{% endif %}');
        $result = $template->render([]);

        $this->assertEquals('OK', $result);
    }

    #[Test]
    public function it_allows_for_tag(): void
    {
        $template = Twig::createTemplate('{% for i in range(1, 3) %}{{ i }}{% endfor %}');
        $result = $template->render([]);

        $this->assertEquals('123', $result);
    }

    #[Test]
    public function it_allows_set_tag(): void
    {
        $template = Twig::createTemplate('{% set x = "hello" %}{{ x }}');
        $result = $template->render([]);

        $this->assertEquals('hello', $result);
    }

    #[Test]
    public function it_allows_block_and_extends(): void
    {
        // This tests that block is allowed (extends requires a file)
        $template = Twig::createTemplate('{% block test %}Content{% endblock %}');
        $result = $template->render([]);

        $this->assertEquals('Content', $result);
    }

    #[Test]
    public function it_allows_macro_tag(): void
    {
        $template = Twig::createTemplate('{% macro hello(name) %}Hello {{ name }}{% endmacro %}{% from _self import hello %}{{ hello("World") }}');
        $result = $template->render([]);

        $this->assertEquals('Hello World', $result);
    }

    #[Test]
    public function it_allows_with_tag(): void
    {
        $template = Twig::createTemplate('{% with { x: "value" } %}{{ x }}{% endwith %}');
        $result = $template->render([]);

        $this->assertEquals('value', $result);
    }

    #[Test]
    public function it_allows_apply_spaceless_filter(): void
    {
        // In Twig 3, spaceless is a filter applied via `apply`, not a tag
        $template = Twig::createTemplate('{% apply spaceless %}<div>  <p>  </p>  </div>{% endapply %}');
        $result = $template->render([]);

        $this->assertEquals('<div><p></p></div>', $result);
    }

    #[Test]
    public function it_allows_verbatim_tag(): void
    {
        $template = Twig::createTemplate('{% verbatim %}{{ not_parsed }}{% endverbatim %}');
        $result = $template->render([]);

        $this->assertEquals('{{ not_parsed }}', $result);
    }

    #[Test]
    public function it_blocks_sandbox_tag(): void
    {
        $env = $this->createSandboxedEnvironment();

        // The sandbox tag throws a SyntaxError because it can only contain include tags
        // Our security policy also blocks it via the forbidden tags list
        $this->expectException(\Twig\Error\SyntaxError::class);

        $template = $env->createTemplate('{% sandbox %}{{ test }}{% endsandbox %}');
        $template->render([]);
    }

    #[Test]
    public function it_allows_escape_filter(): void
    {
        $template = Twig::createTemplate('{{ text|escape }}');
        $result = $template->render(['text' => '<script>']);

        $this->assertEquals('&lt;script&gt;', $result);
    }

    #[Test]
    public function it_allows_default_filter(): void
    {
        $template = Twig::createTemplate('{{ missing|default("fallback") }}');
        $result = $template->render([]);

        $this->assertEquals('fallback', $result);
    }

    #[Test]
    public function it_allows_json_encode_filter(): void
    {
        // json_encode output is escaped by default autoescape, use |raw to get unescaped output
        $template = Twig::createTemplate('{{ data|json_encode|raw }}');
        $result = $template->render(['data' => ['key' => 'value']]);

        $this->assertEquals('{"key":"value"}', $result);
    }

    #[Test]
    public function it_allows_route_function(): void
    {
        $template = Twig::createTemplate('{{ route("install.welcome") }}');
        $result = $template->render([]);

        $this->assertNotEmpty($result);
    }

    #[Test]
    public function it_allows_range_function(): void
    {
        $template = Twig::createTemplate('{{ range(1, 3)|join(",") }}');
        $result = $template->render([]);

        $this->assertEquals('1,2,3', $result);
    }

    #[Test]
    public function it_allows_min_max_functions(): void
    {
        $template = Twig::createTemplate('{{ min(1, 2, 3) }}-{{ max(1, 2, 3) }}');
        $result = $template->render([]);

        $this->assertEquals('1-3', $result);
    }

    #[Test]
    public function it_prevents_method_access_on_arbitrary_objects(): void
    {
        $env = $this->createSandboxedEnvironment();

        $this->expectException(SecurityError::class);

        $obj = new class
        {
            public function dangerousMethod(): string
            {
                return 'danger';
            }
        };

        $template = $env->createTemplate('{{ obj.dangerousMethod() }}');
        $template->render(['obj' => $obj]);
    }

    #[Test]
    public function it_prevents_property_access_on_arbitrary_objects(): void
    {
        $env = $this->createSandboxedEnvironment();

        $this->expectException(SecurityError::class);

        $obj = new class
        {
            public string $secret = 'hidden';
        };

        $template = $env->createTemplate('{{ obj.secret }}');
        $template->render(['obj' => $obj]);
    }

    #[Test]
    public function it_allows_array_access(): void
    {
        $template = Twig::createTemplate('{{ data.key }}');
        $result = $template->render(['data' => ['key' => 'value']]);

        $this->assertEquals('value', $result);
    }

    #[Test]
    public function it_allows_extends_tag(): void
    {
        // Test that extends works with actual template files
        $template = Twig::createTemplate('{% extends "layouts/guest.twig" %}{% block content %}Test content{% endblock %}');
        $result = $template->render([]);

        $this->assertStringContainsString('Test content', $result);
    }

    #[Test]
    public function it_allows_include_tag(): void
    {
        $template = Twig::createTemplate('{% include "components/button.twig" with {label: "Test", variant: "primary"} %}');
        $result = $template->render([]);

        $this->assertStringContainsString('Test', $result);
    }

    #[Test]
    public function it_allows_embed_tag(): void
    {
        $template = Twig::createTemplate('{% embed "layouts/guest.twig" %}{% block content %}Embedded content{% endblock %}{% endembed %}');
        $result = $template->render([]);

        $this->assertStringContainsString('Embedded content', $result);
    }

    #[Test]
    public function it_allows_from_import(): void
    {
        // from/import allows importing macros
        $template = Twig::createTemplate('{% macro test() %}Hello{% endmacro %}{% from _self import test %}{{ test() }}');
        $result = $template->render([]);

        $this->assertEquals('Hello', $result);
    }

    #[Test]
    public function it_allows_autoescape_tag(): void
    {
        $template = Twig::createTemplate('{% autoescape "html" %}{{ text }}{% endautoescape %}');
        $result = $template->render(['text' => '<script>']);

        $this->assertEquals('&lt;script&gt;', $result);
    }

    #[Test]
    public function it_allows_do_tag(): void
    {
        // do tag executes expression without outputting
        $template = Twig::createTemplate('{% do 1 + 1 %}OK');
        $result = $template->render([]);

        $this->assertEquals('OK', $result);
    }

    #[Test]
    public function it_allows_flush_tag(): void
    {
        // flush tag flushes output buffer
        $template = Twig::createTemplate('{% flush %}OK');
        $result = $template->render([]);

        $this->assertEquals('OK', $result);
    }

    #[Test]
    public function sandbox_blocks_dangerous_filters(): void
    {
        $env = $this->createSandboxedEnvironment([
            'allowed_filters' => ['escape'], // Only allow escape
        ]);

        $this->expectException(SecurityError::class);

        // Try to use a filter not in the allowed list
        $template = $env->createTemplate('{{ text|upper }}');
        $template->render(['text' => 'hello']);
    }

    #[Test]
    public function sandbox_blocks_dangerous_functions(): void
    {
        $env = $this->createSandboxedEnvironment([
            'allowed_functions' => ['range'], // Only allow range
        ]);

        $this->expectException(SecurityError::class);

        // Try to use a function not in the allowed list
        $template = $env->createTemplate('{{ random([1,2,3]) }}');
        $template->render([]);
    }

    #[Test]
    public function it_allows_defined_test(): void
    {
        $template = Twig::createTemplate('{% if var is defined %}yes{% else %}no{% endif %}');

        $result = $template->render(['var' => 'value']);
        $this->assertEquals('yes', $result);

        $result = $template->render([]);
        $this->assertEquals('no', $result);
    }

    #[Test]
    public function it_allows_empty_test(): void
    {
        $template = Twig::createTemplate('{% if items is empty %}empty{% else %}has items{% endif %}');

        $result = $template->render(['items' => []]);
        $this->assertEquals('empty', $result);

        $result = $template->render(['items' => [1, 2, 3]]);
        $this->assertEquals('has items', $result);
    }

    #[Test]
    public function it_allows_iterable_test(): void
    {
        $template = Twig::createTemplate('{% if items is iterable %}yes{% else %}no{% endif %}');

        $result = $template->render(['items' => [1, 2, 3]]);
        $this->assertEquals('yes', $result);

        $result = $template->render(['items' => 'string']);
        $this->assertEquals('no', $result);
    }
}
