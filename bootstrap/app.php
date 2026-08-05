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
        $middleware->redirectGuestsTo(fn () => config('services.portal_login_url'));
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            'logout',
            '*/product/read-sheets',
            '*/product/import-data',
            '*/vave/import-data',
            // User Access Management (already protected by auth middleware)
            // 419 occurs due to shared session cookie conflict between inventory & portal apps
            'inventory/user-access',
            'inventory/user-access/*',
            'inventory/user-menus',
            'inventory/user-menus/*',
            'inventory/roles',
            'inventory/roles/*',
            'inventory/role-menus',
            'inventory/role-menus/*',
            'inventory/users',
            'inventory/users/*',
            'inventory/menus',
            'inventory/menus/*',
        ]);
        $middleware->encryptCookies(except: [
            'promise_auth_session'
        ]);
        $middleware->alias([
            'inventory.role' => \App\Http\Middleware\CheckInventoryRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, $request) {
            return response()->view('errors.index', ['exception' => $e], $e->getStatusCode());
        });
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('inventory:sync-stock')->dailyAt('01:00');
    })->create();
