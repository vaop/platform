<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\LoginRequest;
use Domain\User\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended(route('home'));
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            $request->hitRateLimiter();

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => __('auth.failed')]);
        }

        /** @var User $user */
        $user = Auth::user();

        // Check if user can login based on status
        if (! $user->canLogin()) {
            Auth::logout();
            $request->hitRateLimiter();

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => __('auth.status.'.$user->status->name)]);
        }

        $request->clearRateLimiter();

        // Update last login timestamp
        $user->update(['last_login_at' => now()]);

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }
}
