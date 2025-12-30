<?php

declare(strict_types=1);

namespace System\View\Twig\Extensions;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Core Twig functions for Laravel integration.
 *
 * Provides routing, security, form, localization, session, auth, and asset functions.
 */
class CoreFunctionsExtension extends AbstractExtension
{
    /** @var array<string> */
    private array $allowedConfigKeys;

    public function __construct()
    {
        $this->allowedConfigKeys = config('twig.allowed_config_keys', []);
    }

    /**
     * @return array<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            // Routing
            new TwigFunction('route', [$this, 'route']),
            new TwigFunction('url', [$this, 'url']),
            new TwigFunction('asset', [$this, 'asset']),

            // Assets (Vite)
            new TwigFunction('vite', [$this, 'vite'], ['is_safe' => ['html']]),

            // Security
            new TwigFunction('csrf_field', [$this, 'csrfField'], ['is_safe' => ['html']]),
            new TwigFunction('csrf_token', [$this, 'csrfToken']),
            new TwigFunction('method_field', [$this, 'methodField'], ['is_safe' => ['html']]),

            // Form
            new TwigFunction('old', [$this, 'old']),

            // Localization
            new TwigFunction('__', [$this, 'trans']),
            new TwigFunction('trans_choice', [$this, 'transChoice']),

            // Session
            new TwigFunction('session', [$this, 'session']),
            new TwigFunction('has_flash', [$this, 'hasFlash']),
            new TwigFunction('flash', [$this, 'flash']),
            new TwigFunction('flash_all', [$this, 'flashAll']),

            // Auth
            new TwigFunction('is_authenticated', [$this, 'isAuthenticated']),
            new TwigFunction('is_guest', [$this, 'isGuest']),

            // Misc
            new TwigFunction('now', [$this, 'now']),
            new TwigFunction('dump', [$this, 'dump']),
            new TwigFunction('config', [$this, 'config']),
        ];
    }

    // -------------------------------------------------------------------------
    // Routing functions
    // -------------------------------------------------------------------------

    /**
     * Generate URL for a named route.
     *
     * @param  array<string, mixed>  $parameters
     */
    public function route(string $name, array $parameters = [], bool $absolute = true): string
    {
        return route($name, $parameters, $absolute);
    }

    /**
     * Generate a URL for a given path.
     *
     * @param  array<string, mixed>  $parameters
     */
    public function url(string $path = '', array $parameters = []): string
    {
        return url($path, $parameters);
    }

    /**
     * Generate URL for an asset.
     */
    public function asset(string $path): string
    {
        return asset($path);
    }

    // -------------------------------------------------------------------------
    // Asset functions
    // -------------------------------------------------------------------------

    /**
     * Generate Vite asset tags.
     *
     * @param  string|array<string>  $entrypoints
     */
    public function vite(string|array $entrypoints, ?string $buildDirectory = null): HtmlString
    {
        if ($buildDirectory !== null) {
            return Vite::useBuildDirectory($buildDirectory)->__invoke($entrypoints);
        }

        return Vite::__invoke($entrypoints);
    }

    // -------------------------------------------------------------------------
    // Security functions
    // -------------------------------------------------------------------------

    /**
     * Generate CSRF hidden input field.
     */
    public function csrfField(): HtmlString
    {
        $token = $this->csrfToken();

        return new HtmlString('<input type="hidden" name="_token" value="'.$token.'" autocomplete="off">');
    }

    /**
     * Get CSRF token value.
     *
     * Returns empty string if no session is available (e.g., error pages).
     */
    public function csrfToken(): string
    {
        return csrf_token() ?? '';
    }

    /**
     * Generate hidden input for HTTP method spoofing.
     */
    public function methodField(string $method): HtmlString
    {
        return method_field($method);
    }

    // -------------------------------------------------------------------------
    // Form functions
    // -------------------------------------------------------------------------

    /**
     * Get old input value.
     */
    public function old(string $key, mixed $default = null): mixed
    {
        return old($key, $default);
    }

    // -------------------------------------------------------------------------
    // Localization functions
    // -------------------------------------------------------------------------

    /**
     * Translate a given message.
     *
     * @param  array<string, mixed>  $replace
     */
    public function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        return __($key, $replace, $locale);
    }

    /**
     * Translate a pluralized message.
     *
     * @param  array<string, mixed>  $replace
     */
    public function transChoice(string $key, int|float|\Countable $number, array $replace = [], ?string $locale = null): string
    {
        return trans_choice($key, $number, $replace, $locale);
    }

    // -------------------------------------------------------------------------
    // Session functions
    // -------------------------------------------------------------------------

    /**
     * Get session value.
     */
    public function session(string $key, mixed $default = null): mixed
    {
        return Session::get($key, $default);
    }

    /**
     * Check if flash message exists.
     */
    public function hasFlash(string $key): bool
    {
        return Session::has($key);
    }

    /**
     * Get flash message value.
     */
    public function flash(string $key, mixed $default = null): mixed
    {
        return Session::get($key, $default);
    }

    /**
     * Get all flash messages.
     *
     * @return array<string, mixed>
     */
    public function flashAll(): array
    {
        return Session::all();
    }

    // -------------------------------------------------------------------------
    // Auth functions
    // -------------------------------------------------------------------------

    /**
     * Check if user is authenticated.
     */
    public function isAuthenticated(?string $guard = null): bool
    {
        return Auth::guard($guard)->check();
    }

    /**
     * Check if user is a guest.
     */
    public function isGuest(?string $guard = null): bool
    {
        return Auth::guard($guard)->guest();
    }

    // -------------------------------------------------------------------------
    // Misc functions
    // -------------------------------------------------------------------------

    /**
     * Get current date/time.
     */
    public function now(?string $format = null): Carbon|string
    {
        $now = now();

        return $format ? $now->format($format) : $now;
    }

    /**
     * Dump variables (only in debug mode).
     */
    public function dump(mixed ...$vars): void
    {
        if (config('app.debug')) {
            dump(...$vars);
        }
    }

    /**
     * Get configuration value (restricted to allowed keys).
     */
    public function config(string $key, mixed $default = null): mixed
    {
        // Only allow whitelisted config keys for security
        if (! in_array($key, $this->allowedConfigKeys, true)) {
            return $default;
        }

        return config($key, $default);
    }
}
