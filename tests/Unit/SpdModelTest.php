<?php

namespace Tests\Unit;

use App\Models\Spd;
use App\Models\Employee;
use App\Models\User;
use App\Models\Budget;
use App\Models\Approval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpdModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_spd_belongs_to_employee()
    {
        $employee = Employee::factory()->create();
        $spd = Spd::factory()->create(['employee_id' => $employee->id]);

        $this->assertInstanceOf(Employee::class, $spd->employee);
        $this->assertEquals($employee->id, $spd->employee->id);
    }

    public function test_spd_belongs_to_budget()
    {
        $budget = Budget::factory()->create();
        $spd = Spd::factory()->create(['budget_id' => $budget->id]);

        $this->assertInstanceOf(Budget::class, $spd->budget);
        $this->assertEquals($budget->id, $spd->budget->id);
    }

    public function test_status_color_attribute()
    {
        $spd = Spd::factory()->create(['status' => 'approved']);
        $this->assertEquals('green', $spd->status_color);

        $spd->status = 'rejected';
        $this->assertEquals('red', $spd->status_color);

        $spd->status = 'submitted';
        $this->assertEquals('yellow', $spd->status_color);
    }

    public function test_status_label_attribute()
    {
        $spd = Spd::factory()->create(['status' => 'approved']);
        $this->assertEquals('Disetujui', $spd->status_label);

        $spd->status = 'rejected';
        $this->assertEquals('Ditolak', $spd->status_label);
    }

    public function test_can_edit_only_draft()
    {
        $spd = Spd::factory()->create(['status' => 'draft']);
        $this->assertTrue($spd->canEdit());

        $spd->status = 'submitted';
        $this->assertFalse($spd->canEdit());
    }

    public function test_format_cost()
    {
        $spd = Spd::factory()->create(['estimated_cost' => 1500000]);
        $this->assertEquals('Rp 1.500.000', $spd->formatCost());
    }

    public function test_this_month_scope()
    {
        Spd::factory()->count(3)->create();
        Spd::factory()->create(['created_at' => now()->subMonth()]);

        $thisMonthCount = Spd::thisMonth()->count();
        $this->assertEquals(3, $thisMonthCount);
    }
}
