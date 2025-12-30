<?php

declare(strict_types=1);

namespace System\View\Twig\Environment;

use Twig\Environment;
use Twig\Loader\LoaderInterface;

/**
 * Custom Twig Environment that enforces security defaults.
 *
 * This environment ensures that strict_variables and autoescape are always
 * enabled, regardless of what configuration is passed.
 */
class ThemeEnvironment extends Environment
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(LoaderInterface $loader, array $options = [])
    {
        // Enforce security defaults - these cannot be overridden
        $options['strict_variables'] = true;
        $options['autoescape'] = 'html';

        parent::__construct($loader, $options);
    }
}
