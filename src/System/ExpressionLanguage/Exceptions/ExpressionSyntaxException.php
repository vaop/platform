<?php

declare(strict_types=1);

namespace System\ExpressionLanguage\Exceptions;

/**
 * Exception thrown when an expression has invalid syntax.
 */
class ExpressionSyntaxException extends ExpressionException
{
    public static function invalidSyntax(string $expression, string $details): self
    {
        return new self(
            "Invalid expression syntax: {$details}",
            $expression,
        );
    }

    public static function unexpectedToken(string $expression, string $token, int $position): self
    {
        return new self(
            "Unexpected token '{$token}' at position {$position}",
            $expression,
        );
    }
}
