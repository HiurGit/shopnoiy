<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
        ]);

        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\EnsureAdminAuthenticated::class,
            'admin.only' => \App\Http\Middleware\EnsureAdminRole::class,
            'customer.auth' => \App\Http\Middleware\EnsureCustomerAuthenticated::class,
            'backend.permission' => \App\Http\Middleware\EnsureBackendPermission::class,
            'backend.activity' => \App\Http\Middleware\LogBackendActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'session' => 'Phiên làm việc đã hết hạn. Vui lòng thử đăng nhập lại.',
                ]);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if ($exception->getStatusCode() !== 419) {
                return null;
            }

            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'session' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.',
                ]);
        });
    })
    ->create();

// Hosting hien tai dung thu muc goc lam web root thay vi /public.
$app->usePublicPath(dirname(__DIR__));

return $app;
