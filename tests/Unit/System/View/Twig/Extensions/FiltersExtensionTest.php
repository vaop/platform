<?php

declare(strict_types=1);

namespace Tests\Unit\System\View\Twig\Extensions;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use System\View\Twig\Extensions\FiltersExtension;
use Tests\TestCase;

class FiltersExtensionTest extends TestCase
{
    private FiltersExtension $extension;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new FiltersExtension;
    }

    #[Test]
    public function it_truncates_text(): void
    {
        $text = 'This is a long text that should be truncated';

        $result = $this->extension->truncate($text, 20);

        // Str::limit truncates at the word boundary when possible
        $this->assertEquals('This is a long text...', $result);
    }

    #[Test]
    public function it_truncates_with_custom_ending(): void
    {
        $text = 'This is a long text';

        $result = $this->extension->truncate($text, 10, '…');

        $this->assertEquals('This is a…', $result);
    }

    #[Test]
    public function it_handles_null_in_truncate(): void
    {
        $result = $this->extension->truncate(null);

        $this->assertEquals('', $result);
    }

    #[Test]
    public function it_creates_excerpt(): void
    {
        $text = 'This is a very long text with some important content in the middle that we want to excerpt.';

        $result = $this->extension->excerpt($text, 'important', 10);

        $this->assertStringContainsString('important', $result);
    }

    #[Test]
    public function it_handles_null_in_excerpt(): void
    {
        $result = $this->extension->excerpt(null);

        $this->assertEquals('', $result);
    }

    #[Test]
    public function it_converts_markdown_to_html(): void
    {
        $markdown = '**bold** and *italic*';

        $result = $this->extension->markdown($markdown);

        $this->assertStringContainsString('<strong>bold</strong>', $result);
        $this->assertStringContainsString('<em>italic</em>', $result);
    }

    #[Test]
    public function it_handles_null_in_markdown(): void
    {
        $result = $this->extension->markdown(null);

        $this->assertEquals('', $result);
    }

    #[Test]
    public function it_converts_newlines_to_br(): void
    {
        $text = "Line 1\nLine 2";

        $result = $this->extension->nl2br($text);

        $this->assertStringContainsString('<br />', $result);
    }

    #[Test]
    public function it_escapes_html_in_nl2br(): void
    {
        $text = "<script>alert('xss')</script>\nLine 2";

        $result = $this->extension->nl2br($text);

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    #[Test]
    public function it_strips_html_tags(): void
    {
        $html = '<p>Hello <strong>World</strong></p>';

        $result = $this->extension->stripTags($html);

        $this->assertEquals('Hello World', $result);
    }

    #[Test]
    public function it_strips_tags_with_allowlist(): void
    {
        $html = '<p>Hello <strong>World</strong></p>';

        $result = $this->extension->stripTags($html, ['p']);

        $this->assertEquals('<p>Hello World</p>', $result);
    }

    #[Test]
    public function it_formats_datetime(): void
    {
        $date = Carbon::create(2025, 12, 25, 10, 30);

        $result = $this->extension->formatDateTime($date, 'Y-m-d H:i');

        $this->assertEquals('2025-12-25 10:30', $result);
    }

    #[Test]
    public function it_formats_datetime_from_string(): void
    {
        $result = $this->extension->formatDateTime('2025-12-25', 'Y-m-d');

        $this->assertEquals('2025-12-25', $result);
    }

    #[Test]
    public function it_handles_null_in_datetime(): void
    {
        $result = $this->extension->formatDateTime(null);

        $this->assertEquals('', $result);
    }

    #[Test]
    public function it_returns_relative_time(): void
    {
        $date = Carbon::now()->subHours(2);

        $result = $this->extension->relativeTime($date);

        $this->assertStringContainsString('ago', $result);
    }

    #[Test]
    public function it_handles_null_in_relative_time(): void
    {
        $result = $this->extension->relativeTime(null);

        $this->assertEquals('', $result);
    }

    #[Test]
    public function it_registers_all_expected_filters(): void
    {
        $filters = $this->extension->getFilters();
        $filterNames = array_map(fn ($f) => $f->getName(), $filters);

        $expectedFilters = [
            'truncate',
            'excerpt',
            'markdown',
            'nl2br',
            'strip_tags',
            'datetime',
            'relative',
        ];

        foreach ($expectedFilters as $expected) {
            $this->assertContains($expected, $filterNames, "Filter '{$expected}' should be registered");
        }
    }
}
