<?php

declare(strict_types=1);

namespace System\View\Twig\Security;

use Twig\Extension\SandboxExtension as TwigSandboxExtension;

/**
 * Factory for creating a Twig SandboxExtension with our custom ThemeSecurityPolicy.
 *
 * Since Twig's SandboxExtension is final, we provide a factory method
 * to create an instance with our security policy.
 */
class SandboxExtension
{
    /**
     * Create a Twig SandboxExtension with the ThemeSecurityPolicy.
     */
    public static function create(ThemeSecurityPolicy $policy, bool $sandboxed = false): TwigSandboxExtension
    {
        return new TwigSandboxExtension($policy, $sandboxed);
    }
}
