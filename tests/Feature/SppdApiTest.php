<?php

namespace Tests\Feature;

use App\Models\Spd;
use App\Models\User;
use App\Models\Employee;
use App\Models\Budget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SppdApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Employee $employee;
    protected Budget $budget;

    protected function setUp(): void
    {
        parent::setUp();

        // Create organization and unit first
        $organization = \App\Models\Organization::factory()->create();
        $unit = \App\Models\Unit::factory()->create(['organization_id' => $organization->id]);

        $this->user = User::factory()->create();

        // Create employee and link to user
        $this->employee = Employee::factory()->create([
            'organization_id' => $organization->id,
            'unit_id' => $unit->id,
            'user_id' => $this->user->id,
        ]);

        // Link user to employee
        $this->user->update(['employee_id' => $this->employee->id]);

        $this->budget = Budget::factory()->create(['organization_id' => $organization->id]);
    }

    public function test_can_list_sppd()
    {
        Sanctum::actingAs($this->user);

        // Create SPPD with proper relationships
        Spd::factory()->count(5)->create();

        $response = $this->getJson('/api/sppd');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => ['current_page', 'total'],
            ]);
    }

    public function test_can_create_sppd()
    {
        Sanctum::actingAs($this->user);

        // Ensure we have organization and unit
        $organization = \App\Models\Organization::factory()->create();
        $unit = \App\Models\Unit::factory()->create(['organization_id' => $organization->id]);

        // Update employee to be in the same organization
        $this->employee->update([
            'organization_id' => $organization->id,
            'unit_id' => $unit->id,
        ]);

        // Update budget to same organization
        $this->budget->update(['organization_id' => $organization->id]);

        $response = $this->postJson('/api/sppd', [

            'employee_id' => $this->employee->id,
            'destination' => 'Jakarta',
            'purpose' => 'Rapat koordinasi',
            'departure_date' => now()->addDays(10)->format('Y-m-d'),
            'return_date' => now()->addDays(5)->format('Y-m-d'),
            'transport_type' => 'pesawat',
            'budget_id' => $this->budget->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_can_show_sppd()
    {
        Sanctum::actingAs($this->user);
        $spd = Spd::factory()->create();

        $response = $this->getJson("/api/sppd/{$spd->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_can_update_draft_sppd()
    {
        Sanctum::actingAs($this->user);
        $spd = Spd::factory()->create([
            'status' => 'draft',
            'employee_id' => $this->employee->id,
        ]);

        $response = $this->putJson("/api/sppd/{$spd->id}", [
            'destination' => 'Surabaya Updated',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Surabaya Updated', $spd->fresh()->destination);
    }

    public function test_cannot_update_submitted_sppd()
    {
        Sanctum::actingAs($this->user);
        $spd = Spd::factory()->create([
            'status' => 'submitted',
            'employee_id' => $this->employee->id,
        ]);

        $response = $this->putJson("/api/sppd/{$spd->id}", [
            'destination' => 'Surabaya Updated',
        ]);

        $response->assertStatus(403);
    }

    public function test_can_delete_draft_sppd()
    {
        Sanctum::actingAs($this->user);
        $spd = Spd::factory()->create([
            'status' => 'draft',
            'employee_id' => $this->employee->id,
        ]);

        $response = $this->deleteJson("/api/sppd/{$spd->id}");

        $response->assertStatus(200);
        $this->assertNull(Spd::find($spd->id));
    }

    public function test_can_submit_sppd()
    {
        Sanctum::actingAs($this->user);
        $spd = Spd::factory()->create([
            'status' => 'draft',
            'employee_id' => $this->employee->id,
        ]);

        $response = $this->postJson("/api/sppd/{$spd->id}/submit");

        $response->assertStatus(200);
        $this->assertEquals('submitted', $spd->fresh()->status);
    }

    public function test_unauthenticated_access_denied()
    {
        $response = $this->getJson('/api/sppd');

        $response->assertStatus(401);
    }
}
