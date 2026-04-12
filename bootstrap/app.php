<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'thanh-toan/vietqr/sepay-webhook',
            'theo-doi-khach',
            'theo-doi-san-pham-quan-tam',
        ]);

        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\EnsureAdminAuthenticated::class,
            'admin.only' => \App\Http\Middleware\EnsureAdminRole::class,
            'backend.permission' => \App\Http\Middleware\EnsureBackendPermission::class,
            'backend.activity' => \App\Http\Middleware\LogBackendActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();

// Hosting hien tai dung thu muc goc lam web root thay vi /public.
$app->usePublicPath(dirname(__DIR__));

return $app;
