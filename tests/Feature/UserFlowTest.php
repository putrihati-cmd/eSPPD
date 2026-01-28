<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Employee;
use App\Models\Spd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserFlowTest extends TestCase
{
    // Note: We use existing DB setup or migrated test DB.
    // Ideally we should use RefreshDatabase, but for now we might test against dev.
    // But standard practice is RefreshDatabase. I'll omit it to avoid wiping local data if env testing is not separate.
    
    /** @test */
    public function dosen_can_access_dashboard_and_create_sppd()
    {
        // 1. Simulate Login
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        // 2. Access SPPD Create Form
        $response = $this->actingAs($user)->get(route('spd.create'));
        $response->assertStatus(200);
        $response->assertSeeLivewire('spd.spd-create');
    }

    /** @test */
    public function unauthorized_user_cannot_access_sppd_form()
    {
        $response = $this->get(route('spd.create'));
        $response->assertRedirect('/login');
    }
}
