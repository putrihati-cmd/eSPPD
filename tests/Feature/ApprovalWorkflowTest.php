<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Approval;
use App\Models\Spd;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\Budget;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Approval Workflow Tests
 */
class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $approver;
    private User $employee;
    private Spd $spd;
    private Organization $organization;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        // Create organization and unit
        $this->organization = Organization::factory()->create();
        $this->unit = Unit::factory()->create(['organization_id' => $this->organization->id]);

        // Create employee user with proper relationships
        $this->employee = User::factory()->create(['role' => 'employee']);
        $employeeModel = Employee::factory()->create([
            'organization_id' => $this->organization->id,
            'unit_id' => $this->unit->id,
            'user_id' => $this->employee->id,
        ]);
        $this->employee->update(['employee_id' => $employeeModel->id]);

        // Create approver user with proper relationships
        $this->approver = User::factory()->create(['role' => 'approver']);
        $approverEmployee = Employee::factory()->create([
            'organization_id' => $this->organization->id,
            'unit_id' => $this->unit->id,
            'user_id' => $this->approver->id,
        ]);
        $this->approver->update(['employee_id' => $approverEmployee->id]);

        // Create SPPD with proper relationships
        $budget = Budget::factory()->create(['organization_id' => $this->organization->id]);
        $this->spd = Spd::factory()->create([
            'status' => 'submitted',
            'employee_id' => $employeeModel->id,
            'organization_id' => $this->organization->id,
            'unit_id' => $this->unit->id,
            'budget_id' => $budget->id,
        ]);
    }

    /**
     * Test approval creation
     */
    public function test_approval_can_be_created(): void
    {
        $response = $this->actingAs($this->approver)
            ->post("/api/spd/{$this->spd->id}/approvals", [
                'status' => 'approved',
                'notes' => 'Disetujui dengan catatan'
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('approvals', [
            'spd_id' => $this->spd->id,
            'status' => 'approved'
        ]);
    }

    /**
     * Test rejection workflow
     */
    public function test_approval_can_be_rejected(): void
    {
        $response = $this->actingAs($this->approver)
            ->post("/api/spd/{$this->spd->id}/approvals", [
                'status' => 'rejected',
                'notes' => 'Ditolak karena data tidak lengkap'
            ]);

        $response->assertStatus(201);
        $this->assertEquals('rejected', $this->spd->fresh()->status);
    }

    /**
     * Test multi-level approval
     */
    public function test_multi_level_approval_sequence(): void
    {
        // Create approvers WITH employee records
        $approver1 = User::factory()->create(['role' => 'approver']);
        $approver1Employee = Employee::factory()->create([
            'organization_id' => $this->organization->id,
            'unit_id' => $this->unit->id,
            'user_id' => $approver1->id,
        ]);
        $approver1->update(['employee_id' => $approver1Employee->id]);

        $approver2 = User::factory()->create(['role' => 'approver']);
        $approver2Employee = Employee::factory()->create([
            'organization_id' => $this->organization->id,
            'unit_id' => $this->unit->id,
            'user_id' => $approver2->id,
        ]);
        $approver2->update(['employee_id' => $approver2Employee->id]);

        // First approval
        $this->actingAs($approver1)
            ->post("/api/spd/{$this->spd->id}/approvals", [
                'status' => 'approved',
                'level' => 1
            ]);

        // Second approval
        $response = $this->actingAs($approver2)
            ->post("/api/spd/{$this->spd->id}/approvals", [
                'status' => 'approved',
                'level' => 2
            ]);

        $response->assertStatus(201);
        $this->assertCount(2, $this->spd->approvals);
    }

    /**
     * Test approver cannot approve own SPPD
     */
    public function test_employee_cannot_approve_own_sppd(): void
    {
        $response = $this->actingAs($this->employee)
            ->post("/api/spd/{$this->spd->id}/approvals", [
                'status' => 'approved'
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test approval history
     */
    public function test_approval_history_is_recorded(): void
    {
        $this->actingAs($this->approver)
            ->post("/api/spd/{$this->spd->id}/approvals", [
                'status' => 'approved',
                'notes' => 'Perjalanan disetujui'
            ]);

        $response = $this->actingAs($this->employee)
            ->get("/api/spd/{$this->spd->id}/approvals");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }
}
