<?php
require_once 'config/config.php';
require_once 'config/database.php';

// Test bind_param with the exact same parameters
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

echo "Testing bind_param...\n";

$query = "INSERT INTO checkups (patient_id, systolic_pressure, diastolic_pressure, blood_sugar, 
                              height, weight, bmi, waist_circumference, family_history, personal_history,
                              smoking_status, physical_activity, fruit_vegetable_consumption) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);

if (!$stmt) {
    echo "Prepare failed: " . $conn->error . "\n";
    exit;
}

echo "Query prepared successfully\n";

$result = $stmt->bind_param("iiiddddsssss", 
    $patient_id, 
    $systolic_pressure, 
    $diastolic_pressure, 
    $blood_sugar, 
    $height, 
    $weight, 
    $bmi, 
    $waist_circumference, 
    $family_history, 
    $personal_history, 
    $smoking_status, 
    $physical_activity, 
    $fruit_vegetable_consumption
);

if (!$result) {
    echo "Bind failed: " . $stmt->error . "\n";
    exit;
}

echo "Bind successful\n";

$result = $stmt->execute();

if (!$result) {
    echo "Execute failed: " . $stmt->error . "\n";
    exit;
}

echo "Execute successful\n";
?> 