<?php
require_once 'config/database.php';

// Set output file
$output_file = 'laporan_perhitungan_manual.txt';
$fp = fopen($output_file, 'w');

// Header
fwrite($fp, "LAPORAN HASIL PERHITUNGAN MANUAL (NAIVE BAYES)\n");
fwrite($fp, "========================================================\n");
fwrite($fp, "Tanggal: " . date('Y-m-d H:i:s') . "\n");
fwrite($fp, "Metode: Naive Bayes Classifier (Fixed Knowledge Base - Expert Probabilities)\n");
fwrite($fp, "Note: Perhitungan menggunakan 7 Indikator sesuai request user (Riwayat diberi bobot 1.0 agar skala nilai tetap).\n");
fwrite($fp, "Rumus: P(Usia) x P(JK) x P(Tensi) x P(Gula) x P(BMI) x P(RK) x P(RP) = Hasil\n\n");

// --- STEP 1: GATHER DATA & TRAIN MODEL (CALCULATE FREQUENCIES) ---

$query = "
    SELECT p.id, p.name, p.age, p.gender, 
           c.systolic_pressure, c.diastolic_pressure, c.blood_sugar, c.height, c.weight,
           c.family_history, c.personal_history,
           (c.weight / ((c.height/100) * (c.height/100))) as bmi,
           pr.status as system_prediction
    FROM patients p
    JOIN checkups c ON p.id = c.patient_id
    LEFT JOIN predictions pr ON p.id = pr.patient_id
    WHERE c.id = (
        SELECT id FROM checkups 
        WHERE patient_id = p.id 
        ORDER BY created_at DESC 
        LIMIT 1
    )
    ORDER BY p.name ASC
";

$result = $conn->query($query);
$patients_data = [];
$stats = [
    'counts' => ['Tidak Berpotensi' => 0, 'Cukup Berpotensi' => 0, 'Sangat Berpotensi' => 0],
    'age' => [],
    'gender' => [],
    'bp' => [],
    'sugar' => [],
    'bmi' => []
];

$classes = ['Tidak Berpotensi', 'Cukup Berpotensi', 'Sangat Berpotensi'];

// Initialize stat arrays
foreach ($classes as $c) {
    $stats['age'][$c] = ['Muda' => 0, 'Paruh Baya' => 0, 'Tua' => 0];
    $stats['gender'][$c] = ['L' => 0, 'P' => 0];
    $stats['bp'][$c] = ['Normal' => 0, 'Elevated' => 0, 'Stage 1' => 0, 'Stage 2' => 0];
    $stats['sugar'][$c] = ['Normal' => 0, 'Prediabetes' => 0, 'Diabetes' => 0];
    $stats['bmi'][$c] = ['Underweight' => 0, 'Normal' => 0, 'Overweight' => 0, 'Obese' => 0];
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $patients_data[] = $row;
        
        // Only train on patients that have a system prediction
        if (!empty($row['system_prediction']) && in_array($row['system_prediction'], $classes)) {
            $class = $row['system_prediction'];
            $stats['counts'][$class]++;
            
            // Age
            $age_cat = getAgeCategory($row['age']);
            $stats['age'][$class][$age_cat]++;
            
            // Gender
            $stats['gender'][$class][$row['gender']]++;
            
            // BP
            $bp_cat = getBPCategory($row['systolic_pressure'], $row['diastolic_pressure']);
            $stats['bp'][$class][$bp_cat]++;
            
            // Sugar
            $sugar_cat = getSugarCategory($row['blood_sugar']);
            $stats['sugar'][$class][$sugar_cat]++;
            
            // BMI
            $bmi_cat = getBMICategory($row['bmi']);
            $stats['bmi'][$class][$bmi_cat]++;
        }
    }
}

// Calculate Priors
$total_samples = array_sum($stats['counts']);
$priors = [];
foreach ($classes as $class) {
    // Add Laplace smoothing for priors? Or just simple fraction. Simple fraction for transparency.
    $priors[$class] = ($total_samples > 0) ? ($stats['counts'][$class] / $total_samples) : 0.33;
}

// --- STEP 2: PREDICT & GENERATE REPORT ---

$table_data = [];
$no = 1;

foreach ($patients_data as $patient) {
    $name = $patient['name'];
    $age = $patient['age'];
    $gender = $patient['gender'];
    $systolic = $patient['systolic_pressure'];
    $diastolic = $patient['diastolic_pressure'];
    $sugar = $patient['blood_sugar'];
    $bmi = $patient['bmi'];
    $family_history = $patient['family_history'] ?? 'Tidak Ada';
    $personal_history = $patient['personal_history'] ?? 'Tidak Ada';
    
    // Categorize features for display and calculation
    $age_cat = getAgeCategory($age);
    $gender_disp = ($gender == 'L' ? 'Laki-laki' : 'Perempuan');
    $bp_cat = getBPCategory($systolic, $diastolic);
    $sugar_cat = getSugarCategory($sugar);
    $bmi_cat = getBMICategory($bmi);
    
    // --- FIXED KNOWLEDGE BASE (BASIS PENGETAHUAN PAKAR) ---
    // Source: laporan_manual_terbaru.txt
    
    // 1. Usia
    $prob_age = [
        'Muda' => 0.2,       // Dewasa Awal (17-39)
        'Paruh Baya' => 0.6, // Dewasa Akhir (40-59)
        'Tua' => 0.8         // Lansia (> 60)
    ];
    
    // 2. Jenis Kelamin
    $prob_gender = [
        'L' => 0.5,
        'P' => 0.5
    ];
    
    // 3. Tekanan Darah
    $prob_bp = [
        'Normal' => 0.2,
        'Elevated' => 0.4,
        'Stage 1' => 0.7,
        'Stage 2' => 0.9
    ];
    
    // 4. Gula Darah
    $prob_sugar = [
        'Normal' => 0.2,
        'Prediabetes' => 0.6,
        'Diabetes' => 0.8
    ];
    
    // 5. BMI
    $prob_bmi = [
        'Underweight' => 0.1,
        'Normal' => 0.3,
        'Overweight' => 0.6,
        'Obese' => 0.8
    ];

    // 6. Riwayat Keluarga
    $prob_family = [
        'Tidak Ada' => 1.0, // Neutral (Request User: Maintain Score Scale)
        'Hipertensi' => 1.0,
        'Diabetes Melitus' => 1.0,
        'Hipertensi dan Diabetes Melitus' => 1.0
    ];
    
    // 7. Riwayat Penyakit (Personal)
    $prob_personal = [
        'Tidak Ada' => 1.0, // Neutral (Request User: Maintain Score Scale)
        'Hipertensi' => 1.0,
        'Diabetes Melitus' => 1.0,
        'Hipertensi dan Diabetes Melitus' => 1.0
    ];

    // Get Fixed Probabilities
    $p_age_val = $prob_age[$age_cat] ?? 0.1;
    $p_gender_val = $prob_gender[$gender] ?? 0.5;
    $p_bp_val = $prob_bp[$bp_cat] ?? 0.2;
    $p_sugar_val = $prob_sugar[$sugar_cat] ?? 0.2;
    $p_bmi_val = $prob_bmi[$bmi_cat] ?? 0.1;
    $p_family_val = $prob_family[$family_history] ?? 1.0;
    $p_personal_val = $prob_personal[$personal_history] ?? 1.0;

    // Calculate Score (7 Indikator - Sesuai Bab 3 Revised)
    // Rumus: P(Usia) x P(JK) x P(Tensi) x P(Gula) x P(BMI) x P(RK) x P(RP)
    $score = $p_age_val * $p_gender_val * $p_bp_val * $p_sugar_val * $p_bmi_val * $p_family_val * $p_personal_val;
    
    // Write Calculation Step to File
    fwrite($fp, "Pasien: $name (Usia: $age, JK: $gender_disp, Tensi: $systolic/$diastolic, Gula: $sugar, BMI: $bmi)\n");
    fwrite($fp, "Kategori: Usia=$age_cat, Tensi=$bp_cat, Gula=$sugar_cat, BMI=$bmi_cat, RK=$family_history, RP=$personal_history\n");
    fwrite($fp, "Rumus: P(Usia) x P(JK) x P(Tensi) x P(Gula) x P(BMI) x P(RK) x P(RP) [7 Indikator]\n");
    fwrite($fp, "Hitung: $p_age_val x $p_gender_val x $p_bp_val x $p_sugar_val x $p_bmi_val x $p_family_val x $p_personal_val = " . number_format($score, 5) . "\n");
    fwrite($fp, "----------------------------------------------------------------------\n");
    
    // Determine Result using Original Thresholds (Matches Bab 3 Scale)
    // Sangat: >= 0.10
    // Cukup: >= 0.02
    // Tidak: < 0.02
    
    $result_text = 'Tidak Berpotensi';
    if ($score >= 0.10) {
        $result_text = 'Sangat Berpotensi';
    } elseif ($score >= 0.02) {
        $result_text = 'Cukup Berpotensi';
    }

    $table_data[] = [
        'name' => $name,
        'probs' => ['Sangat Berpotensi' => $score],
        'result' => $result_text,
        'score' => $score
    ];
    $no++;
}

// Remove the old frequency calculation logic block entirely
// We can just empty the previous loops or skip them.
// But for cleaner code, let's just use the table_data part.
// Actually, I need to replace the WHOLE calculation loop section.

// Let's comment out or remove the old frequency gathering and loop.
// The SearchReplace below targets the specific section.

/* 
   We need to replace the loop: foreach ($patients_data as $patient) { ... }
   with the new logic.
*/

// Custom sort function
usort($table_data, function($a, $b) {
    // Define priority map (Higher risk = higher priority)
    $priority = [
        'Sangat Berpotensi' => 3,
        'Cukup Berpotensi' => 2,
        'Tidak Berpotensi' => 1
    ];
    
    $scoreA = $priority[$a['result']] ?? 0;
    $scoreB = $priority[$b['result']] ?? 0;
    
    // Sort by Status (Desc)
    if ($scoreA != $scoreB) {
        return $scoreB - $scoreA;
    }
    
    // Then by Probability Score (Desc)
    return ($b['score'] <=> $a['score']);
});

// Summary Table (SIMPLIFIED as requested)
fwrite($fp, sprintf("%-25s | %-20s | %-15s\n", "Nama Pasien", "Hasil Akhir", "Nilai Probabilitas"));
fwrite($fp, "----------------------------------------------------------------------\n");

foreach ($table_data as $data) {
    fwrite($fp, sprintf("%-25s | %-20s | %-15s\n", 
        substr($data['name'], 0, 25),
        $data['result'],
        number_format($data['score'], 5)
    ));
}
fwrite($fp, "----------------------------------------------------------------------\n");

// Add Explanation of Thresholds at the bottom
fwrite($fp, "\n");
fwrite($fp, "PENJELASAN LOGIKA KLASIFIKASI (THRESHOLD):\n");
fwrite($fp, "1. SANGAT BERPOTENSI (Score >= 0.10)\n");
fwrite($fp, "   - Dicapai jika mayoritas indikator bernilai risiko tinggi (cth: AINI = 0.17280).\n");
fwrite($fp, "   - Representasi: Pasien dengan Usia Tua, Tensi Tinggi, Gula Tinggi, & Obesitas.\n\n");
fwrite($fp, "2. CUKUP BERPOTENSI (0.02 <= Score < 0.10)\n");
fwrite($fp, "   - Dicapai jika indikator risiko bercampur dengan indikator sehat (cth: IDA ROYANI = 0.04860).\n");
fwrite($fp, "   - Representasi: Pasien dengan beberapa risiko tinggi tapi faktor lain normal.\n\n");
fwrite($fp, "3. TIDAK BERPOTENSI (Score < 0.02)\n");
fwrite($fp, "   - Dicapai jika mayoritas indikator bernilai normal/sehat (cth: DONA = 0.01680).\n");
fwrite($fp, "   - Representasi: Pasien muda dengan parameter kesehatan baik.\n");

fclose($fp);
echo "Laporan berhasil dibuat: $output_file \n";


// --- Helper Functions for Categorization ---

function getAgeCategory($age) {
    if ($age < 40) return 'Muda';
    if ($age < 60) return 'Paruh Baya';
    return 'Tua';
}

function getBPCategory($systolic, $diastolic) {
    if ($systolic < 120 && $diastolic < 80) return 'Normal';
    if ($systolic < 130 && $diastolic < 80) return 'Elevated';
    if ($systolic >= 140 || $diastolic >= 90) return 'Stage 2';
    return 'Stage 1';
}

function getSugarCategory($sugar) {
    if ($sugar < 100) return 'Normal';
    if ($sugar < 126) return 'Prediabetes';
    return 'Diabetes';
}

function getBMICategory($bmi) {
    if ($bmi < 18.5) return 'Underweight';
    if ($bmi < 25) return 'Normal';
    if ($bmi < 30) return 'Overweight';
    return 'Obese';
}
?>