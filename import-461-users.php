<?php

/**
 * Import 461 Production Users & Fix Passwords
 * NIP format: TTHHBBTTGGKKKRRRJ
 * - TT = Tahun lahir
 * - HH = Hari lahir
 * - BB = Bulan lahir
 * DDMMYYYY Password = HH + BB + TT
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

echo "\n╔════════════════════════════════════════════════════════════════════╗\n";
echo "║        IMPORT 461 PRODUCTION USERS & FIX PASSWORDS (DDMMYYYY)      ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

// Step 1: Import SQL file
echo "[1/3] Importing 461 users from SQL file...\n";
$sqlFile = __DIR__ . '/output/sql/02_upsert_users.sql';

if (file_exists($sqlFile)) {
    $sql = file_get_contents($sqlFile);
    try {
        DB::unprepared($sql);
        echo "✅ SQL import completed\n\n";
    } catch (\Exception $e) {
        echo "❌ SQL import failed: " . $e->getMessage() . "\n\n";
    }
} else {
    echo "⚠️  SQL file not found: {$sqlFile}\n";
}

// Step 2: Update all users with DDMMYYYY password from NIP
echo "[2/3] Updating 461 users with DDMMYYYY passwords from NIP...\n";

$users = User::all();
$updated = 0;
$errors = 0;

foreach ($users as $user) {
    try {
        // NIP format: TTHHBBTTGGKKKRRRJ (18 digits)
        // Position: 0-1=TT, 2-3=HH, 4-5=BB
        $nip = $user->nip;

        if (strlen($nip) >= 6) {
            $day = substr($nip, 2, 2);     // HH (hari)
            $month = substr($nip, 4, 2);   // BB (bulan)
            $year = substr($nip, 0, 2);    // TT (tahun)

            // Create DDMMYYYY: HH + MM + TT
            $ddmmyyyy = $day . $month . $year;

            // Update password
            $user->password = Hash::make($ddmmyyyy);
            $user->is_password_reset = false; // Force change on first login
            $user->save();
            $updated++;
        }
    } catch (\Exception $e) {
        $errors++;
    }
}

echo "✅ Updated {$updated} users with DDMMYYYY passwords\n";
if ($errors > 0) {
    echo "⚠️  {$errors} errors encountered\n";
}

// Step 3: Verification
echo "\n[3/3] Verifying import...\n";

$totalUsers = User::count();
$totalWithoutReset = User::where('is_password_reset', false)->count();
$totalWithReset = User::where('is_password_reset', true)->count();

echo "✅ Total users in database: {$totalUsers}\n";
echo "✅ Users requiring password change: {$totalWithoutReset}\n";
echo "✅ Users already changed password: {$totalWithReset}\n";

// Sample data
echo "\n" . str_repeat("─", 70) . "\n";
echo "SAMPLE USERS (First 5):\n";
echo str_repeat("─", 70) . "\n";

$samples = User::orderBy('nip')->limit(5)->get();
foreach ($samples as $i => $user) {
    $nip = $user->nip;
    $day = substr($nip, 2, 2);
    $month = substr($nip, 4, 2);
    $year = substr($nip, 0, 2);
    $ddmmyyyy = $day . $month . $year;

    echo "\n[" . ($i + 1) . "] {$user->name}\n";
    echo "    NIP: {$nip}\n";
    echo "    Email: {$user->email}\n";
    echo "    Password (DDMMYYYY): {$ddmmyyyy}\n";
    echo "    Force Change: " . ($user->is_password_reset ? "No" : "Yes") . "\n";
}

echo "\n" . str_repeat("═", 70) . "\n";
echo "✅ READY FOR TESTING!\n";
echo "Go to: https://esppd.infiatin.cloud/login\n";
echo "Use any NIP + DDMMYYYY password from above\n";
echo str_repeat("═", 70) . "\n\n";
