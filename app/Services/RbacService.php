<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Unified RBAC Service
 * Consolidates role-based and permission-based access control
 */
class RbacService
{
    /**
     * Check if user has permission
     */
    public static function userHasPermission(User $user, string $permission): bool
    {
        // Superadmin/admin bypass all checks
        if ($user->isAdmin()) {
            return true;
        }

        // Check direct user permissions
        if ($user->permissions()->where('name', $permission)->exists()) {
            return true;
        }

        // Check role permissions
        if ($user->roleModel && $user->roleModel->permissions()->where('name', $permission)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Check if user has any permission from array
     */
    public static function userHasAnyPermission(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (self::userHasPermission($user, $permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user can approve based on budget amount
     */
    public static function canApproveAmount(User $user, int $amount): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $limit = $user->roleModel?->getApprovalLimit();

        // No limit = can approve any amount
        if ($limit === null) {
            return true;
        }

        return $amount <= $limit;
    }

    /**
     * Get all permissions for user
     */
    public static function getUserPermissions(User $user): Collection
    {
        if ($user->isAdmin()) {
            return Permission::all();
        }

        $directPermissions = $user->permissions()->pluck('name');
        $rolePermissions = $user->roleModel?->permissions()->pluck('name') ?? collect([]);

        return $directPermissions->merge($rolePermissions)->unique();
    }

    /**
     * Check if user can delegate to another user
     */
    public static function canDelegate(User $user, User $delegateTo): bool
    {
        // Only Wadek+ can delegate
        if (!$user->canDelegate()) {
            return false;
        }

        // Cannot delegate to lower level
        if ($delegateTo->role_level < $user->role_level) {
            return false;
        }

        // Cannot delegate to self
        if ($user->id === $delegateTo->id) {
            return false;
        }

        return true;
    }

    /**
     * Get all roles with their permission count
     */
    public static function getRolesWithPermissions(): Collection
    {
        return Role::with('permissions')->get();
    }

    /**
     * Assign permission to role
     */
    public static function assignPermissionToRole(Role $role, string $permissionName): bool
    {
        $permission = Permission::where('name', $permissionName)->first();
        if (!$permission) {
            return false;
        }

        return !$role->permissions()->where('id', $permission->id)->exists() &&
               $role->permissions()->attach($permission->id);
    }

    /**
     * Revoke permission from role
     */
    public static function revokePermissionFromRole(Role $role, string $permissionName): bool
    {
        $permission = Permission::where('name', $permissionName)->first();
        if (!$permission) {
            return false;
        }

        return $role->permissions()->detach($permission->id);
    }
}
