<?php
// Replicating NaiveBayes class logic for testing without DB
class NaiveBayesTest {
    private $classes = ['Tidak Berpotensi', 'Cukup Berpotensi', 'Sangat Berpotensi'];

    public function predict($data) {
        $probabilities = $this->calculateNormalizedProbabilities($data);
        $predicted_class = array_keys($probabilities, max($probabilities))[0];
        $confidence = max($probabilities);
        
        return [
            'status' => $predicted_class,
            'confidence' => $confidence,
            'probabilities' => $probabilities
        ];
    }

    private function calculateNormalizedProbabilities($data) {
        $probabilities = [];
        $total = 0;
        
        foreach ($this->classes as $class) {
            $prob = $this->calculateClassProbability($data, $class);
            $probabilities[$class] = $prob;
            $total += $prob;
        }
        
        if ($total > 0) {
            foreach ($probabilities as $class => $prob) {
                $probabilities[$class] = $prob / $total;
            }
        }
        
        return $probabilities;
    }

    private function calculateClassProbability($data, $class) {
        $prior_prob = 1 / count($this->classes);
        $likelihood = $this->calculateLikelihood($data, $class);
        return $likelihood * $prior_prob;
    }

    private function calculateLikelihood($data, $class) {
        $likelihood = 1.0;
        $likelihood *= $this->getAgeProbability($data['age'], $class);
        $likelihood *= $this->getGenderProbability($data['gender'], $class);
        $likelihood *= $this->getBloodPressureProbability($data['systolic_pressure'], $data['diastolic_pressure'], $class);
        $likelihood *= $this->getBloodSugarProbability($data['blood_sugar'], $class);
        $likelihood *= $this->getBMIProbability($data['bmi'], $class);
        $likelihood *= $this->getWaistCircumferenceProbability($data['waist_circumference'], $data['gender'], $class);
        $likelihood *= $this->getFamilyHistoryProbability($data['family_history'], $class);
        $likelihood *= $this->getPersonalHistoryProbability($data['personal_history'], $class);
        $likelihood *= $this->getSmokingProbability($data['smoking_status'], $class);
        $likelihood *= $this->getPhysicalActivityProbability($data['physical_activity'], $class);
        $likelihood *= $this->getFruitVegetableProbability($data['fruit_vegetable_consumption'], $class);
        return $likelihood;
    }

    // --- COPIED PROBABILITY METHODS FROM algorithms/naive_bayes.php ---

    private function getAgeProbability($age, $class) {
        switch ($class) {
            case 'Tidak Berpotensi': return $age < 40 ? 0.8 : ($age < 60 ? 0.4 : 0.2);
            case 'Cukup Berpotensi': return $age < 40 ? 0.1 : ($age < 60 ? 0.5 : 0.3);
            case 'Sangat Berpotensi': return $age < 40 ? 0.1 : ($age < 60 ? 0.1 : 0.5);
            default: return 0.33;
        }
    }

    private function getGenderProbability($gender, $class) {
        switch ($class) {
            case 'Tidak Berpotensi': return $gender == 'L' ? 0.6 : 0.4;
            case 'Cukup Berpotensi': return $gender == 'L' ? 0.5 : 0.5;
            case 'Sangat Berpotensi': return $gender == 'L' ? 0.4 : 0.6;
            default: return 0.5;
        }
    }

    private function getBloodPressureProbability($systolic, $diastolic, $class) {
        $pressure_level = $this->getBloodPressureLevel($systolic, $diastolic);
        switch ($class) {
            case 'Tidak Berpotensi': return $pressure_level == 'Normal' ? 0.8 : 0.1;
            case 'Cukup Berpotensi': return $pressure_level == 'Elevated' || $pressure_level == 'Stage 1' ? 0.7 : 0.2;
            case 'Sangat Berpotensi': return $pressure_level == 'Stage 2' ? 0.9 : 0.1;
            default: return 0.33;
        }
    }

    private function getBloodSugarProbability($blood_sugar, $class) {
        $sugar_level = $this->getBloodSugarLevel($blood_sugar);
        switch ($class) {
            case 'Tidak Berpotensi': return $sugar_level == 'Normal' ? 0.8 : 0.2;
            case 'Cukup Berpotensi': return $sugar_level == 'Prediabetes' ? 0.7 : 0.3;
            case 'Sangat Berpotensi': return $sugar_level == 'Diabetes' ? 0.9 : 0.1;
            default: return 0.33;
        }
    }

    private function getBMIProbability($bmi, $class) {
        $bmi_category = $this->getBMICategory($bmi);
        switch ($class) {
            case 'Tidak Berpotensi': return $bmi_category == 'Normal' ? 0.8 : 0.2;
            case 'Cukup Berpotensi': return $bmi_category == 'Overweight' ? 0.7 : 0.3;
            case 'Sangat Berpotensi': return $bmi_category == 'Obese' ? 0.9 : 0.1;
            default: return 0.33;
        }
    }

    private function getWaistCircumferenceProbability($waist, $gender, $class) {
        $waist_category = $this->getWaistCategory($waist, $gender);
        switch ($class) {
            case 'Tidak Berpotensi': return $waist_category == 'Normal' ? 0.8 : 0.2;
            case 'Cukup Berpotensi': return $waist_category == 'High' ? 0.7 : 0.3;
            case 'Sangat Berpotensi': return $waist_category == 'Very High' ? 0.9 : 0.1;
            default: return 0.33;
        }
    }

    private function getFamilyHistoryProbability($family_history, $class) {
        switch ($class) {
            case 'Tidak Berpotensi': return $family_history == 'Tidak Ada' ? 0.8 : 0.2;
            case 'Cukup Berpotensi': return $family_history == 'Ada' ? 0.7 : 0.3;
            case 'Sangat Berpotensi': return $family_history == 'Ada' ? 0.9 : 0.1;
            default: return 0.5;
        }
    }

    private function getPersonalHistoryProbability($personal_history, $class) {
        switch ($class) {
            case 'Tidak Berpotensi': return $personal_history == 'Tidak Ada' ? 0.8 : 0.2;
            case 'Cukup Berpotensi': return $personal_history == 'Ada' ? 0.7 : 0.3;
            case 'Sangat Berpotensi': return $personal_history == 'Ada' ? 0.9 : 0.1;
            default: return 0.5;
        }
    }

    private function getSmokingProbability($smoking_status, $class) {
        switch ($class) {
            case 'Tidak Berpotensi': return $smoking_status == 'Tidak Merokok' ? 0.8 : 0.2;
            case 'Cukup Berpotensi': return $smoking_status == 'Merokok' ? 0.7 : 0.3;
            case 'Sangat Berpotensi': return $smoking_status == 'Merokok' ? 0.9 : 0.1;
            default: return 0.33;
        }
    }

    private function getPhysicalActivityProbability($physical_activity, $class) {
        switch ($class) {
            case 'Tidak Berpotensi': return $physical_activity == 'Aktif' ? 0.8 : 0.2;
            case 'Cukup Berpotensi': return $physical_activity == 'Ringan' ? 0.7 : 0.3;
            case 'Sangat Berpotensi': return $physical_activity == 'Tidak Aktif' ? 0.9 : 0.1;
            default: return 0.33;
        }
    }

    private function getFruitVegetableProbability($consumption, $class) {
        switch ($class) {
            case 'Tidak Berpotensi': return $consumption == 'Cukup' ? 0.8 : 0.2;
            case 'Cukup Berpotensi': return $consumption == 'Kurang' ? 0.7 : 0.3;
            case 'Sangat Berpotensi': return $consumption == 'Kurang' ? 0.9 : 0.1;
            default: return 0.33;
        }
    }

    private function getBloodPressureLevel($systolic, $diastolic) {
        if ($systolic < 120 && $diastolic < 80) return 'Normal';
        if ($systolic < 130 && $diastolic < 80) return 'Elevated';
        if ($systolic < 140 || $diastolic < 90) return 'Stage 1';
        return 'Stage 2';
    }

    private function getBloodSugarLevel($blood_sugar) {
        if ($blood_sugar < 100) return 'Normal';
        if ($blood_sugar < 126) return 'Prediabetes';
        return 'Diabetes';
    }

    private function getBMICategory($bmi) {
        if ($bmi < 18.5) return 'Underweight';
        if ($bmi < 25) return 'Normal';
        if ($bmi < 30) return 'Overweight';
        return 'Obese';
    }

    private function getWaistCategory($waist, $gender) {
        if ($gender == 'L') {
            return $waist < 90 ? 'Normal' : ($waist < 102 ? 'High' : 'Very High');
        } else {
            return $waist < 80 ? 'Normal' : ($waist < 88 ? 'High' : 'Very High');
        }
    }
}

// Data Samples
$samples = [
    [
        'name' => 'Sampel 1 (Aini)',
        'age' => 52, 'gender' => 'P', 'systolic_pressure' => 150, 'diastolic_pressure' => 84,
        'blood_sugar' => 165, 'bmi' => 31.79, 'waist_circumference' => 102,
        'family_history' => 'Tidak Ada', 'personal_history' => 'Tidak Ada',
        'smoking_status' => 'Tidak Merokok', 'physical_activity' => 'Ringan', 'fruit_vegetable_consumption' => 'Cukup',
        'manual_class' => 'Hipertensi' // Based on user's manual calculation
    ],
    [
        'name' => 'Sampel 2 (Sumarni)',
        'age' => 52, 'gender' => 'P', 'systolic_pressure' => 160, 'diastolic_pressure' => 65,
        'blood_sugar' => 165, 'bmi' => 27.77, 'waist_circumference' => 92,
        'family_history' => 'Tidak Ada', 'personal_history' => 'Tidak Ada',
        'smoking_status' => 'Tidak Merokok', 'physical_activity' => 'Tidak Aktif', 'fruit_vegetable_consumption' => 'Cukup',
        'manual_class' => 'Hipertensi'
    ],
    [
        'name' => 'Sampel 3 (Ruli)',
        'age' => 43, 'gender' => 'P', 'systolic_pressure' => 113, 'diastolic_pressure' => 79,
        'blood_sugar' => 115, 'bmi' => 28.4, 'waist_circumference' => 87,
        'family_history' => 'Tidak Ada', 'personal_history' => 'Tidak Ada',
        'smoking_status' => 'Tidak Merokok', 'physical_activity' => 'Aktif', 'fruit_vegetable_consumption' => 'Cukup',
        'manual_class' => 'Normal'
    ],
    [
        'name' => 'Sampel 4 (Rosnaini)',
        'age' => 56, 'gender' => 'P', 'systolic_pressure' => 132, 'diastolic_pressure' => 71,
        'blood_sugar' => 124, 'bmi' => 21.62, 'waist_circumference' => 89,
        'family_history' => 'Tidak Ada', 'personal_history' => 'Tidak Ada',
        'smoking_status' => 'Tidak Merokok', 'physical_activity' => 'Aktif', 'fruit_vegetable_consumption' => 'Cukup',
        'manual_class' => 'Hipertensi'
    ],
    [
        'name' => 'Sampel 5 (Dona)',
        'age' => 25, 'gender' => 'P', 'systolic_pressure' => 117, 'diastolic_pressure' => 87,
        'blood_sugar' => 126, 'bmi' => 19.29, 'waist_circumference' => 79,
        'family_history' => 'Tidak Ada', 'personal_history' => 'Tidak Ada',
        'smoking_status' => 'Tidak Merokok', 'physical_activity' => 'Aktif', 'fruit_vegetable_consumption' => 'Cukup',
        'manual_class' => 'Normal'
    ]
];

$nb = new NaiveBayesTest();

echo "HASIL PENGUJIAN SISTEM VS MANUAL\n";
echo "==================================================\n";
printf("%-20s | %-15s | %-20s | %-10s\n", "Sampel", "Manual Class", "System Prediction", "Match?");
echo "--------------------------------------------------\n";

$correct = 0;
foreach ($samples as $sample) {
    $result = $nb->predict($sample);
    $sys_class = $result['status'];
    
    // Map System Class to Binary for Comparison
    // Tidak Berpotensi -> Normal
    // Cukup/Sangat -> Hipertensi
    $sys_binary = ($sys_class == 'Tidak Berpotensi') ? 'Normal' : 'Hipertensi';
    
    $match = ($sample['manual_class'] == $sys_binary) ? 'MATCH' : 'MISMATCH';
    if ($match == 'MATCH') $correct++;
    
    printf("%-20s | %-15s | %-20s | %-10s\n", 
        $sample['name'], 
        $sample['manual_class'], 
        $sys_class . " (" . $sys_binary . ")", 
        $match
    );
}

echo "--------------------------------------------------\n";
$accuracy = ($correct / count($samples)) * 100;
echo "Akurasi: $accuracy%\n";
?>