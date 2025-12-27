<?php

declare(strict_types=1);

namespace App\Install\Controllers;

use App\Install\Services\RequirementsChecker;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class InstallController extends Controller
{
    public function __construct(
        private readonly RequirementsChecker $requirements,
    ) {}

    public function welcome(): View
    {
        return view('install.steps.welcome');
    }

    public function requirements(): View
    {
        $checks = $this->requirements->check();

        return view('install.steps.requirements', [
            'checks' => $checks,
        ]);
    }

    public function requirementsCheck(): RedirectResponse
    {
        if (! $this->requirements->allPassed()) {
            return redirect()
                ->route('install.requirements')
                ->with('error', 'Please resolve all requirements before continuing.');
        }

        return redirect()->route('install.database');
    }

    public function complete(): View
    {
        return view('install.steps.complete');
    }
}
