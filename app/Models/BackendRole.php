<?php

namespace App\Models;

use App\Support\BackendPermissionRegistry;
use Illuminate\Database\Eloquent\Model;

class BackendRole extends Model
{
    protected $table = 'backend_roles';

    protected $fillable = [
        'role_key',
        'display_name',
        'permissions',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_locked' => 'boolean',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function normalizedPermissions(): array
    {
        if ($this->role_key === 'admin') {
            return array_keys(BackendPermissionRegistry::definitions());
        }

        $permissions = $this->permissions;
        $allowedKeys = array_keys(BackendPermissionRegistry::definitions());

        if (!is_array($permissions)) {
            return [];
        }

        return collect($permissions)
            ->map(fn ($permission) => (string) $permission)
            ->filter(fn ($permission) => in_array($permission, $allowedKeys, true))
            ->unique()
            ->values()
            ->all();
    }
}
