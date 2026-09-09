<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

// Get checkup ID from URL
$checkup_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($checkup_id <= 0) {
    header('Location: ' . BASE_URL . 'checkups/index.php?error=ID pengecekan tidak valid!');
    exit();
}

// Get checkup data with patient info
$stmt = $conn->prepare("
    SELECT c.*, p.name as patient_name 
    FROM checkups c 
    JOIN patients p ON c.patient_id = p.id 
    WHERE c.id = ?
");
$stmt->bind_param("i", $checkup_id);
$stmt->execute();
$result = $stmt->get_result();
$checkup = $result->fetch_assoc();

if (!$checkup) {
    header('Location: ' . BASE_URL . 'checkups/index.php?error=Pengecekan tidak ditemukan!');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengecekan - SPK Hipertensi Predictor</title>
    <?php include '../includes/header.php'; ?>
</head>
<body class="bg-gray-50">
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main">
        <main class="content">
            <div class="container-fluid p-4">
                    <!-- Header Section -->
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">
                            Detail Pengecekan
                        </h1>
                        <div class="flex gap-2">
                            <a href="<?php echo BASE_URL; ?>checkups/index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <a href="<?php echo BASE_URL; ?>checkups/edit.php?id=<?php echo $checkup_id; ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Edit
                            </a>
                        </div>
                    </div>
                    
                    <!-- Patient Information Card -->
                    <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h5 class="m-0 font-bold text-indigo-600">
                                <i class="fas fa-user me-2"></i>Informasi Pasien
                            </h5>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Nama Pasien:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo htmlspecialchars($checkup['patient_name']); ?></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Tanggal Pengecekan:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo date('d/m/Y', strtotime($checkup['created_at'])); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Checkup Results Card -->
                    <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h5 class="m-0 font-bold text-indigo-600">
                                <i class="fas fa-clipboard-list me-2"></i>Hasil Pengecekan
                            </h5>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Tekanan Darah Sistolik:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo $checkup['systolic_pressure']; ?> mmHg</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Tekanan Darah Diastolik:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo $checkup['diastolic_pressure']; ?> mmHg</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Tinggi Badan:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo $checkup['height']; ?> cm</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Gula Darah:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo $checkup['blood_sugar']; ?> mg/dL</div>
                                    </div>
                                </div>
                                <div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Berat Badan:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo $checkup['weight']; ?> kg</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">BMI:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo number_format($checkup['bmi'], 2); ?></div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Lingkar Perut:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo $checkup['waist_circumference']; ?> cm</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Status Merokok:</div>
                                        <div class="col-span-2">
                                            <span class="<?php echo $checkup['smoking_status'] == 'Merokok' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> px-2 py-1 rounded-full text-xs font-semibold">
                                                <?php echo $checkup['smoking_status']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Riwayat Pribadi:</div>
                                        <div class="col-span-2">
                                            <span class="<?php echo $checkup['personal_history'] != 'Tidak Ada' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> px-2 py-1 rounded-full text-xs font-semibold">
                                                <?php echo $checkup['personal_history']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Aktivitas Fisik:</div>
                                        <div class="col-span-2">
                                            <?php 
                                            $activityClass = '';
                                            switch ($checkup['physical_activity']) {
                                                case 'Tidak Aktif': $activityClass = 'bg-red-100 text-red-800'; break;
                                                case 'Ringan': $activityClass = 'bg-yellow-100 text-yellow-800'; break;
                                                case 'Aktif': $activityClass = 'bg-green-100 text-green-800'; break;
                                            }
                                            ?>
                                            <span class="<?php echo $activityClass; ?> px-2 py-1 rounded-full text-xs font-semibold">
                                                <?php echo $checkup['physical_activity']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Riwayat Keluarga:</div>
                                        <div class="col-span-2">
                                            <span class="<?php echo $checkup['family_history'] != 'Tidak Ada' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> px-2 py-1 rounded-full text-xs font-semibold">
                                                <?php echo $checkup['family_history']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Konsumsi Buah/Sayur:</div>
                                        <div class="col-span-2">
                                            <span class="<?php echo $checkup['fruit_vegetable_consumption'] == 'Kurang' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'; ?> px-2 py-1 rounded-full text-xs font-semibold">
                                                <?php echo $checkup['fruit_vegetable_consumption']; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Blood Pressure Status -->
                            <div class="mt-6">
                                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                                    <h6 class="font-bold text-blue-700 mb-2">
                                        <i class="fas fa-heartbeat me-2"></i>Status Tekanan Darah:
                                    </h6>
                                    <?php
                                    $systolic = $checkup['systolic_pressure'];
                                    $diastolic = $checkup['diastolic_pressure'];
                                    $status = '';
                                    $statusClass = '';
                                    
                                    if ($systolic < 120 && $diastolic < 80) {
                                        $status = 'Normal';
                                        $statusClass = 'bg-green-100 text-green-800';
                                    } elseif ($systolic < 130 && $diastolic < 80) {
                                        $status = 'Elevated';
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                    } elseif ($systolic < 140 || $diastolic < 90) {
                                        $status = 'Stage 1 Hypertension';
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                    } elseif ($systolic >= 140 || $diastolic >= 90) {
                                        $status = 'Stage 2 Hypertension';
                                        $statusClass = 'bg-red-100 text-red-800';
                                    } else {
                                        $status = 'Hypertensive Crisis';
                                        $statusClass = 'bg-red-100 text-red-800';
                                    }
                                    ?>
                                    <span class="<?php echo $statusClass; ?> px-2 py-1 rounded-full text-xs font-semibold">
                                        <?php echo $status; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html> 