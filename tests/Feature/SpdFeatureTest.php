<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Spd;
use App\Models\Employee;
use App\Models\Approval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;

/**
 * SPPD Feature Tests
 */
class SpdFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee;
    private Employee $employeeModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->employee = User::factory()->create(['role' => 'employee']);
        $this->employeeModel = Employee::factory()->create(['user_id' => $this->employee->id]);

        // Link employee to user
        $this->employee->update(['employee_id' => $this->employeeModel->id]);
    }

    /**
     * Test SPPD creation by employee
     */
    public function test_employee_can_create_sppd(): void
    {
        $budget = \App\Models\Budget::factory()->create([
            'organization_id' => $this->employeeModel->organization_id
        ]);

        $response = $this->actingAs($this->employee)
            ->post('/api/spd', [
                'employee_id' => $this->employeeModel->id,
                'destination' => 'Jakarta',
                'purpose' => 'Rapat Dinas',
                'departure_date' => now()->addDays(1)->toDateString(),
                'return_date' => now()->addDays(3)->toDateString(),
                'transport_type' => 'pesawat',
                'budget_id' => $budget->id,
            ]);

        $response->assertStatus(201);
    }

    /**
     * Test SPPD validation
     */
    public function test_sppd_requires_valid_data(): void
    {
        $response = $this->actingAs($this->employee)
            ->post('/api/spd', [
                'destination' => 'Jakarta',
                'purpose' => 'Rapat',
                'departure_date' => now()->subDays(1)->toDateString(),
                'return_date' => now()->addDays(3)->toDateString(),
                'transport_type' => 'pesawat',
                'budget_id' => 'invalid-uuid',
                'employee_id' => 'invalid-uuid',
            ], ['Accept' => 'application/json']);

        $response->assertStatus(422);
    }

    /**
     * Test SPPD approval workflow
     */
    public function test_approval_workflow(): void
    {
        $spd = Spd::factory()->create([
            'status' => 'draft',
            'employee_id' => $this->employeeModel->id
        ]);

        $approver = User::factory()->create(['role' => 'approver']);

        // Submit for approval using submit endpoint
        $response = $this->actingAs($this->employee)
            ->post("/api/spd/{$spd->id}/submit");

        $response->assertStatus(200);
        $this->assertEquals('submitted', $spd->fresh()->status);
    }

    /**
     * Test unauthorized access
     */
    public function test_unauthorized_user_cannot_approve(): void
    {
        $approverUser = User::factory()->create(['role' => 'approver']);
        $approverEmployee = Employee::factory()->create(['user_id' => $approverUser->id]);
        $approverUser->update(['employee_id' => $approverEmployee->id]);

        $spd = Spd::factory()->create(['status' => 'submitted']);

        $response = $this->actingAs($this->employee)
            ->post("/api/spd/{$spd->id}/approvals", [
                'status' => 'approved'
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test SPPD retrieval
     */
    public function test_user_can_view_own_sppd(): void
    {
        $spd = Spd::factory()->create(['employee_id' => $this->employeeModel->id]);

        $response = $this->actingAs($this->employee)
            ->get("/api/spd/{$spd->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $spd->id);
    }

    /**
     * Test list SPPDs with pagination
     */
    public function test_list_sppds_with_pagination(): void
    {
        Spd::factory(15)->create();

        $response = $this->actingAs($this->admin)
            ->get('/api/spd?page=1&limit=10');

        $response->assertStatus(200);
        $response->assertJsonPath('meta.total', 15);
        $this->assertCount(10, $response->json('data'));
    }

    /**
     * Test SPPD deletion
     */
    public function test_draft_sppd_can_be_deleted(): void
    {
        $spd = Spd::factory()->create([
            'status' => 'draft',
            'employee_id' => $this->employeeModel->id
        ]);

        $response = $this->actingAs($this->employee)
            ->delete("/api/spd/{$spd->id}");

        $response->assertStatus(200);
        // Check that the record is soft-deleted
        $this->assertNotNull(Spd::withTrashed()->find($spd->id)->deleted_at);
    }

    /**
     * Test submitted SPPD cannot be deleted
     */
    public function test_submitted_sppd_cannot_be_deleted(): void
    {
        $spd = Spd::factory()->create([
            'status' => 'submitted',
            'employee_id' => $this->employeeModel->id
        ]);

        $response = $this->actingAs($this->employee)
            ->delete("/api/spd/{$spd->id}");

        $response->assertStatus(403);
    }

    /**
     * Test SPPD export to PDF
     */
    public function test_spd_can_be_exported_to_pdf(): void
    {
        Queue::fake();

        $spd = Spd::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post("/api/spd/{$spd->id}/export-pdf");

        $response->assertStatus(200);
        Queue::assertPushed(\App\Jobs\GenerateSpdPdfJob::class);
    }

    /**
     * Test SPPD search functionality
     */
    public function test_search_sppd_by_number(): void
    {
        Spd::factory()->create(['spd_number' => 'SPD/2025/01/001']);
        Spd::factory()->create(['spd_number' => 'SPD/2025/01/002']);

        $response = $this->actingAs($this->admin)
            ->get('/api/spd?search=SPD/2025/01/001');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    /**
     * Test SPPD filtering by status
     */
    public function test_filter_sppd_by_status(): void
    {
        Spd::factory(5)->create(['status' => 'draft']);
        Spd::factory(3)->create(['status' => 'submitted']);

        $response = $this->actingAs($this->admin)
            ->get('/api/spd?status=draft');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }
}
