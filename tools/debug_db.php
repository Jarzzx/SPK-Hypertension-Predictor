<?php
require_once __DIR__ . '/../config/database.php';

echo "Tables:\n";
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_row()) {
    echo "- " . $row[0] . "\n";
    $count = $conn->query("SELECT COUNT(*) FROM " . $row[0])->fetch_row()[0];
    echo "  Count: $count\n";
}

echo "\nLatest Checkups:\n";
$res = $conn->query("SELECT * FROM checkups ORDER BY created_at DESC LIMIT 5");
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "No checkups found.\n";
}

echo "\nLatest Patients:\n";
$res = $conn->query("SELECT * FROM patients ORDER BY created_at DESC LIMIT 5");
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "No patients found.\n";
}
?>