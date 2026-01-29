<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Spd;
use App\Models\User;
use App\Models\Approval;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SpdAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $approverUser;
    protected User $employeeUser;
    protected Spd $spd;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 1. Seed RBAC Roles (Critical for CheckRoleLevel middleware)
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        $approverRole = \App\Models\Role::where('name', 'kabag')->first(); // Level 2
        $employeeRole = \App\Models\Role::where('name', 'dosen')->first(); // Level 1
        
        // 2. Create Shared Unit
        $unit = \App\Models\Unit::factory()->create();

        // 3. Create Employees linked to Unit
        $adminEmployee = Employee::factory()->create(['unit_id' => $unit->id]);
        $approverEmployee = Employee::factory()->create(['unit_id' => $unit->id]);
        $employeeEmployee = Employee::factory()->create(['unit_id' => $unit->id]);
        
        // 4. Create Users (with Role ID)
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'role_id' => $adminRole->id,
            'employee_id' => $adminEmployee->id,
        ]);
        
        $this->approverUser = User::factory()->create([
            'role' => 'approver',
            'role_id' => $approverRole->id,
            'employee_id' => $approverEmployee->id,
        ]);
        
        $this->employeeUser = User::factory()->create([
            'role' => 'employee',
            'role_id' => $employeeRole->id,
            'employee_id' => $employeeEmployee->id,
        ]);
        
        // 5. Create test SPD
        $this->spd = Spd::factory()->create([
            'employee_id' => $employeeEmployee->id,
            'unit_id' => $unit->id,
            'organization_id' => $unit->organization_id, // Ensure org matches
            'status' => 'submitted',
        ]);
        
        // 6. Create Initial Approval
        Approval::create([
            'spd_id' => $this->spd->id,
            'approver_id' => $approverEmployee->id,
            'level' => 1,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function employee_can_only_submit_own_spd()
    {
        // Set to draft first so it can be submitted
        $this->spd->update(['status' => 'draft']);

        // Owner should be able to submit
        $this->actingAs($this->employeeUser);
        $response = $this->postJson("/api/sppd/{$this->spd->id}/submit");
        $response->assertStatus(200);
        
        // Non-owner should not be able to submit
        $this->spd->update(['status' => 'draft']);
        
        // Non-owner should not be able to submit
        $otherEmployee = Employee::factory()->create();
        $otherUser = User::factory()->create([
            'role' => 'pegawai',
            'employee_id' => $otherEmployee->id,
        ]);
        
        /** @var \App\Models\User $otherUser */
        $this->actingAs($otherUser);
        $response = $this->postJson("/api/sppd/{$this->spd->id}/submit");
        $response->assertStatus(403);
    }

    /** @test */
    public function approver_can_approve_assigned_spd()
    {
        $this->actingAs($this->approverUser);
        $response = $this->postJson("/api/sppd/{$this->spd->id}/approve");
        $response->assertStatus(200);
        
        $this->spd->refresh();
        $this->assertEquals('approved', $this->spd->status);
    }

    /** @test */
    public function non_approver_cannot_approve_spd()
    {
        $this->actingAs($this->employeeUser);
        $response = $this->postJson("/api/sppd/{$this->spd->id}/approve");
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_approve_any_spd()
    {
        // Remove pending approval and create new one for admin
        Approval::where('spd_id', $this->spd->id)->delete();
        
        Approval::create([
            'spd_id' => $this->spd->id,
            'approver_id' => $this->adminUser->employee_id,
            'level' => 1,
            'status' => 'pending',
        ]);
        
        $this->actingAs($this->adminUser);
        $response = $this->postJson("/api/sppd/{$this->spd->id}/approve");
        $response->assertStatus(200);
    }
}
