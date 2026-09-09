<?php
/**
 * Naive Bayes Algorithm for Hypertension Prediction
 * 
 * This class implements the Naive Bayes algorithm to predict hypertension risk
 * based on patient checkup data. The algorithm uses multiple features including
 * age, gender, family history, personal history, smoking, physical activity,
 * fruit/vegetable consumption, height, weight, BMI, waist circumference,
 * blood pressure, and blood sugar.
 */
class NaiveBayes {
    private $conn;
    private $classes = ['Tidak Berpotensi', 'Cukup Berpotensi', 'Sangat Berpotensi'];
    
    // Weights removed to match manual calculation method (standard Naive Bayes multiplication)
    // Importance is now handled purely by probability variance in each helper function.
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Predict hypertension risk for a patient using Standard Naive Bayes
     */
    public function predict($patient_id) {
        // Get patient's checkup data (up to last 3 checkups for sequential update)
        $checkups = $this->getPatientCheckups($patient_id);
        
        if (empty($checkups)) {
            return false; // No data
        }
        
        // Get patient info
        $patient = $this->getPatientInfo($patient_id);
        
        // Initialize priors (Uniform)
        $priors = [];
        foreach ($this->classes as $class) {
            $priors[$class] = 1 / count($this->classes);
        }
        
        // Sequential Bayesian Update
        foreach ($checkups as $index => $checkup) {
            // Merge patient static data with checkup dynamic data
            $data = array_merge($checkup, [
                'age' => $patient['age'],
                'gender' => $patient['gender']
            ]);
            
            $current_probs = [];
            $total_prob = 0;
            
            foreach ($this->classes as $class) {
                // Likelihood P(Data|Class) - Standard Multiplication
                $likelihood = $this->calculateLikelihood($data, $class);
                
                // Posterior P(Class|Data) = Likelihood * Prior
                $posterior = $likelihood * $priors[$class];
                
                $current_probs[$class] = $posterior;
                $total_prob += $posterior;
            }
            
            // Normalize probabilities
            if ($total_prob > 0) {
                foreach ($current_probs as $class => $prob) {
                    $priors[$class] = $prob / $total_prob;
                }
            }
        }
        
        // The final priors are our final probabilities
        $final_probabilities = $priors;
        
        // Find the class with highest probability
        $predicted_class = array_keys($final_probabilities, max($final_probabilities))[0];
        
        // Calculate Risk Score (Weighted Probability)
        // Tidak Berpotensi = 0% Risk
        // Cukup Berpotensi = 50% Risk Contribution
        // Sangat Berpotensi = 100% Risk Contribution
        $risk_score = ($final_probabilities['Cukup Berpotensi'] * 0.5) + ($final_probabilities['Sangat Berpotensi'] * 1.0);
        
        // Save prediction to database
        // We save risk_score as 'confidence' for consistent display percentage (0-100% Risk)
        $this->savePrediction($patient_id, $predicted_class, $risk_score);
        
        return [
            'status' => $predicted_class,
            'confidence' => $risk_score, // Return risk score as confidence
            'probabilities' => $final_probabilities
        ];
    }
    
    /**
     * Get patient's checkup data (Last 3, sorted chronologically)
     */
    private function getPatientCheckups($patient_id) {
        // Get last 3 checkups, but order them by time ASC so we update sequentially
        $query = "SELECT * FROM (
                    SELECT * FROM checkups 
                    WHERE patient_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 3
                  ) sub 
                  ORDER BY created_at ASC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get patient information
     */
    private function getPatientInfo($patient_id) {
        $query = "SELECT * FROM patients WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Calculate likelihood P(features|class)
     */
    private function calculateLikelihood($data, $class) {
        $likelihood = 1.0;
        
        // Age
        $likelihood *= $this->getAgeProbability($data['age'], $class);
        
        // Gender
        $likelihood *= $this->getGenderProbability($data['gender'], $class);
        
        // Blood pressure features
        $likelihood *= $this->getBloodPressureProbability($data['systolic_pressure'], $data['diastolic_pressure'], $class);
        
        // Blood sugar
        $likelihood *= $this->getBloodSugarProbability($data['blood_sugar'], $class);
        
        // BMI
        $likelihood *= $this->getBMIProbability($data['bmi'], $class);
        
        // Waist circumference
        $likelihood *= $this->getWaistCircumferenceProbability($data['waist_circumference'], $data['gender'], $class);
        
        // Lifestyle factors
        $likelihood *= $this->getFamilyHistoryProbability($data['family_history'], $class);
        $likelihood *= $this->getPersonalHistoryProbability($data['personal_history'], $class);
        $likelihood *= $this->getSmokingProbability($data['smoking_status'], $class);
        $likelihood *= $this->getPhysicalActivityProbability($data['physical_activity'], $class);
        $likelihood *= $this->getFruitVegetableProbability($data['fruit_vegetable_consumption'], $class);
        
        return $likelihood;
    }
    
    /**
     * Age probability
     */
    private function getAgeProbability($age, $class) {
        // Distribution (Sum to 1 per class approx)
        // Age Categories: Young (<40), Middle (40-60), Old (>60)
        
        $is_young = $age < 40;
        $is_middle = $age >= 40 && $age < 60;
        $is_old = $age >= 60;

        switch ($class) {
            case 'Tidak Berpotensi':
                // Young people are most likely to be here
                if ($is_young) return 0.6;
                if ($is_middle) return 0.3;
                return 0.1; 
                
            case 'Cukup Berpotensi':
                // Middle age is the peak for "Starting to have issues"
                if ($is_young) return 0.2;
                if ($is_middle) return 0.5;
                return 0.3;

            case 'Sangat Berpotensi':
                // Old age is major risk
                if ($is_young) return 0.1;
                if ($is_middle) return 0.3;
                return 0.6; 
                
            default:
                return 0.33;
        }
    }
    
    /**
     * Gender probability
     */
    private function getGenderProbability($gender, $class) {
        // Men have higher risk generally until older age
        switch ($class) {
            case 'Tidak Berpotensi':
                return $gender == 'P' ? 0.6 : 0.4; // Women slightly healthier stats
            case 'Cukup Berpotensi':
                return 0.5; // Neutral
            case 'Sangat Berpotensi':
                return $gender == 'L' ? 0.6 : 0.4; // Men higher risk
            default:
                return 0.5;
        }
    }
    
    /**
     * Blood pressure probability based on hypertension guidelines
     */
    private function getBloodPressureProbability($systolic, $diastolic, $class) {
        $pressure_level = $this->getBloodPressureLevel($systolic, $diastolic);
        
        switch ($class) {
            case 'Tidak Berpotensi':
                // Distribution: Normal(70%), Elevated(20%), Stage1(9%), Stage2(1%)
                if ($pressure_level == 'Normal') return 0.70;
                if ($pressure_level == 'Elevated') return 0.20;
                if ($pressure_level == 'Stage 1') return 0.099;
                return 0.001; // Stage 2 is almost impossible for healthy people

            case 'Cukup Berpotensi':
                // Distribution: Normal(10%), Elevated(30%), Stage1(50%), Stage2(10%)
                if ($pressure_level == 'Normal') return 0.10;
                if ($pressure_level == 'Elevated') return 0.30;
                if ($pressure_level == 'Stage 1') return 0.59;
                return 0.01;

            case 'Sangat Berpotensi':
                // Distribution: Normal(1%), Elevated(9%), Stage1(30%), Stage2(60%)
                if ($pressure_level == 'Normal') return 0.0001;
                if ($pressure_level == 'Elevated') return 0.0099;
                if ($pressure_level == 'Stage 1') return 0.04;
                return 0.95; // Stage 2 is a massive indicator for High Risk
                
            default:
                return 0.25;
        }
    }

    /**
     * Blood sugar probability
     */
    private function getBloodSugarProbability($blood_sugar, $class) {
        $sugar_level = $this->getBloodSugarLevel($blood_sugar);
        
        switch ($class) {
            case 'Tidak Berpotensi':
                // Distribution: Normal(80%), Prediabetes(15%), Diabetes(5%)
                if ($sugar_level == 'Normal') return 0.85;
                if ($sugar_level == 'Prediabetes') return 0.149;
                return 0.001; // Diabetes is almost impossible for healthy people

            case 'Cukup Berpotensi':
                // Distribution: Normal(20%), Prediabetes(60%), Diabetes(20%)
                if ($sugar_level == 'Normal') return 0.20;
                if ($sugar_level == 'Prediabetes') return 0.79;
                return 0.01;

            case 'Sangat Berpotensi':
                // Distribution: Normal(5%), Prediabetes(25%), Diabetes(70%)
                if ($sugar_level == 'Normal') return 0.0001;
                if ($sugar_level == 'Prediabetes') return 0.0099;
                return 0.99; // Diabetes is a massive indicator for High Risk
                
            default:
                return 0.33;
        }
    }
    
    /**
     * BMI probability
     */
    private function getBMIProbability($bmi, $class) {
        $bmi_category = $this->getBMICategory($bmi);
        // Cats: Underweight, Normal, Overweight, Obese
        
        switch ($class) {
            case 'Tidak Berpotensi':
                // Normal/Underweight dominant
                if ($bmi_category == 'Normal' || $bmi_category == 'Underweight') return 0.70;
                if ($bmi_category == 'Overweight') return 0.20;
                return 0.10; // Obese

            case 'Cukup Berpotensi':
                // Overweight dominant
                if ($bmi_category == 'Normal' || $bmi_category == 'Underweight') return 0.20;
                if ($bmi_category == 'Overweight') return 0.50;
                return 0.30; // Obese

            case 'Sangat Berpotensi':
                // Obese dominant
                if ($bmi_category == 'Normal' || $bmi_category == 'Underweight') return 0.10;
                if ($bmi_category == 'Overweight') return 0.30;
                return 0.60; // Obese
                
            default:
                return 0.33;
        }
    }
    
    /**
     * Waist circumference probability
     */
    private function getWaistCircumferenceProbability($waist, $gender, $class) {
        $waist_category = $this->getWaistCategory($waist, $gender);
        // Cats: Normal, High, Very High
        
        switch ($class) {
            case 'Tidak Berpotensi':
                if ($waist_category == 'Normal') return 0.70;
                if ($waist_category == 'High') return 0.20;
                return 0.10;

            case 'Cukup Berpotensi':
                if ($waist_category == 'Normal') return 0.20;
                if ($waist_category == 'High') return 0.50;
                return 0.30;

            case 'Sangat Berpotensi':
                if ($waist_category == 'Normal') return 0.10;
                if ($waist_category == 'High') return 0.30;
                return 0.60;
                
            default:
                return 0.33;
        }
    }
    
    /**
     * Family history probability
     */
    private function getFamilyHistoryProbability($family_history, $class) {
        // Values: 'Tidak Ada', 'Hipertensi', 'Diabetes Melitus', 'Hipertensi dan Diabetes Melitus', 'Ada'
        // Simplified: None, One (HT/DM/Ada), Both
        
        $is_none = ($family_history == 'Tidak Ada');
        $is_both = ($family_history == 'Hipertensi dan Diabetes Melitus');
        $is_one = !$is_none && !$is_both; // HT, DM, or Ada
        
        switch ($class) {
            case 'Tidak Berpotensi':
                if ($is_none) return 0.70;
                if ($is_one) return 0.20;
                return 0.10; // Both

            case 'Cukup Berpotensi':
                if ($is_none) return 0.30;
                if ($is_one) return 0.50;
                return 0.20;

            case 'Sangat Berpotensi':
                if ($is_none) return 0.10;
                if ($is_one) return 0.40;
                return 0.50; // Both is strong indicator
                
            default:
                return 0.33;
        }
    }

    /**
     * Personal history probability
     */
    private function getPersonalHistoryProbability($personal_history, $class) {
        // Same logic as Family History but stronger weights for Personal
        
        $is_none = ($personal_history == 'Tidak Ada');
        $is_both = ($personal_history == 'Hipertensi dan Diabetes Melitus');
        $is_one = !$is_none && !$is_both;
        
        switch ($class) {
            case 'Tidak Berpotensi':
                if ($is_none) return 0.95;
                if ($is_one) return 0.01; 
                return 0.001;

            case 'Cukup Berpotensi':
                if ($is_none) return 0.89;
                if ($is_one) return 0.10; 
                return 0.01;

            case 'Sangat Berpotensi':
                if ($is_none) return 0.09; // Possible to be high risk without history
                if ($is_one) return 0.90; // High correlation
                return 0.99; // Both is almost certain
                
            default:
                return 0.33;
        }
    }

    /**
     * Smoking probability
     */
    private function getSmokingProbability($smoking_status, $class) {
        $is_smoker = ($smoking_status == 'Merokok');
        
        switch ($class) {
            case 'Tidak Berpotensi':
                return $is_smoker ? 0.30 : 0.70; // Non-smokers dominant
            case 'Cukup Berpotensi':
                return 0.50; // Mixed
            case 'Sangat Berpotensi':
                return $is_smoker ? 0.70 : 0.30; // Smokers dominant
            default:
                return 0.5;
        }
    }

    /**
     * Physical activity probability
     */
    private function getPhysicalActivityProbability($physical_activity, $class) {
        $is_active = ($physical_activity == 'Sering' || $physical_activity == 'Aktif' || $physical_activity == 'Sedang');
        
        switch ($class) {
            case 'Tidak Berpotensi':
                return $is_active ? 0.70 : 0.30;
            case 'Cukup Berpotensi':
                return 0.50;
            case 'Sangat Berpotensi':
                return $is_active ? 0.30 : 0.70;
            default:
                return 0.5;
        }
    }

    /**
     * Fruit and vegetable probability
     */
    private function getFruitVegetableProbability($consumption, $class) {
        $is_good = ($consumption == 'Sering' || $consumption == 'Cukup');
        
        switch ($class) {
            case 'Tidak Berpotensi':
                return $is_good ? 0.70 : 0.30;
            case 'Cukup Berpotensi':
                return 0.50;
            case 'Sangat Berpotensi':
                return $is_good ? 0.30 : 0.70;
            default:
                return 0.5;
        }
    }
    
    /**
     * Get blood pressure level
     */
    private function getBloodPressureLevel($systolic, $diastolic) {
        // Values based on user provided image (ACC/AHA 2017 style values, labeled as JNC 7 in image)
        if ($systolic < 120 && $diastolic < 80) {
            return 'Normal';
        } elseif ($systolic < 130 && $diastolic < 80) {
            return 'Elevated';
        } elseif ($systolic >= 140 || $diastolic >= 90) {
            return 'Stage 2';
        } else {
            // Stage 1: Sistolik 130-139 OR Diastolik 80-89
            return 'Stage 1';
        }
    }
    
    /**
     * Get blood sugar level
     */
    private function getBloodSugarLevel($blood_sugar) {
        if ($blood_sugar < 100) {
            return 'Normal';
        } elseif ($blood_sugar < 126) {
            return 'Prediabetes';
        } else {
            return 'Diabetes';
        }
    }
    
    /**
     * Get BMI category
     */
    private function getBMICategory($bmi) {
        if ($bmi < 18.5) {
            return 'Underweight';
        } elseif ($bmi < 25) {
            return 'Normal';
        } elseif ($bmi < 30) {
            return 'Overweight';
        } else {
            return 'Obese';
        }
    }
    
    /**
     * Get waist circumference category
     */
    private function getWaistCategory($waist, $gender) {
        if ($gender == 'L') {
            if ($waist < 90) {
                return 'Normal';
            } elseif ($waist < 102) {
                return 'High';
            } else {
                return 'Very High';
            }
        } else {
            if ($waist < 80) {
                return 'Normal';
            } elseif ($waist < 88) {
                return 'High';
            } else {
                return 'Very High';
            }
        }
    }
    
    /**
     * Save prediction to database
     */
    private function savePrediction($patient_id, $status, $confidence) {
        // Delete existing prediction for this patient
        $delete_query = "DELETE FROM predictions WHERE patient_id = ?";
        $delete_stmt = $this->conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $patient_id);
        $delete_stmt->execute();
        
        // Insert new prediction
        $insert_query = "INSERT INTO predictions (patient_id, status, probability, confidence) VALUES (?, ?, ?, ?)";
        $insert_stmt = $this->conn->prepare($insert_query);
        $insert_stmt->bind_param("isdd", $patient_id, $status, $confidence, $confidence);
        $insert_stmt->execute();
    }
}
?> 
