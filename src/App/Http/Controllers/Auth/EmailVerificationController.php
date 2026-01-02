<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Domain\User\Enums\UserStatus;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use System\Settings\RegistrationSettings;

class EmailVerificationController extends Controller
{
    public function __construct(
        private readonly RegistrationSettings $settings
    ) {}

    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));

            // Activate user if approval is not required
            // (they were pending only for email verification)
            if (! $this->settings->requireApproval) {
                $request->user()->update(['status' => UserStatus::Active]);
            }
        }

        return redirect()->route('home')->with('status', __('auth.email_verified'));
    }

    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', __('auth.verification_link_sent'));
    }
}
