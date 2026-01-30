<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\User;

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║         IMPORT USER FROM EXCEL - AUTO LOGIN TEST              ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$excelFile = __DIR__.'/storage/data_dosen.xlsx';

if (!file_exists($excelFile)) {
    echo "❌ ERROR: File tidak ditemukan: $excelFile\n";
    exit(1);
}

echo "STEP 1: Load Excel file\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━\n";
try {
    $spreadsheet = IOFactory::load($excelFile);
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();
    echo "✅ File loaded: $excelFile\n";
    echo "   Total rows: $highestRow\n\n";
} catch (\Exception $e) {
    echo "❌ ERROR loading Excel: " . $e->getMessage() . "\n";
    exit(1);
}

echo "STEP 2: Extract data and prepare for import\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$users = [];
$headers = [];
$importedCount = 0;
$failedCount = 0;

// Read header row
for ($col = 1; $col <= 10; $col++) {
    $cell = $worksheet->getCellByColumnAndRow($col, 1)->getValue();
    if ($cell) {
        $headers[$col] = $cell;
    }
}

echo "Headers found: " . implode(', ', $headers) . "\n\n";

// Read data rows
for ($row = 2; $row <= $highestRow; $row++) {
    $nipCell = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
    $namaCell = $worksheet->getCellByColumnAndRow(2, $row)->getValue();

    if (!$nipCell || !$namaCell) {
        continue; // Skip empty rows
    }

    $nip = trim((string)$nipCell);
    $nama = trim((string)$namaCell);

    // Convert NIP to email format
    $email = preg_replace('/\s+/', '', $nip) . '@uinsaizu.ac.id';

    // Generate default password (NIP last 6 digits + 123)
    $lastSixDigits = substr(preg_replace('/\D/', '', $nip), -6) ?: '000000';
    $defaultPassword = $lastSixDigits . '123';
    $passwordHash = Hash::make($defaultPassword);

    $users[] = [
        'nip' => $nip,
        'name' => $nama,
        'email' => $email,
        'password' => $passwordHash,
        'default_password' => $defaultPassword,
        'role' => 'employee'
    ];
}

echo "Prepared $" . count($users) . " users for import\n\n";

echo "STEP 3: Import users to database\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

foreach ($users as $userData) {
    try {
        $user = User::updateOrCreate(
            ['email' => $userData['email']],
            [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'role' => $userData['role'],
                'is_password_reset' => false
            ]
        );
        $importedCount++;

        if ($importedCount <= 5) {
            echo "✅ User imported: {$userData['name']} ({$userData['email']})\n";
        }
    } catch (\Exception $e) {
        $failedCount++;
        echo "❌ Failed: {$userData['name']} - " . $e->getMessage() . "\n";
    }
}

echo "\nImport summary: $importedCount users imported, $failedCount failed\n\n";

echo "STEP 4: Test login dengan user dari Excel\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if (!empty($users)) {
    $testUser = $users[0];
    echo "Testing login dengan user: {$testUser['name']}\n";
    echo "   Email: {$testUser['email']}\n";
    echo "   Default Password: {$testUser['default_password']}\n";

    $loginTest = Auth::attempt([
        'email' => $testUser['email'],
        'password' => $testUser['default_password']
    ]);

    if ($loginTest) {
        echo "✅ LOGIN SUCCESSFUL!\n";
        echo "   Authenticated as: " . Auth::user()->email . "\n";
        echo "   User role: " . Auth::user()->role . "\n";
        Auth::logout();
    } else {
        echo "❌ Login failed\n";
    }
}

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                         SUMMARY                                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
echo "✅ Import dari Excel berhasil!\n";
echo "✅ Total users tersimpan: $importedCount\n";
echo "✅ Users siap untuk login\n\n";
echo "Data users dari Excel sudah bisa digunakan untuk login di:\n";
echo "   🌐 http://192.168.1.27:8083\n\n";

$totalUsers = User::count();
echo "Total users di database sekarang: $totalUsers\n";
?>
