<?php

declare(strict_types=1);

namespace App\Install\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return $next($request);
    }

    private function isInstalled(): bool
    {
        return file_exists(storage_path('installed'));
    }
}
