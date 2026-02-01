<?php

// Direct database connection without full Laravel bootstrap
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
$db->bootEloquent();

echo "====== GENERATE LOGIN CREDENTIALS ======\n\n";

// Get all users with their birth dates from employees table
$query = "SELECT u.id, u.nip, u.name, u.email, e.birth_date
          FROM users u
          LEFT JOIN employees e ON u.id = e.user_id
          ORDER BY u.nip";
$users = DB::select($query);

echo "Total users found: " . count($users) . "\n\n";

if (empty($users)) {
    echo "⚠ No users found in database!\n";
    exit(1);
}

// Create spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers
$headers = ['No', 'NIP', 'Nama', 'Email', 'Tanggal Lahir', 'Password Format', 'Catatan'];
$cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

foreach ($cols as $index => $col) {
    $sheet->setCellValue($col . '1', $headers[$index]);
    $sheet->getStyle($col . '1')->getFont()->setBold(true);
    $sheet->getStyle($col . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
    $sheet->getStyle($col . '1')->getFill()->getStartColor()->setARGB('FFD3D3D3');
}

// Populate data
$row = 2;
$count = 0;
$missingDates = 0;

foreach ($users as $user) {
    // Skip users without NIP
    if (!$user->nip) {
        continue;
    }

    $count++;

    // Generate password: NIP + DDMMYYYY (birth_date)
    $password = '';
    $birthDateStr = '';
    $note = '';

    if ($user->birth_date) {
        try {
            $birthDate = new DateTime($user->birth_date);
            $ddmmyyyy = $birthDate->format('dmY');
            $password = $user->nip . $ddmmyyyy;
            $birthDateStr = $user->birth_date;
        } catch (Exception $e) {
            $password = 'ERROR_INVALID_DATE';
            $note = 'Format tanggal salah: ' . $user->birth_date;
        }
    } else {
        $password = 'NO_BIRTH_DATE';
        $note = 'Perlu input tanggal lahir';
        $missingDates++;
    }

    $sheet->setCellValue('A' . $row, $count);
    $sheet->setCellValue('B' . $row, trim($user->nip));
    $sheet->setCellValue('C' . $row, $user->name);
    $sheet->setCellValue('D' . $row, $user->email);
    $sheet->setCellValue('E' . $row, $birthDateStr ?? '-');
    $sheet->setCellValue('F' . $row, $password);
    $sheet->setCellValue('G' . $row, $note);

    echo "[" . str_pad($count, 3, '0', STR_PAD_LEFT) . "] "
        . str_pad(trim($user->nip), 20) . " | "
        . str_pad($user->name, 30) . " | "
        . str_pad($birthDateStr ?? 'NO DATE', 12) . " → "
        . $password . "\n";

    $row++;
}

// Adjust column widths
$sheet->getColumnDimension('A')->setWidth(6);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(35);
$sheet->getColumnDimension('D')->setWidth(30);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(30);
$sheet->getColumnDimension('G')->setWidth(30);

// Save file
$filename = __DIR__ . '/LOGIN_CREDENTIALS_' . date('Ymd_His') . '.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($filename);

echo "\n" . str_repeat("=", 70) . "\n";
echo "✓ File saved: " . basename($filename) . "\n";
echo "✓ Location: " . $filename . "\n";
echo str_repeat("=", 70) . "\n";

echo "\n📊 SUMMARY\n";
echo "━" . str_repeat("━", 68) . "━\n";
echo "Total users:                " . count($users) . "\n";
echo "Users dengan birth date:    " . ($count - $missingDates) . "\n";
echo "Users tanpa birth date:     " . $missingDates . "\n";

if ($missingDates > 0) {
    echo "\n⚠  Perhatian: " . $missingDates . " user(s) perlu input tanggal lahir\n";
    echo "   untuk format password NIP+DDMMYYYY\n";
}

echo "\n✅ File siap digunakan untuk login credentials!\n";
