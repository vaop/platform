<?php

declare(strict_types=1);

namespace System\ExpressionLanguage\Caching;

use DateTimeImmutable;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\ExpressionLanguage\ParsedExpression;

/**
 * Cache for parsed expressions.
 *
 * Caches parsed expressions to avoid re-parsing on every evaluation.
 * Uses Laravel's cache system with a configurable TTL.
 */
class ExpressionCache
{
    private const CACHE_PREFIX = 'expr:';

    /**
     * In-memory cache for the current request.
     *
     * @var array<string, ExpressionCacheItem>
     */
    private array $memory = [];

    public function __construct(
        private readonly int $defaultTtl = 3600,
        private readonly bool $useMemoryCache = true,
        private readonly bool $usePersistentCache = true,
    ) {}

    /**
     * Get a cached parsed expression.
     */
    public function get(string $expression): ?ParsedExpression
    {
        $key = $this->getCacheKey($expression);

        // Check memory cache first
        if ($this->useMemoryCache && isset($this->memory[$key])) {
            $item = $this->memory[$key];
            if (! $item->isExpired()) {
                return $item->parsed;
            }
            unset($this->memory[$key]);
        }

        // Check persistent cache
        if ($this->usePersistentCache) {
            $item = Cache::get(self::CACHE_PREFIX.$key);
            if ($item instanceof ExpressionCacheItem && ! $item->isExpired()) {
                // Populate memory cache
                if ($this->useMemoryCache) {
                    $this->memory[$key] = $item;
                }

                return $item->parsed;
            }
        }

        return null;
    }

    /**
     * Cache a parsed expression.
     */
    public function put(string $expression, ParsedExpression $parsed, ?int $ttl = null): void
    {
        $key = $this->getCacheKey($expression);
        $ttl = $ttl ?? $this->defaultTtl;

        $item = new ExpressionCacheItem(
            parsed: $parsed,
            createdAt: new DateTimeImmutable,
            ttl: $ttl,
        );

        // Store in memory cache
        if ($this->useMemoryCache) {
            $this->memory[$key] = $item;
        }

        // Store in persistent cache
        if ($this->usePersistentCache) {
            Cache::put(self::CACHE_PREFIX.$key, $item, $ttl);
        }
    }

    /**
     * Remove an expression from the cache.
     */
    public function forget(string $expression): void
    {
        $key = $this->getCacheKey($expression);

        unset($this->memory[$key]);

        if ($this->usePersistentCache) {
            Cache::forget(self::CACHE_PREFIX.$key);
        }
    }

    /**
     * Clear all cached expressions.
     */
    public function flush(): void
    {
        $this->memory = [];

        // Note: This only clears in-memory cache.
        // Persistent cache would need pattern-based deletion
        // which isn't supported by all cache drivers.
    }

    /**
     * Generate a cache key for an expression.
     */
    private function getCacheKey(string $expression): string
    {
        return hash('xxh3', $expression);
    }
}
