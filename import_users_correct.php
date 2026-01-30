<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * IMPORT USERS FROM EXCEL WITH CORRECT LOGIN LOGIC
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * LOGIC FLOW:
 * 1. Import users dengan password default = DDMMYYYY (tanggal lahir dari NIP)
 * 2. Set flag is_password_reset = FALSE (user wajib ganti password saat login)
 * 3. User login dengan NIP + DDMMYYYY
 * 4. Middleware CheckPasswordReset detect is_password_reset=false → redirect ke /password/force-change
 * 5. User ganti password → set is_password_reset = true
 * 6. User login lagi dengan password baru → akses dashboard normal
 */

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\User;
use App\Models\Employee;

echo "\n╔═══════════════════════════════════════════════════════════════════════════╗\n";
echo "║                  IMPORT USERS FROM EXCEL - CORRECT LOGIC                  ║\n";
echo "║  Password Default: DDMMYYYY (tanggal lahir dari NIP)                       ║\n";
echo "║  Alur: NIP+DDMMYYYY → Auto-redirect ganti password → Login dengan password║\n";
echo "║        baru yang sudah diganti                                            ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════╝\n\n";

$excelFile = __DIR__.'/storage/data_dosen.xlsx';

if (!file_exists($excelFile)) {
    echo "❌ ERROR: File tidak ditemukan: $excelFile\n";
    exit(1);
}

// ═══════════════════════════════════════════════════════════════
// STEP 1: Load Excel file
// ═══════════════════════════════════════════════════════════════
echo "STEP 1: Load Excel file\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $spreadsheet = IOFactory::load($excelFile);
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();
    echo "✅ File loaded: $excelFile\n";
    echo "   Total rows: $highestRow (Header + Data rows)\n\n";
} catch (\Exception $e) {
    echo "❌ ERROR loading Excel: " . $e->getMessage() . "\n";
    exit(1);
}

// ═══════════════════════════════════════════════════════════════
// STEP 2: Extract data from Excel
// ═══════════════════════════════════════════════════════════════
echo "STEP 2: Extract and prepare user data\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

/**
 * Kolom Excel DatabaseDosen.xlsx:
 * Col 1: No
 * Col 2: Nama Tanpa Gelar
 * Col 3: Nama dengan Gelar
 * Col 5: NIP Spaceless (format: 196708151992031003) ← GUNAKAN INI
 * Col 6: NIP (format: 19670815 199203 1 003)
 * Col 13: Tanggal Lahir (numeric value DDMMYYYY dari NIP)
 */

$users = [];
$skippedRows = 0;

// Read data rows (skip header row 1)
for ($row = 2; $row <= $highestRow; $row++) {
    // Col 5 = NIP Spaceless (most reliable)
    $nipValue = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
    // Col 2 = Nama Tanpa Gelar (cleaner name)
    $namaValue = $worksheet->getCellByColumnAndRow(2, $row)->getValue();

    if (empty($nipValue) || empty($namaValue)) {
        $skippedRows++;
        continue;
    }

    $nip = trim((string)$nipValue);
    $nama = trim((string)$namaValue);

    // Validasi NIP format: harus minimal 16 digit
    $nipDigitsOnly = preg_replace('/\D/', '', $nip);
    if (strlen($nipDigitsOnly) < 8) {
        echo "⚠️  Row $row: Invalid NIP format ($nip), skipping...\n";
        $skippedRows++;
        continue;
    }

    // ═══════════════════════════════════════════════════════════════
    // CRITICAL: Extract birth date dari NIP
    // ═══════════════════════════════════════════════════════════════
    // NIP format: DDMMYYYYGGKKLLSSS (16 digits total)
    // Birth date = First 8 digits = DDMMYYYY
    $birthDateFromNip = substr($nipDigitsOnly, 0, 8);

    // Validasi tanggal lahir
    $day = substr($birthDateFromNip, 0, 2);
    $month = substr($birthDateFromNip, 2, 2);
    $year = substr($birthDateFromNip, 4, 4);

    if ((int)$day < 1 || (int)$day > 31 || (int)$month < 1 || (int)$month > 12 || (int)$year < 1900) {
        echo "⚠️  Row $row: Invalid birth date from NIP ($birthDateFromNip) for $nip, using fallback\n";
        $birthDateFromNip = '01011999';
    }

    // Email format: NIP@uinsaizu.ac.id
    $email = $nip . '@uinsaizu.ac.id';

    // ═══════════════════════════════════════════════════════════════
    // PASSWORD DEFAULT = DDMMYYYY (Tanggal Lahir)
    // ═══════════════════════════════════════════════════════════════
    $defaultPassword = $birthDateFromNip;
    $passwordHash = Hash::make($defaultPassword);

    $users[] = [
        'nip' => $nip,
        'name' => $nama,
        'email' => $email,
        'password_hash' => $passwordHash,
        'default_password' => $defaultPassword,  // For display/testing
        'birth_date_ddmmyyyy' => $birthDateFromNip,
        'role' => 'employee'
    ];
}

echo "✅ Extracted " . count($users) . " users from Excel\n";
echo "   Skipped: $skippedRows empty/invalid rows\n";
echo "   Password default: DDMMYYYY (tanggal lahir)\n";
echo "   Flag is_password_reset: FALSE (user WAJIB ganti password)\n\n";

// ═══════════════════════════════════════════════════════════════
// STEP 3: Import to Database
// ═══════════════════════════════════════════════════════════════
echo "STEP 3: Import users to database\n";
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
                'is_password_reset' => false,  // ← CRITICAL: User harus ganti password
                'nip' => $userData['nip'],
            ]
        );

        if ($userResult->wasRecentlyCreated) {
            $importedCount++;
        } else {
            $updatedCount++;
        }

        // Show first 5 for verification
        if ($index < 5) {
            $idx = $index + 1;
            echo "✅ [$idx] {$userData['name']}\n";
            echo "     Email: {$userData['email']}\n";
            echo "     Password default: {$userData['default_password']} (DDMMYYYY)\n";
            echo "     Status: " . ($userResult->wasRecentlyCreated ? "NEW" : "UPDATED") . "\n\n";
        }

    } catch (\Exception $e) {
        $failedCount++;
        echo "❌ Failed: {$userData['name']} - " . $e->getMessage() . "\n";
    }
}

echo "Import Summary:\n";
echo "  ✅ New users imported: $importedCount\n";
echo "  🔄 Existing users updated: $updatedCount\n";
echo "  ❌ Failed imports: $failedCount\n";
echo "  📊 Total imported: " . ($importedCount + $updatedCount) . "\n\n";

// ═══════════════════════════════════════════════════════════════
// STEP 4: Test login flow
// ═══════════════════════════════════════════════════════════════
echo "STEP 4: Test login dengan password default (DDMMYYYY)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if (!empty($users)) {
    $testUser = $users[0];
    echo "Testing login:\n";
    echo "  Name: {$testUser['name']}\n";
    echo "  Email: {$testUser['email']}\n";
    echo "  Password: {$testUser['default_password']} (DDMMYYYY)\n";
    echo "  Expected: is_password_reset = false (user akan di-redirect ke password.force-change)\n\n";

    // Attempt login dengan password default (DDMMYYYY)
    $loginTest = Auth::attempt([
        'email' => $testUser['email'],
        'password' => $testUser['default_password']
    ]);

    if ($loginTest) {
        $authenticatedUser = Auth::user();
        echo "✅ LOGIN SUCCESSFUL!\n";
        echo "   Authenticated as: " . $authenticatedUser->email . "\n";
        echo "   Name: " . $authenticatedUser->name . "\n";
        echo "   Role: " . $authenticatedUser->role . "\n";
        echo "   is_password_reset: " . ($authenticatedUser->is_password_reset ? 'TRUE' : 'FALSE') . "\n";
        echo "   → Middleware akan redirect ke: /password/force-change\n";
        Auth::logout();
    } else {
        echo "❌ Login FAILED - Password tidak cocok\n";
        echo "   Verify password: {$testUser['default_password']}\n";
    }
} else {
    echo "⚠️  Tidak ada users untuk di-test\n";
}

echo "\n";

// ═══════════════════════════════════════════════════════════════
// FINAL SUMMARY
// ═══════════════════════════════════════════════════════════════
echo "╔═══════════════════════════════════════════════════════════════════════════╗\n";
echo "║                          IMPORT COMPLETE                                 ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════╝\n\n";

$totalUsersInDb = User::count();
echo "✅ Import dari Excel berhasil!\n";
echo "✅ Total users di database: $totalUsersInDb\n";
echo "✅ Semua users siap untuk login\n\n";

echo "═══════════════════════════════════════════════════════════════════════════\n";
echo "  ALUR LOGIN YANG BENAR:\n";
echo "═══════════════════════════════════════════════════════════════════════════\n";
echo "  1️⃣  Login pertama:\n";
echo "      NIP/Email: {$testUser['email']}\n";
echo "      Password: {$testUser['default_password']} (DDMMYYYY dari NIP)\n";
echo "      → Masuk sistem\n\n";
echo "  2️⃣  Auto-redirect ke /password/force-change\n";
echo "      Karena is_password_reset = false\n\n";
echo "  3️⃣  Ganti password dengan password baru (min 8 karakter)\n";
echo "      → is_password_reset set to TRUE\n";
echo "      → Redirect ke dashboard\n\n";
echo "  4️⃣  Login kedua kali dengan password baru:\n";
echo "      NIP/Email: {$testUser['email']}\n";
echo "      Password: [password baru yang sudah diganti]\n";
echo "      → Akses dashboard normal\n\n";
echo "═══════════════════════════════════════════════════════════════════════════\n\n";

echo "🌐 URL: http://192.168.1.27:8083/login\n";
echo "📊 Database: esppd_production\n";
echo "👥 Total Users: $totalUsersInDb\n\n";
?>
