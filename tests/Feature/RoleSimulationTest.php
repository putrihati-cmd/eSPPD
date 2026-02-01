<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RoleSimulationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed all data including production accounts
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Data Provider for all roles
     */
    public static function roleUserProvider(): array
    {
        return [
            'Super Admin'   => ['888888888888880099', 'superadmin', 99],
            'Administrator' => ['888888888888880098', 'admin', 98],
            'Rektor'        => ['888888888888880006', 'rektor', 6],
            'Wakil Rektor'  => ['888888888888880005', 'warek', 5],
            'Dekan'         => ['888888888888880004', 'dekan', 4],
            'Wakil Dekan'   => ['888888888888880003', 'wadek', 3],
            'Kepala Prodi'  => ['888888888888880002', 'kaprodi', 2],
            'Dosen'         => ['888888888888880001', 'dosen', 1],
            'Pegawai'       => ['888888888888880011', 'pegawai', 1],
        ];
    }

    /**
     * @test
     * @dataProvider roleUserProvider
     */
    public function simulation_verify_role_access($nip, $roleName, $expectedLevel)
    {
        // Use correct flow: NIP → Employee → User
        $employee = \App\Models\Employee::where('nip', $nip)->first();
        $this->assertNotNull($employee, "Employee with NIP {$nip} not found in database.");

        $user = $employee->user;
        $this->assertNotNull($user, "User record for Employee {$nip} not found in database.");
        $this->assertEquals($roleName, $user->role, "Role mapping mismatch for NIP {$nip}.");
        $this->assertEquals($expectedLevel, $user->role_level, "Level mismatch for NIP {$nip}.");

        // 2. Simulation: Login Process (NIP -> Employee -> User email)
        $component = Volt::test('pages.auth.login')
            ->set('nip', $nip) // Testing the NIP input (finds Employee by NIP, then gets User.email)
            ->set('password', 'password');

        $component->call('login');

        $component->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);

        // 3. Permission & Route Simulation
        $this->actingAs($user);

        // SPD Index (Everyone)
        $this->get('/spd')->assertStatus(200);

        // Approval Queue (Level 2+)
        if ($user->role_level >= 2) {
            $this->get('/approvals/queue')->assertStatus(200);
        } else {
            // CheckRoleLevel middleware redirects to dashboard for level mismatch
            $this->get('/approvals/queue')->assertRedirect(route('dashboard'));
        }

        // User Management (Admin only: Level 98+)
        if ($user->isAdmin()) {
            $this->get('/admin/users')->assertStatus(200);
        } else {
            // Non-admin should be restricted with 403 (handled in controller via abort)
            $this->get('/admin/users')->assertStatus(403);
        }

        // Settings (All auth users have access in routes/web.php, but check visibility)
        $this->get('/settings')->assertStatus(200);

        echo "\n[SIMULASI] ✓ Login & Otorisasi berhasil: [{$user->roleModel->label}] NIP: {$nip} (Level: {$expectedLevel})";
    }
}
