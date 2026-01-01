<?php

declare(strict_types=1);

namespace Support\Exceptions;

use Exception;

/**
 * Base exception for domain-level errors.
 *
 * Domain exceptions represent errors in business logic or rule violations.
 * They should be thrown when domain invariants are violated or when
 * business rules cannot be satisfied.
 */
class DomainException extends Exception
{
    /**
     * Create a new domain exception.
     */
    public function __construct(
        string $message,
        public readonly ?string $context = null,
        ?Exception $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Create an exception for an invalid state.
     */
    public static function invalidState(string $message, ?string $context = null): self
    {
        return new self($message, $context);
    }

    /**
     * Create an exception for a business rule violation.
     */
    public static function ruleViolation(string $rule, ?string $context = null): self
    {
        return new self("Business rule violated: {$rule}", $context);
    }
}
