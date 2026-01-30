<?php
/**
 * Import Users from Excel - Direct Handler
 * Ini adalah cara Python-compatible: semua logic di-handle oleh PHP/Laravel
 */

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\User;

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║    IMPORT USERS FROM EXCEL - PHP HANDLER (PYTHON METHOD)     ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$excelFile = __DIR__.'/storage/data_dosen.xlsx';

// STEP 1: Load Excel
echo "STEP 1: Load Excel file\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━\n";

if (!file_exists($excelFile)) {
    echo "❌ File tidak ditemukan: $excelFile\n";
    exit(1);
}

try {
    $spreadsheet = IOFactory::load($excelFile);
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();
    echo "✅ File loaded: $excelFile\n";
    echo "   Total rows: $highestRow\n\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// STEP 2: Extract data
echo "STEP 2: Extract and prepare data\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$users = [];

for ($row = 2; $row <= min($highestRow, 100); $row++) {
    // Column F = NIP, Column C = Nama dengan Gelar
    $nipCell = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
    $namaCell = $worksheet->getCellByColumnAndRow(3, $row)->getValue();

    if (!$nipCell || !$namaCell) {
        continue;
    }

    $nip = trim((string)$nipCell);
    $nama = trim((string)$namaCell);

    if (strlen($nip) < 5) {
        continue;
    }

    // Convert to email
    $email = preg_replace('/\s+/', '', $nip) . '@uinsaizu.ac.id';

    // Generate password: last 6 digits + 123
    $digits = preg_replace('/\D/', '', $nip);
    $lastSixDigits = substr($digits, -6) ?: '000000';
    $password = $lastSixDigits . '123';

    $users[] = [
        'nip' => $nip,
        'name' => $nama,
        'email' => $email,
        'password' => Hash::make($password),
        'plain_password' => $password,
        'role' => 'employee'
    ];
}

echo "Prepared: " . count($users) . " users\n\n";

// STEP 3: Import to database
echo "STEP 3: Import ke database\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$importCount = 0;
$failCount = 0;

foreach ($users as $userData) {
    try {
        User::updateOrCreate(
            ['email' => $userData['email']],
            [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'role' => $userData['role']
            ]
        );
        $importCount++;

        if ($importCount <= 3 || $importCount % 10 === 0) {
            echo "✅ [{$importCount}] " . substr($userData['name'], 0, 30) . "\n";
        }
    } catch (Exception $e) {
        $failCount++;
    }
}

echo "\n" . $importCount . " users imported\n\n";

// STEP 4: Test login
echo "STEP 4: Test login dengan user dari Excel\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if (!empty($users)) {
    $testUser = $users[0];
    echo "User: " . $testUser['name'] . "\n";
    echo "Email: " . $testUser['email'] . "\n";
    echo "Password: " . $testUser['plain_password'] . "\n\n";

    $loginResult = Auth::attempt([
        'email' => $testUser['email'],
        'password' => $testUser['plain_password']
    ]);

    if ($loginResult) {
        echo "✅ LOGIN SUCCESSFUL!\n";
        echo "   Authenticated as: " . Auth::user()->email . "\n";
        Auth::logout();
    } else {
        echo "❌ Login test failed\n";
    }
}

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                       SUMMARY                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$totalUsers = User::count();
echo "✅ Import dari Excel berhasil!\n";
echo "✅ Total users di database: $totalUsers\n";
echo "✅ Siap untuk login di: http://192.168.1.27:8083\n\n";
?>
