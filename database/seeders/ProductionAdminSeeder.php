<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class ProductionAdminSeeder extends Seeder
{
    public function run()
    {
        $org = Organization::first();
        $unit = Unit::first();
        $role = Role::where('name', 'admin')->first();

        if (!$org || !$unit || !$role) {
            $this->command->error('Basic data missing. Run DatabaseSeeder first.');
            return;
        }

        $nip = '194508170000000000';
        $email = $nip . '@uinsaizu.ac.id';
        $password = 'Admin@eSPPD2023'; // User said 2026? Wait, let me check.
        // User said: Admin@eSPPD2026

        $emp = Employee::updateOrCreate(
            ['nip' => $nip],
            [
                'name' => 'Admin e-SPPD',
                'organization_id' => $org->id,
                'unit_id' => $unit->id,
                'email' => 'admin@esppd.cloud',
                'position' => 'Administrator',
                'rank' => 'Pembina',
                'grade' => 'IV/a',
                'employment_status' => 'PNS'
            ]
        );

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin e-SPPD',
                'password' => Hash::make('Admin@eSPPD2026'),
                'organization_id' => $org->id,
                'employee_id' => $emp->id,
                'role' => 'admin',
                'role_id' => $role->id
            ]
        );

        $this->command->info('Production Admin created successfully: ' . $email);
    }
}
