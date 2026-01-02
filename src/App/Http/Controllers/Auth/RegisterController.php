<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use DateTimeZone;
use Domain\User\Enums\UserStatus;
use Domain\User\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use System\Settings\RegistrationSettings;

class RegisterController extends Controller
{
    public function __construct(
        private readonly RegistrationSettings $settings
    ) {}

    public function showRegistrationForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        if (! $this->settings->registrationOpen) {
            return redirect()->route('login')
                ->with('error', __('auth.registration_closed'));
        }

        return view('auth.register', [
            'registrationSettings' => $this->settings,
            'timezones' => $this->getTimezones(),
        ]);
    }

    /**
     * Get a list of timezones.
     *
     * @return array<string, string>
     */
    private function getTimezones(): array
    {
        $timezones = [];

        foreach (DateTimeZone::listIdentifiers() as $timezone) {
            $timezones[$timezone] = str_replace('_', ' ', $timezone);
        }

        return $timezones;
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $status = $this->settings->requireApproval
            ? UserStatus::Pending
            : UserStatus::Active;

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'country' => $request->input('country'),
            'timezone' => $request->input('timezone'),
            'status' => $status,
        ]);

        $user->assignRole('pilot');

        event(new Registered($user));

        if ($this->settings->requireApproval) {
            return redirect()->route('login')
                ->with('status', __('auth.registration_pending'));
        }

        Auth::login($user);

        return redirect()->route('home');
    }
}
