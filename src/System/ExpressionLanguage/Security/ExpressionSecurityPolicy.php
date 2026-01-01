<?php

declare(strict_types=1);

namespace System\ExpressionLanguage\Security;

use System\ExpressionLanguage\Exceptions\ExpressionSecurityException;

/**
 * Security policy for expression evaluation.
 *
 * Defines which functions, operators, and patterns are allowed
 * in expressions to prevent security vulnerabilities.
 */
class ExpressionSecurityPolicy
{
    /**
     * Default allowed functions (safe mathematical and utility functions).
     *
     * @var array<int, string>
     */
    private const DEFAULT_ALLOWED_FUNCTIONS = [
        // Math functions
        'abs',
        'ceil',
        'floor',
        'round',
        'max',
        'min',
        'pow',
        'sqrt',

        // String functions
        'lower',
        'upper',
        'trim',
        'length',

        // Logical/utility
        'in',
        'not in',
        'matches',
    ];

    /**
     * Operators that are always blocked.
     *
     * @var array<int, string>
     */
    private const BLOCKED_OPERATORS = [
        '`',  // Shell execution
        '$$', // Variable variables
    ];

    /**
     * Patterns that indicate potentially dangerous expressions.
     *
     * @var array<int, string>
     */
    private const DANGEROUS_PATTERNS = [
        '/\beval\b/i',
        '/\bexec\b/i',
        '/\bsystem\b/i',
        '/\bshell_exec\b/i',
        '/\bpassthru\b/i',
        '/\bproc_open\b/i',
        '/\bpopen\b/i',
        '/\bfile_get_contents\b/i',
        '/\bfile_put_contents\b/i',
        '/\binclude\b/i',
        '/\brequire\b/i',
        '/\bphpinfo\b/i',
        '/\$_(?:GET|POST|REQUEST|SERVER|FILES|ENV|COOKIE|SESSION)/i',
    ];

    /**
     * @param  array<int, string>  $allowedFunctions  Additional functions to allow
     * @param  array<int, string>  $blockedFunctions  Functions to explicitly block
     */
    public function __construct(
        private readonly array $allowedFunctions = [],
        private readonly array $blockedFunctions = [],
    ) {}

    /**
     * Create a policy with default settings.
     */
    public static function default(): self
    {
        return new self;
    }

    /**
     * Create a restrictive policy (only basic math).
     */
    public static function restrictive(): self
    {
        return new self(
            allowedFunctions: ['abs', 'ceil', 'floor', 'round', 'max', 'min'],
        );
    }

    /**
     * Validate an expression against the security policy.
     *
     * @throws ExpressionSecurityException If the expression violates the policy
     */
    public function validate(string $expression): void
    {
        $this->checkDangerousPatterns($expression);
        $this->checkBlockedOperators($expression);
    }

    /**
     * Check if a function is allowed.
     */
    public function isFunctionAllowed(string $function): bool
    {
        $function = strtolower($function);

        // Explicitly blocked functions are never allowed
        if (in_array($function, array_map('strtolower', $this->blockedFunctions), true)) {
            return false;
        }

        // Check against allowed functions
        $allowed = array_merge(
            self::DEFAULT_ALLOWED_FUNCTIONS,
            array_map('strtolower', $this->allowedFunctions),
        );

        return in_array($function, $allowed, true);
    }

    /**
     * Get all allowed functions.
     *
     * @return array<int, string>
     */
    public function getAllowedFunctions(): array
    {
        return array_merge(
            self::DEFAULT_ALLOWED_FUNCTIONS,
            $this->allowedFunctions,
        );
    }

    /**
     * Check for dangerous patterns in the expression.
     *
     * @throws ExpressionSecurityException
     */
    private function checkDangerousPatterns(string $expression): void
    {
        foreach (self::DANGEROUS_PATTERNS as $pattern) {
            if (preg_match($pattern, $expression, $matches)) {
                throw ExpressionSecurityException::disallowedFunction(
                    $expression,
                    $matches[0],
                );
            }
        }
    }

    /**
     * Check for blocked operators in the expression.
     *
     * @throws ExpressionSecurityException
     */
    private function checkBlockedOperators(string $expression): void
    {
        foreach (self::BLOCKED_OPERATORS as $operator) {
            if (str_contains($expression, $operator)) {
                throw ExpressionSecurityException::disallowedOperator(
                    $expression,
                    $operator,
                );
            }
        }
    }
}
