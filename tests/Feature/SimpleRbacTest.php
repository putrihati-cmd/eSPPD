<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimpleRbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_gate_bypass(): void
    {
        // Create admin role and user
        $adminRole = Role::create(['name' => 'admin', 'label' => 'Admin', 'level' => 98]);
        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'role' => 'admin'
        ]);

        // Admin should pass all gates
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->can('create-spd'));
        $this->assertTrue($admin->can('approve-spd'));
    }

    public function test_approver_can_approve(): void
    {
        // Create approver role
        $approverRole = Role::create(['name' => 'approver', 'label' => 'Approver', 'level' => 3]);
        $approver = User::factory()->create([
            'role_id' => $approverRole->id,
            'role' => 'approver'
        ]);

        // Approver should be able to approve
        $this->assertTrue($approver->isApprover());
        $this->assertTrue($approver->can('approve-spd'));
    }

    public function test_employee_cannot_approve(): void
    {
        // Create employee role
        $employeeRole = Role::create(['name' => 'employee', 'label' => 'Employee', 'level' => 1]);
        $employee = User::factory()->create([
            'role_id' => $employeeRole->id,
            'role' => 'employee'
        ]);

        // Employee should NOT be able to approve
        $this->assertFalse($employee->isApprover());
        $this->assertFalse($employee->can('approve-spd'));
    }

    public function test_role_level_hierarchy(): void
    {
        $wadekRole = Role::create(['name' => 'wadek', 'label' => 'Wadek', 'level' => 3]);
        $dekanRole = Role::create(['name' => 'dekan', 'label' => 'Dekan', 'level' => 4]);

        $wadek = User::factory()->create(['role_id' => $wadekRole->id]);
        $dekan = User::factory()->create(['role_id' => $dekanRole->id]);

        $this->assertEquals(3, $wadek->role_level);
        $this->assertEquals(4, $dekan->role_level);
        $this->assertTrue($dekan->role_level > $wadek->role_level);
    }
}
