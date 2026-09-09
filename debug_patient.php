<?php
require_once 'config/config.php';
require_once 'config/database.php';

echo "<h2>Debug Patient Data</h2>";

// Check if form was submitted
if ($_POST) {
    echo "<h3>POST Data Received:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Check database connection
    if ($conn->connect_error) {
        echo "<p style='color: red;'>Database connection failed: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>Database connection successful</p>";
        
        // Try to insert test data
        $name = trim($_POST['name'] ?? 'Test Patient');
        $age = (int)($_POST['age'] ?? 25);
        $gender = trim($_POST['gender'] ?? 'L');
        $address = trim($_POST['address'] ?? 'Test Address');
        $phone = trim($_POST['phone'] ?? '08123456789');
        
        $query = "INSERT INTO patients (name, age, gender, address, phone, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("sisss", $name, $age, $gender, $address, $phone);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✅ Data berhasil dimasukkan! ID: " . $stmt->insert_id . "</p>";
            } else {
                echo "<p style='color: red;'>❌ Gagal memasukkan data: " . $stmt->error . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Gagal prepare statement: " . $conn->error . "</p>";
        }
    }
}

// Show current patients
echo "<h3>Current Patients in Database:</h3>";
$result = $conn->query("SELECT * FROM patients ORDER BY created_at DESC LIMIT 10");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Age</th><th>Gender</th><th>Address</th><th>Phone</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . $row['age'] . "</td>";
        echo "<td>" . $row['gender'] . "</td>";
        echo "<td>" . htmlspecialchars($row['address']) . "</td>";
        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Gagal query patients: " . $conn->error . "</p>";
}
?>

<h3>Test Form:</h3>
<form method="POST">
    <p>
        <label>Name: <input type="text" name="name" value="Test Patient" required></label>
    </p>
    <p>
        <label>Age: <input type="number" name="age" value="25" required></label>
    </p>
    <p>
        <label>Gender: 
            <select name="gender" required>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
            </select>
        </label>
    </p>
    <p>
        <label>Address: <textarea name="address" required>Test Address</textarea></label>
    </p>
    <p>
        <label>Phone: <input type="text" name="phone" value="08123456789"></label>
    </p>
    <p>
        <button type="submit">Test Insert</button>
    </p>
</form> 