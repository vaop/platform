<?php

declare(strict_types=1);

namespace App\Install\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstallerEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isInstallerEnabled()) {
            abort(404);
        }

        return $next($request);
    }

    private function isInstallerEnabled(): bool
    {
        return (bool) config('vaop.installer.enabled', true);
    }
}
