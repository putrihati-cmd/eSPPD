<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use App\Models\Organization;
use App\Models\Unit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DosenImport implements ToModel, WithHeadingRow
{
    protected $organization;
    protected $defaultUnit;
    protected $roles;

    public function __construct()
    {
        $this->organization = Organization::first() ?? Organization::create([
            'name' => 'UIN Saizu Purwokerto',
            'code' => 'Un.19',
        ]);
        
        $this->defaultUnit = Unit::first() ?? Unit::create([
            'organization_id' => $this->organization->id,
            'name' => 'Fakultas Psikologi',
            'code' => 'FP',
        ]);

        $this->roles = Role::all()->pluck('id', 'name')->toArray();
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        Log::info("Importing row: " . json_encode($row));
        if (empty($row['nip']) || empty($row['nama_dengan_gelar'])) {
            return null;
        }

        $nip = (string) $row['nip'];
        
        // Skip if employee already exists
        if (Employee::where('nip', $nip)->exists()) {
            return null;
        }

        // Handle birth date
        $birthDate = null;
        if (!empty($row['tanggal_lahir'])) {
            try {
                if (is_numeric($row['tanggal_lahir'])) {
                    $birthDate = Carbon::instance(Date::excelToDateTimeObject($row['tanggal_lahir']));
                } else {
                    $birthDate = Carbon::parse($row['tanggal_lahir']);
                }
            } catch (\Exception $e) {
                Log::warning("Could not parse date for NIP {$nip}: {$row['tanggal_lahir']}");
            }
        }

        // Create Employee
        $employee = Employee::create([
            'organization_id' => $this->organization->id,
            'unit_id' => $this->defaultUnit->id,
            'nip' => $nip,
            'name' => $row['nama_dengan_gelar'],
            'email' => $row['nip'] . '@uinsaizu.ac.id', // Placeholder email
            'birth_date' => $birthDate ? $birthDate->format('Y-m-d') : null,
            'position' => $row['jabatan'] ?? '',
            'rank' => $row['pangkat'] ?? '',
            'grade' => $row['gol'] ?? '',
            'employment_status' => $row['status_kepegawaian'] ?? 'PNS',
            'approval_level' => 1, // Default level info
        ]);

        // Map role based on Jabatan
        $roleName = 'dosen';
        $jabatanLower = strtolower($row['jabatan'] ?? '');
        $jabatanTambahan = strtolower($row['jabatan_1'] ?? '');

        if (str_contains($jabatanLower, 'rektor') && !str_contains($jabatanLower, 'wakil')) {
            $roleName = 'rektor';
            $employee->update(['approval_level' => 6]);
        } elseif (str_contains($jabatanLower, 'wakil rektor')) {
            $roleName = 'warek';
            $employee->update(['approval_level' => 5]);
        } elseif (str_contains($jabatanLower, 'dekan') && !str_contains($jabatanLower, 'wakil')) {
            $roleName = 'dekan';
            $employee->update(['approval_level' => 4]);
        } elseif (str_contains($jabatanLower, 'wakil dekan')) {
            $roleName = 'wadek';
            $employee->update(['approval_level' => 3]);
        } elseif (str_contains($jabatanTambahan, 'kajur') || str_contains($jabatanTambahan, 'kaprodi')) {
            $roleName = 'kabag';
            $employee->update(['approval_level' => 2]);
        }

        // Create User
        $password = $birthDate ? $birthDate->format('dmY') : 'password123';
        
        User::create([
            'name' => $employee->name,
            'email' => $employee->email,
            'nip' => $employee->nip,
            'password' => Hash::make($password),
            'organization_id' => $this->organization->id,
            'employee_id' => $employee->id,
            'role' => $roleName,
            'role_id' => $this->roles[$roleName] ?? $this->roles['dosen'],
            'is_password_reset' => false,
        ]);

        return $employee;
    }
}
