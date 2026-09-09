<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

// Get patient ID from URL
$patient_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($patient_id <= 0) {
    header('Location: ' . BASE_URL . 'patients/index.php?error=ID pasien tidak valid!');
    exit();
}

// Get patient data
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

if (!$patient) {
    header('Location: ' . BASE_URL . 'patients/index.php?error=Pasien tidak ditemukan!');
    exit();
}

// Get patient checkups
$stmt = $conn->prepare("SELECT * FROM checkups WHERE patient_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$checkups = $stmt->get_result();

// Get patient predictions
$stmt = $conn->prepare("SELECT * FROM predictions WHERE patient_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$predictions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pasien - SPK Hipertensi Predictor</title>
    <?php include '../includes/header.php'; ?>
</head>
<body class="bg-gray-50">
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main">
            <main class="content">
                <div class="container mx-auto px-4 py-6">
                    <!-- Header Section -->
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">
                            Detail Pasien
                        </h1>
                        <div class="flex gap-2">
                            <a href="<?php echo BASE_URL; ?>patients/index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <a href="<?php echo BASE_URL; ?>patients/edit.php?id=<?php echo $patient_id; ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Edit
                            </a>
                            <a href="<?php echo BASE_URL; ?>checkups/create.php?patient_id=<?php echo $patient_id; ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Tambah Pengecekan
                            </a>
                        </div>
                    </div>
                    
                    <!-- Patient Information Card -->
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h5 class="m-0 font-bold text-indigo-600">
                                <i class="fas fa-info-circle me-2"></i>Informasi Pasien
                            </h5>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <div class="grid grid-cols-3 gap-4 mb-4">
                                        <div class="text-gray-500 font-medium">Nama:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo htmlspecialchars($patient['name']); ?></div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-4">
                                        <div class="text-gray-500 font-medium">Umur:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo $patient['age']; ?> tahun</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-4">
                                        <div class="text-gray-500 font-medium">Jenis Kelamin:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo $patient['gender'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="grid grid-cols-3 gap-4 mb-4">
                                        <div class="text-gray-500 font-medium">Alamat:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo htmlspecialchars($patient['address']); ?></div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-4">
                                        <div class="text-gray-500 font-medium">Telepon:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo htmlspecialchars($patient['phone']); ?></div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-4">
                                        <div class="text-gray-500 font-medium">Tanggal Registrasi:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo date('d/m/Y', strtotime($patient['created_at'])); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Checkups Card -->
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h5 class="m-0 font-bold text-indigo-600">
                                <i class="fas fa-stethoscope me-2"></i>Riwayat Pengecekan
                            </h5>
                        </div>
                        <div class="p-6">
                            <?php if ($checkups->num_rows > 0): ?>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-left text-gray-500" id="checkupsTable">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3">Tanggal</th>
                                                <th class="px-4 py-3">Tekanan Darah</th>
                                                <th class="px-4 py-3">Gula Darah</th>
                                                <th class="px-4 py-3">IMT (BMI)</th>
                                                <th class="px-4 py-3">Lingkar Perut</th>
                                                <th class="px-4 py-3">Merokok</th>
                                                <th class="px-4 py-3">Aktivitas Fisik</th>
                                                <th class="px-4 py-3">Riwayat Keluarga</th>
                                                <th class="px-4 py-3">Konsumsi Sayur/Buah</th>
                                                <th class="px-4 py-3">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($checkup = $checkups->fetch_assoc()): ?>
                                                <tr class="bg-white border-b hover:bg-gray-50">
                                                    <td class="px-4 py-3"><?php echo date('d/m/Y', strtotime($checkup['created_at'])); ?></td>
                                                    <td class="px-4 py-3"><?php echo $checkup['systolic_pressure']; ?>/<?php echo $checkup['diastolic_pressure']; ?> mmHg</td>
                                                    <td class="px-4 py-3"><?php echo $checkup['blood_sugar']; ?> mg/dL</td>
                                                    <td class="px-4 py-3"><?php echo number_format($checkup['bmi'], 1); ?></td>
                                                    <td class="px-4 py-3"><?php echo $checkup['waist_circumference']; ?> cm</td>
                                                    <td class="px-4 py-3">
                                                        <span class="badge <?php echo $checkup['smoking_status'] == 'Merokok' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                                            <?php echo $checkup['smoking_status']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <span class="badge <?php 
                                                            echo $checkup['physical_activity'] == 'Aktif' ? 'bg-green-100 text-green-800' : 
                                                                ($checkup['physical_activity'] == 'Ringan' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                                        ?>">
                                                            <?php echo $checkup['physical_activity']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <span class="badge <?php echo $checkup['family_history'] == 'Ada' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                                            <?php echo $checkup['family_history']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <span class="badge <?php echo $checkup['fruit_vegetable_consumption'] == 'Kurang' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                                            <?php echo $checkup['fruit_vegetable_consumption']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <a href="<?php echo BASE_URL; ?>checkups/view.php?id=<?php echo $checkup['id']; ?>" class="btn btn-sm btn-info" title="Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-clipboard-list fa-3x text-gray-300 mb-3"></i>
                                    <p class="text-gray-500">Belum ada data pengecekan</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Predictions Card -->
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h5 class="m-0 font-bold text-indigo-600">
                                <i class="fas fa-chart-line me-2"></i>Riwayat Prediksi
                            </h5>
                        </div>
                        <div class="p-6">
                            <?php if ($predictions->num_rows > 0): ?>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-left text-gray-500" id="predictionsTable">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3">Tanggal</th>
                                                <th class="px-4 py-3">Status Prediksi</th>
                                                <th class="px-4 py-3">Tingkat Keyakinan</th>
                                                <th class="px-4 py-3">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($prediction = $predictions->fetch_assoc()): ?>
                                                <tr class="bg-white border-b hover:bg-gray-50">
                                                    <td class="px-4 py-3"><?php echo date('d/m/Y', strtotime($prediction['created_at'])); ?></td>
                                                    <td class="px-4 py-3">
                                                        <?php
                                                        $statusClass = '';
                                                        switch ($prediction['status']) {
                                                            case 'Tidak Berpotensi':
                                                                $statusClass = 'bg-green-100 text-green-800';
                                                                break;
                                                            case 'Cukup Berpotensi':
                                                                $statusClass = 'bg-yellow-100 text-yellow-800';
                                                                break;
                                                            case 'Sangat Berpotensi':
                                                                $statusClass = 'bg-red-100 text-red-800';
                                                                break;
                                                            default:
                                                                $statusClass = 'bg-gray-100 text-gray-800';
                                                        }
                                                        ?>
                                                        <span class="badge <?php echo $statusClass; ?>">
                                                            <?php echo $prediction['status']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <?php echo number_format($prediction['confidence'] * 100, 1); ?>%
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <a href="<?php echo BASE_URL; ?>predictions/view.php?id=<?php echo $prediction['id']; ?>" class="btn btn-sm btn-info" title="Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-chart-bar fa-3x text-gray-300 mb-3"></i>
                                    <p class="text-gray-500">Belum ada riwayat prediksi</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script>
        $(document).ready(function() {
            $('#checkupsTable').DataTable({
                responsive: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                drawCallback: applyTailwindPagination
            });
            $('#predictionsTable').DataTable({
                responsive: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                drawCallback: applyTailwindPagination
            });
        });
    </script>
</body>
</html>
