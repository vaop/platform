<?php

declare(strict_types=1);

namespace System\ExpressionLanguage\Caching;

use DateTimeImmutable;
use Symfony\Component\ExpressionLanguage\ParsedExpression;

/**
 * Cached item for a parsed expression.
 */
final readonly class ExpressionCacheItem
{
    public function __construct(
        public ParsedExpression $parsed,
        public DateTimeImmutable $createdAt,
        public ?int $ttl = null,
    ) {}

    /**
     * Check if the cache item has expired.
     */
    public function isExpired(): bool
    {
        if ($this->ttl === null) {
            return false;
        }

        $expiresAt = $this->createdAt->modify("+{$this->ttl} seconds");

        return new DateTimeImmutable > $expiresAt;
    }
}
