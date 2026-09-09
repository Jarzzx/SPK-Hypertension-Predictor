<?php
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set document properties
$spreadsheet->getProperties()->setCreator('SPK Hipertensi Predictor')
    ->setLastModifiedBy('SPK Hipertensi Predictor')
    ->setTitle('Template Import Data Pasien')
    ->setSubject('Template Import Data Pasien')
    ->setDescription('Template untuk import data pasien SPK Hipertensi Predictor')
    ->setKeywords('template import pasien');

// Set Header
$headers = ['Nama Lengkap', 'Umur', 'Jenis Kelamin', 'Alamat', 'Telepon'];
$sheet->fromArray($headers, NULL, 'A1');

// Style Header
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4F46E5'], // Indigo-600
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
];
$sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
$sheet->getRowDimension('1')->setRowHeight(25);

// Add sample data (optional, or just comments)
// $sheet->setCellValue('A2', 'Contoh: Budi Santoso');
// $sheet->setCellValue('B2', '45');
// $sheet->setCellValue('C2', 'L');
// $sheet->setCellValue('D2', 'Jl. Merdeka No. 1');
// $sheet->setCellValue('E2', '08123456789');

// Data Validation for Gender (L/P)
// Apply to rows 2-1000
$validation = $sheet->getCell('C2')->getDataValidation();
$validation->setType(DataValidation::TYPE_LIST);
$validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
$validation->setAllowBlank(false);
$validation->setShowInputMessage(true);
$validation->setShowErrorMessage(true);
$validation->setShowDropDown(true);
$validation->setErrorTitle('Input Error');
$validation->setError('Nilai harus L atau P.');
$validation->setPromptTitle('Pilih Jenis Kelamin');
$validation->setPrompt('Pilih L untuk Laki-laki atau P untuk Perempuan.');
$validation->setFormula1('"L,P"');

// Clone validation to other rows
for ($i = 3; $i <= 1000; $i++) {
    $sheet->getCell("C$i")->setDataValidation(clone $validation);
}

// Add comments/instructions
$sheet->getComment('A1')->getText()->createTextRun('Isi dengan nama lengkap pasien');
$sheet->getComment('B1')->getText()->createTextRun('Isi dengan angka umur (1-150)');
$sheet->getComment('C1')->getText()->createTextRun('Pilih L atau P');
$sheet->getComment('D1')->getText()->createTextRun('Alamat lengkap');
$sheet->getComment('E1')->getText()->createTextRun('Nomor telepon (opsional)');

// Auto size columns
foreach (range('A', 'E') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Redirect output to a client’s web browser (Xlsx)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Template_Import_Pasien.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
