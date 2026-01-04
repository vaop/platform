<?php

declare(strict_types=1);

namespace System\Auth\Providers;

use Domain\User\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind User model to Authenticatable for queue workers and other contexts
        $this->app->bind(Authenticatable::class, User::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Superadmin bypasses all permission checks
        Gate::before(function ($user, $ability) {
            return $user->hasRole('superadmin') ? true : null;
        });
    }
}
