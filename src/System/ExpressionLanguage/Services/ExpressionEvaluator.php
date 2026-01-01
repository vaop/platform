<?php

declare(strict_types=1);

namespace System\ExpressionLanguage\Services;

use Exception;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use System\ExpressionLanguage\Caching\ExpressionCache;
use System\ExpressionLanguage\Contracts\ExpressionContextInterface;
use System\ExpressionLanguage\Exceptions\ExpressionEvaluationException;
use System\ExpressionLanguage\Exceptions\ExpressionSyntaxException;
use System\ExpressionLanguage\Security\ExpressionSecurityPolicy;

/**
 * Evaluates expressions with caching and security.
 *
 * Usage:
 *   $result = $evaluator->evaluate('distance * 2 + bonus', $scoringContext);
 *   $result = $evaluator->evaluate('distance > 100', $context);
 */
class ExpressionEvaluator
{
    public function __construct(
        private readonly ExpressionLanguage $expressionLanguage,
        private readonly ExpressionSecurityPolicy $securityPolicy,
        private readonly ExpressionCache $cache,
    ) {}

    /**
     * Evaluate an expression with the given context.
     *
     * @param  string  $expression  The expression to evaluate
     * @param  ExpressionContextInterface  $context  The evaluation context
     * @return mixed The result of the evaluation
     *
     * @throws ExpressionSyntaxException If the expression has invalid syntax
     * @throws ExpressionEvaluationException If evaluation fails
     */
    public function evaluate(string $expression, ExpressionContextInterface $context): mixed
    {
        // Security check
        $this->securityPolicy->validate($expression);

        // Get variables from context
        $variables = $context->getVariables();

        try {
            // Try to get cached parsed expression
            $parsed = $this->cache->get($expression);

            if ($parsed !== null) {
                return $this->expressionLanguage->evaluate($parsed, $variables);
            }

            // Parse and cache
            $parsed = $this->expressionLanguage->parse($expression, array_keys($variables));
            $this->cache->put($expression, $parsed);

            return $this->expressionLanguage->evaluate($parsed, $variables);
        } catch (SyntaxError $e) {
            throw ExpressionSyntaxException::invalidSyntax($expression, $e->getMessage());
        } catch (Exception $e) {
            throw ExpressionEvaluationException::evaluationFailed(
                $expression,
                $e->getMessage(),
                $e,
            );
        }
    }

    /**
     * Evaluate an expression and return a boolean result.
     *
     * @throws ExpressionSyntaxException
     * @throws ExpressionEvaluationException
     */
    public function evaluateBoolean(string $expression, ExpressionContextInterface $context): bool
    {
        $result = $this->evaluate($expression, $context);

        return (bool) $result;
    }

    /**
     * Evaluate an expression and return a numeric result.
     *
     * @throws ExpressionSyntaxException
     * @throws ExpressionEvaluationException
     */
    public function evaluateNumeric(string $expression, ExpressionContextInterface $context): float|int
    {
        $result = $this->evaluate($expression, $context);

        if (! is_numeric($result)) {
            throw ExpressionEvaluationException::typeMismatch(
                $expression,
                'numeric',
                gettype($result),
            );
        }

        return $result + 0; // Convert to int or float
    }

    /**
     * Evaluate multiple expressions and return results keyed by expression.
     *
     * @param  array<string, string>  $expressions  Named expressions to evaluate
     * @param  ExpressionContextInterface  $context  The evaluation context
     * @return array<string, mixed> Results keyed by expression name
     *
     * @throws ExpressionSyntaxException
     * @throws ExpressionEvaluationException
     */
    public function evaluateMany(array $expressions, ExpressionContextInterface $context): array
    {
        $results = [];

        foreach ($expressions as $name => $expression) {
            $results[$name] = $this->evaluate($expression, $context);
        }

        return $results;
    }

    /**
     * Safely evaluate an expression, returning a default value on error.
     *
     * @param  string  $expression  The expression to evaluate
     * @param  ExpressionContextInterface  $context  The evaluation context
     * @param  mixed  $default  Default value if evaluation fails
     * @return mixed The result or default value
     */
    public function evaluateSafe(string $expression, ExpressionContextInterface $context, mixed $default = null): mixed
    {
        try {
            return $this->evaluate($expression, $context);
        } catch (Exception) {
            return $default;
        }
    }
}
