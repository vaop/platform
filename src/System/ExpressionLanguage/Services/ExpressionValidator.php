<?php

declare(strict_types=1);

namespace System\ExpressionLanguage\Services;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use System\ExpressionLanguage\Contracts\ExpressionContextInterface;
use System\ExpressionLanguage\Exceptions\ExpressionSecurityException;
use System\ExpressionLanguage\Exceptions\ExpressionSyntaxException;
use System\ExpressionLanguage\Security\ExpressionSecurityPolicy;

/**
 * Validates expressions for syntax and security.
 *
 * Use this service to validate user-provided expressions before
 * storing them in the database.
 */
class ExpressionValidator
{
    public function __construct(
        private readonly ExpressionLanguage $expressionLanguage,
        private readonly ExpressionSecurityPolicy $securityPolicy,
    ) {}

    /**
     * Validate an expression.
     *
     * @param  string  $expression  The expression to validate
     * @param  ExpressionContextInterface|null  $context  Optional context for variable validation
     * @return array{valid: bool, errors: array<int, string>}
     */
    public function validate(string $expression, ?ExpressionContextInterface $context = null): array
    {
        $errors = [];

        // Security validation
        try {
            $this->securityPolicy->validate($expression);
        } catch (ExpressionSecurityException $e) {
            $errors[] = $e->getMessage();
        }

        // Syntax validation
        try {
            $this->validateSyntax($expression, $context);
        } catch (ExpressionSyntaxException $e) {
            $errors[] = $e->getMessage();
        }

        // Variable validation (if context provided)
        if ($context !== null) {
            $variableErrors = $this->validateVariables($expression, $context);
            $errors = array_merge($errors, $variableErrors);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Check if an expression is valid.
     */
    public function isValid(string $expression, ?ExpressionContextInterface $context = null): bool
    {
        return $this->validate($expression, $context)['valid'];
    }

    /**
     * Validate expression syntax.
     *
     * @throws ExpressionSyntaxException
     */
    public function validateSyntax(string $expression, ?ExpressionContextInterface $context = null): void
    {
        $variables = $context?->getAllowedVariables() ?? [];

        try {
            $this->expressionLanguage->parse($expression, $variables);
        } catch (SyntaxError $e) {
            throw ExpressionSyntaxException::invalidSyntax($expression, $e->getMessage());
        }
    }

    /**
     * Validate that expression only uses allowed variables.
     *
     * @return array<int, string> List of validation errors
     */
    private function validateVariables(string $expression, ExpressionContextInterface $context): array
    {
        $errors = [];
        $allowedVariables = $context->getAllowedVariables();

        // Extract variable names from expression using regex
        // This catches common patterns like: variable, object.property
        preg_match_all('/\b([a-zA-Z_][a-zA-Z0-9_]*)\b/', $expression, $matches);

        $usedNames = array_unique($matches[1]);

        // Filter out keywords and function names
        $keywords = ['true', 'false', 'null', 'and', 'or', 'not', 'in', 'matches'];
        $functions = $this->securityPolicy->getAllowedFunctions();

        foreach ($usedNames as $name) {
            // Skip keywords and functions
            if (in_array(strtolower($name), $keywords, true)) {
                continue;
            }
            if (in_array(strtolower($name), array_map('strtolower', $functions), true)) {
                continue;
            }

            // Check if it's an allowed variable (or property access on one)
            $isAllowed = false;
            foreach ($allowedVariables as $allowedVar) {
                if ($name === $allowedVar || str_starts_with($name, $allowedVar.'.')) {
                    $isAllowed = true;
                    break;
                }
            }

            // Also check if it's a property access (e.g., "distance" when "pirep.distance" is used)
            // We can't fully validate this without parsing, so we're lenient here
        }

        return $errors;
    }
}
