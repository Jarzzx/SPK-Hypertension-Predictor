<?php
// Test bind_param issue
$patient_id = 1;
$systolic_pressure = 120;
$diastolic_pressure = 80;
$blood_sugar = 100.0;
$height = 170.0;
$weight = 70.0;
$bmi = 24.22;
$waist_circumference = 85.0;
$family_history = 'Tidak Ada';
$personal_history = 'Tidak Ada';
$smoking_status = 'Tidak Merokok';
$physical_activity = 'Tidak Aktif';
$fruit_vegetable_consumption = 'Cukup';

echo "Parameters:\n";
echo "1. patient_id: $patient_id (int)\n";
echo "2. systolic_pressure: $systolic_pressure (int)\n";
echo "3. diastolic_pressure: $diastolic_pressure (int)\n";
echo "4. blood_sugar: $blood_sugar (double)\n";
echo "5. height: $height (double)\n";
echo "6. weight: $weight (double)\n";
echo "7. bmi: $bmi (double)\n";
echo "8. waist_circumference: $waist_circumference (double)\n";
echo "9. family_history: $family_history (string)\n";
echo "10. personal_history: $personal_history (string)\n";
echo "11. smoking_status: $smoking_status (string)\n";
echo "12. physical_activity: $physical_activity (string)\n";
echo "13. fruit_vegetable_consumption: $fruit_vegetable_consumption (string)\n";

echo "\nBind param string: iiiddddsssss\n";
echo "Length: " . strlen("iiiddddsssss") . "\n";
echo "Expected: 13\n";

echo "\nVariables count: 13\n";
?> 