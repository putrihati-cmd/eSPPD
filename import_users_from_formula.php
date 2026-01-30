<?php
/**
 * IMPORT USERS - EXTRACT NIP DARI FORMULA EXCEL
 *
 * Excel DatabaseDosen.xlsx menggunakan formula Google Sheets yang tidak di-evaluate
 * Strategi: Extract NIP dari formula string, kemudian generate DDMMYYYY dari NIP
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
echo "║           IMPORT USERS - EXTRACT NIP FROM GOOGLE SHEETS FORMULAS          ║\n";
echo "║  Strategy: Extract NIP dari Formula → Generate DDMMYYYY → Hash password  ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════╝\n\n";

$excelFile = __DIR__.'/md/DatabaseDosen.xlsx';

if (!file_exists($excelFile)) {
    echo "❌ ERROR: File tidak ditemukan: $excelFile\n";
    exit(1);
}

// ═══════════════════════════════════════════════════════════════
// STEP 1: Load Excel dan analyze formula
// ═══════════════════════════════════════════════════════════════
echo "STEP 1: Load Excel file dan analyze formula\n";
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
// STEP 2: Parse dan extract NIP dari formula
// ═══════════════════════════════════════════════════════════════
echo "STEP 2: Extract NIP dari formula cells\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

/**
 * Helper function: Extract string antara kutip dari IFERROR formula
 * Formula format: =IFERROR(...,"VALUE")
 * Dari ini kita extract "VALUE"
 */
function extractNIPFromFormula($formula) {
    // Pattern: "VALUE" at the end before closing paren
    if (preg_match('/"([^"]+)"\s*\)/', $formula, $matches)) {
        $value = $matches[1];
        // Clean up invisible characters
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
        return trim($value);
    }
    return null;
}

$users = [];
$skippedRows = 0;
$errors = [];

// Read data rows (skip header row 1)
for ($row = 2; $row <= $highestRow; $row++) {
    // Col 5 = NIP Spaceless (dari formula)
    $nipCell = $worksheet->getCellByColumnAndRow(5, $row);
    // Col 2 = Nama Tanpa Gelar (dari formula)
    $namaCell = $worksheet->getCellByColumnAndRow(2, $row);

    // Get formula first (karena file pakai Google Sheets formula)
    $nipFormula = $nipCell->getValue();
    $namaFormula = $namaCell->getValue();

    if (empty($nipFormula) || empty($namaFormula)) {
        $skippedRows++;
        continue;
    }

    // Extract NIP dan Nama dari formula string
    $nip = extractNIPFromFormula($nipFormula);
    $nama = extractNIPFromFormula($namaFormula);

    if (empty($nip) || empty($nama)) {
        $skippedRows++;
        continue;
    }

    // Validasi NIP: harus 16 digit
    $nipDigitsOnly = preg_replace('/\D/', '', $nip);
    if (strlen($nipDigitsOnly) != 16) {
        $errors[] = "Row $row: Invalid NIP length ($nip) - " . strlen($nipDigitsOnly) . " digits";
        continue;
    }

    // ═══════════════════════════════════════════════════════════════
    // CRITICAL: Extract DDMMYYYY dari NIP
    // NIP format: DDMMYYYYGGKKLLSSS (16 digits)
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

    // Password default = DDMMYYYY
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

echo "✅ Extracted " . count($users) . " users from Excel\n";
echo "   Skipped: $skippedRows empty rows\n";
if (count($errors) > 0) {
    echo "   Errors: " . count($errors) . "\n";
    foreach (array_slice($errors, 0, 5) as $err) {
        echo "   - $err\n";
    }
    if (count($errors) > 5) {
        echo "   ... and " . (count($errors) - 5) . " more errors\n";
    }
}
echo "\n";

// ═══════════════════════════════════════════════════════════════
// STEP 3: Import ke database
// ═══════════════════════════════════════════════════════════════
echo "STEP 3: Import users ke database\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$importedCount = 0;
$updatedCount = 0;
$failedCount = 0;

foreach ($users as $index => $userData) {
    try {
        $userResult = User::updateOrCreate(
            ['email' => $userData['email']],
            [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password_hash'],
                'role' => $userData['role'],
                'is_password_reset' => false,  // ← CRITICAL: Wajib ganti password
                'nip' => $userData['nip'],
            ]
        );

        if ($userResult->wasRecentlyCreated) {
            $importedCount++;
        } else {
            $updatedCount++;
        }

        // Show first 5
        if ($index < 5) {
            $idx = $index + 1;
            echo "✅ [$idx] {$userData['name']} (NIP: {$userData['nip']})\n";
            echo "     Email: {$userData['email']}\n";
            echo "     Pass: {$userData['default_password']} (DDMMYYYY)\n";
            echo "     Status: " . ($userResult->wasRecentlyCreated ? "NEW" : "UPDATED") . "\n\n";
        }

    } catch (\Exception $e) {
        $failedCount++;
        if ($index < 3) {
            echo "❌ [$index] Failed: {$userData['name']} - " . $e->getMessage() . "\n";
        }
    }
}

echo "\nImport Summary:\n";
echo "  ✅ New users: $importedCount\n";
echo "  🔄 Updated: $updatedCount\n";
echo "  ❌ Failed: $failedCount\n";
echo "  📊 Total: " . ($importedCount + $updatedCount) . "\n\n";

// ═══════════════════════════════════════════════════════════════
// STEP 4: Test login
// ═══════════════════════════════════════════════════════════════
echo "STEP 4: Test login dengan password default (DDMMYYYY)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if (!empty($users)) {
    $testUser = $users[0];
    echo "Testing dengan user:\n";
    echo "  Name: {$testUser['name']}\n";
    echo "  NIP: {$testUser['nip']}\n";
    echo "  Email: {$testUser['email']}\n";
    echo "  Password: {$testUser['default_password']} (DDMMYYYY)\n";
    echo "  Expected: is_password_reset = false\n\n";

    $loginAttempt = Auth::attempt([
        'email' => $testUser['email'],
        'password' => $testUser['default_password']
    ]);

    if ($loginAttempt) {
        $user = Auth::user();
        echo "✅ LOGIN SUCCESSFUL!\n";
        echo "   Email: " . $user->email . "\n";
        echo "   Name: " . $user->name . "\n";
        echo "   Role: " . $user->role . "\n";
        echo "   is_password_reset: " . ($user->is_password_reset ? 'TRUE' : 'FALSE') . "\n";
        echo "   → Middleware akan redirect ke: /password/force-change\n";
        Auth::logout();
    } else {
        echo "❌ LOGIN FAILED\n";
        echo "   Password yang ditest: {$testUser['default_password']}\n";
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
echo "║                        ✅ IMPORT COMPLETE ✅                             ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════╝\n\n";

echo "Database status:\n";
echo "  ✅ Total users: $totalUsers\n";
echo "  ✅ Latest imported: " . ($importedCount + $updatedCount) . " users\n\n";

echo "═══════════════════════════════════════════════════════════════════════════\n";
echo "                      ALUR LOGIN YANG BENAR\n";
echo "═══════════════════════════════════════════════════════════════════════════\n\n";

if (!empty($users)) {
    $t = $users[0];
    echo "1️⃣  LOGIN PERTAMA - Gunakan password default:\n";
    echo "   NIP: {$t['nip']}\n";
    echo "   Password: {$t['default_password']} (DDMMYYYY dari NIP)\n";
    echo "   → Login berhasil, masuk sistem\n\n";

    echo "2️⃣  AUTO-REDIRECT KE HALAMAN GANTI PASSWORD:\n";
    echo "   Karena flag is_password_reset = FALSE\n";
    echo "   Middleware otomatis redirect ke: /password/force-change\n\n";

    echo "3️⃣  GANTI PASSWORD:\n";
    echo "   Masukkan password baru (min 8 karakter)\n";
    echo "   Konfirmasi password\n";
    echo "   Submit\n";
    echo "   → is_password_reset diset ke TRUE\n";
    echo "   → Redirect ke dashboard\n\n";

    echo "4️⃣  LOGIN KEDUA KALI:\n";
    echo "   NIP: {$t['nip']}\n";
    echo "   Password: [password baru yang sudah diganti]\n";
    echo "   → Login berhasil, akses dashboard normal\n\n";
}

echo "═══════════════════════════════════════════════════════════════════════════\n\n";
echo "🌐 Login URL: http://192.168.1.27:8083/login\n";
echo "📊 Database: esppd_production\n";
echo "👥 Total Users: $totalUsers\n\n";
?>
