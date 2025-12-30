<?php

declare(strict_types=1);

namespace System\View\Twig\Security;

use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityPolicy;
use Twig\Sandbox\SecurityPolicyInterface;

/**
 * Custom security policy that extends Twig's sandbox with forbidden tag support.
 *
 * This policy wraps the standard SecurityPolicy and adds the ability to
 * explicitly forbid certain tags (like 'sandbox') to prevent sandbox escapes.
 */
class ThemeSecurityPolicy implements SecurityPolicyInterface
{
    private SecurityPolicy $policy;

    /** @var array<string> */
    private array $forbiddenTags;

    /**
     * @param  array<string>  $allowedTags
     * @param  array<string>  $allowedFilters
     * @param  array<string, array<string>>  $allowedMethods
     * @param  array<string, array<string>>  $allowedProperties
     * @param  array<string>  $allowedFunctions
     * @param  array<string>  $forbiddenTags
     */
    public function __construct(
        array $allowedTags = [],
        array $allowedFilters = [],
        array $allowedMethods = [],
        array $allowedProperties = [],
        array $allowedFunctions = [],
        array $forbiddenTags = []
    ) {
        // Twig's SecurityPolicy constructor order: tags, filters, methods, properties, functions
        $this->policy = new SecurityPolicy(
            $allowedTags,
            $allowedFilters,
            $allowedMethods,
            $allowedProperties,
            $allowedFunctions
        );
        $this->forbiddenTags = $forbiddenTags;
    }

    /**
     * @param  array<string>  $tags
     * @param  array<string>  $filters
     * @param  array<string>  $functions
     *
     * @throws SecurityError
     */
    public function checkSecurity($tags, $filters, $functions): void
    {
        // Check forbidden tags first (these are always blocked, even if in allowed list)
        foreach ($tags as $tag) {
            if (in_array($tag, $this->forbiddenTags, true)) {
                throw new SecurityError(sprintf('Tag "%s" is forbidden.', $tag));
            }
        }

        // Delegate to the standard policy for allowlist checking
        $this->policy->checkSecurity($tags, $filters, $functions);
    }

    /**
     * @param  object  $obj
     * @param  string  $method
     *
     * @throws SecurityError
     */
    public function checkMethodAllowed($obj, $method): void
    {
        $this->policy->checkMethodAllowed($obj, $method);
    }

    /**
     * @param  object  $obj
     * @param  string  $property
     *
     * @throws SecurityError
     */
    public function checkPropertyAllowed($obj, $property): void
    {
        $this->policy->checkPropertyAllowed($obj, $property);
    }
}
