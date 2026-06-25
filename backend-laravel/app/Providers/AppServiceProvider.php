<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Gate: hanya role 'owner' yang boleh akses manajemen owner
        Gate::define('owner', function ($user) {
            return $user->role === 'owner';
        });
    }
}
