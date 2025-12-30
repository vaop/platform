<?php

declare(strict_types=1);

namespace App\Install\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use System\Database\DatabaseValidator;
use System\Environment\EnvironmentWriter;

class DatabaseController extends Controller
{
    public function __construct(
        private readonly DatabaseValidator $validator,
        private readonly EnvironmentWriter $env,
    ) {}

    public function index(): View
    {
        return view('install.steps.database', [
            'config' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => '3306',
                'database' => '',
                'username' => '',
            ],
        ]);
    }

    public function test(Request $request): JsonResponse
    {
        $config = $request->validate([
            'driver' => 'required|in:mysql,mariadb',
            'host' => 'required|string',
            'port' => 'required|integer',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        $result = $this->validator->test($config);

        return response()->json($result);
    }

    public function store(Request $request): RedirectResponse
    {
        $config = $request->validate([
            'driver' => 'required|in:mysql,mariadb',
            'host' => 'required|string',
            'port' => 'required|integer',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        $result = $this->validator->test($config);

        if (! $result['success']) {
            return redirect()
                ->route('install.database')
                ->withInput()
                ->with('error', $result['message']);
        }

        $this->env->setMultiple([
            'DB_CONNECTION' => $config['driver'] === 'mariadb' ? 'mysql' : $config['driver'],
            'DB_HOST' => $config['host'] ?? '',
            'DB_PORT' => (string) ($config['port'] ?? '3306'),
            'DB_DATABASE' => $config['database'],
            'DB_USERNAME' => $config['username'] ?? '',
            'DB_PASSWORD' => $config['password'] ?? '',
        ]);

        return redirect()->route('install.environment');
    }
}
