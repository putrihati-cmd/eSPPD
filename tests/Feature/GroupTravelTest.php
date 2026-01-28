<?php

namespace Tests\Feature;

use App\Models\Spd;
use App\Models\User;
use App\Models\Employee;
use App\Models\Budget;
use App\Models\Organization;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupTravelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Employee $employee;
    protected Employee $follower;
    protected Budget $budget;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Organization & Unit
        $org = Organization::factory()->create();
        $unit = Unit::factory()->create(['organization_id' => $org->id]);

        $this->user = User::factory()->create();
        $this->employee = Employee::factory()->create([
            'organization_id' => $org->id, 
            'unit_id' => $unit->id,
            'user_id' => $this->user->id
        ]);
        
        $this->follower = Employee::factory()->create([
            'organization_id' => $org->id, 
            'unit_id' => $unit->id
        ]);
        
        $this->budget = Budget::factory()->create([
            'organization_id' => $org->id,
            'available_budget' => 10000000
        ]);
    }

    public function test_can_create_spd_with_followers()
    {
        $this->actingAs($this->user);

        // Simulate Livewire component state or direct model creation since Livewire testing requires Livewire::test()
        // Here we test the Model logic closely or use Livewire test helper if possible.
        // For simplicity in Feature test without Livewire dependency here, we test the Controller logic if it exists, 
        // OR we use Livewire::test if we want to test the component interaction.
        // Let's test the Model/Database relationship first to ensure "persistence" is verified by test.

        $spd = Spd::create([
            'organization_id' => $this->employee->organization_id,
            'unit_id' => $this->employee->unit_id,
            'employee_id' => $this->employee->id,
            'spt_number' => 'SPT/TEST/001',
            'spd_number' => 'SPD/TEST/001',
            'destination' => 'Test City',
            'purpose' => 'Group Travel Test',
            'departure_date' => now()->addDays(1),
            'return_date' => now()->addDays(3),
            'duration' => 3,
            'budget_id' => $this->budget->id,
            'estimated_cost' => 5000000,
            'transport_type' => 'pesawat',
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $spd->followers()->create([
            'employee_id' => $this->follower->id
        ]);

        $this->assertDatabaseHas('spds', ['id' => $spd->id]);
        $this->assertDatabaseHas('spd_followers', [
            'spd_id' => $spd->id, 
            'employee_id' => $this->follower->id
        ]);
        
        $this->assertEquals(1, $spd->followers()->count());
        $this->assertEquals($this->follower->id, $spd->followers->first()->employee_id);
    }
}
