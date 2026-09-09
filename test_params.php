<?php
// Test parameters exactly as they appear in the form
$patient_id = 9;
$systolic_pressure = 141;
$diastolic_pressure = 81;
$blood_sugar = 158.0;
$height = 153.0;
$weight = 64.0;
$bmi = 27.339911999658;
$waist_circumference = 91.0;
$family_history = 'Tidak Ada';
$personal_history = 'Tidak Ada';
$smoking_status = 'Tidak Merokok';
$physical_activity = 'Tidak Aktif';
$fruit_vegetable_consumption = 'Cukup';

echo "Testing parameters:\n";
echo "1. patient_id: $patient_id\n";
echo "2. systolic_pressure: $systolic_pressure\n";
echo "3. diastolic_pressure: $diastolic_pressure\n";
echo "4. blood_sugar: $blood_sugar\n";
echo "5. height: $height\n";
echo "6. weight: $weight\n";
echo "7. bmi: $bmi\n";
echo "8. waist_circumference: $waist_circumference\n";
echo "9. family_history: $family_history\n";
echo "10. personal_history: $personal_history\n";
echo "11. smoking_status: $smoking_status\n";
echo "12. physical_activity: $physical_activity\n";
echo "13. fruit_vegetable_consumption: $fruit_vegetable_consumption\n";

echo "\nTotal parameters: 13\n";
echo "Type string: iiiddddsssss\n";
echo "Type string length: " . strlen("iiiddddsssss") . "\n";

// Test bind_param
$query = "INSERT INTO test_table (col1, col2, col3, col4, col5, col6, col7, col8, col9, col10, col11, col12, col13) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
echo "\nQuery placeholders: " . substr_count($query, '?') . "\n";
?> 