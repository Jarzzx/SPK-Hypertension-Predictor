<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Define the 5 specific samples based on user request (Specific Patients)
// 1. Aini (ID 22)
// 2. Sumarni (ID 19)
// 3. Ruli Erpina (ID 25)
// 4. Rosnaini (ID 24)
// 5. Dona (ID 26)

$target_ids = [22, 19, 25, 24, 26];

$query = "SELECT p.name, p.age, p.gender, c.* 
          FROM checkups c 
          JOIN patients p ON c.patient_id = p.id 
          WHERE c.id IN (22, 19, 25, 24, 26)";
$result = $conn->query($query);

if (!$result || $result->num_rows == 0) {
    die("Tidak ada data checkup ditemukan.");
}

$raw_samples = [];
while ($row = $result->fetch_assoc()) {
    $raw_samples[$row['id']] = $row;
}

// Order them specifically as requested
$samples = [];
$order = [22, 19, 25, 24, 26];
foreach ($order as $id) {
    if (isset($raw_samples[$id])) {
        $samples[] = $raw_samples[$id];
    }
}

// Helper functions for logic (copied/adapted from NaiveBayes.php)
function getAgeCategory($age) {
    if ($age < 40) return "Dewasa Awal";
    if ($age < 60) return "Dewasa Akhir";
    return "Lansia";
}

function getBPCategory($s, $d) {
    // Logic based on JNC 7
    if ($s >= 140 || $d >= 90) return 'Stage 2';
    if ($s >= 130 || $d >= 80) return 'Stage 1';
    if ($s >= 120 && $d < 80) return 'Elevated'; // Strict definition: S 120-129 AND D < 80
    if ($s < 120 && $d < 80) return 'Normal';
    
    // Fallback for edge cases not covered above (e.g., S < 120 but D >= 80, covered by Stage 1/2)
    // If S < 120 and D 80-89 -> Stage 1
    // If S < 120 and D >= 90 -> Stage 2
    // The first two lines cover all cases where D >= 80.
    // The third line covers S >= 120 (and D < 80 implicitly because if D >= 80 it would be caught above).
    // The fourth line covers S < 120 and D < 80.
    // So logic is complete.
    
    return 'Normal'; // Default
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

// Probability Helpers (Hardcoded from NaiveBayes.php)
function getProb($type, $val, $class, $gender = null) {
    // Simplified mapping based on NaiveBayes.php logic
    // Returns probability
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
    // ... add others if needed, but user image emphasizes Age, Gender, BP, Sugar, BMI
    return 0.5;
}

// Generate Output
echo "TABEL DATA SAMPEL (11 INDIKATOR)\n";
echo "Sampel\tUsia\tJK\tTensi (mmHg)\tGula Darah\tBMI\tLingkar Pinggang\tRiw. Keluarga\tRiw. Pribadi\tMerokok\tFisik\tSayur/Buah\n";

$processed_samples = [];
$i = 1;
foreach ($samples as $s) {
    // Calc Attributes
    $age = $s['age'];
    $bmi = $s['weight'] / (($s['height']/100) * ($s['height']/100));
    $bmi = round($bmi, 2);
    
    $bp_cat = getBPCategory($s['systolic_pressure'], $s['diastolic_pressure']);
    $sugar_cat = getSugarCategory($s['blood_sugar']);
    $bmi_cat = getBMICat($bmi);
    $waist_cat = getWaistCat($s['waist_circumference'], $s['gender']);
    
    echo "Sampel $i\t$age\t{$s['gender']}\t{$s['systolic_pressure']}/{$s['diastolic_pressure']}\t{$s['blood_sugar']}\t$bmi\t{$s['waist_circumference']}\t{$s['family_history']}\t{$s['personal_history']}\t{$s['smoking_status']}\t{$s['physical_activity']}\t{$s['fruit_vegetable_consumption']}\n";
    
    $processed_samples[] = [
        'id' => $i,
        'name' => $s['name'],
        'age' => $age,
        'gender' => $s['gender'],
        'bp_val' => "{$s['systolic_pressure']}/{$s['diastolic_pressure']}",
        'sugar_val' => $s['blood_sugar'],
        'bmi_val' => $bmi,
        'age_cat' => getAgeCategory($age),
        'bp_cat' => $bp_cat,
        'sugar_cat' => $sugar_cat,
        'bmi_cat' => $bmi_cat,
        'waist_cat' => $waist_cat
    ];
    $i++;
}

// Narrative Section
echo "\n====================================================================================================\n\n";
echo "ANALISIS DISTRIBUSI DATA SAMPEL\n";
echo "Dari lima data sampel didapatlah:\n\n";

$count = count($processed_samples);
// Re-structure cats to store ID/Name
$cats = ['age_cat'=>[], 'gender'=>[], 'bp_cat'=>[], 'sugar_cat'=>[], 'bmi_cat'=>[]];

foreach ($processed_samples as $p) {
    $cats['age_cat'][$p['age_cat']][] = "Sampel {$p['id']}";
    $cats['gender'][$p['gender'] == 'P' ? 'Perempuan' : 'Laki-laki'][] = "Sampel {$p['id']}";
    $cats['bp_cat'][$p['bp_cat']][] = "Sampel {$p['id']}";
    $cats['sugar_cat'][$p['sugar_cat']][] = "Sampel {$p['id']}";
    $cats['bmi_cat'][$p['bmi_cat']][] = "Sampel {$p['id']}";
}

function printNarrative($letter, $attr_name, $data, $total) {
    echo "$letter. $attr_name\n\n";
    
    $text = [];
    
    // Description mapping
    $descriptions = [
        'Dewasa Awal' => '(17-39 tahun)',
        'Dewasa Akhir' => '(40-59 tahun)',
        'Lansia' => '(> 60 tahun)',
        'Stage 1' => '(Hipertensi Tahap 1)',
        'Stage 2' => '(Hipertensi Tahap 2)',
        'Elevated' => '(Meningkat)',
        'Normal' => '', // No extra desc usually needed
        'Diabetes' => '(>= 126 mg/dL)',
        'Prediabetes' => '(100-125 mg/dL)',
        'Obese' => '(> 30)',
        'Overweight' => '(25-29.9)',
        'Underweight' => '(< 18.5)'
    ];
    
    // Iterate through categories found in data
    foreach ($data as $cat => $samples) {
        $v = count($samples);
        $sample_list = "(" . implode(", ", $samples) . ")";
        $desc = isset($descriptions[$cat]) ? " " . $descriptions[$cat] : "";
        
        // Build the string piece
        // "X dari Y sampel (Sampel A, Sampel B) masuk..."
        $piece = "$v dari $total sampel $sample_list";
        
        // We will combine pieces later with the predicate
        $text[] = ['count' => $v, 'samples' => $sample_list, 'cat' => $cat, 'desc' => $desc];
    }
    
    // Sort text by count descending for nicer reading? Or just keep order.
    // Let's construct the full sentence.
    
    $sentences = [];
    
    if ($attr_name == 'Jenis Kelamin') {
         $prefix = "Pada atribut data Jenis Kelamin didapatkan";
         foreach ($text as $item) {
             $sentences[] = "{$item['count']} dari $total sampel data {$item['samples']} merupakan \"{$item['cat']}\"";
         }
    } elseif ($attr_name == 'Tekanan Darah') {
        $prefix = "Untuk atribut data Tekanan Darah,";
        foreach ($text as $item) {
             $sentences[] = "{$item['count']} dari $total sampel {$item['samples']} tergolong \"{$item['cat']}\"{$item['desc']}";
        }
    } elseif ($attr_name == 'Gula Darah') {
        $prefix = "Pada atribut Gula Darah,";
        foreach ($text as $item) {
             $sentences[] = "{$item['count']} dari $total sampel {$item['samples']} masuk ke dalam kategori \"{$item['cat']}\"{$item['desc']}";
        }
    } elseif ($attr_name == 'BMI') {
        $prefix = "Sebanyak";
        foreach ($text as $item) {
             $sentences[] = "{$item['count']} dari $total sampel {$item['samples']} masuk kategori \"{$item['cat']}\"{$item['desc']}";
        }
    } else { // Umur
        $prefix = "Untuk atribut data $attr_name didapatlah";
        foreach ($text as $item) {
             $sentences[] = "{$item['count']} dari $total sampel {$item['samples']} masuk ke dalam kategori \"{$item['cat']}\"{$item['desc']}";
        }
    }
    
    echo "$prefix " . implode(" dan ", $sentences) . ".\n\n";
}

printNarrative("a", "Umur", $cats['age_cat'], $count);
printNarrative("b", "Jenis Kelamin", $cats['gender'], $count);
printNarrative("c", "Tekanan Darah", $cats['bp_cat'], $count);
printNarrative("d", "Gula Darah", $cats['sugar_cat'], $count);
printNarrative("e", "BMI", $cats['bmi_cat'], $count);

echo "\n====================================================================================================\n\n";
echo "PERHITUNGAN MANUAL METODE NAIVE BAYES\n";
echo "Studi Kasus: Sistem Pakar Prediksi Risiko Hipertensi\n\n";

foreach ($processed_samples as $p) {
    echo "Sampel {$p['id']} ({$p['name']}, {$p['age']} Tahun, " . ($p['gender']=='P'?'Perempuan':'Laki-laki') . ", Tekanan Darah {$p['bp_val']}, Gula {$p['sugar_val']}, BMI {$p['bmi_val']}, {$p['bmi_cat']})\n";
    
    // Calculate Probabilities
    $classes = ['Tidak Berpotensi' => 'C1', 'Cukup Berpotensi' => 'C2', 'Sangat Berpotensi' => 'C3'];
    $priors = 0.33;
    
    $results = [];
    
    foreach ($classes as $label => $code) {
        // Factors
        $p_age = getProb('age', $p['age'], $code);
        $p_gender = getProb('gender', $p['gender'], $code);
        $p_bp = getProb('bp', $p['bp_cat'], $code);
        $p_sugar = getProb('sugar', $p['sugar_cat'], $code);
        $p_bmi = getProb('bmi', $p['bmi_cat'], $code);
        
        // Full calculation would include other factors, but user image shows simplified "P(Usia) x P(JK) x P(Tensi) x P(Gula) x P(BMI)"
        // We will follow the simplified format for display, but maybe calculate full?
        // The user explicitly asked "kaya gini" referring to the image. The image has 5 factors.
        // We will show the 5 factors calculation.
        
        $prob = $p_age * $p_gender * $p_bp * $p_sugar * $p_bmi; // * $priors (Image doesn't show prior in the multiplication line, but typically it's included)
        // Wait, image result 0.3456 = 0.8*0.6*1.0*0.8*0.9. No prior. It's Likelihood.
        
        $results[$label] = [
            'p_age' => $p_age,
            'p_gender' => $p_gender,
            'p_bp' => $p_bp,
            'p_sugar' => $p_sugar,
            'p_bmi' => $p_bmi,
            'total' => $prob
        ];
    }
    
    // Find Max (Heuristic based on Likelihood for this simplified view)
    $max_prob = -1;
    $best_class = '';
    foreach ($results as $label => $res) {
        if ($res['total'] > $max_prob) {
            $max_prob = $res['total'];
            $best_class = $label;
        }
    }
    
    // Output like image
    // Note: Image shows P(Hipertensi|X). It likely picks the class that represents "Hipertensi" (C2 or C3).
    // Or maybe it just shows the calculation for the "Winner" class?
    // Let's show the calculation for the "Winner" class to be most helpful.
    
    echo "P({$best_class}|{$p['name']}) = P({$p['age_cat']}) x P(" . ($p['gender']=='P'?'Perempuan':'Laki-laki') . ") x P({$p['bp_cat']}) x P({$p['sugar_cat']}) x P({$p['bmi_cat']})\n";
    echo "                      = {$results[$best_class]['p_age']} x {$results[$best_class]['p_gender']} x {$results[$best_class]['p_bp']} x {$results[$best_class]['p_sugar']} x {$results[$best_class]['p_bmi']}\n";
    echo "                      = " . number_format($results[$best_class]['total'], 4) . "\n\n";
}
?>
