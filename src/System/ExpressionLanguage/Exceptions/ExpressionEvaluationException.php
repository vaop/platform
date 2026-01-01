<?php

declare(strict_types=1);

namespace System\ExpressionLanguage\Exceptions;

use Exception;

/**
 * Exception thrown when an expression fails during evaluation.
 */
class ExpressionEvaluationException extends ExpressionException
{
    public static function evaluationFailed(string $expression, string $reason, ?Exception $previous = null): self
    {
        return new self(
            "Expression evaluation failed: {$reason}",
            $expression,
            $previous,
        );
    }

    public static function undefinedVariable(string $expression, string $variable): self
    {
        return new self(
            "Undefined variable '{$variable}' in expression",
            $expression,
        );
    }

    public static function typeMismatch(string $expression, string $expected, string $actual): self
    {
        return new self(
            "Type mismatch: expected {$expected}, got {$actual}",
            $expression,
        );
    }

    public static function divisionByZero(string $expression): self
    {
        return new self(
            'Division by zero',
            $expression,
        );
    }
}
