<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

// Get statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM patients");
$stmt->execute();
$total_patients = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM checkups");
$stmt->execute();
$total_checkups = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM predictions");
$stmt->execute();
$total_predictions = $stmt->get_result()->fetch_assoc()['total'];

// Get prediction statistics
$stmt = $conn->prepare("
    SELECT 
        status,
        COUNT(*) as count,
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM predictions), 2) as percentage
    FROM predictions 
    GROUP BY status
");
$stmt->execute();
$prediction_stats = $stmt->get_result();

// Get all predictions for export
$stmt = $conn->prepare("
    SELECT p.*, pt.name as patient_name, pt.age, pt.gender, pt.address, pt.phone
    FROM predictions p 
    JOIN patients pt ON p.patient_id = pt.id 
    ORDER BY p.created_at DESC
");
$stmt->execute();
$all_predictions = $stmt->get_result();

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set document properties
$spreadsheet->getProperties()->setCreator('SPK Hipertensi Predictor')
    ->setLastModifiedBy($_SESSION['user_name'] ?? 'Operator')
    ->setTitle('Laporan Prediksi Hipertensi')
    ->setSubject('Laporan Prediksi Hipertensi')
    ->setDescription('Laporan lengkap prediksi hipertensi beserta statistik.');

// Styles
$titleStyle = [
    'font' => ['bold' => true, 'size' => 14],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
];
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$borderStyle = [
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];

// 1. Report Header
$sheet->setCellValue('A1', 'LAPORAN PREDIKSI HIPERTENSI');
$sheet->mergeCells('A1:H1');
$sheet->getStyle('A1')->applyFromArray($titleStyle);

$sheet->setCellValue('A2', 'Tanggal Cetak: ' . date('d/m/Y H:i'));
$sheet->mergeCells('A2:H2');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// 2. System Statistics
$sheet->setCellValue('A4', 'STATISTIK SISTEM');
$sheet->getStyle('A4')->getFont()->setBold(true);

$statsHeader = ['Total Pasien', 'Total Pengecekan', 'Total Prediksi', 'Rasio Prediksi'];
$sheet->fromArray($statsHeader, NULL, 'A5');
$sheet->getStyle('A5:D5')->applyFromArray($headerStyle);

$statsData = [
    $total_patients,
    $total_checkups,
    $total_predictions,
    ($total_predictions > 0 ? round(($total_predictions / $total_patients) * 100, 1) : 0) . '%'
];
$sheet->fromArray($statsData, NULL, 'A6');
$sheet->getStyle('A6:D6')->applyFromArray($borderStyle);
$sheet->getStyle('A6:D6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// 3. Prediction Statistics
$sheet->setCellValue('A9', 'STATISTIK PREDIKSI');
$sheet->getStyle('A9')->getFont()->setBold(true);

$predStatsHeader = ['Status', 'Jumlah', 'Persentase'];
$sheet->fromArray($predStatsHeader, NULL, 'A10');
$sheet->getStyle('A10:C10')->applyFromArray($headerStyle);

$row = 11;
while ($stat = $prediction_stats->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $stat['status']);
    $sheet->setCellValue('B' . $row, $stat['count']);
    $sheet->setCellValue('C' . $row, $stat['percentage'] . '%');
    $row++;
}
$sheet->getStyle('A11:C' . ($row - 1))->applyFromArray($borderStyle);
$sheet->getStyle('B11:C' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// 4. Detailed Predictions
$detailRow = $row + 3;
$sheet->setCellValue('A' . $detailRow, 'DETAIL DATA PREDIKSI');
$sheet->getStyle('A' . $detailRow)->getFont()->setBold(true);

$detailRow++;
$detailHeader = ['No', 'Tanggal', 'Nama Pasien', 'Umur', 'Gender', 'Status', 'Probabilitas', 'Confidence'];
$sheet->fromArray($detailHeader, NULL, 'A' . $detailRow);
$sheet->getStyle('A' . $detailRow . ':H' . $detailRow)->applyFromArray($headerStyle);

$no = 1;
$dataRow = $detailRow + 1;
while ($prediction = $all_predictions->fetch_assoc()) {
    $sheet->setCellValue('A' . $dataRow, $no++);
    $sheet->setCellValue('B' . $dataRow, date('d/m/Y H:i', strtotime($prediction['created_at'])));
    $sheet->setCellValue('C' . $dataRow, $prediction['patient_name']);
    $sheet->setCellValue('D' . $dataRow, $prediction['age']);
    $sheet->setCellValue('E' . $dataRow, $prediction['gender']);
    $sheet->setCellValue('F' . $dataRow, $prediction['status']);
    $sheet->setCellValue('G' . $dataRow, number_format($prediction['probability'] * 100, 2) . '%');
    $sheet->setCellValue('H' . $dataRow, number_format($prediction['confidence'] * 100, 2) . '%');
    
    // Color coding for status
    $statusColor = 'FFFFFF';
    if ($prediction['status'] == 'Sangat Berpotensi') $statusColor = 'FEE2E2'; // Red-100
    elseif ($prediction['status'] == 'Cukup Berpotensi') $statusColor = 'FEF3C7'; // Yellow-100
    elseif ($prediction['status'] == 'Tidak Berpotensi') $statusColor = 'D1FAE5'; // Green-100
    
    if ($statusColor != 'FFFFFF') {
        $sheet->getStyle('F' . $dataRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($statusColor);
    }
    
    $dataRow++;
}

// Apply borders to data table
$sheet->getStyle('A' . $detailRow . ':H' . ($dataRow - 1))->applyFromArray($borderStyle);

// Auto size columns
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Clear any previous output to ensure clean file download
if (ob_get_length()) ob_end_clean();

// Redirect output to a client’s web browser (Xlsx)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Laporan_Hipertensi_' . date('Y-m-d') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?> 