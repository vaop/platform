<?php

declare(strict_types=1);

namespace App\Install\Controllers;

use DateTimeZone;
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
                'timezone' => 'UTC',
            ],
            'timezones' => DateTimeZone::listIdentifiers(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $config = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'timezone' => 'required|string|timezone',
        ]);

        $this->env->setMultiple([
            'APP_NAME' => $config['app_name'],
            'APP_URL' => rtrim($config['app_url'], '/'),
            'APP_TIMEZONE' => $config['timezone'],
        ]);

        $this->env->generateAppKey();

        return redirect()->route('install.admin');
    }
}
