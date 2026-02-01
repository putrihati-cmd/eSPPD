<?php
echo "TEST START\n";
require 'vendor/autoload.php';
echo "Autoload loaded\n";

try {
    $app = require_once 'bootstrap/app.php';
    echo "App bootstrapped\n";
} catch (Exception $e) {
    echo "Error bootstrapping: " . $e->getMessage() . "\n";
    exit(1);
}

echo "TEST END\n";
