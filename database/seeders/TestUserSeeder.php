<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles or create them
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['label' => 'Admin', 'level' => 98, 'description' => 'System Administrator']
        );

        $rektorRole = Role::firstOrCreate(
            ['name' => 'rektor'],
            ['label' => 'Rektor', 'level' => 6, 'description' => 'University Rector']
        );

        $warekRole = Role::firstOrCreate(
            ['name' => 'warek'],
            ['label' => 'Wakil Rektor', 'level' => 5, 'description' => 'Vice Rector']
        );

        $dekanRole = Role::firstOrCreate(
            ['name' => 'dekan'],
            ['label' => 'Dekan', 'level' => 4, 'description' => 'Dean']
        );

        $wadekRole = Role::firstOrCreate(
            ['name' => 'wadek'],
            ['label' => 'Wakil Dekan', 'level' => 3, 'description' => 'Vice Dean']
        );

        $kaprodiRole = Role::firstOrCreate(
            ['name' => 'kaprodi'],
            ['label' => 'Kaprodi', 'level' => 2, 'description' => 'Head of Study Program']
        );

        $employeeRole = Role::firstOrCreate(
            ['name' => 'employee'],
            ['label' => 'Pegawai', 'level' => 1, 'description' => 'Staff/Lecturer']
        );

        // Create test users
        $testUsers = [
            [
                'name' => 'Admin User',
                'email' => 'admin@esppd.test',
                'nip' => '198501051989031001',
                'birth_date' => '1985-01-05',
                'role' => 'admin',
                'role_id' => $adminRole->id,
                'is_password_reset' => false, // Must change password on first login
            ],
            [
                'name' => 'Rektor Uin',
                'email' => 'rektor@esppd.test',
                'nip' => '195508151985031005',
                'birth_date' => '1955-08-15',
                'role' => 'rektor',
                'role_id' => $rektorRole->id,
                'is_password_reset' => false,
            ],
            [
                'name' => 'Wakil Rektor',
                'email' => 'warek@esppd.test',
                'nip' => '196003201992031002',
                'birth_date' => '1960-03-20',
                'role' => 'warek',
                'role_id' => $warekRole->id,
                'is_password_reset' => false,
            ],
            [
                'name' => 'Dekan Fakultas',
                'email' => 'dekan@esppd.test',
                'nip' => '197505051999031001',
                'birth_date' => '1975-05-05',
                'role' => 'dekan',
                'role_id' => $dekanRole->id,
                'is_password_reset' => false,
            ],
            [
                'name' => 'Wakil Dekan',
                'email' => 'wadek@esppd.test',
                'nip' => '198602101998021001',
                'birth_date' => '1986-02-10',
                'role' => 'wadek',
                'role_id' => $wadekRole->id,
                'is_password_reset' => false,
            ],
            [
                'name' => 'Kaprodi Program',
                'email' => 'kaprodi@esppd.test',
                'nip' => '198711151999032001',
                'birth_date' => '1987-11-15',
                'role' => 'kaprodi',
                'role_id' => $kaprodiRole->id,
                'is_password_reset' => false,
            ],
            [
                'name' => 'Dosen Biasa',
                'email' => 'dosen@esppd.test',
                'nip' => '198003152020122002',
                'birth_date' => '1980-03-15',
                'role' => 'employee',
                'role_id' => $employeeRole->id,
                'is_password_reset' => false,
            ],
        ];

        foreach ($testUsers as $userData) {
            // Extract data
            $nip = $userData['nip'];
            $birthDate = $userData['birth_date'];
            $email = $userData['email'];

            // Generate password: DDMMYYYY dari birth_date
            $passwordDefault = \Carbon\Carbon::createFromFormat('Y-m-d', $birthDate)->format('dmY');

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $userData['name'],
                    'nip' => $nip,
                    'password' => Hash::make($passwordDefault),
                    'role' => $userData['role'],
                    'role_id' => $userData['role_id'],
                    'email_verified_at' => now(),
                    'is_password_reset' => $userData['is_password_reset'] ?? false,
                ]
            );
        }

        $this->command->info('Test users created successfully!');
        $this->command->line('');
        $this->command->line('Test Accounts:');
        $this->command->line('  Admin:     admin@esppd.test / password123');
        $this->command->line('  Rektor:    rektor@esppd.test / password123');
        $this->command->line('  Warek:     warek@esppd.test / password123');
        $this->command->line('  Dekan:     dekan@esppd.test / password123');
        $this->command->line('  Wadek:     wadek@esppd.test / password123');
        $this->command->line('  Kaprodi:   kaprodi@esppd.test / password123');
        $this->command->line('  Dosen:     dosen@esppd.test / password123');
    }
}
