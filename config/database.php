<?php
// Database configuration with environment variable support
$host     = getenv('DB_HOST')     ?: 'localhost';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_DATABASE') ?: 'spk_hipertensipredictor';

// Create connection
$conn = new mysqli($host, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Create database if not exists (only on localhost)
if ($host === 'localhost' || $host === '127.0.0.1') {
    $conn->query("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
}
$conn->select_db($database);

// Create tables if not exists and ensure required columns exist
createTables($conn);
ensureCheckupColumns($conn);
seedDefaultOperator($conn);

function createTables($conn) {
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        role ENUM('operator', 'admin') DEFAULT 'operator',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS patients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        age INT NOT NULL,
        gender ENUM('L', 'P') NOT NULL,
        address TEXT,
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS checkups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        systolic_pressure INT NOT NULL,
        diastolic_pressure INT NOT NULL,
        blood_sugar FLOAT NOT NULL,
        height FLOAT NOT NULL,
        weight FLOAT NOT NULL,
        bmi FLOAT NOT NULL,
        waist_circumference FLOAT NOT NULL,
        family_history ENUM('Tidak Ada', 'Hipertensi', 'Diabetes Melitus', 'Hipertensi dan Diabetes Melitus') NOT NULL,
        personal_history ENUM('Tidak Ada', 'Hipertensi', 'Diabetes Melitus', 'Hipertensi dan Diabetes Melitus') NOT NULL,
        smoking_status ENUM('Tidak Merokok', 'Merokok') NOT NULL,
        physical_activity ENUM('Tidak Aktif', 'Ringan', 'Aktif') NOT NULL,
        fruit_vegetable_consumption ENUM('Kurang', 'Cukup') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS predictions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        status ENUM('Tidak Berpotensi', 'Cukup Berpotensi', 'Sangat Berpotensi') NOT NULL,
        probability FLOAT NOT NULL,
        confidence FLOAT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
    )");
}

function ensureCheckupColumns($conn) {
    $requiredColumns = [
        'height' => "ALTER TABLE checkups ADD COLUMN height FLOAT NOT NULL DEFAULT 170",
        'weight' => "ALTER TABLE checkups ADD COLUMN weight FLOAT NOT NULL DEFAULT 70",
        'waist_circumference' => "ALTER TABLE checkups ADD COLUMN waist_circumference FLOAT NOT NULL DEFAULT 80",
        'family_history' => "ALTER TABLE checkups MODIFY COLUMN family_history ENUM('Tidak Ada', 'Hipertensi', 'Diabetes Melitus', 'Hipertensi dan Diabetes Melitus') NOT NULL",
        'personal_history' => "ALTER TABLE checkups ADD COLUMN personal_history ENUM('Tidak Ada', 'Hipertensi', 'Diabetes Melitus', 'Hipertensi dan Diabetes Melitus') NOT NULL DEFAULT 'Tidak Ada'",
        'smoking_status' => "ALTER TABLE checkups MODIFY COLUMN smoking_status ENUM('Tidak Merokok','Merokok') NOT NULL",
        'physical_activity' => "ALTER TABLE checkups MODIFY COLUMN physical_activity ENUM('Tidak Aktif','Ringan','Aktif') NOT NULL",
        'fruit_vegetable_consumption' => "ALTER TABLE checkups ADD COLUMN fruit_vegetable_consumption ENUM('Kurang','Cukup') NOT NULL DEFAULT 'Cukup'",
        'bmi' => "ALTER TABLE checkups ADD COLUMN bmi FLOAT NOT NULL DEFAULT 0"
    ];

    foreach ($requiredColumns as $col => $alter) {
        $existsQuery = $conn->prepare("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'checkups' AND COLUMN_NAME = ?");
        $existsQuery->bind_param("s", $col);
        $existsQuery->execute();
        $cnt = $existsQuery->get_result()->fetch_assoc()['cnt'];
        $existsQuery->close();
        if ($cnt == 0 || in_array($col, ['family_history','smoking_status','physical_activity'])) {
            $conn->query($alter);
        }
    }
}

function seedDefaultOperator($conn) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = 'operator'");
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    if (!$exists) {
        $hash = password_hash('operator123', PASSWORD_DEFAULT);
        $ins = $conn->prepare("INSERT INTO users (username, password, name, role) VALUES ('operator', ?, 'Operator Puskesmas', 'operator')");
        $ins->bind_param("s", $hash);
        $ins->execute();
        $ins->close();
    }
}
?> 
