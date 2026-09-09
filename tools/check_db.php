<?php
require_once 'config/database.php';
$res = $conn->query("SELECT * FROM checkups WHERE systolic_pressure=160 AND diastolic_pressure=65");
if ($res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "No match found.\n";
    // List all to check
    $res = $conn->query("SELECT * FROM checkups");
    while($row = $res->fetch_assoc()) {
        echo "ID: {$row['id']}, BP: {$row['systolic_pressure']}/{$row['diastolic_pressure']}\n";
    }
}
?>