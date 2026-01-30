<?php
require 'vendor/autoload.php';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('md/DatabaseDosen.xlsx');
$worksheet = $spreadsheet->getActiveSheet();
echo "Col 3 Row 2: " . $worksheet->getCellByColumnAndRow(3, 2)->getValue() . "\n";
echo "Col 6 Row 2: " . $worksheet->getCellByColumnAndRow(6, 2)->getValue() . "\n";
