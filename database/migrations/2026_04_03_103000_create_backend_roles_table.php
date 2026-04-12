<?php

use App\Support\BackendPermissionRegistry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backend_roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_key', 50)->unique();
            $table->string('display_name', 120);
            $table->json('permissions')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });

        $now = now();
        $staffPermissions = DB::table('users')
            ->where('role', 'staff')
            ->whereNotNull('backend_permissions')
            ->value('backend_permissions');

        $decodedStaffPermissions = json_decode((string) $staffPermissions, true);
        if (!is_array($decodedStaffPermissions) || $decodedStaffPermissions === []) {
            $decodedStaffPermissions = BackendPermissionRegistry::defaultStaffPermissions();
        }

        DB::table('backend_roles')->insert([
            [
                'role_key' => 'admin',
                'display_name' => 'Admin',
                'permissions' => json_encode(array_keys(BackendPermissionRegistry::definitions())),
                'is_locked' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'role_key' => 'staff',
                'display_name' => 'Staff',
                'permissions' => json_encode(array_values($decodedStaffPermissions)),
                'is_locked' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'role_key' => 'customer',
                'display_name' => 'Customer',
                'permissions' => json_encode([]),
                'is_locked' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $existingRoles = DB::table('users')
            ->select('role')
            ->distinct()
            ->pluck('role')
            ->filter()
            ->map(fn ($role) => (string) $role)
            ->reject(fn ($role) => in_array($role, ['admin', 'staff', 'customer'], true))
            ->values();

        foreach ($existingRoles as $roleKey) {
            DB::table('backend_roles')->insert([
                'role_key' => $roleKey,
                'display_name' => ucfirst(str_replace(['_', '-'], ' ', $roleKey)),
                'permissions' => json_encode([]),
                'is_locked' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('backend_roles');
    }
};
