<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$prediction_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($prediction_id <= 0) {
    header('Location: ' . BASE_URL . 'predictions/index.php?error=ID prediksi tidak valid');
    exit();
}

// Get prediction details with patient info
$stmt = $conn->prepare("
    SELECT p.*, pt.name as patient_name, pt.age, pt.gender, pt.address, pt.phone
    FROM predictions p
    JOIN patients pt ON p.patient_id = pt.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $prediction_id);
$stmt->execute();
$prediction = $stmt->get_result()->fetch_assoc();

if (!$prediction) {
    header('Location: ' . BASE_URL . 'predictions/index.php?error=Prediksi tidak ditemukan');
    exit();
}

// Get checkup data used for this prediction
$stmt = $conn->prepare("
    SELECT * FROM checkups 
    WHERE patient_id = ? 
    ORDER BY created_at DESC 
    LIMIT 3
");
$stmt->bind_param("i", $prediction['patient_id']);
$stmt->execute();
$checkups = $stmt->get_result();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<main class="content p-6 lg:p-8 bg-gray-50/50 min-h-screen">
    <div class="container mx-auto p-4">
            <!-- Header Section -->
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <h1 class="text-2xl font-bold text-gray-800">
                            Detail Prediksi
                        </h1>
                        <a href="<?php echo BASE_URL; ?>predictions/index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>
                    
                    <!-- Patient Information Card -->
                    <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h5 class="m-0 font-bold text-indigo-600">
                                Informasi Pasien
                            </h5>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Nama Pasien:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo htmlspecialchars($prediction['patient_name']); ?></div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Umur:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo $prediction['age']; ?> tahun</div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Jenis Kelamin:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo $prediction['gender']; ?></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Alamat:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo htmlspecialchars($prediction['address']); ?></div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Telepon:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo htmlspecialchars($prediction['phone']); ?></div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Tanggal Prediksi:</div>
                                        <div class="col-span-2 font-semibold text-gray-800"><?php echo date('d/m/Y H:i', strtotime($prediction['created_at'])); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prediction Result Card -->
                    <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h5 class="m-0 font-bold text-indigo-600">
                                Hasil Prediksi
                            </h5>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium flex items-center">Status Prediksi:</div>
                                        <div class="col-span-2">
                                            <?php
                                            $statusClass = '';
                                            switch ($prediction['status']) {
                                                case 'Tidak Berpotensi':
                                                    $statusClass = 'bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold';
                                                    break;
                                                case 'Cukup Berpotensi':
                                                    $statusClass = 'bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold';
                                                    break;
                                                case 'Sangat Berpotensi':
                                                    $statusClass = 'bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold';
                                                    break;
                                            }
                                            ?>
                                            <span class="<?php echo $statusClass; ?>">
                                                <?php echo $prediction['status']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Estimasi Risiko:</div>
                                        <div class="col-span-2">
                                            <span class="font-semibold text-gray-800"><?php echo number_format($prediction['probability'] * 100, 2); ?>%</span>
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                                                <div class="bg-green-500 h-2.5 rounded-full" style="width: <?php echo $prediction['probability'] * 100; ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Tingkat Risiko:</div>
                                        <div class="col-span-2">
                                            <span class="font-semibold text-gray-800"><?php echo number_format($prediction['confidence'] * 100, 2); ?>%</span>
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                                                <div class="bg-blue-500 h-2.5 rounded-full" style="width: <?php echo $prediction['confidence'] * 100; ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div class="text-gray-500 font-medium">Algoritma:</div>
                                        <div class="col-span-2 font-semibold text-gray-800">Naive Bayes</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Checkup Data Used Card -->
                    <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h5 class="m-0 font-bold text-indigo-600">
                                Data Pengecekan yang Digunakan
                            </h5>
                        </div>
                        <div class="p-6">
                            <?php if ($checkups->num_rows > 0): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sistolik</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diastolik</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gula Darah</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tinggi</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Berat</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BMI</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lingkar Perut</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Merokok</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktivitas</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php while ($checkup = $checkups->fetch_assoc()): ?>
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($checkup['created_at'])); ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $checkup['systolic_pressure']; ?> mmHg</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $checkup['diastolic_pressure']; ?> mmHg</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $checkup['blood_sugar']; ?> mg/dL</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $checkup['height']; ?> cm</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $checkup['weight']; ?> kg</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($checkup['bmi'], 2); ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $checkup['waist_circumference']; ?> cm</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $checkup['smoking_status'] == 'Merokok' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                                            <?php echo $checkup['smoking_status']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                                            echo $checkup['physical_activity'] == 'Aktif' ? 'bg-green-100 text-green-800' : 
                                                                ($checkup['physical_activity'] == 'Ringan' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                                        ?>">
                                                            <?php echo $checkup['physical_activity']; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-gray-500">
                                    <i class="fas fa-info-circle mr-2"></i>Tidak ada data pengecekan yang ditemukan.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </main>
    
    <?php include '../includes/footer.php'; ?>
