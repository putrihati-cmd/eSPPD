<?php

/**
 * PHP Wrapper untuk Python Microservice: Generate Login Credentials
 *
 * Fungsi: Call Python script untuk export login credentials dari database
 * Output: Excel file dengan format NIP + DDMMYYYY
 */

require __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$pythonScript = __DIR__ . '/python_scripts/export_credentials.py';
$outputDir = __DIR__;
$timestamp = date('Ymd_His');
$outputFile = "{$outputDir}/LOGIN_CREDENTIALS_{$timestamp}.xlsx";

// Prepare environment variables for Python
$env = array_merge($_ENV, [
    'DB_HOST' => env('DB_HOST', '127.0.0.1'),
    'DB_PORT' => env('DB_PORT', '5432'),
    'DB_DATABASE' => env('DB_DATABASE', 'esppd'),
    'DB_USERNAME' => env('DB_USERNAME', 'postgres'),
    'DB_PASSWORD' => env('DB_PASSWORD', ''),
]);

echo "====== GENERATE LOGIN CREDENTIALS (Python Microservice) ======\n\n";

// Check if Python script exists
if (!file_exists($pythonScript)) {
    echo "❌ Python script not found: {$pythonScript}\n";
    exit(1);
}

echo "🐍 Calling Python microservice...\n";
echo "   Script: {$pythonScript}\n";
echo "   Output: {$outputFile}\n\n";

// Execute Python script using PowerShell
// PowerShell handles encoding better for Python unicode output
$psScript = <<<'PS'
param([string]$OutputFile)
cd "C:\laragon\www\eSPPD"
& .\.venv\Scripts\Activate.ps1
python python_scripts/export_credentials.py --output "$OutputFile"
PS;

// Write script to temp file
$scriptFile = sys_get_temp_dir() . '/export_creds_' . uniqid() . '.ps1';
file_put_contents($scriptFile, $psScript);

$command = sprintf(
    'powershell.exe -ExecutionPolicy Bypass -File "%s" -OutputFile "%s"',
    $scriptFile,
    $outputFile
);

// Use proc_open for better output handling
$descriptors = [
    1 => ['pipe', 'w'],  // stdout
    2 => ['pipe', 'w']   // stderr
];

$process = proc_open($command, $descriptors, $pipes);

if (!is_resource($process)) {
    echo "❌ Failed to execute Python script\n";
    exit(1);
}

// Read output
$output = '';
$error = '';

while (!feof($pipes[1])) {
    $output .= fgets($pipes[1]);
}

while (!feof($pipes[2])) {
    $error .= fgets($pipes[2]);
}

$returnCode = proc_close($process);

// Display output
if (!empty($output)) {
    echo $output;
}

if (!empty($error)) {
    echo "\n⚠️ Warnings/Errors:\n";
    echo $error;
}

// Check result
if ($returnCode === 0 && file_exists($outputFile)) {
    echo "\n✅ SUCCESS!\n";
    echo "File size: " . number_format(filesize($outputFile), 0) . " bytes\n";
    exit(0);
} else {
    echo "\n❌ FAILED!\n";
    echo "Return code: {$returnCode}\n";
    exit(1);
}
