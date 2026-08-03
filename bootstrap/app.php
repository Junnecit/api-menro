<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);

        $middleware->throttleApi('60,1');

        // Trust all proxies in local/tunnel setups so IP-keyed rate limits see
        // the real client. In production, set TRUSTED_PROXIES to the load
        // balancer / reverse-proxy IPs (comma-separated) to prevent spoofing.
        $trusted = env('TRUSTED_PROXIES');
        if ($trusted === null || $trusted === '' || $trusted === '*') {
            $middleware->trustProxies(at: '*');
        } else {
            $middleware->trustProxies(at: array_values(array_filter(array_map(
                'trim',
                explode(',', $trusted)
            ))));
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
