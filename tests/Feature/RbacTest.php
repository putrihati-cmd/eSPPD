<?php

namespace Tests\Feature;

use App\Models\ApprovalDelegation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\RbacService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $rektor;
    protected User $dekan;
    protected User $wadek;
    protected User $employee;

    public function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $adminRole = Role::create(['name' => 'admin', 'label' => 'Admin', 'level' => 98]);
        $rektorRole = Role::create(['name' => 'rektor', 'label' => 'Rektor', 'level' => 6]);
        $dekanRole = Role::create(['name' => 'dekan', 'label' => 'Dekan', 'level' => 4]);
        $wadekRole = Role::create(['name' => 'wadek', 'label' => 'Wadek', 'level' => 3]);
        $employeeRole = Role::create(['name' => 'employee', 'label' => 'Pegawai', 'level' => 1]);

        // Create users
        $this->admin = User::factory()->create(['role_id' => $adminRole->id, 'role' => 'admin']);
        $this->rektor = User::factory()->create(['role_id' => $rektorRole->id, 'role_level' => 6]);
        $this->dekan = User::factory()->create(['role_id' => $dekanRole->id, 'role_level' => 4]);
        $this->wadek = User::factory()->create(['role_id' => $wadekRole->id, 'role_level' => 3]);
        $this->employee = User::factory()->create(['role_id' => $employeeRole->id, 'role_level' => 1]);

        // Create permissions
        Permission::create(['name' => 'spd.create', 'label' => 'Create SPD', 'category' => 'spd']);
        Permission::create(['name' => 'approval.approve', 'label' => 'Approve SPD', 'category' => 'approval']);
        Permission::create(['name' => 'approval.delegate', 'label' => 'Delegate Approval', 'category' => 'approval']);
        Permission::create(['name' => 'finance.manage-budget', 'label' => 'Manage Budget', 'category' => 'finance']);
    }

    /**
     * Test admin can do everything
     */
    public function test_admin_bypasses_all_checks(): void
    {
        $this->assertTrue($this->admin->isAdmin());
        
        // Admin can approve any amount
        $this->assertTrue(RbacService::canApproveAmount($this->admin, 1000000000));
        
        // Admin has all permissions
        $this->assertTrue(RbacService::userHasPermission($this->admin, 'spd.create'));
        $this->assertTrue(RbacService::userHasPermission($this->admin, 'approval.approve'));
        $this->assertTrue(RbacService::userHasPermission($this->admin, 'finance.manage-budget'));
    }

    /**
     * Test role-based approval limits
     */
    public function test_approval_limits_by_role(): void
    {
        // Wadek: 10jt limit
        $this->assertTrue(RbacService::canApproveAmount($this->wadek, 10000000));
        $this->assertFalse(RbacService::canApproveAmount($this->wadek, 50000001));

        // Dekan: 50jt limit
        $this->assertTrue(RbacService::canApproveAmount($this->dekan, 50000000));
        $this->assertFalse(RbacService::canApproveAmount($this->dekan, 100000001));

        // Rektor: unlimited
        $this->assertTrue(RbacService::canApproveAmount($this->rektor, 1000000000));

        // Employee: cannot approve
        $this->assertFalse(RbacService::canApproveAmount($this->employee, 1000000));
    }

    /**
     * Test permission assignment to roles
     */
    public function test_assign_permission_to_role(): void
    {
        $wadekRole = $this->wadek->role;
        $spd_create_perm = Permission::where('name', 'spd.create')->first();

        // Assign permission
        RbacService::assignPermissionToRole($wadekRole, 'spd.create');
        
        // Verify assignment
        $this->assertTrue($wadekRole->permissions()->where('id', $spd_create_perm->id)->exists());
        $this->assertTrue(RbacService::userHasPermission($this->wadek, 'spd.create'));
    }

    /**
     * Test permission revocation from role
     */
    public function test_revoke_permission_from_role(): void
    {
        $wadekRole = $this->wadek->role;
        
        // First assign
        RbacService::assignPermissionToRole($wadekRole, 'spd.create');
        $this->assertTrue(RbacService::userHasPermission($this->wadek, 'spd.create'));

        // Then revoke
        RbacService::revokePermissionFromRole($wadekRole, 'spd.create');
        $this->assertFalse(RbacService::userHasPermission($this->wadek, 'spd.create'));
    }

    /**
     * Test delegation eligibility
     */
    public function test_delegation_eligibility(): void
    {
        // Wadek can delegate to Dekan (higher level)
        $this->assertTrue(RbacService::canDelegate($this->wadek, $this->dekan));

        // Wadek cannot delegate to Employee (lower level)
        $this->assertFalse(RbacService::canDelegate($this->wadek, $this->employee));

        // Wadek cannot delegate to self
        $this->assertFalse(RbacService::canDelegate($this->wadek, $this->wadek));

        // Employee cannot delegate
        $this->assertFalse(RbacService::canDelegate($this->employee, $this->wadek));
    }

    /**
     * Test approval delegation creation
     */
    public function test_create_approval_delegation(): void
    {
        $delegation = ApprovalDelegation::create([
            'delegator_id' => $this->dekan->id,
            'delegate_id' => $this->wadek->id,
            'reason' => 'Cuti mingguan',
            'valid_from' => now(),
            'valid_until' => now()->addDays(7),
            'is_active' => true,
        ]);

        $this->assertTrue($delegation->isValid());
        
        // Check it exists
        $this->assertDatabaseHas('approval_delegations', [
            'delegator_id' => $this->dekan->id,
            'delegate_id' => $this->wadek->id,
        ]);
    }

    /**
     * Test delegation validity checks
     */
    public function test_delegation_validity(): void
    {
        $futureDelegate = ApprovalDelegation::create([
            'delegator_id' => $this->dekan->id,
            'delegate_id' => $this->wadek->id,
            'valid_from' => now()->addDays(5),
            'is_active' => true,
        ]);

        $expiredDelegate = ApprovalDelegation::create([
            'delegator_id' => $this->dekan->id,
            'delegate_id' => $this->wadek->id,
            'valid_from' => now()->subDays(10),
            'valid_until' => now()->subDays(1),
            'is_active' => true,
        ]);

        $this->assertFalse($futureDelegate->isValid()); // Not yet valid
        $this->assertFalse($expiredDelegate->isValid()); // Expired
    }

    /**
     * Test get active delegations
     */
    public function test_get_active_delegations(): void
    {
        // Create valid delegation
        ApprovalDelegation::create([
            'delegator_id' => $this->dekan->id,
            'delegate_id' => $this->wadek->id,
            'valid_from' => now(),
            'is_active' => true,
        ]);

        // Create inactive delegation
        ApprovalDelegation::create([
            'delegator_id' => $this->dekan->id,
            'delegate_id' => $this->employee->id,
            'valid_from' => now(),
            'is_active' => false,
        ]);

        $active = ApprovalDelegation::getActiveDelegations($this->dekan);
        
        $this->assertEquals(1, $active->count());
        $this->assertEquals($this->wadek->id, $active->first()->delegate_id);
    }

    /**
     * Test user role level checking
     */
    public function test_user_role_level_hierarchy(): void
    {
        $this->assertEquals(1, $this->employee->role_level);
        $this->assertEquals(3, $this->wadek->role_level);
        $this->assertEquals(4, $this->dekan->role_level);
        $this->assertEquals(6, $this->rektor->role_level);
        
        // Min level checks
        $this->assertTrue($this->rektor->hasMinLevel(4));
        $this->assertTrue($this->dekan->hasMinLevel(4));
        $this->assertFalse($this->wadek->hasMinLevel(4));
        $this->assertFalse($this->employee->hasMinLevel(4));
    }
}
