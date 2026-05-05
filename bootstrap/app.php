<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Redirect to login on CSRF expiry (full-page loads)
        $exceptions->renderable(function (TokenMismatchException $e, Request $request) {
            if (! $request->expectsJson()) {
                return redirect()->route('login')
                    ->with('status', 'Tu sesión ha expirado. Por favor inicia sesión nuevamente.');
            }
        });
    })->create();
