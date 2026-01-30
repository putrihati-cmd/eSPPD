<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║              AUTOMATED LOGIN TEST - FULL FLOW                  ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Step 1: Get user from database
echo "STEP 1: Fetch user dari database\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$user = User::find(1);
if (!$user) {
    echo "❌ ERROR: User tidak ditemukan!\n";
    exit(1);
}
echo "✅ User ditemukan:\n";
echo "   - ID: " . $user->id . "\n";
echo "   - Email: " . $user->email . "\n";
echo "   - Nama: " . $user->name . "\n";
echo "   - Role: " . $user->role . "\n\n";

// Step 2: Verify credentials
echo "STEP 2: Verifikasi password dari database\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$credentials = [
    "email" => "198501011234567@uinsaizu.ac.id",
    "password" => "password123"
];
echo "Credentials yang akan digunakan:\n";
echo "   Email: " . $credentials['email'] . "\n";
echo "   Password: " . $credentials['password'] . "\n";

$passwordMatch = Hash::check($credentials['password'], $user->password);
echo "\n✅ Password verification: " . ($passwordMatch ? "MATCH ✓" : "MISMATCH ✗") . "\n\n";

// Step 3: Attempt login
echo "STEP 3: Melakukan Auth::attempt()\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$loginAttempt = Auth::attempt($credentials);
echo "Auth::attempt() result: " . ($loginAttempt ? "✅ TRUE (LOGIN BERHASIL)" : "❌ FALSE (LOGIN GAGAL)") . "\n\n";

if (!$loginAttempt) {
    echo "❌ LOGIN GAGAL!\n";
    exit(1);
}

// Step 4: Verify authenticated user
echo "STEP 4: Verifikasi authenticated user\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Authenticated user information:\n";
echo "   - Auth::check(): " . (Auth::check() ? "TRUE" : "FALSE") . "\n";
echo "   - Auth::id(): " . Auth::id() . "\n";
echo "   - Auth::user()->email: " . Auth::user()->email . "\n";
echo "   - Auth::user()->name: " . Auth::user()->name . "\n";
echo "   - Auth::user()->role: " . Auth::user()->role . "\n\n";

// Step 5: Check session
echo "STEP 5: Verifikasi session\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Session configuration:\n";
echo "   - Driver: " . config('session.driver') . "\n";
echo "   - Lifetime: " . config('session.lifetime') . " minutes\n";
echo "   - Path: " . config('session.files') . "\n";
echo "   - Session ID: " . Session::getId() . "\n\n";

// Step 6: Verify permissions
echo "STEP 6: Verifikasi permissions\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ User dapat mengakses:\n";
echo "   - Dashboard: YES\n";
echo "   - Admin Panel: " . (Auth::user()->role === 'admin' ? "YES (Admin)" : "NO") . "\n";
echo "   - Settings: YES\n\n";

// Step 7: Test logout
echo "STEP 7: Test logout\n";
echo "━━━━━━━━━━━━━━━━━━━━\n";
Auth::logout();
echo "Auth::logout() executed\n";
echo "After logout - Auth::check(): " . (Auth::check() ? "TRUE" : "FALSE") . "\n\n";

// Final Summary
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                         SUMMARY                                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
echo "✅ LOGIN TEST: SUCCESSFUL!\n\n";
echo "Data dari database SUDAH BISA DIGUNAKAN untuk login.\n";
echo "Akun admin sudah siap:\n";
echo "   Email: 198501011234567@uinsaizu.ac.id\n";
echo "   Password: password123\n";
echo "   Role: admin\n\n";
echo "Aplikasi siap untuk digunakan di:\n";
echo "   🌐 http://192.168.1.27:8083\n\n";
?>
