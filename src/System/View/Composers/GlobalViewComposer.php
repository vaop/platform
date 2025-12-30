<?php

declare(strict_types=1);

namespace System\View\Composers;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\View\View;

/**
 * Provides shared data to all views (both Blade and Twig).
 */
readonly class GlobalViewComposer
{
    public function __construct(
        private Guard $auth
    ) {}

    public function compose(View $view): void
    {
        $view->with([
            'currentUser' => $this->auth->user(),
        ]);
    }
}
