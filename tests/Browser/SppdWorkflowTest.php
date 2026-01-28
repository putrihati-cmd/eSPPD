<?php

namespace Tests\Browser;

use App\Models\Spd;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SppdWorkflowTest extends DuskTestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test SPPD creation form
     */
    public function test_can_create_sppd(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/spd/create')
                ->assertSee('Buat SPD Baru')
                ->type('destination', 'Jakarta')
                ->type('purpose', 'Rapat Koordinasi')
                ->type('departure_date', now()->addDays(7)->format('Y-m-d'))
                ->type('return_date', now()->addDays(10)->format('Y-m-d'))
                ->select('transport_type', 'pesawat')
                ->press('Simpan')
                ->waitForText('SPD berhasil dibuat')
                ->assertPathBeginsWith('/spd/');
        });
    }

    /**
     * Test SPPD submission
     */
    public function test_can_submit_sppd(): void
    {
        $spd = Spd::factory()->create([
            'employee_id' => $this->user->employee_id,
            'status' => 'draft',
        ]);

        $this->browse(function (Browser $browser) use ($spd) {
            $browser->loginAs($this->user)
                ->visit("/spd/{$spd->id}")
                ->assertSee($spd->spd_number)
                ->press('Ajukan Approval')
                ->waitForText('diajukan')
                ->assertSee('Menunggu Approval');
        });
    }

    /**
     * Test SPPD approval by approver
     */
    public function test_approver_can_approve(): void
    {
        $approver = User::factory()->create(['role' => 'atasan']);
        $spd = Spd::factory()->submitted()->create();

        $this->browse(function (Browser $browser) use ($approver, $spd) {
            $browser->loginAs($approver)
                ->visit('/approvals')
                ->assertSee($spd->spd_number)
                ->press('Approve')
                ->waitForText('berhasil disetujui');
        });
    }

    /**
     * Test SPPD rejection with reason
     */
    public function test_approver_can_reject_with_reason(): void
    {
        $approver = User::factory()->create(['role' => 'atasan']);
        $spd = Spd::factory()->submitted()->create();

        $this->browse(function (Browser $browser) use ($approver, $spd) {
            $browser->loginAs($approver)
                ->visit('/approvals')
                ->assertSee($spd->spd_number)
                ->click('@reject-button')
                ->type('@rejection-reason', 'Budget tidak mencukupi untuk perjalanan ini.')
                ->press('Konfirmasi Reject')
                ->waitForText('berhasil ditolak');
        });
    }

    /**
     * Test trip report creation
     */
    public function test_can_create_trip_report(): void
    {
        $spd = Spd::factory()->approved()->create([
            'employee_id' => $this->user->employee_id,
        ]);

        $this->browse(function (Browser $browser) use ($spd) {
            $browser->loginAs($this->user)
                ->visit("/spd/{$spd->id}")
                ->press('Buat Laporan')
                ->assertPathContains('/trip-report/create')
                ->type('actual_departure_date', $spd->departure_date->format('Y-m-d'))
                ->type('actual_return_date', $spd->return_date->format('Y-m-d'))
                ->press('Tambah Aktivitas')
                ->type('@activity-description', 'Menghadiri rapat koordinasi')
                ->press('Simpan Laporan')
                ->waitForText('Laporan berhasil disimpan');
        });
    }

    /**
     * Test dashboard displays correct stats
     */
    public function test_dashboard_shows_statistics(): void
    {
        Spd::factory()->count(3)->create([
            'employee_id' => $this->user->employee_id,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/dashboard')
                ->assertSee('Total SPD Bulan Ini')
                ->assertSee('Menunggu Approval')
                ->assertSee('Disetujui')
                ->assertPresent('#monthlyTrendChart')
                ->assertPresent('#statusChart');
        });
    }

    /**
     * Test validation errors display correctly
     */
    public function test_form_validation_errors(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/spd/create')
                ->press('Simpan')
                ->waitForText('wajib diisi')
                ->assertSee('Tujuan wajib diisi');
        });
    }

    /**
     * Test Excel export download
     */
    public function test_can_export_excel(): void
    {
        Spd::factory()->count(5)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/excel')
                ->select('exportStatus', 'approved')
                ->press('Export ke Excel');
                // Note: Actual file download testing requires additional setup
        });
    }
}
