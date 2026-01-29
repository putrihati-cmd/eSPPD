<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Unit;
use Illuminate\Support\Facades\Hash;

class BacktestUserSeeder extends Seeder
{
    public function run()
    {
        $org = Organization::first();
        $unit = Unit::first();

        if (!$org || !$unit) {
            $this->command->error('Organization or Unit not found. Please ensure database is seeded with basic data.');
            return;
        }

        // Define roles and their levels
        $roles = [
            ['name' => 'superadmin', 'level' => 99, 'label' => 'Super Admin'],
            ['name' => 'admin', 'level' => 98, 'label' => 'Administrator'],
            ['name' => 'rektor', 'level' => 6, 'label' => 'Rektor'],
            ['name' => 'warek', 'level' => 5, 'label' => 'Wakil Rektor'],
            ['name' => 'dekan', 'level' => 4, 'label' => 'Dekan'],
            ['name' => 'wadek', 'level' => 3, 'label' => 'Wakil Dekan'],
            ['name' => 'kaprodi', 'level' => 2, 'label' => 'Kepala Prodi'],
            ['name' => 'dosen', 'level' => 1, 'label' => 'Dosen'],
            ['name' => 'pegawai', 'level' => 1, 'label' => 'Pegawai'],
        ];

        foreach ($roles as $roleData) {
            // Ensure Role exists
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                [
                    'label' => $roleData['label'],
                    'level' => $roleData['level'],
                    'description' => 'Backtest Role Created by Seeder',
                ]
            );

            // Generate NIP (18 Digits)
            // Pattern: 888888888888.88.00.XX (Level)
            // Base: 14 digits + 2 zeros + 2 level digits
            // Example Level 99 -> 888888888888880099
            
            // Adjust to ensure unique NIP even if levels are same (dosen vs pegawai)
            // Use level + hash of name for last 2 digits if needed, but let's simple mapping
            // Dosen (1) -> 01, Pegawai (1) -> 11 (custom logic)
            
            $suffix = $roleData['level'];
            if ($roleData['name'] === 'pegawai') $suffix = 11;
            
            $nipSuffix = str_pad($suffix, 2, '0', STR_PAD_LEFT);
            $nip = "8888888888888800{$nipSuffix}";

            $name = "Tholib Adil ({$roleData['label']})";
            $email = "{$nip}@uinsaizu.ac.id"; // Email format for login logic if needed

            // Creates User with granular role (constraint removed)
            $user = User::updateOrCreate(
                ['email' => $email], 
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'organization_id' => $org->id,
                    'role_id' => $role->id,
                    'role' => $roleData['name'], // Using actual role name (e.g., rektor)
                ]
            );

            // Create Employee linked to User
            $employee = Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'organization_id' => $org->id,
                    'unit_id' => $unit->id,
                    'nip' => $nip,
                    'name' => $name,
                    'email' => $email,
                    'position' => $roleData['label'],
                    'birth_date' => now()->subYears(rand(25, 50))->format('Y-m-d'), // Random birth date
                    'rank' => 'Penata',
                    'grade' => 'III/c',
                    'employment_status' => 'PNS',
                    'is_active' => true,
                ]
            );

            // Update user with employee_id
            $user->employee_id = $employee->id;
            $user->save();

            $this->command->info("Created: {$name} | Role: {$roleData['name']} | NIP: {$nip}");
        }
    }
}
