<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use DateTimeZone;
use Domain\Geography\Models\Country;
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
            'countries' => $this->getCountries(),
            'countryCodes' => $this->getCountryCodes(),
            'timezones' => $this->getTimezones(),
        ]);
    }

    /**
     * Get a list of countries for the registration form.
     *
     * @return array<int, string>
     */
    private function getCountries(): array
    {
        return Country::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Get a mapping of ISO alpha-2 codes to country IDs for auto-detection.
     *
     * @return array<string, int>
     */
    private function getCountryCodes(): array
    {
        return Country::query()
            ->pluck('id', 'iso_alpha2')
            ->toArray();
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
        // Determine initial status:
        // - If approval required: Pending (admin must approve)
        // - If email verification required: Pending (until verified)
        // - Otherwise: Active immediately
        $requiresActivation = $this->settings->requireApproval
            || $this->settings->requireEmailVerification;

        $status = $requiresActivation
            ? UserStatus::Pending
            : UserStatus::Active;

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'country_id' => $request->input('country_id'),
            'timezone' => $request->input('timezone'),
            'status' => $status,
        ]);

        event(new Registered($user));

        if ($this->settings->requireApproval) {
            return redirect()->route('login')
                ->with('status', __('auth.registration_pending'));
        }

        if ($this->settings->requireEmailVerification) {
            Auth::login($user);

            return redirect()->route('verification.notice');
        }

        Auth::login($user);

        return redirect()->route('home');
    }
}
