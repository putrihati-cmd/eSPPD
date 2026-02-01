<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use App\Models\Budget;
use App\Models\DailyAllowance;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\SbmSetting;
use App\Models\Spd;
use App\Models\Cost;
use App\Models\Transportation;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed new RBAC roles first
        $this->call([
            RoleSeeder::class,
        ]);

        // Create Organization
        $organization = Organization::create([
            'name' => 'UIN Saizu Purwokerto',
            'code' => 'Un.19',
            'address' => 'Jl. A. Yani No. 40A, Purwokerto 53126',
            'phone' => '(0281) 635624',
            'email' => 'info@uinsaizu.ac.id',
            'website' => 'https://uinsaizu.ac.id',
        ]);

        // Create Units
        $fakultasPsikologi = Unit::create([
            'organization_id' => $organization->id,
            'name' => 'Fakultas Psikologi',
            'code' => 'FP',
        ]);

        $fakultasTarbiyah = Unit::create([
            'organization_id' => $organization->id,
            'name' => 'Fakultas Tarbiyah',
            'code' => 'FT',
        ]);

        $fakultasSyariah = Unit::create([
            'organization_id' => $organization->id,
            'name' => 'Fakultas Syariah',
            'code' => 'FS',
        ]);

        // Create Employees
        $employees = [
            [
                'nip' => '195001011990031099',
                'name' => 'Super Admin System',
                'email' => 'superadmin@uinsaizu.ac.id',
                'birth_date' => '1950-01-01',
                'position' => 'Super Administrator',
                'rank' => 'Pembina Utama',
                'grade' => 'IV/e',
                'employment_status' => 'PNS',
                'unit_id' => $fakultasPsikologi->id,
                'role_name' => 'superadmin',
                'approval_level' => 6, // LOGIC MAP: Superadmin has rektor-level (6)
            ],
            [
                'nip' => '198302082015031501',
                'name' => 'Mawi Khusni Albar',
                'email' => 'mawikhusni@uinsaizu.ac.id',
                'birth_date' => '1983-02-08',
                'position' => 'Lektor Kepala',
                'rank' => 'Pembina',
                'grade' => 'IV/a',
                'employment_status' => 'PNS',
                'unit_id' => $fakultasPsikologi->id,
                'role_name' => 'admin',
                'approval_level' => 6, // LOGIC MAP: Admin has rektor-level (6)
            ],
            [
                'nip' => '195301011988031006',
                'name' => 'Dr. Rektor UIN',
                'email' => 'rektor@uinsaizu.ac.id',
                'birth_date' => '1953-01-01',
                'position' => 'Rektor',
                'rank' => 'Pembina Utama Madya',
                'grade' => 'IV/d',
                'employment_status' => 'PNS',
                'unit_id' => $fakultasPsikologi->id,
                'role_name' => 'rektor',
                'approval_level' => 6, // LOGIC MAP: Rektor = level 6
            ],
            [
                'nip' => '195402151992031005',
                'name' => 'Dr. Wakil Rektor',
                'email' => 'warek@uinsaizu.ac.id',
                'birth_date' => '1954-02-15',
                'position' => 'Wakil Rektor',
                'rank' => 'Pembina Utama Muda',
                'grade' => 'IV/c',
                'employment_status' => 'PNS',
                'unit_id' => $fakultasPsikologi->id,
                'role_name' => 'warek',
                'approval_level' => 5, // LOGIC MAP: Warek = level 5
            ],
            [
                'nip' => '197505152006041001',
                'name' => 'Ansori',
                'email' => 'ansori@uinsaizu.ac.id',
                'birth_date' => '1975-05-15',
                'position' => 'Dekan',
                'rank' => 'Pembina Utama Muda',
                'grade' => 'IV/c',
                'employment_status' => 'PNS',
                'unit_id' => $fakultasPsikologi->id,
                'role_name' => 'dekan',
                'approval_level' => 4, // LOGIC MAP: Dekan = level 4
            ],
            [
                'nip' => '197608201998031003',
                'name' => 'Dr. Wadek Fakultas',
                'email' => 'wadek@uinsaizu.ac.id',
                'birth_date' => '1976-08-20',
                'position' => 'Wakil Dekan',
                'rank' => 'Penata Tk.I',
                'grade' => 'III/d',
                'employment_status' => 'PNS',
                'unit_id' => $fakultasTarbiyah->id,
                'role_name' => 'wadek',
                'approval_level' => 3, // LOGIC MAP: Wadek = level 3
            ],
            [
                'nip' => '197903101999031002',
                'name' => 'Dr. Kepala Bagian',
                'email' => 'kaprodi@uinsaizu.ac.id',
                'birth_date' => '1979-03-10',
                'position' => 'Kepala Bagian / Kaprodi',
                'rank' => 'Penata',
                'grade' => 'III/c',
                'employment_status' => 'PNS',
                'unit_id' => $fakultasSyariah->id,
                'role_name' => 'kabag',
                'approval_level' => 2, // LOGIC MAP: Kaprodi = level 2
            ],
            [
                'nip' => '198811202019031001',
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmadfauzi@uinsaizu.ac.id',
                'birth_date' => '1988-11-20',
                'position' => 'Lektor',
                'rank' => 'Penata Tk.I',
                'grade' => 'III/d',
                'employment_status' => 'PNS',
                'unit_id' => $fakultasTarbiyah->id,
                'role_name' => 'dosen',
                'approval_level' => 1, // LOGIC MAP: Dosen = level 1
            ],
            [
                'nip' => '199003152020122001',
                'name' => 'Siti Nurhaliza',
                'email' => 'sitinurhaliza@uinsaizu.ac.id',
                'birth_date' => '1990-03-15',
                'position' => 'Asisten Ahli',
                'rank' => 'Penata Muda Tk.I',
                'grade' => 'III/b',
                'employment_status' => 'PNS',
                'unit_id' => $fakultasSyariah->id,
                'role_name' => 'dosen',
                'approval_level' => 1, // LOGIC MAP: Dosen = level 1
            ],
            [
                'nip' => '199505012022011001',
                'name' => 'Budi Santoso',
                'email' => 'budisantoso@uinsaizu.ac.id',
                'birth_date' => '1995-05-01',
                'position' => 'Tenaga Kependidikan',
                'rank' => 'Pengatur',
                'grade' => 'II/c',
                'employment_status' => 'PPPK',
                'unit_id' => $fakultasPsikologi->id,
                'role_name' => 'dosen',
                'approval_level' => 1, // LOGIC MAP: Staff = level 1
            ],
        ];

        foreach ($employees as $empData) {
            $birthDate = $empData['birth_date'];
            $roleName = $empData['role_name'] ?? 'dosen';
            unset($empData['role_name']);

            $employee = Employee::create([
                ...$empData,
                'organization_id' => $organization->id,
            ]);

            // Generate password: DDMMYYYY from birth_date
            $passwordDefault = \Carbon\Carbon::createFromFormat('Y-m-d', $birthDate)->format('dmY');

            // Get role model
            $roleModel = \App\Models\Role::where('name', $roleName)->first();

            User::create([
                'name' => $empData['name'],
                'email' => $empData['email'],
                'nip' => $empData['nip'],
                'password' => Hash::make($passwordDefault),
                'organization_id' => $organization->id,
                'employee_id' => $employee->id,
                'role' => $roleName,
                'role_id' => $roleModel?->id,
                'is_password_reset' => false,
            ]);
        }

        // Create Budgets (MAK)
        $budgets = [
            ['code' => '524111', 'name' => 'Belanja Perjalanan Dinas Biasa', 'total_budget' => 150000000],
            ['code' => '524112', 'name' => 'Belanja Perjalanan Dinas Tetap', 'total_budget' => 75000000],
            ['code' => '524113', 'name' => 'Belanja Perjalanan Dinas Dalam Kota', 'total_budget' => 50000000],
            ['code' => '524114', 'name' => 'Belanja Perjalanan Dinas Paket Meeting', 'total_budget' => 100000000],
            ['code' => '524119', 'name' => 'Belanja Perjalanan Dinas Lainnya', 'total_budget' => 75000000],
        ];

        foreach ($budgets as $budgetData) {
            Budget::create([
                ...$budgetData,
                'organization_id' => $organization->id,
                'year' => 2026,
                'used_budget' => rand(10000000, 50000000),
            ]);
        }

        // Create SBM Settings
        $sbmSetting = SbmSetting::create([
            'organization_id' => $organization->id,
            'year' => 2026,
            'pmk_number' => 'PMK 32/2025',
            'effective_date' => '2026-01-01',
            'is_active' => true,
        ]);

        // Create Daily Allowances by Province
        $provinces = [
            'Jawa Tengah' => 380000,
            'Jawa Barat' => 430000,
            'Jawa Timur' => 410000,
            'DKI Jakarta' => 530000,
            'DIY Yogyakarta' => 420000,
            'Bali' => 480000,
            'Sumatera Utara' => 430000,
            'Sulawesi Selatan' => 430000,
            'Kalimantan Timur' => 480000,
            'Papua' => 580000,
        ];

        foreach ($provinces as $province => $amount) {
            DailyAllowance::create([
                'sbm_setting_id' => $sbmSetting->id,
                'province' => $province,
                'category' => 'luar_kota',
                'amount' => $amount,
                'representation_amount' => $amount * 0.4,
            ]);
        }

        // Create Accommodation rates
        $gradeAccommodations = [
            'golongan_IV' => 900000,
            'golongan_III' => 700000,
            'golongan_II' => 500000,
            'golongan_I' => 400000,
        ];

        foreach ($provinces as $province => $dailyAmount) {
            foreach ($gradeAccommodations as $grade => $baseAmount) {
                $multiplier = $province === 'DKI Jakarta' ? 1.4 : ($province === 'Bali' ? 1.3 : 1.0);
                Accommodation::create([
                    'sbm_setting_id' => $sbmSetting->id,
                    'province' => $province,
                    'grade_level' => $grade,
                    'max_amount' => $baseAmount * $multiplier,
                ]);
            }
        }

        // Create Transportation rates
        $transportTypes = [
            ['type' => 'pesawat', 'is_riil' => true, 'max_amount' => null],
            ['type' => 'kereta', 'is_riil' => true, 'max_amount' => null],
            ['type' => 'bus', 'is_riil' => true, 'max_amount' => 500000],
            ['type' => 'taxi', 'max_amount' => 300000, 'is_riil' => true],
        ];

        foreach ($transportTypes as $transport) {
            Transportation::create([
                'sbm_setting_id' => $sbmSetting->id,
                ...$transport,
            ]);
        }

        // Create sample SPDs
        $firstEmployee = Employee::first();
        $firstBudget = Budget::first();

        $sampleSpds = [
            [
                'destination' => 'UIN Raden Mas Said Surakarta',
                'purpose' => 'Workshop Penguatan Moderasi Beragama pada Era Digital',
                'departure_date' => '2026-01-31',
                'return_date' => '2026-02-01',
                'status' => 'approved',
                'estimated_cost' => 2560000,
            ],
            [
                'destination' => 'UIN Sunan Kalijaga Yogyakarta',
                'purpose' => 'Seminar Nasional Psikologi Pendidikan',
                'departure_date' => '2026-02-10',
                'return_date' => '2026-02-11',
                'status' => 'submitted',
                'estimated_cost' => 1940000,
            ],
            [
                'destination' => 'Kementerian Agama RI Jakarta',
                'purpose' => 'Rapat Koordinasi Program Kerja 2026',
                'departure_date' => '2026-02-15',
                'return_date' => '2026-02-17',
                'status' => 'draft',
                'estimated_cost' => 4250000,
            ],
        ];

        $sptCounter = 2470;
        foreach ($sampleSpds as $spdData) {
            $duration = (new \DateTime($spdData['departure_date']))->diff(new \DateTime($spdData['return_date']))->days + 1;

            $spd = Spd::create([
                'organization_id' => $organization->id,
                'unit_id' => $firstEmployee->unit_id,
                'employee_id' => $firstEmployee->id,
                'spt_number' => $sptCounter . '/Un.19/K.AUPK/FP.16/01/2026',
                'spd_number' => ($sptCounter + 1) . '/Un.19/K.AUPK/FP.16/01/2026',
                'destination' => $spdData['destination'],
                'purpose' => $spdData['purpose'],
                'departure_date' => $spdData['departure_date'],
                'return_date' => $spdData['return_date'],
                'duration' => $duration,
                'budget_id' => $firstBudget->id,
                'estimated_cost' => $spdData['estimated_cost'],
                'transport_type' => 'pesawat',
                'needs_accommodation' => true,
                'status' => $spdData['status'],
                'created_by' => $firstEmployee->user_id ?? 'system',
            ]);

            // Add cost items
            Cost::create([
                'spd_id' => $spd->id,
                'category' => 'uang_harian',
                'description' => "Uang Harian {$duration} hari",
                'estimated_amount' => 380000 * $duration,
            ]);

            Cost::create([
                'spd_id' => $spd->id,
                'category' => 'penginapan',
                'description' => "Penginapan " . ($duration - 1) . " malam",
                'estimated_amount' => 900000 * ($duration - 1),
            ]);

            Cost::create([
                'spd_id' => $spd->id,
                'category' => 'transport',
                'description' => 'Transportasi PP',
                'estimated_amount' => 1200000,
            ]);

            $sptCounter += 2;
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Login with: mawikhusni@uinsaizu.ac.id / password');
    }
}
