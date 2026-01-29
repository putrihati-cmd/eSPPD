<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * RoleSeeder - RBAC.md Implementation
 * Seeds 8 roles with proper hierarchy levels
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'superadmin',
                'label' => 'Super Administrator',
                'level' => 99,
                'description' => 'Full system access, manage all users and settings'
            ],
            [
                'name' => 'admin',
                'label' => 'Admin Kepegawaian',
                'level' => 98,
                'description' => 'Manage employees, import data, system settings'
            ],
            [
                'name' => 'rektor',
                'label' => 'Rektor',
                'level' => 6,
                'description' => 'Final approval for all SPD, institution-wide view'
            ],
            [
                'name' => 'warek',
                'label' => 'Wakil Rektor',
                'level' => 5,
                'description' => 'Approve SPD luar negeri, institution-wide view'
            ],
            [
                'name' => 'dekan',
                'label' => 'Dekan',
                'level' => 4,
                'description' => 'Approve SPD faculty, override cancel, executive view'
            ],
            [
                'name' => 'wadek',
                'label' => 'Wakil Dekan',
                'level' => 3,
                'description' => 'Approve SPD dalam kota, delegation, faculty view'
            ],
            [
                'name' => 'kabag',
                'label' => 'Kepala Bagian / Kaprodi',
                'level' => 2,
                'description' => 'Approve subordinate SPD, forward to Wadek'
            ],
            [
                'name' => 'dosen',
                'label' => 'Dosen / Pegawai',
                'level' => 1,
                'description' => 'Submit SPD, view own history, download documents'
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }

        $this->command->info('✅ Seeded 8 roles with hierarchy levels');
    }
}
