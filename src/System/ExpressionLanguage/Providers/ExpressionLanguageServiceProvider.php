<?php

declare(strict_types=1);

namespace System\ExpressionLanguage\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use System\ExpressionLanguage\Caching\ExpressionCache;
use System\ExpressionLanguage\Security\ExpressionSecurityPolicy;
use System\ExpressionLanguage\Services\ExpressionEvaluator;
use System\ExpressionLanguage\Services\ExpressionValidator;

/**
 * Service provider for the expression language infrastructure.
 *
 * Registers the expression evaluator, validator, and related services.
 */
class ExpressionLanguageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register Symfony ExpressionLanguage as singleton
        $this->app->singleton(ExpressionLanguage::class, function () {
            $expressionLanguage = new ExpressionLanguage;

            // Register custom functions here if needed
            $this->registerCustomFunctions($expressionLanguage);

            return $expressionLanguage;
        });

        // Register security policy
        $this->app->singleton(ExpressionSecurityPolicy::class, function () {
            return ExpressionSecurityPolicy::default();
        });

        // Register expression cache
        $this->app->singleton(ExpressionCache::class, function () {
            return new ExpressionCache(
                defaultTtl: config('expression.cache.ttl', 3600),
                useMemoryCache: config('expression.cache.memory', true),
                usePersistentCache: config('expression.cache.persistent', true),
            );
        });

        // Register validator
        $this->app->singleton(ExpressionValidator::class, function ($app) {
            return new ExpressionValidator(
                expressionLanguage: $app->make(ExpressionLanguage::class),
                securityPolicy: $app->make(ExpressionSecurityPolicy::class),
            );
        });

        // Register evaluator
        $this->app->singleton(ExpressionEvaluator::class, function ($app) {
            return new ExpressionEvaluator(
                expressionLanguage: $app->make(ExpressionLanguage::class),
                securityPolicy: $app->make(ExpressionSecurityPolicy::class),
                cache: $app->make(ExpressionCache::class),
            );
        });
    }

    public function boot(): void
    {
        // Publish configuration if needed
        // $this->publishes([
        //     __DIR__.'/../config/expression.php' => config_path('expression.php'),
        // ], 'expression-config');
    }

    /**
     * Register custom functions for expressions.
     */
    private function registerCustomFunctions(ExpressionLanguage $expressionLanguage): void
    {
        // Standard math functions
        $this->registerMathFunctions($expressionLanguage);

        // String functions
        $this->registerStringFunctions($expressionLanguage);

        // Custom utility functions
        $this->registerUtilityFunctions($expressionLanguage);
    }

    /**
     * Register standard math functions.
     */
    private function registerMathFunctions(ExpressionLanguage $expressionLanguage): void
    {
        // abs(value)
        $expressionLanguage->register(
            'abs',
            fn ($value) => sprintf('abs(%s)', $value),
            fn ($arguments, $value) => abs($value),
        );

        // ceil(value)
        $expressionLanguage->register(
            'ceil',
            fn ($value) => sprintf('ceil(%s)', $value),
            fn ($arguments, $value) => ceil($value),
        );

        // floor(value)
        $expressionLanguage->register(
            'floor',
            fn ($value) => sprintf('floor(%s)', $value),
            fn ($arguments, $value) => floor($value),
        );

        // round(value, precision = 0)
        $expressionLanguage->register(
            'round',
            fn ($value, $precision = 0) => sprintf('round(%s, %s)', $value, $precision),
            fn ($arguments, $value, $precision = 0) => round($value, (int) $precision),
        );

        // max(a, b, ...)
        $expressionLanguage->register(
            'max',
            fn (...$args) => sprintf('max(%s)', implode(', ', $args)),
            fn ($arguments, ...$values) => max($values),
        );

        // min(a, b, ...)
        $expressionLanguage->register(
            'min',
            fn (...$args) => sprintf('min(%s)', implode(', ', $args)),
            fn ($arguments, ...$values) => min($values),
        );

        // pow(base, exp)
        $expressionLanguage->register(
            'pow',
            fn ($base, $exp) => sprintf('pow(%s, %s)', $base, $exp),
            fn ($arguments, $base, $exp) => pow($base, $exp),
        );

        // sqrt(value)
        $expressionLanguage->register(
            'sqrt',
            fn ($value) => sprintf('sqrt(%s)', $value),
            fn ($arguments, $value) => sqrt($value),
        );
    }

    /**
     * Register string functions.
     */
    private function registerStringFunctions(ExpressionLanguage $expressionLanguage): void
    {
        // lower(string)
        $expressionLanguage->register(
            'lower',
            fn ($str) => sprintf('strtolower(%s)', $str),
            fn ($arguments, $str) => strtolower((string) $str),
        );

        // upper(string)
        $expressionLanguage->register(
            'upper',
            fn ($str) => sprintf('strtoupper(%s)', $str),
            fn ($arguments, $str) => strtoupper((string) $str),
        );

        // trim(string)
        $expressionLanguage->register(
            'trim',
            fn ($str) => sprintf('trim(%s)', $str),
            fn ($arguments, $str) => trim((string) $str),
        );

        // length(string)
        $expressionLanguage->register(
            'length',
            fn ($str) => sprintf('strlen(%s)', $str),
            fn ($arguments, $str) => strlen((string) $str),
        );
    }

    /**
     * Register utility functions.
     */
    private function registerUtilityFunctions(ExpressionLanguage $expressionLanguage): void
    {
        // clamp(value, min, max) - Clamp a value between min and max
        $expressionLanguage->register(
            'clamp',
            fn ($value, $min, $max) => sprintf('max(%2$s, min(%3$s, %1$s))', $value, $min, $max),
            fn ($arguments, $value, $min, $max) => max($min, min($max, $value)),
        );

        // percentage(value, total) - Calculate percentage
        $expressionLanguage->register(
            'percentage',
            fn ($value, $total) => sprintf('(%1$s / %2$s) * 100', $value, $total),
            fn ($arguments, $value, $total) => $total != 0 ? ($value / $total) * 100 : 0,
        );

        // between(value, min, max) - Check if value is between min and max (inclusive)
        $expressionLanguage->register(
            'between',
            fn ($value, $min, $max) => sprintf('(%1$s >= %2$s and %1$s <= %3$s)', $value, $min, $max),
            fn ($arguments, $value, $min, $max) => $value >= $min && $value <= $max,
        );

        // default(value, fallback) - Return fallback if value is null
        $expressionLanguage->register(
            'default',
            fn ($value, $fallback) => sprintf('(%1$s ?? %2$s)', $value, $fallback),
            fn ($arguments, $value, $fallback) => $value ?? $fallback,
        );
    }
}
