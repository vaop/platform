<?php

declare(strict_types=1);

namespace App\Install\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use System\Environment\EnvironmentWriter;

class EnvironmentController extends Controller
{
    public function __construct(
        private readonly EnvironmentWriter $env,
    ) {}

    public function index(): View
    {
        return view('install.steps.environment', [
            'config' => [
                'app_name' => 'My Virtual Airline',
                'app_url' => request()->schemeAndHttpHost(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $config = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
        ]);

        $this->env->generateAppKey();

        // Store VA name and URL in session (will be saved to settings after migrations)
        session([
            'va_name' => $config['app_name'],
            'site_url' => rtrim($config['app_url'], '/'),
        ]);

        return redirect()->route('install.admin');
    }
}
