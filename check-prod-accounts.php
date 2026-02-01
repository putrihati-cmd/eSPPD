<?php

/**
 * Quick Database Status Check
 */
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "\n=== PRODUCTION ACCOUNTS READY ===\n\n";

$prodAccounts = [
    ['email' => 'mawikhusni@uinsaizu.ac.id', 'name' => 'Mawi Khusni (Admin)', 'password_ddmmyyyy' => '08021983'],
    ['email' => 'ansori@uinsaizu.ac.id', 'name' => 'Ansori (Dekan)', 'password_ddmmyyyy' => '15051975'],
    ['email' => 'ahmadfauzi@uinsaizu.ac.id', 'name' => 'Ahmad Fauzi (Dosen)', 'password_ddmmyyyy' => '20111988'],
    ['email' => 'sitinurhaliza@uinsaizu.ac.id', 'name' => 'Siti Nurhaliza (Dosen)', 'password_ddmmyyyy' => '15031990'],
    ['email' => 'budisantoso@uinsaizu.ac.id', 'name' => 'Budi Santoso (Dosen)', 'password_ddmmyyyy' => '01051995'],
];

$count = 0;
foreach ($prodAccounts as $acc) {
    $user = User::where('email', $acc['email'])->first();
    if ($user) {
        echo "✅ {$acc['name']}\n";
        echo "   Email: {$acc['email']}\n";
        echo "   NIP: {$user->nip}\n";
        echo "   Password: {$acc['password_ddmmyyyy']} (DDMMYYYY)\n";
        echo "   Role: {$user->role}\n";
        echo "   Force Change: " . ($user->is_password_reset ? "No (login to dashboard)" : "Yes (forced on first login)") . "\n";
        echo "\n";
        $count++;
    }
}

echo "Total Production Users: {$count}\n";
if ($count === count($prodAccounts)) {
    echo "✅ All production accounts ready!\n\n";
    echo "LOGIN FLOW:\n";
    echo "1. Go to http://localhost:8000/login\n";
    echo "2. Enter NIP (18 digits) + Password (DDMMYYYY from birth_date)\n";
    echo "3. If force_change=Yes: will redirect to password change page\n";
    echo "4. Change password, then go to dashboard\n";
}
