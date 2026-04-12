<?php

namespace App\Models;

use App\Models\BackendRole;
use App\Support\BackendPermissionRegistry;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password_hash',
        'role',
        'status',
        'backend_permissions',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'password_hash' => 'hashed',
            'backend_permissions' => 'array',
        ];
    }

    /**
     * Use custom password column for Laravel authentication.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function backendRole(): ?BackendRole
    {
        return BackendRole::query()
            ->where('role_key', (string) $this->role)
            ->first();
    }

    /**
     * @return array<int, string>
     */
    public function backendPermissions(): array
    {
        return $this->backendRole()?->normalizedPermissions() ?? [];
    }

    public function hasBackendPermission(string $permission): bool
    {
        return in_array($permission, $this->backendPermissions(), true);
    }

    public function defaultBackendRouteName(): ?string
    {
        foreach ($this->backendPermissions() as $permission) {
            $routeName = BackendPermissionRegistry::routeForPermission($permission);
            if ($routeName !== null) {
                return $routeName;
            }
        }

        return null;
    }

    public function canAccessBackend(): bool
    {
        return $this->status === 'active'
            && $this->role !== 'customer'
            && $this->backendRole() !== null;
    }
}
