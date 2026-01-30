<?php
require 'vendor/autoload.php';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('md/DatabaseDosen.xlsx');
$worksheet = $spreadsheet->getActiveSheet();
$highestColumn = $worksheet->getHighestColumn();
$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

echo "Headings:\n";
for ($col = 1; $col <= $highestColumnIndex; $col++) {
    echo "Col $col: " . $worksheet->getCellByColumnAndRow($col, 1)->getValue() . "\n";
}
