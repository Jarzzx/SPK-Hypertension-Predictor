<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

// Get statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM patients");
$stmt->execute();
$total_patients = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM checkups");
$stmt->execute();
$total_checkups = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM predictions");
$stmt->execute();
$total_predictions = $stmt->get_result()->fetch_assoc()['total'];

// Get prediction statistics
$stmt = $conn->prepare("
    SELECT 
        status,
        COUNT(*) as count,
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM predictions), 2) as percentage
    FROM predictions 
    GROUP BY status
");
$stmt->execute();
$prediction_stats = $stmt->get_result();

// Get recent predictions
$stmt = $conn->prepare("
    SELECT p.id as prediction_id, p.*, pt.name as patient_name 
    FROM predictions p 
    JOIN patients pt ON p.patient_id = pt.id 
    ORDER BY p.created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recent_predictions = $stmt->get_result();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<main class="content p-6 lg:p-8 bg-gray-50/50 min-h-screen">
    <div class="container mx-auto px-4 py-6">
            <!-- Header Section -->
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 pb-4 border-b border-gray-200">
                        <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">
                            Laporan Sistem
                        </h1>
                        <div class="flex gap-2">
                            <a href="<?php echo BASE_URL; ?>reports/export_patients_pdf.php" class="btn btn-info" target="_blank">
                                <i class="fas fa-file-pdf me-2"></i>Laporan Pasien
                            </a>
                            <a href="<?php echo BASE_URL; ?>reports/export_pdf.php" class="btn btn-danger" target="_blank">
                                <i class="fas fa-file-pdf me-2"></i>Laporan Prediksi
                            </a>
                            <a href="<?php echo BASE_URL; ?>reports/export_excel.php" class="btn btn-success">
                                <i class="fas fa-file-excel me-2"></i>Export Excel
                            </a>
                        </div>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <!-- Total Pasien -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_2px_10px_-4px_rgba(6,81,237,0.1)] hover:shadow-lg transition-all duration-300 group">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium mb-1">Total Pasien</p>
                                    <h3 class="text-3xl font-bold text-gray-800 group-hover:text-indigo-600 transition-colors"><?php echo number_format($total_patients); ?></h3>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-xs font-medium text-indigo-500 bg-indigo-50 w-fit px-2 py-1 rounded-lg">
                                <i class="fas fa-database mr-1"></i> <span>Terdaftar</span>
                            </div>
                        </div>
                        
                        <!-- Total Pengecekan -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_2px_10px_-4px_rgba(6,81,237,0.1)] hover:shadow-lg transition-all duration-300 group">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium mb-1">Total Pengecekan</p>
                                    <h3 class="text-3xl font-bold text-gray-800 group-hover:text-blue-600 transition-colors"><?php echo number_format($total_checkups); ?></h3>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                                    <i class="fas fa-stethoscope"></i>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-xs font-medium text-blue-500 bg-blue-50 w-fit px-2 py-1 rounded-lg">
                                <i class="fas fa-notes-medical mr-1"></i> <span>Pemeriksaan</span>
                            </div>
                        </div>
                        
                        <!-- Total Prediksi -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_2px_10px_-4px_rgba(6,81,237,0.1)] hover:shadow-lg transition-all duration-300 group">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium mb-1">Total Prediksi</p>
                                    <h3 class="text-3xl font-bold text-gray-800 group-hover:text-emerald-600 transition-colors"><?php echo number_format($total_predictions); ?></h3>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-xs font-medium text-emerald-500 bg-emerald-50 w-fit px-2 py-1 rounded-lg">
                                <i class="fas fa-calculator mr-1"></i> <span>Hasil Analisis</span>
                            </div>
                        </div>
                        
                        <!-- Rasio Prediksi -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_2px_10px_-4px_rgba(6,81,237,0.1)] hover:shadow-lg transition-all duration-300 group">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium mb-1">Rasio Prediksi</p>
                                    <h3 class="text-3xl font-bold text-gray-800 group-hover:text-rose-600 transition-colors"><?php echo $total_predictions > 0 ? round(($total_predictions / $total_patients) * 100, 1) : 0; ?>%</h3>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                                    <i class="fas fa-percentage"></i>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-xs font-medium text-rose-500 bg-rose-50 w-fit px-2 py-1 rounded-lg">
                                <i class="fas fa-chart-pie mr-1"></i> <span>Dari Total Pasien</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prediction Statistics -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-lg shadow-lg overflow-hidden h-full">
                                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                    <h6 class="m-0 font-bold text-indigo-600">
                                        <i class="fas fa-chart-pie me-2"></i>Statistik Prediksi
                                    </h6>
                                </div>
                                <div class="p-6">
                                    <div class="relative h-72 w-full">
                                        <canvas id="reportPredictionChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-1">
                            <div class="bg-white rounded-lg shadow-lg overflow-hidden h-full">
                                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                    <h6 class="m-0 font-bold text-indigo-600">
                                        <i class="fas fa-list me-2"></i>Ringkasan Prediksi
                                    </h6>
                                </div>
                                <div class="p-6">
                                    <?php 
                                    if ($prediction_stats->num_rows > 0) {
                                        $prediction_stats->data_seek(0);
                                        while ($stat = $prediction_stats->fetch_assoc()): 
                                    ?>
                                        <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100 last:border-0 last:mb-0 last:pb-0">
                                            <span class="badge <?php 
                                                echo $stat['status'] == 'Tidak Berpotensi' ? 'bg-green-100 text-green-800' : 
                                                    ($stat['status'] == 'Cukup Berpotensi' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                            ?>">
                                                <?php echo $stat['status']; ?>
                                            </span>
                                            <div class="text-right">
                                                <div class="font-bold text-gray-800"><?php echo $stat['count']; ?></div>
                                                <small class="text-gray-500"><?php echo $stat['percentage']; ?>%</small>
                                            </div>
                                        </div>
                                    <?php 
                                        endwhile; 
                                    } else {
                                        echo '<div class="text-center text-gray-500 py-4">Belum ada data prediksi</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Predictions -->
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h6 class="m-0 font-bold text-indigo-600">
                                <i class="fas fa-clock me-2"></i>Prediksi Terbaru
                            </h6>
                        </div>
                        <div class="p-0">
                            <?php if ($recent_predictions->num_rows > 0): ?>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-left text-gray-500">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3">Tanggal</th>
                                                <th class="px-6 py-3">Nama Pasien</th>
                                                <th class="px-6 py-3">Status</th>
                                                <th class="px-6 py-3">Probabilitas</th>
                                                <th class="px-6 py-3">Confidence</th>
                                                <th class="px-6 py-3">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($prediction = $recent_predictions->fetch_assoc()): ?>
                                                <tr class="bg-white border-b hover:bg-gray-50">
                                                    <td class="px-6 py-4"><?php echo date('d/m/Y H:i', strtotime($prediction['created_at'])); ?></td>
                                                    <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($prediction['patient_name']); ?></td>
                                                    <td class="px-6 py-4">
                                                        <?php
                                                        $statusClass = '';
                                                        switch ($prediction['status']) {
                                                            case 'Tidak Berpotensi':
                                                                $statusClass = 'badge bg-green-100 text-green-800';
                                                                break;
                                                            case 'Cukup Berpotensi':
                                                                $statusClass = 'badge bg-yellow-100 text-yellow-800';
                                                                break;
                                                            case 'Sangat Berpotensi':
                                                                $statusClass = 'badge bg-red-100 text-red-800';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="<?php echo $statusClass; ?>">
                                                            <?php echo $prediction['status']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4"><?php echo number_format($prediction['probability'] * 100, 2); ?>%</td>
                                                    <td class="px-6 py-4"><?php echo number_format($prediction['confidence'] * 100, 2); ?>%</td>
                                                    <td class="px-6 py-4">
                                                        <a href="<?php echo BASE_URL; ?>predictions/view.php?id=<?php echo $prediction['prediction_id']; ?>" class="btn btn-sm btn-primary">
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
                                    <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
                                    <p class="text-gray-500">Belum ada data prediksi</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
            </div>
        </main>
    
    <script>
        // Chart.js untuk statistik prediksi
        const canvas = document.getElementById('reportPredictionChart');
        if (canvas && typeof Chart !== 'undefined') {
            const ctx = canvas.getContext('2d');
            
            <?php 
            $labels = [];
            $data = [];
            $colors = [];
            
            if ($prediction_stats->num_rows > 0) {
                $prediction_stats->data_seek(0);
                while ($stat = $prediction_stats->fetch_assoc()) {
                    $labels[] = $stat['status'];
                    $data[] = $stat['count'];
                    switch ($stat['status']) {
                        case 'Tidak Berpotensi':
                            $colors[] = '#28a745';
                            break;
                        case 'Cukup Berpotensi':
                            $colors[] = '#ffc107';
                            break;
                        case 'Sangat Berpotensi':
                            $colors[] = '#dc3545';
                            break;
                    }
                }
            }
            ?>
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($data); ?>,
                        backgroundColor: <?php echo json_encode($colors); ?>,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        }
    </script>

    <?php include '../includes/footer.php'; ?> 