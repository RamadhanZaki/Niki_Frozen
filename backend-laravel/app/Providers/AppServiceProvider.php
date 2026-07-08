<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Rate limiter khusus login: dibatasi per kombinasi email + IP,
        // supaya satu kasir yang salah ketik password tidak ikut mengunci
        // kasir lain yang kebetulan berada di jaringan/IP yang sama.
        RateLimiter::for('login', function (Request $request) {
            $emailKey = Str::transliterate(Str::lower((string) $request->input('email')));
            $throttleKey = $emailKey.'|'.$request->ip();

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
