<?php

declare(strict_types=1);

namespace System\View\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use System\View\Composers\GlobalViewComposer;
use System\View\Exceptions\TwigErrorHandler;
use System\View\Twig\Extensions\CoreFunctionsExtension;
use System\View\Twig\Extensions\FiltersExtension;
use System\View\Twig\Extensions\GlobalsExtension;
use System\View\Twig\Extensions\UnitsExtension;
use System\View\Twig\Security\SandboxExtension;
use System\View\Twig\Security\ThemeSecurityPolicy;
use TwigBridge\Facade\Twig;

/**
 * Service provider for Twig templating with sandbox security.
 *
 * This provider:
 * - Loads Twig configuration
 * - Registers sandbox security policy
 * - Registers custom Twig extensions (functions, filters, globals)
 * - Registers view composers
 */
class TwigServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            config_path('twig.php'),
            'twig'
        );

        $this->app->singleton(TwigErrorHandler::class);
    }

    public function boot(): void
    {
        // Ensure Twig cache directory exists
        $this->ensureCacheDirectoryExists();

        // Register sandbox and extensions after TwigBridge has booted
        $this->app->booted(function () {
            $this->registerSandboxExtension();
            $this->registerCustomExtensions();
        });

        // Register view composers
        $this->registerViewComposers();
    }

    /**
     * Ensure the Twig cache directory exists.
     */
    private function ensureCacheDirectoryExists(): void
    {
        $cachePath = storage_path('framework/views/twig');

        if (! is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
    }

    /**
     * Register the sandbox extension with security policy.
     */
    private function registerSandboxExtension(): void
    {
        $config = config('twig.sandbox');

        if (! $config['enabled']) {
            return;
        }

        $policy = new ThemeSecurityPolicy(
            allowedTags: $config['allowed_tags'],
            allowedFilters: $config['allowed_filters'],
            allowedMethods: $config['allowed_methods'],
            allowedProperties: $config['allowed_properties'],
            allowedFunctions: $config['allowed_functions'],
            forbiddenTags: $config['forbidden_tags']
        );

        $sandbox = SandboxExtension::create($policy, $config['global']);

        Twig::addExtension($sandbox);
    }

    /**
     * Register custom Twig extensions.
     */
    private function registerCustomExtensions(): void
    {
        Twig::addExtension($this->app->make(CoreFunctionsExtension::class));
        Twig::addExtension($this->app->make(FiltersExtension::class));
        Twig::addExtension($this->app->make(GlobalsExtension::class));
        Twig::addExtension($this->app->make(UnitsExtension::class));
    }

    /**
     * Register view composers.
     */
    private function registerViewComposers(): void
    {
        // Apply to all Twig views
        View::composer('*.twig', GlobalViewComposer::class);
    }
}
