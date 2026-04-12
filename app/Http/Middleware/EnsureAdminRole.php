<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->role === 'admin' && $user->status === 'active') {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        return redirect()
            ->route('backend.products')
            ->with('error', 'Tài khoản staff chỉ được phép quản lý sản phẩm.');
    }
}
