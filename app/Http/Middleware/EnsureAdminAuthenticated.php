<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('backend.login');
        }

        $user = Auth::user();

        if (!$user || !$user->canAccessBackend()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('backend.login')
                ->withErrors([
                    'email' => 'Bạn không có quyền truy cập trang quản trị.',
                ]);
        }

        return $next($request);
    }
}
