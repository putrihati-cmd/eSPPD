<?php

/**
 * Comprehensive login button inspection
 * Run: php artisan tinker < this_file.php
 */

// 1. Check button HTML structure
echo "=" . str_repeat("=", 68) . "\n";
echo "BUTTON INSPECTION TEST\n";
echo "=" . str_repeat("=", 68) . "\n\n";

// Read the login.blade.php file
$loginFile = __DIR__ . '/resources/views/livewire/pages/auth/login.blade.php';
$content = file_get_contents($loginFile);

// 1. Check for submit button
echo "[CHECK 1] Submit Button\n";
if (strpos($content, 'type="submit"') !== false) {
    echo "✓ Submit button found\n";

    // Extract button code
    if (preg_match('/<button[^>]*type="submit"[^>]*>.*?<\/button>/is', $content, $matches)) {
        echo "  Code snippet:\n";
        echo "  " . substr($matches[0], 0, 120) . "...\n";

        // Check for wire:loading attributes
        if (strpos($matches[0], 'wire:loading') !== false) {
            echo "  ✓ Has wire:loading attributes\n";
        }

        // Check button classes
        if (preg_match('/class="([^"]*)"/', $matches[0], $classMatch)) {
            $classes = $classMatch[1];
            echo "  Classes: " . substr($classes, 0, 100) . "...\n";

            // Check important classes
            $checks = [
                'w-full' => 'Full width',
                'h-14' => 'Height set',
                'rounded-2xl' => 'Border radius',
                'disabled:opacity-70' => 'Disabled styling',
            ];

            foreach ($checks as $class => $desc) {
                echo "    - $desc ($class): " . (strpos($classes, $class) !== false ? '✓' : '✗') . "\n";
            }
        }
    }
} else {
    echo "✗ Submit button NOT found\n";
}

// 2. Check password toggle button
echo "\n[CHECK 2] Password Toggle Button\n";
if (preg_match('/<button[^>]*wire:click="togglePasswordVisibility"[^>]*>/is', $content, $matches)) {
    echo "✓ Password toggle button found\n";
    echo "  Type: " . (strpos($matches[0], 'type="button"') ? 'button' : 'UNDEFINED') . "\n";

    // Check if button has proper classes
    if (preg_match('/class="([^"]*)"/', $matches[0], $classMatch)) {
        $classes = $classMatch[1];
        echo "  Classes include:\n";
        echo "    - Positioning (absolute): " . (strpos($classes, 'absolute') !== false ? '✓' : '✗') . "\n";
        echo "    - Hover effect: " . (strpos($classes, 'hover') !== false ? '✓' : '✗') . "\n";
    }
} else {
    echo "✗ Password toggle button NOT found\n";
}

// 3. Check "Lupa password" link
echo "\n[CHECK 3] Forgot Password Link\n";
if (strpos($content, 'password.request') !== false) {
    echo "✓ Forgot password link found\n";

    if (preg_match('/<a[^>]*href="{{[^}]*password\.request[^}]*}}"[^>]*>([^<]*)<\/a>/is', $content, $matches)) {
        echo "  Link text: " . trim($matches[1]) . "\n";
        echo "  Route: password.request\n";
    }
} else {
    echo "✗ Forgot password link NOT found\n";
}

// 4. Check form wire:submit
echo "\n[CHECK 4] Form Submission\n";
if (preg_match('/<form[^>]*wire:submit="([^"]*)"[^>]*>/is', $content, $matches)) {
    echo "✓ Form wire:submit found\n";
    echo "  Method: " . $matches[1] . "\n";
} else {
    echo "✗ Form wire:submit NOT found\n";
}

// 5. Check inputs
echo "\n[CHECK 5] Form Inputs\n";
$nipInput = preg_match('/id="nip".*?wire:model="nip"/is', $content);
$passInput = preg_match('/id="password".*?wire:model="password"/is', $content);
echo "  NIP input: " . ($nipInput ? '✓' : '✗') . "\n";
echo "  Password input: " . ($passInput ? '✓' : '✗') . "\n";

// 6. Check for any syntax errors in the blade file
echo "\n[CHECK 6] Blade Syntax\n";
$lines = file($loginFile, FILE_IGNORE_NEW_LINES);
$bracketStack = [];
$errors = [];

foreach ($lines as $lineNum => $line) {
    // Simple check for mismatched brackets
    $openCount = substr_count($line, '{{') + substr_count($line, '{%') + substr_count($line, '@');
    $closeCount = substr_count($line, '}}') + substr_count($line, '%}');
}

echo "✓ File parsed successfully\n";
echo "  Total lines: " . count($lines) . "\n";

echo "\n" . "=" . str_repeat("=", 68) . "\n";
echo "INSPECTION COMPLETE\n";
echo "=" . str_repeat("=", 68) . "\n";
