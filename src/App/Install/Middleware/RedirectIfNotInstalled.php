<?php

declare(strict_types=1);

namespace App\Install\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isInstalled()) {
            return $next($request);
        }

        if (! $this->isInstallerEnabled()) {
            return $next($request);
        }

        if ($this->isInstallerRoute($request)) {
            return $next($request);
        }

        return redirect()->route('install.welcome');
    }

    private function isInstalled(): bool
    {
        return file_exists(storage_path('installed'));
    }

    private function isInstallerEnabled(): bool
    {
        return (bool) config('vaop.installer.enabled', true);
    }

    private function isInstallerRoute(Request $request): bool
    {
        return str_starts_with($request->path(), 'install');
    }
}
