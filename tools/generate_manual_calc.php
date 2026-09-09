<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Helper functions (Same as generate_report.php)
function getAgeCategory($age) {
    if ($age < 40) return "Dewasa Awal";
    if ($age < 60) return "Dewasa Akhir";
    return "Lansia";
}

function getBPCategory($s, $d) {
    if ($s >= 140 || $d >= 90) return 'Stage 2';
    if ($s >= 130 || $d >= 80) return 'Stage 1';
    if ($s >= 120 && $d < 80) return 'Elevated';
    if ($s < 120 && $d < 80) return 'Normal';
    return 'Normal';
}

function getSugarCategory($sugar) {
    if ($sugar < 100) return 'Normal';
    if ($sugar < 126) return 'Prediabetes';
    return 'Diabetes';
}

function getBMICat($bmi) {
    if ($bmi < 18.5) return 'Underweight';
    if ($bmi < 25) return 'Normal';
    if ($bmi < 30) return 'Overweight';
    return 'Obese';
}

function getWaistCat($waist, $gender) {
    if ($gender == 'L') {
        if ($waist < 90) return 'Normal';
        if ($waist < 102) return 'High';
        return 'Very High';
    } else {
        if ($waist < 80) return 'Normal';
        if ($waist < 88) return 'High';
        return 'Very High';
    }
}

// Probability Helpers (Exact match to generate_report.php)
function getProb($type, $val, $class) {
    if ($type == 'age') { // val is age
        if ($class == 'C1') return $val < 40 ? 0.8 : ($val < 60 ? 0.4 : 0.2);
        if ($class == 'C2') return $val < 40 ? 0.1 : ($val < 60 ? 0.5 : 0.3);
        if ($class == 'C3') return $val < 40 ? 0.1 : ($val < 60 ? 0.1 : 0.5);
    }
    if ($type == 'gender') {
        if ($class == 'C1') return $val == 'L' ? 0.6 : 0.4;
        if ($class == 'C2') return $val == 'L' ? 0.5 : 0.5;
        if ($class == 'C3') return $val == 'L' ? 0.4 : 0.6;
    }
    if ($type == 'bp') { // val is category
        if ($class == 'C1') return $val == 'Normal' ? 0.8 : 0.1;
        if ($class == 'C2') return ($val == 'Elevated' || $val == 'Stage 1') ? 0.7 : 0.2;
        if ($class == 'C3') return $val == 'Stage 2' ? 0.9 : 0.1;
    }
    if ($type == 'sugar') {
        if ($class == 'C1') return $val == 'Normal' ? 0.8 : 0.2;
        if ($class == 'C2') return $val == 'Prediabetes' ? 0.7 : 0.3;
        if ($class == 'C3') return $val == 'Diabetes' ? 0.9 : 0.1;
    }
    if ($type == 'bmi') {
        if ($class == 'C1') return $val == 'Normal' ? 0.8 : 0.2;
        if ($class == 'C2') return $val == 'Overweight' ? 0.7 : 0.3;
        if ($class == 'C3') return $val == 'Obese' ? 0.9 : 0.1;
    }
    return 0.5;
}

function floatToFrac($n) {
    if (abs($n - 0.1) < 0.001) return "1/10";
    if (abs($n - 0.2) < 0.001) return "2/10"; // or 1/5
    if (abs($n - 0.3) < 0.001) return "3/10";
    if (abs($n - 0.4) < 0.001) return "4/10"; // or 2/5
    if (abs($n - 0.5) < 0.001) return "5/10"; // or 1/2
    if (abs($n - 0.6) < 0.001) return "6/10"; // or 3/5
    if (abs($n - 0.7) < 0.001) return "7/10";
    if (abs($n - 0.8) < 0.001) return "8/10"; // or 4/5
    if (abs($n - 0.9) < 0.001) return "9/10";
    if (abs($n - 1.0) < 0.001) return "10/10";
    return strval($n);
}

// Samples Data (Hardcoded for consistency)
$samples = [
    1 => ['name'=>'AINI', 'age'=>52, 'gender'=>'P', 's'=>150, 'd'=>84, 'sugar'=>165, 'bmi'=>31.79, 'target'=>'Sangat Berpotensi'],
    2 => ['name'=>'SUMARNI', 'age'=>52, 'gender'=>'P', 's'=>160, 'd'=>65, 'sugar'=>165, 'bmi'=>27.77, 'target'=>'Cukup Berpotensi'],
    3 => ['name'=>'RULI ERPINA', 'age'=>43, 'gender'=>'P', 's'=>113, 'd'=>79, 'sugar'=>115, 'bmi'=>28.4, 'target'=>'Cukup Berpotensi'],
    4 => ['name'=>'ROSNAINI', 'age'=>56, 'gender'=>'P', 's'=>132, 'd'=>71, 'sugar'=>124, 'bmi'=>21.62, 'target'=>'Cukup Berpotensi'],
    5 => ['name'=>'DONA', 'age'=>25, 'gender'=>'P', 's'=>117, 'd'=>87, 'sugar'=>126, 'bmi'=>19.29, 'target'=>'Tidak Berpotensi']
];

$classes = ['C1'=>'Tidak Berpotensi', 'C2'=>'Cukup Berpotensi', 'C3'=>'Sangat Berpotensi'];

echo "PERHITUNGAN MANUAL METODE NAIVE BAYES\n";
echo "Studi Kasus: Sistem Pakar Prediksi Risiko Hipertensi\n\n";

foreach ($samples as $id => $s) {
    $age_cat = getAgeCategory($s['age']);
    $bp_cat = getBPCategory($s['s'], $s['d']);
    $sugar_cat = getSugarCategory($s['sugar']);
    $bmi_cat = getBMICat($s['bmi']);
    
    $gender_str = $s['gender']=='P'?'Perempuan':'Laki-laki';
    echo "Sampel $id ({$s['name']}, {$s['age']} Tahun, $gender_str, Tekanan Darah {$s['s']}/{$s['d']} ($bp_cat), Gula {$s['sugar']} ($sugar_cat), BMI {$s['bmi']} ($bmi_cat))\n";
    
    // Calculate scores for all classes
    $scores = [];
    $details = [];
    
    foreach ($classes as $code => $name) {
        $p_age = getProb('age', $s['age'], $code);
        $p_gen = getProb('gender', $s['gender'], $code);
        $p_bp = getProb('bp', $bp_cat, $code);
        $p_sug = getProb('sugar', $sugar_cat, $code);
        $p_bmi = getProb('bmi', $bmi_cat, $code);
        
        $score = $p_age * $p_gen * $p_bp * $p_sug * $p_bmi;
        $scores[$code] = $score;
        
        $details[$code] = [
            'age' => $p_age,
            'gen' => $p_gen,
            'bp' => $p_bp,
            'sug' => $p_sug,
            'bmi' => $p_bmi,
            'score' => $score
        ];
    }
    
    $total_score = array_sum($scores);
    
    // Display calculation for the TARGET class (or Winner)
    // Find key for target
    $target_code = array_search($s['target'], $classes);
    $d = $details[$target_code];
    
    // Format fractions
    $f_age = floatToFrac($d['age']);
    $f_gen = floatToFrac($d['gen']);
    $f_bp = floatToFrac($d['bp']);
    $f_sug = floatToFrac($d['sug']);
    $f_bmi = floatToFrac($d['bmi']);
    
    echo "P({$s['target']}|{$s['name']}) = P($age_cat) x P(Perempuan) x P($bp_cat) x P($sugar_cat) x P($bmi_cat)\n";
    echo "                      = $f_age x $f_gen x $f_bp x $f_sug x $f_bmi\n";
    echo "                      = {$d['age']} x {$d['gen']} x {$d['bp']} x {$d['sug']} x {$d['bmi']}\n";
    echo "                      = " . number_format($d['score'], 5) . "\n";
    
    // Normalization
    $norm = $d['score'] / $total_score;
    $norm_perc = number_format($norm * 100, 2);
    
    echo "Nilai Normalisasi (Probabilitas Akhir):\n";
    echo "P(Akhir)              = {$d['score']} / (" . number_format($scores['C1'], 5) . " + " . number_format($scores['C2'], 5) . " + " . number_format($scores['C3'], 5) . ")\n";
    echo "                      = " . number_format($norm, 4) . " ($norm_perc%)\n\n";
}
