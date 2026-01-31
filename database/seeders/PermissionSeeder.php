<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // SPD Management
            ['name' => 'spd.create', 'label' => 'Create SPD', 'category' => 'spd', 'description' => 'Create new Surat Perjalanan Dinas'],
            ['name' => 'spd.edit', 'label' => 'Edit SPD', 'category' => 'spd', 'description' => 'Edit own SPD in draft'],
            ['name' => 'spd.delete', 'label' => 'Delete SPD', 'category' => 'spd', 'description' => 'Delete own SPD in draft'],
            ['name' => 'spd.view-all', 'label' => 'View All SPD', 'category' => 'spd', 'description' => 'View all SPD in organization'],

            // Approval
            ['name' => 'approval.approve', 'label' => 'Approve SPD', 'category' => 'approval', 'description' => 'Approve pending SPD'],
            ['name' => 'approval.reject', 'label' => 'Reject SPD', 'category' => 'approval', 'description' => 'Reject and return SPD for revision'],
            ['name' => 'approval.delegate', 'label' => 'Delegate Approval', 'category' => 'approval', 'description' => 'Delegate approval to another user'],
            ['name' => 'approval.override', 'label' => 'Override Approval', 'category' => 'approval', 'description' => 'Force approve/reject regardless of level'],

            // Finance
            ['name' => 'finance.view-budget', 'label' => 'View Budget', 'category' => 'finance', 'description' => 'View budget data'],
            ['name' => 'finance.manage-budget', 'label' => 'Manage Budget', 'category' => 'finance', 'description' => 'Create/edit budget allocations'],
            ['name' => 'finance.approve-cost', 'label' => 'Approve Cost', 'category' => 'finance', 'description' => 'Approve actual cost from trip report'],

            // Reports
            ['name' => 'report.create', 'label' => 'Create Report', 'category' => 'report', 'description' => 'Create trip report'],
            ['name' => 'report.view-all', 'label' => 'View All Reports', 'category' => 'report', 'description' => 'View all trip reports'],
            ['name' => 'report.verify', 'label' => 'Verify Report', 'category' => 'report', 'description' => 'Verify submitted trip reports'],

            // Admin
            ['name' => 'admin.manage-users', 'label' => 'Manage Users', 'category' => 'admin', 'description' => 'Create/edit/delete users'],
            ['name' => 'admin.manage-roles', 'label' => 'Manage Roles', 'category' => 'admin', 'description' => 'Create/edit roles and permissions'],
            ['name' => 'admin.view-logs', 'label' => 'View System Logs', 'category' => 'admin', 'description' => 'Access audit logs'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Assign permissions to roles
        $this->assignRolePermissions();
    }

    private function assignRolePermissions(): void
    {
        $rolePermissions = [
            'employee' => ['spd.create', 'spd.edit', 'spd.delete', 'report.create'],
            'kabag' => ['spd.create', 'spd.edit', 'spd.delete', 'report.create', 'approval.approve', 'approval.reject', 'finance.view-budget'],
            'wadek' => ['spd.view-all', 'approval.approve', 'approval.reject', 'approval.delegate', 'finance.view-budget', 'report.verify'],
            'dekan' => ['spd.view-all', 'approval.approve', 'approval.reject', 'approval.delegate', 'approval.override', 'finance.manage-budget', 'report.verify'],
            'warek' => ['spd.view-all', 'approval.approve', 'approval.reject', 'approval.override', 'finance.manage-budget', 'report.verify'],
            'rektor' => ['spd.view-all', 'approval.approve', 'approval.override', 'finance.manage-budget', 'report.verify'],
            'admin' => [], // Admin bypasses all checks
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if (!$role) continue;

            foreach ($permissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && !$role->permissions()->where('permission_id', $permission->id)->exists()) {
                    $role->permissions()->attach($permission->id);
                }
            }
        }
    }
}
