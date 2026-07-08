<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => \App\Http\Middleware\RoleMiddleware::class]);

        // Percayai reverse proxy Railway supaya Laravel tahu request
        // sebenarnya datang lewat HTTPS (penting untuk url(), signed route,
        // dan cookie secure di session).
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Saat login kena rate limit, redirect balik ke form login
        // dengan pesan yang tampil lewat $errors->first() (sama seperti
        // error validasi lain di login.blade.php), bukan halaman 429 polos.
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('login') && ! $request->expectsJson()) {
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors([
                        'email' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam beberapa saat.',
                    ]);
            }
        });
    })->create();
