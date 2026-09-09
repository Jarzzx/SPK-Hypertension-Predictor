<?php
require_once 'config/database.php';
$names = ['Sumarni', 'Aini', 'Ruli', 'Rosnaini', 'Dona'];
$sql = "SELECT p.id as pid, p.name, c.id as cid, c.created_at FROM patients p JOIN checkups c ON p.id=c.patient_id WHERE ";
$conditions = [];
foreach ($names as $name) {
    $conditions[] = "p.name LIKE '%$name%'";
}
$sql .= implode(' OR ', $conditions);
$sql .= " ORDER BY c.created_at DESC";

echo "Executing: $sql\n";
$res = $conn->query($sql);
if ($res) {
    while($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error: " . $conn->error;
}
?>