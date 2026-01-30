<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportUsersFromExcel extends Command
{
    protected $signature = 'import:users-excel {file=storage/data_dosen.xlsx}';
    protected $description = 'Import users from Excel file';

    public function handle()
    {
        $this->line("\n╔════════════════════════════════════════════════════════════════╗");
        $this->line("║       IMPORT USERS FROM EXCEL - PYTHON COMPATIBLE             ║");
        $this->line("╚════════════════════════════════════════════════════════════════╝\n");

        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $this->info("STEP 1: Load Excel file");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━");

        try {
            $spreadsheet = IOFactory::load($file);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();

            $this->info("✅ File loaded: $file");
            $this->info("   Total rows: $highestRow");
        } catch (\Exception $e) {
            $this->error("ERROR loading Excel: " . $e->getMessage());
            return 1;
        }

        $this->line("\nSTEP 2: Extract and prepare data for import");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $users = [];

        // Read data rows (skip header)
        for ($row = 2; $row <= $highestRow; $row++) {
            // Columns: A=No, B=Nama Tanpa Gelar, C=Nama dengan Gelar, D=Nama, E=NIP Spaceless, F=NIP
            $nipCell = $worksheet->getCellByColumnAndRow(6, $row)->getValue(); // Column F (NIP)
            $namaCell = $worksheet->getCellByColumnAndRow(3, $row)->getValue(); // Column C (Nama dengan Gelar)

            if (!$nipCell || !$namaCell) {
                continue;
            }

            $nip = trim((string)$nipCell);
            $nama = trim((string)$namaCell);

            if (strlen($nip) < 5 || empty($nama)) {
                continue;
            }

            // Convert NIP to email
            $email = preg_replace('/\s+/', '', $nip) . '@uinsaizu.ac.id';

            // Generate default password: last 6 digits + 123
            $digits = preg_replace('/\D/', '', $nip);
            $lastSixDigits = substr($digits, -6) ?: '000000';
            $defaultPassword = $lastSixDigits . '123';

            $users[] = [
                'nip' => $nip,
                'name' => $nama,
                'email' => $email,
                'password' => Hash::make($defaultPassword),
                'default_password' => $defaultPassword,
                'role' => 'employee'
            ];

            if (count($users) >= 1000) break; // Limit for demo
        }

        $this->info("Prepared " . count($users) . " users for import");

        $this->line("\nSTEP 3: Import users to database");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $importedCount = 0;
        $failedCount = 0;

        foreach ($users as $userData) {
            try {
                $user = User::updateOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'password' => $userData['password'],
                        'role' => $userData['role']
                    ]
                );
                $importedCount++;

                if ($importedCount <= 5 || $importedCount % 100 === 0) {
                    $this->line("✅ [{$importedCount}] " . $userData['name'] . " (" . $userData['email'] . ")");
                }
            } catch (\Exception $e) {
                $failedCount++;
            }
        }

        $this->info("\n\nImport Summary: $importedCount users imported, $failedCount failed");

        $this->line("\nSTEP 4: Test login dengan user dari Excel");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        if (!empty($users)) {
            $testUser = $users[0];
            $this->line("Testing login dengan: " . $testUser['name']);
            $this->line("   Email: " . $testUser['email']);
            $this->line("   Default Password: " . $testUser['default_password']);

            if (\Illuminate\Support\Facades\Auth::attempt([
                'email' => $testUser['email'],
                'password' => $testUser['default_password']
            ])) {
                $this->info("✅ LOGIN SUCCESSFUL!");
                $this->line("   Authenticated as: " . \Illuminate\Support\Facades\Auth::user()->email);
                \Illuminate\Support\Facades\Auth::logout();
            } else {
                $this->error("❌ Login failed");
            }
        }

        $this->line("\n╔════════════════════════════════════════════════════════════════╗");
        $this->line("║                         COMPLETE                              ║");
        $this->line("╚════════════════════════════════════════════════════════════════╝");

        $totalUsers = User::count();
        $this->info("\n✅ Import dari Excel berhasil!");
        $this->info("✅ Total users di database: $totalUsers");
        $this->info("✅ Users siap untuk login di: http://192.168.1.27:8083\n");

        return 0;
    }
}
