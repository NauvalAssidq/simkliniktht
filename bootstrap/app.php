<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request) => route('login'));
        
        $middleware->redirectUsersTo(function (Request $request) {
            $role = $request->user()?->role;
            if ($role === 'admin') {
                return route('satusehat.practitioner');
            } elseif ($role === 'dokter') {
                return route('pemeriksaan.index');
            } elseif ($role === 'apotek') {
                return route('apotek.index');
            }
            return route('registration.index');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
