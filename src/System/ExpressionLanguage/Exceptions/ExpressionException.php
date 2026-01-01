<?php

declare(strict_types=1);

namespace System\ExpressionLanguage\Exceptions;

use Exception;

/**
 * Base exception for expression language errors.
 */
class ExpressionException extends Exception
{
    public function __construct(
        string $message,
        protected readonly ?string $expression = null,
        ?Exception $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Get the expression that caused the error.
     */
    public function getExpression(): ?string
    {
        return $this->expression;
    }
}
