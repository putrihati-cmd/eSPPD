<?php

/**
 * Test Login on Production
 * Uses session cookies to simulate real browser login
 */

echo "=== TESTING LOGIN ON PRODUCTION ===\n\n";

// Test accounts with credentials
$testLogins = [
    [
        'name' => 'Pegawai',
        'nip' => '197505051999031001',
        'password' => 'Testing@123',
        'expected_role' => 'employee',
    ],
    [
        'name' => 'Kaprodi',
        'nip' => '196803201990031003',
        'password' => 'Testing@123',
        'expected_role' => 'kabag',
    ],
    [
        'name' => 'Wadek',
        'nip' => '195811081988031004',
        'password' => 'Testing@123',
        'expected_role' => 'wadek',
    ],
    [
        'name' => 'Dekan',
        'nip' => '195508151985031005',
        'password' => 'Testing@123',
        'expected_role' => 'dekan',
    ],
    [
        'name' => 'Admin',
        'nip' => '194508170000000000',
        'password' => 'Admin@eSPPD2026',
        'expected_role' => 'admin',
    ],
];

$baseUrl = 'https://esppd.infiatin.cloud';
$cookieFile = tempnam(sys_get_temp_dir(), 'curl_');

foreach ($testLogins as $test) {
    echo "Testing {$test['name']} login (NIP: {$test['nip']})\n";

    // Step 1: Get login page (to establish session)
    echo "  1. Getting login page...";
    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo " HTTP $httpCode ";

    // Extract CSRF token from login form
    if (preg_match('/<input[^>]*name=["\']_token["\'][^>]*value=["\']([^"\']+)["\']/', $response, $matches)) {
        $csrfToken = $matches[1];
        echo "(CSRF found)";
    } else {
        echo "(NO CSRF FOUND - may fail)";
        $csrfToken = '';
    }
    echo "\n";

    // Step 2: Submit login form
    echo "  2. Submitting login...";

    // The form uses livewire, so we might need to handle that differently
    // But let's try standard form submission first
    $postData = [
        'nip' => $test['nip'],
        'password' => $test['password'],
        'remember' => 'on',
    ];

    if ($csrfToken) {
        $postData['_token'] = $csrfToken;
    }

    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo " HTTP $httpCode ";

    // Check if we got dashboard or stayed on login
    if (strpos($response, 'dashboard') !== false || strpos($response, 'Dashboard') !== false) {
        echo "(Dashboard found - LOGIN SUCCESS)\n";
    } else if (strpos($response, 'password') !== false || strpos($response, 'Login') !== false) {
        echo "(Still on login form - LIKELY FAILED)\n";
    } else {
        echo "(Unknown state)\n";
    }

    echo "\n";
}

// Cleanup
@unlink($cookieFile);

echo "=== TEST COMPLETE ===\n";
