<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Employee;
use App\Models\Spd;
use App\Models\Approval;
use App\Models\SpdFollower;
use App\Models\Cost;
use App\Models\TripReport;
use App\Models\TripActivity;
use App\Models\TripOutput;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

echo "--- PROSES RESET DAN IMPORT DOSEN (VIA CSV) ---\n\n";

$csvPath = base_path('md/dosen_clean.csv');
if (!file_exists($csvPath)) {
    die("❌ Error: File CSV tidak ditemukan di $csvPath\n");
}

try {
    DB::transaction(function () use ($csvPath) {
        echo "1. Menghapus data transaksi (SPD, Approvals, dll)...\n";
        TripActivity::query()->delete();
        TripOutput::query()->delete();
        TripReport::query()->delete();
        Cost::query()->delete();
        SpdFollower::query()->delete();
        Approval::query()->delete();
        Spd::query()->delete();

        echo "2. Menghapus data Akun dan Pegawai lama...\n";
        User::query()->delete();
        Employee::query()->delete();

        echo "3. Menyiapkan Organisasi dan Unit default...\n";
        $organization = Organization::first() ?? Organization::create([
            'name' => 'UIN Saizu Purwokerto',
            'code' => 'Un.19',
        ]);
        
        $unit = Unit::first() ?? Unit::create([
            'organization_id' => $organization->id,
            'name' => 'Institut',
            'code' => 'UIN',
        ]);

        $roles = Role::all()->pluck('id', 'name')->toArray();

        echo "4. Memproses Import CSV...\n";
        $file = fopen($csvPath, 'r');
        $headers = fgetcsv($file); // Skip header row
        
        // Map header index
        $headerMap = array_flip($headers);
        
        $count = 0;
        while (($row = fgetcsv($file)) !== FALSE) {
            $name = $row[$headerMap['Nama dengan Gelar'] ?? 2] ?? null;
            $nip = $row[$headerMap['NIP'] ?? 5] ?? null;
            $birthStr = $row[$headerMap['Tanggal Lahir'] ?? 12] ?? null;
            $jabatan = $row[$headerMap['Jabatan'] ?? 17] ?? '';
            $pangkat = $row[$headerMap['Pangkat'] ?? 13] ?? '';
            $gol = $row[$headerMap['Gol'] ?? 14] ?? '';
            $status = $row[$headerMap['Status Kepegawaian'] ?? 8] ?? 'PNS';
            $jabatanTambahan = $row[$headerMap['Jabatan.1'] ?? 23] ?? '';

            if (!$name || !$nip) continue;

            // Clean NIP (remove spaces for ID/Password logic)
            $nipClean = str_replace(' ', '', $nip);
            
            // Parse birth date
            $birthDate = null;
            if ($birthStr) {
                try {
                    $birthDate = Carbon::parse($birthStr);
                } catch (\Exception $e) {}
            }

            // Create Employee
            $employee = Employee::create([
                'organization_id' => $organization->id,
                'unit_id' => $unit->id,
                'nip' => $nipClean,
                'name' => $name,
                'email' => $nipClean . '@uinsaizu.ac.id',
                'birth_date' => $birthDate ? $birthDate->format('Y-m-d') : null,
                'position' => $jabatan,
                'rank' => $pangkat,
                'grade' => $gol,
                'employment_status' => $status,
                'approval_level' => 1,
            ]);

            // Role mapping
            $roleName = 'dosen';
            $jabLower = strtolower($jabatan);
            $jabTbhLower = strtolower($jabatanTambahan);

            if (str_contains($jabLower, 'rektor') && !str_contains($jabLower, 'wakil')) {
                $roleName = 'rektor';
                $employee->update(['approval_level' => 6]);
            } elseif (str_contains($jabLower, 'wakil rektor')) {
                $roleName = 'warek';
                $employee->update(['approval_level' => 5]);
            } elseif (str_contains($jabLower, 'dekan') && !str_contains($jabLower, 'wakil')) {
                $roleName = 'dekan';
                $employee->update(['approval_level' => 4]);
            } elseif (str_contains($jabLower, 'wakil dekan')) {
                $roleName = 'wadek';
                $employee->update(['approval_level' => 3]);
            } elseif (str_contains($jabTbhLower, 'kajur') || str_contains($jabTbhLower, 'kaprodi') || str_contains($jabTbhLower, 'ketua prodi')) {
                $roleName = 'kabag';
                $employee->update(['approval_level' => 2]);
            }

            // Create User
            $password = $birthDate ? $birthDate->format('dmY') : 'password123';
            User::create([
                'name' => $name,
                'email' => $employee->email,
                'nip' => $nipClean,
                'password' => Hash::make($password),
                'organization_id' => $organization->id,
                'employee_id' => $employee->id,
                'role' => $roleName,
                'role_id' => $roles[$roleName] ?? $roles['dosen'],
                'is_password_reset' => false,
            ]);

            $count++;
        }
        fclose($file);
        
        echo "5. Berhasil mengimport $count data!\n";
    });

    echo "\n--- SELESAI ---\n";

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
