<?php

namespace App\Http\Middleware;

use App\Support\BackendPermissionRegistry;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureBackendPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $routeName = $request->route()?->getName();

        if (!$user || $user->status !== 'active') {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        if ($routeName === 'backend.logout') {
            return $next($request);
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if (!$user->canAccessBackend()) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        $permission = BackendPermissionRegistry::permissionForRoute($routeName);

        if ($permission === null) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        if ($user->hasBackendPermission($permission)) {
            return $next($request);
        }

        $fallbackRoute = $user->defaultBackendRouteName();

        if ($request->expectsJson()) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        if ($fallbackRoute !== null && $fallbackRoute !== $routeName) {
            return redirect()
                ->route($fallbackRoute)
                ->with('error', 'Tài khoản của bạn chưa được cấp quyền vào mục này.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('backend.login')
            ->withErrors([
                'email' => 'Role của tài khoản này chưa được cấp quyền truy cập backend.',
            ]);
    }
}
