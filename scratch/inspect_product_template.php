<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'app/templates/Inventory_Product_Import_Template.xlsx';
if (!file_exists($file)) {
    echo "File not found\n";
    exit;
}

$reader = IOFactory::createReaderForFile($file);
$spreadsheet = $reader->load($file);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray();

echo "Sheet Name: " . $sheet->getTitle() . "\n";
foreach (array_slice($rows, 0, 10) as $i => $row) {
    echo "Row " . ($i + 1) . ": " . implode(' | ', array_map(function($v) { return $v === null ? 'NULL' : $v; }, $row)) . "\n";
}
