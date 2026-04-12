<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BackendRole;
use App\Support\BackendPermissionRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = BackendRole::query()
            ->where('role_key', '!=', 'customer')
            ->orderByRaw("CASE WHEN role_key = 'admin' THEN 0 WHEN role_key = 'staff' THEN 1 ELSE 2 END")
            ->orderBy('display_name')
            ->get();

        $permissionDefinitions = BackendPermissionRegistry::definitions();

        return view('backend.roles.index', compact('roles', 'permissionDefinitions'));
    }

    public function update(Request $request, BackendRole $role): RedirectResponse
    {
        if (in_array($role->role_key, ['admin', 'customer'], true)) {
            $message = $role->role_key === 'customer'
                ? 'Role customer là khách hàng và không quản lý trong màn hình role backend.'
                : 'Role admin luôn có toàn quyền và không chỉnh tại đây.';

            return redirect()
                ->route('backend.roles')
                ->with('error', $message);
        }

        $permissionKeys = array_keys(BackendPermissionRegistry::definitions());
        $submittedPermissions = $request->input('permissions', []);
        $permissions = collect(is_array($submittedPermissions) ? $submittedPermissions : [])
            ->map(fn ($permission) => (string) $permission)
            ->filter(fn ($permission) => in_array($permission, $permissionKeys, true))
            ->unique()
            ->values()
            ->all();

        $role->update([
            'permissions' => $permissions,
        ]);

        return redirect()
            ->route('backend.roles')
            ->with('success', 'Đã cập nhật quyền cho role ' . $role->display_name . '.');
    }
}
