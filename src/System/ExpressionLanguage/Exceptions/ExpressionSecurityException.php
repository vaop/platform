<?php

declare(strict_types=1);

namespace System\ExpressionLanguage\Exceptions;

/**
 * Exception thrown when an expression violates security policies.
 */
class ExpressionSecurityException extends ExpressionException
{
    public static function disallowedFunction(string $expression, string $function): self
    {
        return new self(
            "Function '{$function}' is not allowed in expressions",
            $expression,
        );
    }

    public static function disallowedVariable(string $expression, string $variable): self
    {
        return new self(
            "Variable '{$variable}' is not allowed in this context",
            $expression,
        );
    }

    public static function disallowedOperator(string $expression, string $operator): self
    {
        return new self(
            "Operator '{$operator}' is not allowed in expressions",
            $expression,
        );
    }
}
