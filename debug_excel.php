<?php
require 'vendor/autoload.php';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('md/DatabaseDosen.xlsx');
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();
echo "Total Rows: " . count($rows) . "\n";
echo "Header (Row 1):\n";
print_r($rows[0]);
echo "\nRow 2:\n";
print_r($rows[1]);
