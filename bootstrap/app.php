<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(env('PORTAL_LOGIN_URL', 'https://promise.summitadyawinsa.co.id/dev/login'));
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'inventory.role' => \App\Http\Middleware\CheckInventoryRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, $request) {
            return response()->view('errors.index', ['exception' => $e], $e->getStatusCode());
        });
    })->create();
