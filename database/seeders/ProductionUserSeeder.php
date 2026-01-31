<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles by name
        $adminRole = Role::where('name', 'admin')->first();
        $employeeRole = Role::where('name', 'employee')->first();
        $kaprodiRole = Role::where('name', 'kabag')->first();
        $wadekRole = Role::where('name', 'wadek')->first();
        $dekanRole = Role::where('name', 'dekan')->first();

        // Fallback role IDs if not found
        $adminRoleId = $adminRole?->id ?? 1;
        $employeeRoleId = $employeeRole?->id ?? 7;
        $kaprodiRoleId = $kaprodiRole?->id ?? 2;
        $wadekRoleId = $wadekRole?->id ?? 3;
        $dekanRoleId = $dekanRole?->id ?? 4;

        // Production accounts
        $accounts = [
            [
                'name' => 'Admin e-SPPD',
                'email' => 'admin@infiatin.cloud',
                'password' => 'Admin@eSPPD2026',
                'role_id' => $adminRoleId,
                'role' => 'admin',
            ],
            [
                'name' => 'Iwan Setiawan',
                'email' => 'iwan.setiawan@esppd.id',
                'password' => 'Testing@123',
                'role_id' => $employeeRoleId,
                'role' => 'employee',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@esppd.id',
                'password' => 'Testing@123',
                'role_id' => $employeeRoleId,
                'role' => 'employee',
            ],
            [
                'name' => 'Bambang Sutrisno',
                'email' => 'bambang.sutrisno@esppd.id',
                'password' => 'Testing@123',
                'role_id' => $kaprodiRoleId,
                'role' => 'kabag',
            ],
            [
                'name' => 'Maftuh Asnawi',
                'email' => 'maftuh.asnawi@esppd.id',
                'password' => 'Testing@123',
                'role_id' => $wadekRoleId,
                'role' => 'wadek',
            ],
            [
                'name' => 'Suwito (Dekan)',
                'email' => 'suwito.dekan@esppd.id',
                'password' => 'Testing@123',
                'role_id' => $dekanRoleId,
                'role' => 'dekan',
            ],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make($account['password']),
                    'role_id' => $account['role_id'],
                    'role' => $account['role'],
                ]
            );
        }

        echo "\n✅ Production users created:\n";
        echo "  Admin:   admin@infiatin.cloud / Admin@eSPPD2026\n";
        echo "  Dosen:   iwan.setiawan@esppd.id / Testing@123\n";
        echo "  Pegawai: siti.nurhaliza@esppd.id / Testing@123\n";
        echo "  Kaprodi: bambang.sutrisno@esppd.id / Testing@123\n";
        echo "  Wadek:   maftuh.asnawi@esppd.id / Testing@123\n";
        echo "  Dekan:   suwito.dekan@esppd.id / Testing@123\n\n";
    }
}
