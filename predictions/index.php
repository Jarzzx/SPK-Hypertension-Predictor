<?php
require_once '../config/config.php';
require_once '../config/database.php';

requireLogin();

$success = '';
$error = '';

// Handle manual prediction trigger
if (isset($_GET['predict']) && is_numeric($_GET['predict'])) {
    $patient_id = $_GET['predict'];
    
    // Check if patient has at least 1 checkup (Sequential Bayesian Update allows partial data)
    $checkup_count = $conn->query("SELECT COUNT(*) as count FROM checkups WHERE patient_id = $patient_id")->fetch_assoc()['count'];
    
    if ($checkup_count >= 1) {
        require_once '../algorithms/naive_bayes.php';
        $naiveBayes = new NaiveBayes($conn);
        $prediction = $naiveBayes->predict($patient_id);
        
        if ($prediction) {
            $success = 'Prediksi berhasil dihitung (Update Bayesian Sequential)!';
        } else {
            $error = 'Gagal menghitung prediksi!';
        }
    } else {
        $error = 'Pasien belum memiliki data pengecekan sama sekali!';
    }
}

?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<main class="content p-6 lg:p-8 bg-gray-50/50 min-h-screen">
    <div class="container mx-auto p-4">
    
    <?php if ($success): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showSuccessAlert('<?php echo addslashes($success); ?>');
    });
</script>
<?php endif; ?>
<?php if ($error): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showErrorAlert('<?php echo addslashes($error); ?>');
    });
</script>
<?php endif; ?>
            
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 pb-4 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">Hasil Prediksi</h1>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h6 class="m-0 font-bold text-indigo-600">Daftar Prediksi</h6>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500" id="dataTable" width="100%" cellspacing="0">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Nama Pasien</th>
                                    <th class="px-4 py-3">Umur</th>
                                    <th class="px-4 py-3">Jumlah Pengecekan</th>
                                    <th class="px-4 py-3">Status Prediksi</th>
                                    <th class="px-4 py-3">Tingkat Risiko</th>
                                    <th class="px-4 py-3">Tanggal Prediksi</th>
                                    <th class="px-4 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT p.*, pr.status, pr.confidence, pr.created_at as prediction_date, pr.id as prediction_id,
                                         (SELECT COUNT(*) FROM checkups c WHERE c.patient_id = p.id) as checkup_count
                                         FROM patients p 
                                         LEFT JOIN predictions pr ON p.id = pr.patient_id
                                         WHERE pr.status IS NOT NULL
                                         ORDER BY pr.created_at DESC";
                                $result = $conn->query($query);
                                
                                $no = 1;
                                while ($row = $result->fetch_assoc()):
                                    $statusClass = '';
                                    switch ($row['status']) {
                                        case 'Tidak Berpotensi':
                                            $statusClass = 'badge bg-green-100 text-green-800';
                                            break;
                                        case 'Cukup Berpotensi':
                                            $statusClass = 'badge bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'Sangat Berpotensi':
                                            $statusClass = 'badge bg-red-100 text-red-800';
                                            break;
                                        default:
                                            $statusClass = 'badge bg-blue-100 text-blue-800';
                                            break;
                                    }
                                ?>
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-3"><?php echo $no++; ?></td>
                                    <td class="px-4 py-3 font-medium text-gray-900"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="px-4 py-3"><?php echo $row['age']; ?> tahun</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-indigo-100 bg-indigo-600 rounded-full">
                                            <?php echo $row['checkup_count']; ?>/3
                                        </span>
                                    </td>
                                    <td class="px-4 py-3"><span class="<?php echo $statusClass; ?>"><?php echo $row['status']; ?></span></td>
                                    <td class="px-4 py-3"><?php echo number_format($row['confidence'] * 100, 1); ?>%</td>
                                    <td class="px-4 py-3"><?php echo formatDate($row['prediction_date']); ?></td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-1">
                                            <a href="<?php echo BASE_URL; ?>predictions/view.php?id=<?php echo $row['prediction_id']; ?>" class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="javascript:void(0)" onclick="confirmDelete('<?php echo BASE_URL; ?>predictions/delete.php?id=<?php echo $row['prediction_id']; ?>')" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Patients Ready for Prediction -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h6 class="m-0 font-bold text-indigo-600">Status Data Pasien</h6>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500" width="100%" cellspacing="0">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Nama Pasien</th>
                                    <th class="px-4 py-3">Umur</th>
                                    <th class="px-4 py-3">Jumlah Pengecekan</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT p.*, 
                                         (SELECT COUNT(*) FROM checkups c WHERE c.patient_id = p.id) as checkup_count,
                                         (SELECT COUNT(*) FROM predictions pr WHERE pr.patient_id = p.id) as has_prediction
                                         FROM patients p 
                                         WHERE (SELECT COUNT(*) FROM checkups c WHERE c.patient_id = p.id) >= 1 
                                         ORDER BY p.created_at DESC";
                                $result = $conn->query($query);
                                
                                $no = 1;
                                while ($row = $result->fetch_assoc()):
                                    // With Sequential Bayesian, we can predict as early as 1 checkup
                                    // Ideally, 3 checkups give the most stable probability
                                    $isReady = $row['checkup_count'] >= 1; 
                                    $hasPrediction = $row['has_prediction'] > 0;
                                ?>
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-3"><?php echo $no++; ?></td>
                                    <td class="px-4 py-3 font-medium text-gray-900"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="px-4 py-3"><?php echo $row['age']; ?> tahun</td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="font-medium text-gray-700"><?php echo $row['checkup_count']; ?></span>
                                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                                <div class="h-2 rounded-full <?php echo $row['checkup_count'] >= 3 ? 'bg-green-500' : 'bg-indigo-500'; ?>" style="width: <?php echo min(100, ($row['checkup_count']/3)*100); ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if ($isReady && !$hasPrediction): ?>
                                            <span class="badge bg-blue-100 text-blue-800">Siap Prediksi (Tahap <?php echo $row['checkup_count']; ?>)</span>
                                        <?php elseif ($isReady && $hasPrediction): ?>
                                            <span class="badge bg-green-100 text-green-800">Sudah Diprediksi (Tahap <?php echo $row['checkup_count']; ?>)</span>
                                        <?php else: ?>
                                            <span class="badge bg-yellow-100 text-yellow-800">Belum Ada Data</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if ($isReady): ?>
                                            <a href="javascript:void(0)" onclick="confirmPrediction(<?php echo $row['id']; ?>)" class="btn btn-sm <?php echo $hasPrediction ? 'btn-warning' : 'btn-success text-white'; ?>">
                                                <i class="fas fa-calculator me-1"></i>
                                                <?php echo $hasPrediction ? 'Update Prediksi' : 'Hitung Prediksi'; ?>
                                            </a>
                                            <!-- Allow adding more checkups if less than 3 -->
                                            <?php if ($row['checkup_count'] < 3): ?>
                                            <a href="<?php echo BASE_URL; ?>checkups/create.php?patient_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary ml-1">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="<?php echo BASE_URL; ?>checkups/create.php?patient_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-plus me-1"></i>Tambah Data
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>