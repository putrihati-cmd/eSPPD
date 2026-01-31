<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacGatesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $approver;
    protected User $employee;

    public function setUp(): void
    {
        parent::setUp();
        
        $adminRole = Role::create(['name' => 'admin', 'label' => 'Admin', 'level' => 98]);
        $approverRole = Role::create(['name' => 'approver', 'label' => 'Approver', 'level' => 3]);
        $employeeRole = Role::create(['name' => 'employee', 'label' => 'Employee', 'level' => 1]);

        $this->admin = User::factory()->create(['role_id' => $adminRole->id, 'role' => 'admin']);
        $this->approver = User::factory()->create(['role_id' => $approverRole->id, 'role_level' => 3]);
        $this->employee = User::factory()->create(['role_id' => $employeeRole->id, 'role_level' => 1]);

        // Create permissions
        Permission::create(['name' => 'spd.create', 'label' => 'Create SPD', 'category' => 'spd']);
        Permission::create(['name' => 'approval.approve', 'label' => 'Approve SPD', 'category' => 'approval']);
    }

    /**
     * Test admin can pass all gates
     */
    public function test_admin_passes_all_gates(): void
    {
        // Admin gate bypass triggered first
        $this->assertTrue($this->admin->can('create-spd'));
        $this->assertTrue($this->admin->can('approve-spd'));
        $this->assertTrue($this->admin->can('view-all-spd'));
    }

    /**
     * Test approver gates
     */
    public function test_approver_gates(): void
    {
        $this->assertTrue($this->approver->can('approve-spd'));
        $this->assertTrue($this->approver->can('view-all-spd'));
        $this->assertTrue($this->approver->can('delegate-approval'));
    }

    /**
     * Test employee gates
     */
    public function test_employee_gates(): void
    {
        $this->assertTrue($this->employee->can('create-spd'));
        $this->assertFalse($this->employee->can('approve-spd'));
        $this->assertFalse($this->employee->can('view-all-spd'));
    }

    /**
     * Test dynamic permission gate
     */
    public function test_dynamic_permission_gate(): void
    {
        // Admin can access any permission
        $this->assertTrue($this->admin->can('has-permission', 'spd.create'));
        $this->assertTrue($this->admin->can('has-permission', 'approval.approve'));

        // Employee doesn't have permissions yet
        $this->assertFalse($this->employee->can('has-permission', 'approval.approve'));

        // Assign permission to employee
        $permission = Permission::where('name', 'approval.approve')->first();
        $this->employee->permissions()->attach($permission->id);

        // Now employee has permission
        $this->assertTrue($this->employee->can('has-permission', 'approval.approve'));
    }

    /**
     * Test budget approval gate
     */
    public function test_budget_approval_gate(): void
    {
        // Approver (level 3) can approve up to 10jt
        $this->assertTrue($this->approver->can('approve-budget', 5000000));
        $this->assertTrue($this->approver->can('approve-budget', 10000000));
        $this->assertFalse($this->approver->can('approve-budget', 10000001));

        // Employee cannot approve any amount
        $this->assertFalse($this->employee->can('approve-budget', 1000000));

        // Admin can approve any amount
        $this->assertTrue($this->admin->can('approve-budget', 999999999));
    }

    /**
     * Test role-based gates
     */
    public function test_role_based_gates(): void
    {
        // Create high-level user
        $dekanRole = Role::create(['name' => 'dekan', 'label' => 'Dekan', 'level' => 4]);
        $dekan = User::factory()->create(['role_id' => $dekanRole->id, 'role_level' => 4]);

        // Dekan can override
        $this->assertTrue($dekan->can('override-approval'));

        // Employee cannot
        $this->assertFalse($this->employee->can('override-approval'));

        // Approver (level 3) cannot
        $this->assertFalse($this->approver->can('override-approval'));
    }
}
