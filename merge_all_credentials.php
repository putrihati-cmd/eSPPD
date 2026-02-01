<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Styles\Font;
use PhpOffice\PhpSpreadsheet\Styles\PatternFill;

// Load environment
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Setup database connection
$db = new DB;
$db->addConnection([
    'driver' => 'pgsql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'esppd'),
    'username' => env('DB_USERNAME', 'postgres'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
]);
$db->setAsGlobal();

echo "====== MERGE ALL LOGIN CREDENTIALS ======\n\n";

// Get all users from database
try {
    $query = "SELECT u.id, u.nip, u.name, u.email, e.birth_date
              FROM users u
              LEFT JOIN employees e ON u.id = e.user_id
              ORDER BY u.nip";
    $users = DB::select($query);

    echo "Total users in database: " . count($users) . "\n";
    echo "Expected: 461 users\n\n";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

if (empty($users)) {
    echo "❌ No users found!\n";
    exit(1);
}

// Create spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Login Credentials');

// Set headers
$headers = ['No', 'NIP', 'Nama Lengkap', 'Email', 'Tanggal Lahir', 'Password Format (NIP+DDMMYYYY)', 'Status'];
$cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

// Header formatting
$headerFill = new PatternFill();
$headerFill->setFillType(PatternFill::FILL_SOLID);
$headerFill->getStartColor()->setARGB('FF4472C4');

$headerFont = new Font();
$headerFont->setBold(true);
$headerFont->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));

foreach ($cols as $index => $col) {
    $cell = $col . '1';
    $sheet->setCellValue($cell, $headers[$index]);
    $sheet->getStyle($cell)->setFont($headerFont);
    $sheet->getStyle($cell)->setFill($headerFill);
}

// Populate data
$row = 2;
$count = 0;
$with_birth = 0;
$without_birth = 0;

echo "Processing users...\n";
$progressInterval = max(1, intdiv(count($users), 10));

foreach ($users as $idx => $user) {
    // Skip users without NIP
    if (!$user->nip) {
        continue;
    }

    $count++;

    if ($count % $progressInterval === 0) {
        echo "  Processed: {$count} users\n";
    }

    // Generate password: NIP + DDMMYYYY (birth_date)
    $password = '';
    $birthDateStr = '';
    $status = '';

    if ($user->birth_date) {
        try {
            $birthDate = new \DateTime($user->birth_date);
            $ddmmyyyy = $birthDate->format('dmY');
            $password = trim($user->nip) . $ddmmyyyy;
            $birthDateStr = $user->birth_date;
            $status = '✓ Complete';
            $with_birth++;
        } catch (Exception $e) {
            $password = 'ERROR';
            $status = '✗ Invalid date';
            $without_birth++;
        }
    } else {
        $password = 'PENDING';
        $status = '⚠ No birth date';
        $without_birth++;
    }

    $sheet->setCellValue('A' . $row, $count);
    $sheet->setCellValue('B' . $row, trim($user->nip));
    $sheet->setCellValue('C' . $row, $user->name);
    $sheet->setCellValue('D' . $row, $user->email ?? '-');
    $sheet->setCellValue('E' . $row, $birthDateStr ?? '-');
    $sheet->setCellValue('F' . $row, $password);
    $sheet->setCellValue('G' . $row, $status);

    $row++;
}

echo "  Processed: {$count} users (complete)\n\n";

// Auto-size columns
foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Save file
$filename = __DIR__ . '/LOGIN_CREDENTIALS_ALL_' . date('Ymd_His') . '.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($filename);

echo "✅ MERGE COMPLETE\n";
echo str_repeat("=", 70) . "\n";
echo "File: " . basename($filename) . "\n";
echo "Location: {$filename}\n";
echo str_repeat("=", 70) . "\n";

echo "\n📊 SUMMARY\n";
echo "━" . str_repeat("━", 68) . "━\n";
echo "Total users exported:       " . $count . "\n";
echo "Users dengan birth date:    " . $with_birth . "\n";
echo "Users tanpa birth date:     " . $without_birth . "\n";

if ($without_birth > 0) {
    echo "\n⚠️  Perhatian: " . $without_birth . " user(s) belum punya tanggal lahir\n";
    echo "   Status password: PENDING\n";
    echo "   Diperlukan import data dari file Excel untuk melengkapi\n";
}

echo "\n✅ File siap digunakan!\n";
