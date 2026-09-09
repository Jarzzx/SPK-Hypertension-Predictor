<?php
require_once 'config/config.php';
require_once 'config/database.php';

echo "<h2>Fixing Personal History Column Issue</h2>";

try {
    // Check current ENUM values
    echo "<h3>1. Checking current ENUM values...</h3>";
    $result = $conn->query("SELECT COLUMN_NAME, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'checkups' AND COLUMN_NAME = 'personal_history'");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Current personal_history type: " . $row['COLUMN_TYPE'] . "<br>";
    }
    
    // Fix the column
    echo "<h3>2. Fixing personal_history column...</h3>";
    $fix_query = "ALTER TABLE checkups MODIFY COLUMN personal_history ENUM('Tidak Ada', 'Ada') DEFAULT 'Tidak Ada' NOT NULL";
    if ($conn->query($fix_query)) {
        echo "✅ Column fixed successfully!<br>";
    } else {
        echo "❌ Failed to fix column: " . $conn->error . "<br>";
    }
    
    // Update existing invalid values
    echo "<h3>3. Updating existing invalid values...</h3>";
    $update_query = "UPDATE checkups SET personal_history = 'Tidak Ada' WHERE personal_history IS NULL OR personal_history NOT IN ('Tidak Ada', 'Ada')";
    if ($conn->query($update_query)) {
        echo "✅ Invalid values updated!<br>";
    } else {
        echo "❌ Failed to update values: " . $conn->error . "<br>";
    }
    
    // Verify the fix
    echo "<h3>4. Verifying the fix...</h3>";
    $result = $conn->query("SELECT COLUMN_NAME, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'checkups' AND COLUMN_NAME = 'personal_history'");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "New personal_history type: " . $row['COLUMN_TYPE'] . "<br>";
    }
    
    // Test insert
    echo "<h3>5. Testing insert...</h3>";
    $test_query = "INSERT INTO checkups (patient_id, systolic_pressure, diastolic_pressure, blood_sugar, height, weight, bmi, waist_circumference, family_history, personal_history, smoking_status, physical_activity, fruit_vegetable_consumption) VALUES (1, 120, 80, 100.0, 170.0, 70.0, 24.22, 85.0, 'Tidak Ada', 'Ada', 'Tidak Merokok', 'Tidak Aktif', 'Cukup')";
    if ($conn->query($test_query)) {
        echo "✅ Test insert successful!<br>";
        // Delete test record
        $conn->query("DELETE FROM checkups WHERE id = " . $conn->insert_id);
        echo "✅ Test record cleaned up!<br>";
    } else {
        echo "❌ Test insert failed: " . $conn->error . "<br>";
    }
    
    echo "<h3>✅ Database fix completed!</h3>";
    echo "<p>Now try adding checkup data again. The personal_history issue should be resolved.</p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error during fix:</h3>";
    echo $e->getMessage();
}
?> 