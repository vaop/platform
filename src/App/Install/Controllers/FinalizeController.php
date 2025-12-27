<?php

declare(strict_types=1);

namespace App\Install\Controllers;

use App\Install\Services\EnvironmentWriter;
use App\Install\Services\MigrationRunner;
use Domain\User\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class FinalizeController extends Controller
{
    public function __construct(
        private readonly EnvironmentWriter $env,
        private readonly MigrationRunner $migrations,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $adminUser = session('admin_user');

        if (! $adminUser) {
            return redirect()->route('install.admin');
        }

        $progress = $this->migrations->getProgress();

        return view('install.steps.finalize', [
            'admin_email' => $adminUser['email'],
            'progress' => $progress,
            'step' => $request->query('step', 'ready'),
        ]);
    }

    public function store(Request $request): View|RedirectResponse
    {
        $adminUser = session('admin_user');

        if (! $adminUser) {
            return redirect()->route('install.admin');
        }

        $step = $request->input('step', 'migrate');

        try {
            return match ($step) {
                'migrate' => $this->handleMigrationStep($adminUser),
                'user' => $this->handleUserStep($adminUser),
                'optimize' => $this->handleOptimizeStep($adminUser),
                'complete' => $this->handleCompleteStep(),
                default => redirect()->route('install.finalize'),
            };
        } catch (Throwable $e) {
            return redirect()
                ->route('install.finalize')
                ->with('error', 'Installation failed: '.$e->getMessage());
        }
    }

    private function handleMigrationStep(array $adminUser): View|RedirectResponse
    {
        $this->migrations->runNext();

        $nextStep = $this->migrations->hasPending() ? 'migrate' : 'user';

        return view('install.steps.finalize', [
            'admin_email' => $adminUser['email'],
            'progress' => $this->migrations->getProgress(),
            'step' => 'running',
            'next_step' => $nextStep,
        ]);
    }

    private function handleUserStep(array $adminUser): View
    {
        if (! User::where('email', $adminUser['email'])->exists()) {
            User::create([
                'name' => $adminUser['name'],
                'email' => $adminUser['email'],
                'password' => $adminUser['password'],
                'email_verified_at' => now(),
            ]);
        }

        return view('install.steps.finalize', [
            'admin_email' => $adminUser['email'],
            'progress' => $this->migrations->getProgress(),
            'step' => 'running',
            'next_step' => 'optimize',
        ]);
    }

    private function handleOptimizeStep(array $adminUser): View
    {
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        if (! app()->environment('local')) {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
        }

        if (! file_exists(public_path('storage'))) {
            Artisan::call('storage:link');
        }

        return view('install.steps.finalize', [
            'admin_email' => $adminUser['email'],
            'progress' => $this->migrations->getProgress(),
            'step' => 'running',
            'next_step' => 'complete',
        ]);
    }

    private function handleCompleteStep(): RedirectResponse
    {
        file_put_contents(
            storage_path('installed'),
            json_encode([
                'installed_at' => now()->toIso8601String(),
                'version' => trim(file_get_contents(base_path('VERSION')) ?: 'unknown'),
            ])
        );

        $this->env->set('INSTALLER_ENABLED', 'false');

        session()->forget('admin_user');

        return redirect()->route('install.complete');
    }
}
