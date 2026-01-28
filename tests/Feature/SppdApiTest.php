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
        
        $this->user = User::factory()->create();
        $this->employee = Employee::factory()->create();
        $this->budget = Budget::factory()->create();
    }

    public function test_can_list_sppd()
    {
        Sanctum::actingAs($this->user);
        
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

        $response = $this->postJson('/api/sppd', [
            'employee_id' => $this->employee->id,
            'destination' => 'Jakarta',
            'purpose' => 'Rapat koordinasi',
            'departure_date' => now()->addDays(7)->format('Y-m-d'),
            'return_date' => now()->addDays(9)->format('Y-m-d'),
            'transport_type' => 'pesawat',
            'budget_id' => $this->budget->id,
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);
    }

    public function test_validation_departure_before_return()
    {
        Sanctum::actingAs($this->user);

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
        $spd = Spd::factory()->create(['status' => 'draft']);

        $response = $this->putJson("/api/sppd/{$spd->id}", [
            'destination' => 'Surabaya Updated',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Surabaya Updated', $spd->fresh()->destination);
    }

    public function test_cannot_update_submitted_sppd()
    {
        Sanctum::actingAs($this->user);
        $spd = Spd::factory()->create(['status' => 'submitted']);

        $response = $this->putJson("/api/sppd/{$spd->id}", [
            'destination' => 'Surabaya Updated',
        ]);

        $response->assertStatus(403);
    }

    public function test_can_delete_draft_sppd()
    {
        Sanctum::actingAs($this->user);
        $spd = Spd::factory()->create(['status' => 'draft']);

        $response = $this->deleteJson("/api/sppd/{$spd->id}");

        $response->assertStatus(200);
        $this->assertNull(Spd::find($spd->id));
    }

    public function test_can_submit_sppd()
    {
        Sanctum::actingAs($this->user);
        $spd = Spd::factory()->create(['status' => 'draft']);

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
