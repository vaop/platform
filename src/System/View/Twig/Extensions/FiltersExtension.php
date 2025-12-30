<?php

declare(strict_types=1);

namespace System\View\Twig\Extensions;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig filters for text manipulation, date formatting, and numbers.
 */
class FiltersExtension extends AbstractExtension
{
    /**
     * @return array<TwigFilter>
     */
    public function getFilters(): array
    {
        return [
            // Text filters
            new TwigFilter('truncate', [$this, 'truncate']),
            new TwigFilter('excerpt', [$this, 'excerpt']),
            new TwigFilter('markdown', [$this, 'markdown'], ['is_safe' => ['html']]),
            new TwigFilter('nl2br', [$this, 'nl2br'], ['is_safe' => ['html']]),
            new TwigFilter('strip_tags', [$this, 'stripTags']),

            // Date/Time filters
            new TwigFilter('datetime', [$this, 'formatDateTime']),
            new TwigFilter('relative', [$this, 'relativeTime']),
        ];
    }

    // -------------------------------------------------------------------------
    // Text filters
    // -------------------------------------------------------------------------

    /**
     * Truncate text to a given length with ellipsis.
     */
    public function truncate(?string $value, int $limit = 100, string $end = '...'): string
    {
        if ($value === null) {
            return '';
        }

        return Str::limit($value, $limit, $end);
    }

    /**
     * Extract an excerpt from text, optionally centered around a phrase.
     */
    public function excerpt(?string $text, string $phrase = '', int $radius = 100, string $end = '...'): string
    {
        if ($text === null) {
            return '';
        }

        // If no phrase provided, just truncate from the beginning
        if ($phrase === '') {
            return Str::limit($text, $radius * 2, $end);
        }

        return Str::excerpt($text, $phrase, ['radius' => $radius, 'omission' => $end]) ?? '';
    }

    /**
     * Convert Markdown to HTML.
     */
    public function markdown(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return Str::markdown($value);
    }

    /**
     * Convert newlines to <br> tags (escaped).
     */
    public function nl2br(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return nl2br(e($value));
    }

    /**
     * Strip HTML tags from text.
     *
     * @param  array<string>|string|null  $allowedTags
     */
    public function stripTags(?string $value, array|string|null $allowedTags = null): string
    {
        if ($value === null) {
            return '';
        }

        if (is_array($allowedTags)) {
            $allowedTags = implode('', array_map(fn ($tag) => "<{$tag}>", $allowedTags));
        }

        return strip_tags($value, $allowedTags);
    }

    // -------------------------------------------------------------------------
    // Date/Time filters
    // -------------------------------------------------------------------------

    /**
     * Format date/time value.
     */
    public function formatDateTime(mixed $value, string $format = 'M j, Y g:i A'): string
    {
        $date = $this->parseDate($value);

        return $date?->format($format) ?? '';
    }

    /**
     * Get relative time string (e.g., "2 hours ago").
     */
    public function relativeTime(mixed $value): string
    {
        $date = $this->parseDate($value);

        return $date?->diffForHumans() ?? '';
    }

    /**
     * Parse various date formats into Carbon.
     */
    private function parseDate(mixed $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_string($value) || is_numeric($value)) {
            try {
                return Carbon::parse($value);
            } catch (\Exception) {
                return null;
            }
        }

        return null;
    }
}
