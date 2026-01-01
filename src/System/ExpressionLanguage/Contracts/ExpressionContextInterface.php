<?php

declare(strict_types=1);

namespace System\ExpressionLanguage\Contracts;

/**
 * Interface for expression evaluation contexts.
 *
 * Contexts provide the variables and functions available during
 * expression evaluation. Each domain defines its own context with
 * appropriate variables (e.g., ScoringContext, AwardsContext).
 *
 * Usage:
 *   class ScoringContext implements ExpressionContextInterface
 *   {
 *       public function getVariables(): array
 *       {
 *           return [
 *               'distance' => $this->pirep->distance,
 *               'duration' => $this->pirep->duration,
 *               'aircraft_type' => $this->pirep->aircraft->type,
 *           ];
 *       }
 *   }
 */
interface ExpressionContextInterface
{
    /**
     * Get the variables available in this context.
     *
     * @return array<string, mixed> Variable name => value pairs
     */
    public function getVariables(): array;

    /**
     * Get the name of this context for error messages and logging.
     */
    public function getName(): string;

    /**
     * Get the list of allowed variable names for validation.
     *
     * @return array<int, string>
     */
    public function getAllowedVariables(): array;
}
