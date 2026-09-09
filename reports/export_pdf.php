<?php
ob_start();
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../config/database.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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
    SELECT p.*, pt.name as patient_name, pt.age, pt.gender
    FROM predictions p 
    JOIN patients pt ON p.patient_id = pt.id 
    ORDER BY p.created_at DESC
");
$stmt->execute();
$all_predictions = $stmt->get_result();

// Setup DOMPDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Prepare HTML Content
$html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Prediksi Hipertensi</title>
    <style>
        body { font-family: sans-serif; color: #333; line-height: 1.5; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; }
        .header h1 { color: #4f46e5; margin: 0; font-size: 24px; text-transform: uppercase; }
        .header p { margin: 5px 0; color: #666; font-size: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
        th, td { padding: 8px 10px; border: 1px solid #e2e8f0; text-align: left; }
        th { background-color: #f8fafc; color: #475569; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        tr:nth-child(even) { background-color: #f8fafc; }
        
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 10px; font-weight: bold; color: white; }
        .bg-green { background-color: #10b981; }
        .bg-red { background-color: #ef4444; }
        .bg-yellow { background-color: #f59e0b; }
        .bg-blue { background-color: #3b82f6; }
        .bg-gray { background-color: #6b7280; }
        
        .section-title { font-size: 14px; font-weight: bold; color: #4f46e5; margin-bottom: 10px; border-left: 4px solid #4f46e5; padding-left: 8px; }
        
        .stats-grid { width: 100%; margin-bottom: 20px; }
        .stats-grid td { width: 25%; padding: 10px; text-align: center; border: 1px solid #e2e8f0; background-color: #f8fafc; }
        .stat-number { font-size: 20px; font-weight: bold; color: #4f46e5; display: block; }
        .stat-label { font-size: 10px; color: #64748b; text-transform: uppercase; }
        
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . APP_NAME . '</h1>
        <p>Sistem Pakar Prediksi Risiko Hipertensi - Puskesmas Pekan Heran</p>
        <p>Laporan Statistik & Riwayat Prediksi</p>
    </div>
    
    <div class="section-title">STATISTIK SISTEM</div>
    <table class="stats-grid">
        <tr>
            <td>
                <span class="stat-number">' . $total_patients . '</span>
                <span class="stat-label">Total Pasien</span>
            </td>
            <td>
                <span class="stat-number">' . $total_checkups . '</span>
                <span class="stat-label">Total Pengecekan</span>
            </td>
            <td>
                <span class="stat-number">' . $total_predictions . '</span>
                <span class="stat-label">Total Prediksi</span>
            </td>
            <td>
                <span class="stat-number">' . ($total_predictions > 0 ? round(($total_predictions / $total_patients) * 100, 1) : 0) . '%</span>
                <span class="stat-label">Rasio Prediksi</span>
            </td>
        </tr>
    </table>
    
    <div class="section-title">STATISTIK PREDIKSI</div>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Jumlah</th>
                <th>Persentase</th>
            </tr>
        </thead>
        <tbody>';

while ($stat = $prediction_stats->fetch_assoc()) {
    $html .= '<tr>
        <td>' . $stat['status'] . '</td>
        <td>' . $stat['count'] . '</td>
        <td>' . $stat['percentage'] . '%</td>
    </tr>';
}

$html .= '</tbody>
    </table>
    
    <div class="section-title">DETAIL RIWAYAT PREDIKSI</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 25%;">Nama Pasien</th>
                <th style="width: 10%;">Umur</th>
                <th style="width: 10%;">Gender</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 10%;">Probabilitas</th>
                <th style="width: 10%;">Confidence</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
while ($prediction = $all_predictions->fetch_assoc()) {
    $status_color = 'bg-green';
    if ($prediction['status'] == 'Sangat Berpotensi') $status_color = 'bg-red';
    elseif ($prediction['status'] == 'Cukup Berpotensi') $status_color = 'bg-yellow';
    
    $html .= '<tr>
        <td>' . $no++ . '</td>
        <td>' . date('d/m/Y H:i', strtotime($prediction['created_at'])) . '</td>
        <td>' . htmlspecialchars($prediction['patient_name']) . '</td>
        <td>' . $prediction['age'] . '</td>
        <td>' . ($prediction['gender'] == 'L' ? 'L' : 'P') . '</td>
        <td><span class="badge ' . $status_color . '">' . $prediction['status'] . '</span></td>
        <td>' . number_format($prediction['probability'] * 100, 2) . '%</td>
        <td>' . number_format($prediction['confidence'] * 100, 2) . '%</td>
    </tr>';
}

$html .= '</tbody>
    </table>
    
    <div class="footer">
        Dicetak pada: ' . date('d/m/Y H:i:s') . ' | Oleh: ' . ($_SESSION['user_name'] ?? 'System') . '
    </div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Clean any previous output to prevent PDF corruption
ob_end_clean();

$filename = 'Laporan_Prediksi_' . date('Y-m-d') . '.pdf';
$dompdf->stream($filename, ["Attachment" => false]); // false = preview in browser, true = download
?>