<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\BackendPermissionRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()?->canAccessBackend()) {
            $targetRoute = Auth::user()?->role === 'admin'
                ? 'backend.index'
                : (Auth::user()?->defaultBackendRouteName() ?? BackendPermissionRegistry::routeForPermission('products') ?? 'backend.login');

            if ($targetRoute !== 'backend.login') {
                return redirect()->route($targetRoute);
            }
        }

        return view('backend.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Vui lòng nhập email đăng nhập.',
            'email.email' => 'Email đăng nhập không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        if (!Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Email hoặc mật khẩu chưa đúng.',
                ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        if (!$user || !$user->canAccessBackend()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Tài khoản này không có quyền truy cập trang quản trị.',
                ]);
        }

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        ActivityLogger::log($user, 'login', 'Đăng nhập backend', [
            'route_name' => 'backend.login.submit',
            'method' => 'POST',
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($user->role !== 'admin' && $user->defaultBackendRouteName() === null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Role của tài khoản này chưa được cấp quyền truy cập backend.',
                ]);
        }

        $defaultRoute = $user->role === 'admin'
            ? 'backend.index'
            : ($user->defaultBackendRouteName() ?? 'backend.products');

        return redirect()->intended(route($defaultRoute));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            ActivityLogger::log($user, 'logout', 'Đăng xuất backend', [
                'route_name' => 'backend.logout',
                'method' => 'POST',
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('backend.login')->with('success', 'Đã đăng xuất khỏi trang quản trị.');
    }
}
