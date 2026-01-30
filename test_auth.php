<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

echo "=== PASSWORD VERIFICATION ===\n";
$user = User::find(1);
if (!$user) {
    echo "ERROR: User ID 1 not found\n";
    exit(1);
}

echo "User Email: " . $user->email . "\n";
echo "User Role: " . $user->role . "\n";
echo "Password Hash: " . substr($user->password, 0, 30) . "...\n";

$passwordCorrect = Hash::check("password123", $user->password);
echo "Password 'password123' matches: " . ($passwordCorrect ? "YES ✓" : "NO ✗") . "\n";

echo "\n=== AUTH ATTEMPT ===\n";
$credentials = [
    "email" => "198501011234567@uinsaizu.ac.id",
    "password" => "password123"
];

if (Auth::attempt($credentials)) {
    echo "Auth::attempt SUCCESS ✓\n";
    echo "Authenticated user: " . Auth::user()->email . "\n";
} else {
    echo "Auth::attempt FAILED ✗\n";
    echo "\nChecking why...\n";

    // Manual check
    $userFound = User::where("email", "198501011234567@uinsaizu.ac.id")->first();
    echo "User found by email query: " . ($userFound ? "YES ✓" : "NO ✗") . "\n";

    if ($userFound) {
        $pwMatch = Hash::check("password123", $userFound->password);
        echo "Password matches: " . ($pwMatch ? "YES ✓" : "NO ✗") . "\n";
        echo "User is active: " . ($userFound->active ?? "field doesn't exist") . "\n";
    }
}

echo "\n=== CHECKING AUTH CONFIG ===\n";
echo "Auth Driver: " . config('auth.defaults.guard') . "\n";
echo "Auth Provider: " . config('auth.defaults.provider') . "\n";
echo "User Model: " . config('auth.providers.users.model') . "\n";

echo "\n=== CHECKING LOGINFORM LOGIC ===\n";
// Simulate what LoginForm does
$email = "198501011234567";
$emailToAuth = str_contains($email, '@') ? $email : $email . '@uinsaizu.ac.id';
echo "Input: '$email'\n";
echo "Converted to: '$emailToAuth'\n";
echo "Expected: '198501011234567@uinsaizu.ac.id'\n";
echo "Match: " . ($emailToAuth === "198501011234567@uinsaizu.ac.id" ? "YES ✓" : "NO ✗") . "\n";
?>
