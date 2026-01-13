<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Inertia::share([
            'auth' => fn () => Auth::check()
                ? [
                    'user' => Auth::user(),
                    'permissions' => Auth::user()
                        ->getAllPermissions()
                        ->pluck('name')
                        ->values(),
                    'roles' => Auth::user()
                        ->getRoleNames()
                        ->values(),
                ]
                : null,
        ]);
    }
}
