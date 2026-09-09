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

$mode = isset($_GET['id']) ? 'single' : 'all';
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

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
    <title>Laporan Data Pasien</title>
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
        .info-grid { width: 100%; margin-bottom: 20px; }
        .info-grid td { border: none; padding: 4px 0; }
        .label { font-weight: bold; color: #64748b; width: 150px; }
        
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . APP_NAME . '</h1>
        <p>Sistem Pakar Prediksi Risiko Hipertensi - Puskesmas Pekan Heran</p>
        <p>Laporan Data Pasien & Riwayat Pengecekan</p>
    </div>';

if ($mode === 'single') {
    // Get Patient Info
    $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc();

    if (!$patient) {
        die("Pasien tidak ditemukan.");
    }

    // Get Last 3 Checkups
    $stmt = $conn->prepare("SELECT * FROM checkups WHERE patient_id = ? ORDER BY created_at DESC LIMIT 3");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $checkups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get Latest Prediction
    $stmt = $conn->prepare("SELECT * FROM predictions WHERE patient_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $prediction = $stmt->get_result()->fetch_assoc();

    $html .= '
    <div class="section-title">INFORMASI PASIEN</div>
    <table class="info-grid">
        <tr><td class="label">Nama Lengkap</td><td>: ' . htmlspecialchars($patient['name']) . '</td></tr>
        <tr><td class="label">Jenis Kelamin</td><td>: ' . ($patient['gender'] == 'L' ? 'Laki-laki' : 'Perempuan') . '</td></tr>
        <tr><td class="label">Usia</td><td>: ' . $patient['age'] . ' Tahun</td></tr>
        <tr><td class="label">Alamat</td><td>: ' . htmlspecialchars($patient['address']) . '</td></tr>
        <tr><td class="label">No. Telepon</td><td>: ' . htmlspecialchars($patient['phone']) . '</td></tr>
    </table>

    <div class="section-title">HASIL PREDIKSI TERAKHIR</div>
    <div style="margin-bottom: 20px; padding: 15px; background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 5px;">
        ' . ($prediction ? 
            '<strong>Status: <span class="badge ' . ($prediction['status'] == 'Sangat Berpotensi' ? 'bg-red' : ($prediction['status'] == 'Cukup Berpotensi' ? 'bg-yellow' : 'bg-green')) . '">' . $prediction['status'] . '</span></strong><br>
            <span style="font-size: 10px; color: #666;">Tingkat Keyakinan: ' . number_format($prediction['confidence'] * 100, 1) . '% (Tanggal: ' . date('d/m/Y', strtotime($prediction['created_at'])) . ')</span>' 
            : '<em>Belum ada prediksi.</em>') . '
    </div>

    <div class="section-title">RIWAYAT 3 PENGECEKAN TERAKHIR</div>';

    if (count($checkups) > 0) {
        $html .= '<table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tensi (mmHg)</th>
                    <th>Gula (mg/dL)</th>
                    <th>BMI</th>
                    <th>Lingkar Pinggang</th>
                    <th>Faktor Risiko</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($checkups as $checkup) {
            $risks = [];
            if ($checkup['smoking_status'] == 'Merokok') $risks[] = 'Merokok';
            if ($checkup['family_history'] == 'Ada') $risks[] = 'Riw. Keluarga';
            if ($checkup['personal_history'] == 'Ada') $risks[] = 'Riw. Pribadi';
            
            $html .= '<tr>
                <td>' . date('d/m/Y H:i', strtotime($checkup['created_at'])) . '</td>
                <td>' . $checkup['systolic_pressure'] . '/' . $checkup['diastolic_pressure'] . '</td>
                <td>' . $checkup['blood_sugar'] . '</td>
                <td>' . $checkup['bmi'] . '</td>
                <td>' . $checkup['waist_circumference'] . ' cm</td>
                <td>' . (empty($risks) ? '-' : implode(', ', $risks)) . '</td>
            </tr>';
        }
        $html .= '</tbody></table>';
    } else {
        $html .= '<p style="color: #666; font-style: italic;">Belum ada data checkup.</p>';
    }

} else {
    // Mode ALL Patients
    
    // Custom Sort Order requested by User (matches Skripsi/Thesis data)
    $custom_order = [
        'RUKMINI', 'NETI', 'MEGA', 'SULASTRI', 'JUHANATI', 
        'AINI', 'SUMARNI', 'ROSMALIA', 'SATINEM', 'NINING', 
        'ROSNAINI', 'RAJA PARIDA', 'RULI ERPINA', 'IDA ROYANI', 'DONA'
    ];
    $order_string = "'" . implode("','", $custom_order) . "'";

    $stmt = $conn->prepare("
        SELECT p.*, 
               (SELECT status FROM predictions WHERE patient_id = p.id ORDER BY created_at DESC LIMIT 1) as last_status,
               (SELECT created_at FROM checkups WHERE patient_id = p.id ORDER BY created_at DESC LIMIT 1) as last_checkup
        FROM patients p 
        ORDER BY (FIELD(p.name, $order_string) = 0), FIELD(p.name, $order_string)
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    $html .= '
    <div class="section-title">DAFTAR SELURUH PASIEN</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Nama</th>
                <th style="width: 15%;">Usia/JK</th>
                <th style="width: 30%;">Alamat</th>
                <th style="width: 15%;">Terakhir Cek</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>';
    
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        $status_badge = '-';
        if ($row['last_status']) {
            $color = 'bg-green';
            if ($row['last_status'] == 'Sangat Berpotensi') $color = 'bg-red';
            elseif ($row['last_status'] == 'Cukup Berpotensi') $color = 'bg-yellow';
            $status_badge = '<span class="badge ' . $color . '">' . $row['last_status'] . '</span>';
        }

        $html .= '<tr>
            <td>' . $no++ . '</td>
            <td>' . htmlspecialchars($row['name']) . '</td>
            <td>' . $row['age'] . ' / ' . $row['gender'] . '</td>
            <td>' . htmlspecialchars($row['address']) . '</td>
            <td>' . ($row['last_checkup'] ? date('d/m/Y', strtotime($row['last_checkup'])) : '-') . '</td>
            <td>' . $status_badge . '</td>
        </tr>';
    }
    $html .= '</tbody></table>';
}

$html .= '
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

$filename = ($mode === 'single') ? 'Laporan_Pasien_' . $patient['name'] . '.pdf' : 'Laporan_Seluruh_Pasien.pdf';
$dompdf->stream($filename, ["Attachment" => false]); // false = preview in browser, true = download
?>