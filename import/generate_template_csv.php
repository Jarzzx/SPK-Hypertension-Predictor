<?php
session_start();
require_once '../config/config.php';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="template_pasien.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Create CSV content
$csv_content = "Nama,Umur,Jenis Kelamin,Alamat,Telepon\n";
$csv_content .= "John Doe,25,L,Jl. Contoh No. 123,08123456789\n";
$csv_content .= "Jane Smith,30,P,Jl. Sample No. 456,08987654321\n";
$csv_content .= "Ahmad Rizki,35,L,Jl. Test No. 789,08765432109\n";

// Output CSV content
echo $csv_content;
exit();
?>