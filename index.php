<?php
require_once 'config/config.php';
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

// Get statistics with error handling
// Total patients
$result = $conn->query("SELECT COUNT(*) as total FROM patients");
if (!$result) {
    error_log("Error in patients query: " . $conn->error);
    $totalPatients = 0;
} else {
    $totalPatients = $result->fetch_assoc()['total'];
}

// Total checkups
$result = $conn->query("SELECT COUNT(*) as total FROM checkups");
if (!$result) {
    error_log("Error in checkups query: " . $conn->error);
    $totalCheckups = 0;
} else {
    $totalCheckups = $result->fetch_assoc()['total'];
}

// Total predictions
$result = $conn->query("SELECT COUNT(*) as total FROM predictions");
if (!$result) {
    error_log("Error in predictions query: " . $conn->error);
    $totalPredictions = 0;
} else {
    $totalPredictions = $result->fetch_assoc()['total'];
}

// Patients ready for prediction (3+ checkups)
$result = $conn->query("
    SELECT COUNT(*) as total 
    FROM patients p 
    WHERE (
        SELECT COUNT(*) 
        FROM checkups c 
        WHERE c.patient_id = p.id
    ) >= 3
");
if (!$result) {
    error_log("Error in ready patients query: " . $conn->error);
    $patientsReadyForPrediction = 0;
} else {
    $patientsReadyForPrediction = $result->fetch_assoc()['total'];
}

// Recent patients
$recentPatients = [];
$result = $conn->query("
    SELECT p.*, COUNT(c.id) as checkup_count 
    FROM patients p 
    LEFT JOIN checkups c ON p.id = c.patient_id 
    GROUP BY p.id 
    ORDER BY p.created_at DESC 
    LIMIT 5
");

if (!$result) {
    error_log("Error in recent patients query: " . $conn->error);
} else if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentPatients[] = $row;
    }
}

// Monthly stats for charts
$monthlyCheckupsLabels = [];
$monthlyCheckupsData = [];
$res = $conn->query("
    SELECT m, y, COUNT(*) as c
    FROM (
        SELECT DATE_FORMAT(created_at,'%b') as m, YEAR(created_at) as y
        FROM checkups
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
    ) t
    GROUP BY y, m
    ORDER BY y, FIELD(m,'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec')
");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $monthlyCheckupsLabels[] = $r['m'];
        $monthlyCheckupsData[] = (int)$r['c'];
    }
}

$predictionStatusCounts = [ 'Tidak Berpotensi' => 0, 'Cukup Berpotensi' => 0, 'Sangat Berpotensi' => 0 ];
$res = $conn->query("SELECT status, COUNT(*) as c FROM predictions GROUP BY status");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        if (isset($predictionStatusCounts[$r['status']])) {
            $predictionStatusCounts[$r['status']] = (int)$r['c'];
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="content p-6 lg:p-8 bg-gray-50/50 min-h-screen">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Pasien -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_2px_10px_-4px_rgba(6,81,237,0.1)] hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Pasien</p>
                    <h3 class="text-3xl font-bold text-gray-800 group-hover:text-indigo-600 transition-colors"><?php echo number_format($totalPatients); ?></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-medium text-green-500 bg-green-50 w-fit px-2 py-1 rounded-lg">
                <i class="fas fa-arrow-up mr-1"></i> <span>Data Terupdate</span>
            </div>
        </div>

        <!-- Total Pengecekan -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_2px_10px_-4px_rgba(6,81,237,0.1)] hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Pengecekan</p>
                    <h3 class="text-3xl font-bold text-gray-800 group-hover:text-green-600 transition-colors"><?php echo number_format($totalCheckups); ?></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-stethoscope"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-medium text-green-500 bg-green-50 w-fit px-2 py-1 rounded-lg">
                <i class="fas fa-check-circle mr-1"></i> <span>Aktif</span>
            </div>
        </div>

        <!-- Total Prediksi -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_2px_10px_-4px_rgba(6,81,237,0.1)] hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Prediksi</p>
                    <h3 class="text-3xl font-bold text-gray-800 group-hover:text-purple-600 transition-colors"><?php echo number_format($totalPredictions); ?></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-medium text-purple-500 bg-purple-50 w-fit px-2 py-1 rounded-lg">
                <i class="fas fa-brain mr-1"></i> <span>AI Analysis</span>
            </div>
        </div>

        <!-- Siap Prediksi -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_2px_10px_-4px_rgba(6,81,237,0.1)] hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Siap Prediksi</p>
                    <h3 class="text-3xl font-bold text-gray-800 group-hover:text-orange-600 transition-colors"><?php echo number_format($patientsReadyForPrediction); ?></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-medium text-orange-500 bg-orange-50 w-fit px-2 py-1 rounded-lg">
                <i class="fas fa-hourglass-half mr-1"></i> <span>Menunggu Aksi</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Main Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">Statistik Pengecekan</h3>
                    <p class="text-sm text-gray-500">Tren pemeriksaan kesehatan per bulan</p>
                </div>
                <button class="text-gray-400 hover:text-indigo-600 transition-colors"><i class="fas fa-ellipsis-h"></i></button>
            </div>
            <div class="relative h-80 w-full">
                <canvas id="checkupsChart"></canvas>
            </div>
        </div>

        <!-- Recent Patients List -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-gray-800 text-lg">Pasien Terbaru</h3>
                <a href="<?php echo BASE_URL; ?>patients/index.php" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">View All</a>
            </div>
            
            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar" style="max-height: 320px;">
                <?php if (empty($recentPatients)): ?>
                    <div class="text-center py-8 text-gray-400">Belum ada data</div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recentPatients as $patient): ?>
                        <div class="flex items-center p-3 hover:bg-gray-50 rounded-xl transition-colors border border-transparent hover:border-gray-100">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold mr-4 shrink-0">
                                <?php echo substr($patient['name'], 0, 1); ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-gray-800 truncate"><?php echo htmlspecialchars($patient['name']); ?></h4>
                                <p class="text-xs text-gray-500 truncate">ID: <?php echo $patient['id']; ?> • <?php echo $patient['age']; ?> Thn</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-lg font-medium"><?php echo $patient['checkup_count']; ?> Check</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <button class="w-full mt-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-sm font-medium hover:bg-indigo-100 transition-colors">
                <i class="fas fa-plus mr-2"></i> Tambah Pasien Baru
            </button>
        </div>
    </div>
    
    <!-- Bottom Section: Prediction Status Distribution (Pie) & Recent Predictions (Table) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Prediction Pie Chart -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <h3 class="font-bold text-gray-800 text-lg mb-4">Distribusi Risiko</h3>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="predictionPieChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity / Quick Actions -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <h3 class="font-bold text-gray-800 text-lg mb-4">Aksi Cepat</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="<?php echo BASE_URL; ?>patients/create.php" class="group p-4 rounded-xl border border-gray-100 hover:border-indigo-200 hover:shadow-md transition-all bg-gradient-to-br from-white to-gray-50">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h4 class="font-bold text-gray-800 mb-1">Input Pasien</h4>
                    <p class="text-xs text-gray-500">Tambah data pasien baru</p>
                </a>
                
                <a href="<?php echo BASE_URL; ?>checkups/create.php" class="group p-4 rounded-xl border border-gray-100 hover:border-green-200 hover:shadow-md transition-all bg-gradient-to-br from-white to-gray-50">
                    <div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                    <h4 class="font-bold text-gray-800 mb-1">Cek Kesehatan</h4>
                    <p class="text-xs text-gray-500">Input hasil pemeriksaan</p>
                </a>
                
                <a href="<?php echo BASE_URL; ?>predictions/create.php" class="group p-4 rounded-xl border border-gray-100 hover:border-purple-200 hover:shadow-md transition-all bg-gradient-to-br from-white to-gray-50">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <i class="fas fa-magic"></i>
                    </div>
                    <h4 class="font-bold text-gray-800 mb-1">Hitung Prediksi</h4>
                    <p class="text-xs text-gray-500">Analisa risiko hipertensi</p>
                </a>
            </div>
        </div>
    </div>
</main>

<!-- Chart Config -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Checkups Line Chart
    const ctx = document.getElementById('checkupsChart').getContext('2d');
    
    // Gradient for line chart
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.4)');
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($monthlyCheckupsLabels); ?>,
            datasets: [{
                label: 'Jumlah Pengecekan',
                data: <?php echo json_encode($monthlyCheckupsData); ?>,
                borderColor: '#4f46e5',
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#4f46e5',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    padding: 12,
                    titleFont: { size: 13 },
                    bodyFont: { size: 14 },
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f3f4f6',
                        drawBorder: false
                    },
                    ticks: {
                        font: { family: 'Poppins' },
                        color: '#9ca3af',
                        padding: 10
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { family: 'Poppins' },
                        color: '#9ca3af'
                    }
                }
            }
        }
    });

    // Prediction Pie Chart
    const ctxPie = document.getElementById('predictionPieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Tidak Berpotensi', 'Cukup Berpotensi', 'Sangat Berpotensi'],
            datasets: [{
                data: [
                    <?php echo $predictionStatusCounts['Tidak Berpotensi']; ?>,
                    <?php echo $predictionStatusCounts['Cukup Berpotensi']; ?>,
                    <?php echo $predictionStatusCounts['Sangat Berpotensi']; ?>
                ],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { family: 'Poppins', size: 11 }
                    }
                }
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
