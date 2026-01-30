<?php
/**
 * FINAL IMPORT SCRIPT - CORRECT LOGIC
 * 
 * ✅ NIP format: 18 digits (DDMMYYYYGGKKLLSSSSS) - NOT 16
 * ✅ Password default: DDMMYYYY (first 8 digits dari NIP)  
 * ✅ Flag is_password_reset: FALSE (wajib ganti password saat login)
 * ✅ Alur: NIP+DDMMYYYY → Redirect ganti password → Login lagi dengan password baru
 */

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\User;

echo "\n╔═══════════════════════════════════════════════════════════════════════════╗\n";
echo "║            FINAL IMPORT - CORRECT NIP FORMAT (18 DIGITS)                  ║\n";
echo "║  NIP: DDMMYYYYGGKKLLSSSSS (18 digits)                                     ║\n";
echo "║  Password Default: DDMMYYYY (dari NIP[0-7])                               ║\n";
echo "║  Alur: NIP+DDMMYYYY → Ganti Password → Login dengan password baru         ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════╝\n\n";

$excelFile = __DIR__.'/md/DatabaseDosen.xlsx';

if (!file_exists($excelFile)) {
    echo "❌ File tidak ditemukan: $excelFile\n";
    exit(1);
}

// ═══════════════════════════════════════════════════════════════
// STEP 1: Load Excel
// ═══════════════════════════════════════════════════════════════
echo "STEP 1: Load Excel file\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $spreadsheet = IOFactory::load($excelFile);
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();
    echo "✅ File loaded: $excelFile\n";
    echo "   Total rows: $highestRow\n\n";
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// ═══════════════════════════════════════════════════════════════
// STEP 2: Extract NIP dari formula Excel
// ═══════════════════════════════════════════════════════════════
echo "STEP 2: Extract dan process data\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

function extractFromFormula($cellFormula) {
    if (!is_string($cellFormula) || !strpos($cellFormula, 'IFERROR')) {
        return $cellFormula;
    }
    // Extract string dalam kutip di akhir formula
    if (preg_match('/"([^"]*)"\\s*\\)\\s*$/', $cellFormula, $matches)) {
        $value = $matches[1];
        // Remove invisible/control characters
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
        // Remove zero-width spaces and other unicode nonsense
        $value = str_replace("\u{200B}", '', $value);
        $value = str_replace("\u{200C}", '', $value);
        $value = str_replace("\u{200D}", '', $value);
        return trim($value);
    }
    return null;
}

$users = [];
$skippedRows = 0;
$errors = [];

// Read data rows (skip header row 1)
for ($row = 2; $row <= $highestRow; $row++) {
    // Col 5 = NIP Spaceless
    $nipFormula = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
    // Col 2 = Nama Tanpa Gelar
    $namaFormula = $worksheet->getCellByColumnAndRow(2, $row)->getValue();

    if (empty($nipFormula) || empty($namaFormula)) {
        $skippedRows++;
        continue;
    }

    // Extract values dari formula
    $nip = extractFromFormula($nipFormula);
    $nama = extractFromFormula($namaFormula);

    if (empty($nip) || empty($nama)) {
        $skippedRows++;
        continue;
    }

    // Validasi NIP: 18 digit format
    $nipDigitsOnly = preg_replace('/\D/', '', $nip);
    if (strlen($nipDigitsOnly) != 18) {
        $errors[] = "Row $row: NIP length is " . strlen($nipDigitsOnly) . " (expected 18): $nip";
        continue;
    }

    // ═══════════════════════════════════════════════════════════════
    // CRITICAL: Extract DDMMYYYY dari 18-digit NIP
    // NIP format: DDMMYYYYGGKKLLSSSSS
    // Password = first 8 digits (DDMMYYYY)
    // ═══════════════════════════════════════════════════════════════
    $birthDateDDMMYYYY = substr($nipDigitsOnly, 0, 8);

    // Validasi tanggal
    $day = (int)substr($birthDateDDMMYYYY, 0, 2);
    $month = (int)substr($birthDateDDMMYYYY, 2, 2);
    $year = (int)substr($birthDateDDMMYYYY, 4, 4);

    if ($day < 1 || $day > 31 || $month < 1 || $month > 12 || $year < 1900) {
        $errors[] = "Row $row: Invalid birth date ($birthDateDDMMYYYY) from NIP $nip";
        continue;
    }

    // Email format: NIP@uinsaizu.ac.id
    $email = $nip . '@uinsaizu.ac.id';

    // Password default = DDMMYYYY (tanggal lahir)
    $defaultPassword = $birthDateDDMMYYYY;
    $passwordHash = Hash::make($defaultPassword);

    $users[] = [
        'nip' => $nip,
        'name' => $nama,
        'email' => $email,
        'password_hash' => $passwordHash,
        'default_password' => $defaultPassword,
        'birth_date_ddmmyyyy' => $birthDateDDMMYYYY,
        'role' => 'employee'
    ];
}

echo "✅ Extracted " . count($users) . " users\n";
echo "   Skipped: $skippedRows empty rows\n";
if (count($errors) > 0) {
    echo "   Errors: " . count($errors) . "\n";
    foreach (array_slice($errors, 0, 3) as $e) {
        echo "      $e\n";
    }
    if (count($errors) > 3) {
        echo "      ... and " . (count($errors) - 3) . " more\n";
    }
}
echo "\n";

// ═══════════════════════════════════════════════════════════════
// STEP 3: Delete old bad data
// ═══════════════════════════════════════════════════════════════
echo "STEP 3: Clean up bad data dari import sebelumnya\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$badUsersCount = User::where('name', 'LIKE', '%=IFERROR%')->count();
if ($badUsersCount > 0) {
    User::where('name', 'LIKE', '%=IFERROR%')->delete();
    echo "✅ Deleted $badUsersCount bad records from previous import\n\n";
} else {
    echo "✅ No bad data to clean\n\n";
}

// ═══════════════════════════════════════════════════════════════
// STEP 4: Import users
// ═══════════════════════════════════════════════════════════════
echo "STEP 4: Import users ke database\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$importedCount = 0;
$updatedCount = 0;
$failedCount = 0;

foreach ($users as $index => $userData) {
    try {
        $result = User::updateOrCreate(
            ['email' => $userData['email']],
            [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password_hash'],
                'nip' => $userData['nip'],
                'role' => $userData['role'],
                'is_password_reset' => false,  // ← CRITICAL
            ]
        );

        if ($result->wasRecentlyCreated) {
            $importedCount++;
        } else {
            $updatedCount++;
        }

        // Show first 3
        if ($index < 3) {
            $idx = $index + 1;
            echo "✅ [{$idx}] {$userData['name']}\n";
            echo "     NIP: {$userData['nip']}\n";
            echo "     Email: {$userData['email']}\n";
            echo "     Pass default: {$userData['default_password']} (DDMMYYYY)\n";
            echo "     Status: " . ($result->wasRecentlyCreated ? "NEW" : "UPDATED") . "\n\n";
        }

    } catch (\Exception $e) {
        $failedCount++;
        if ($index < 2) {
            echo "❌ Failed: {$userData['name']} - " . $e->getMessage() . "\n";
        }
    }
}

echo "\nImport Summary:\n";
echo "  ✅ New users: $importedCount\n";
echo "  🔄 Updated: $updatedCount\n";
echo "  ❌ Failed: $failedCount\n";
echo "  📊 Total: " . ($importedCount + $updatedCount) . "\n\n";

// ═══════════════════════════════════════════════════════════════
// STEP 5: Test login
// ═══════════════════════════════════════════════════════════════
echo "STEP 5: Test login dengan password default\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if (!empty($users)) {
    $testUser = $users[0];
    echo "Testing dengan user:\n";
    echo "  Name: {$testUser['name']}\n";
    echo "  NIP: {$testUser['nip']}\n";
    echo "  Email: {$testUser['email']}\n";
    echo "  Password: {$testUser['default_password']} (DDMMYYYY)\n\n";

    if (Auth::attempt(['email' => $testUser['email'], 'password' => $testUser['default_password']])) {
        $user = Auth::user();
        echo "✅ LOGIN SUCCESSFUL!\n";
        echo "   Name: {$user->name}\n";
        echo "   Email: {$user->email}\n";
        echo "   is_password_reset: " . ($user->is_password_reset ? 'TRUE' : 'FALSE') . "\n";
        echo "   → Middleware akan redirect ke /password/force-change\n";
        Auth::logout();
    } else {
        echo "❌ LOGIN FAILED\n";
    }
} else {
    echo "⚠️  Tidak ada users untuk di-test\n";
}

echo "\n";

// ═══════════════════════════════════════════════════════════════
// FINAL SUMMARY
// ═══════════════════════════════════════════════════════════════
$totalUsers = User::count();

echo "╔═══════════════════════════════════════════════════════════════════════════╗\n";
echo "║                       ✅ IMPORT COMPLETE ✅                              ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════╝\n\n";

echo "Database Status:\n";
echo "  ✅ Total users: $totalUsers\n";
echo "  ✅ Latest import: " . ($importedCount + $updatedCount) . " users\n\n";

echo "═══════════════════════════════════════════════════════════════════════════\n";
echo "                  ✅ ALUR LOGIN YANG BENAR ✅\n";
echo "═══════════════════════════════════════════════════════════════════════════\n\n";

if (!empty($users)) {
    $t = $users[0];
    echo "1️⃣  LOGIN PERTAMA - Username + Password Default:\n";
    echo "   NIP: {$t['nip']}\n";
    echo "   Password: {$t['default_password']} (DDMMYYYY)\n";
    echo "   → Masuk sistem\n\n";

    echo "2️⃣  AUTO-REDIRECT KE /password/force-change\n";
    echo "   Flag is_password_reset = FALSE\n";
    echo "   Middleware detects dan redirect otomatis\n\n";

    echo "3️⃣  GANTI PASSWORD:\n";
    echo "   Input password baru (minimum 8 karakter)\n";
    echo "   Konfirmasi password\n";
    echo "   Submit\n";
    echo "   → is_password_reset = TRUE\n";
    echo "   → Redirect ke dashboard\n\n";

    echo "4️⃣  LOGIN KEDUA KALI - Username + Password Baru:\n";
    echo "   NIP: {$t['nip']}\n";
    echo "   Password: [password baru yang sudah diganti]\n";
    echo "   → Akses dashboard normal\n\n";
}

echo "═══════════════════════════════════════════════════════════════════════════\n\n";
echo "🌐 Login URL: http://192.168.1.27:8083/login\n";
echo "📊 Database: esppd_production\n";
echo "👥 Total Users: $totalUsers\n\n";
?>
