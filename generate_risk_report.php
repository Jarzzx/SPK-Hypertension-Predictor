<?php
require_once 'config/database.php';
require_once 'algorithms/naive_bayes.php';

$nb = new NaiveBayes($conn);

// Get all patients who have checkups
$query = "SELECT DISTINCT p.id, p.name FROM patients p 
          JOIN checkups c ON p.id = c.patient_id 
          ORDER BY p.name ASC";
$result = $conn->query($query);

$report_data = [];

if ($result->num_rows > 0) {
    while ($patient = $result->fetch_assoc()) {
        // We want the RAW PROBABILITY now, which we stored in 'probability' column
    // The previous 'confidence' was Risk Score.
    // The previous code fetched 'confidence'. We should fetch 'probability'.
    
    // BUT 'NaiveBayes::predict' returns 'confidence' and 'probabilities' array.
    // AND we modified 'NaiveBayes::predict' to return 'confidence' (Risk Score)
    // AND 'probabilities' (Normalized).
    // WAIT, I didn't modify the return array in 'NaiveBayes::predict' to include raw max prob.
    // I only modified it to SAVE to DB.
    
    // So if I run 'predict()' here, I won't get the raw prob unless I query it or modify return.
    // EASIER: Since 'predict()' saves to DB, I can just query the DB after prediction.
    // OR: I should update 'NaiveBayes::predict' to return raw_prob.
    
    // Let's rely on the DB update I just made.
    // But 'generate_risk_report.php' calls '$nb->predict($patient['id'])'.
    
    $prediction = $nb->predict($patient['id']);
    
    // Fetch the raw probability from DB that was just saved
    $raw_prob_query = "SELECT probability FROM predictions WHERE patient_id = " . $patient['id'];
    $raw_prob_result = $conn->query($raw_prob_query)->fetch_assoc();
    $raw_prob = $raw_prob_result['probability'] ?? 0;
    
    if ($prediction) {
        $report_data[] = [
            'name' => $patient['name'],
            'status' => $prediction['status'],
            'confidence' => $prediction['confidence'],
            'raw_prob' => $raw_prob
        ];
    }
}
}

// Custom sort function
usort($report_data, function($a, $b) {
// Define priority map
$priority = [
    'Sangat Berpotensi' => 3,
    'Cukup Berpotensi' => 2,
    'Tidak Berpotensi' => 1
];

$scoreA = $priority[$a['status']] ?? 0;
$scoreB = $priority[$b['status']] ?? 0;

// Sort by Status (Desc)
if ($scoreA != $scoreB) {
    return $scoreB - $scoreA;
}

// Then by RAW PROBABILITY (Desc) - CHANGED from Confidence
return ($b['raw_prob'] <=> $a['raw_prob']);
});

// Generate Text Table
$output = "LAPORAN RISIKO HIPERTENSI PASIEN\n";
$output .= "Tanggal Generate: " . date('d-m-Y H:i:s') . "\n";
$output .= str_repeat("=", 105) . "\n";
$output .= sprintf("| %-4s | %-25s | %-20s | %-15s | %-20s |\n", "No", "Nama Pasien", "Status Risiko", "Confidence", "Nilai Probabilitas");
$output .= str_repeat("-", 105) . "\n";

$no = 1;
foreach ($report_data as $row) {
$confidence_percent = number_format($row['confidence'] * 100, 2) . "%";
// Show Raw Probability
$prob_value = number_format($row['raw_prob'], 9); // Show more decimals for small numbers
$output .= sprintf("| %-4d | %-25s | %-20s | %-15s | %-20s |\n", 
    $no++, 
    substr($row['name'], 0, 25), 
    $row['status'], 
    $confidence_percent,
    $prob_value
);
}
$output .= str_repeat("=", 105) . "\n";

// Write to file
$filename = 'laporan_risiko_hipertensi.txt';
file_put_contents($filename, $output);

echo "Laporan berhasil dibuat: $filename";
?>