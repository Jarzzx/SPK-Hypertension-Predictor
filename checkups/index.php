<?php
require_once '../config/config.php';
require_once '../config/database.php';

requireLogin();

$success = '';
$error = '';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM checkups WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success = 'Data pengecekan berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus data pengecekan!';
    }
}

?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<main class="content p-6 lg:p-8 bg-gray-50/50 min-h-screen">
    <div class="container mx-auto px-4 py-6">
            
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 pb-4 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">Data Pengecekan</h1>
                <div class="flex gap-2">
                    <a href="<?php echo BASE_URL; ?>checkups/create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Pengecekan
                    </a>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h6 class="m-0 font-bold text-indigo-600">Daftar Pengecekan</h6>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500" id="dataTable" width="100%" cellspacing="0">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Nama Pasien</th>
                                    <th class="px-4 py-3">Tekanan Darah</th>
                                    <th class="px-4 py-3">Gula Darah</th>
                                    <th class="px-4 py-3">BMI</th>
                                    <th class="px-4 py-3">Lingkar Perut</th>
                                    <th class="px-4 py-3">Riwayat Keluarga</th>
                                    <th class="px-4 py-3">Riwayat Pribadi</th>
                                    <th class="px-4 py-3">Merokok</th>
                                    <th class="px-4 py-3">Aktivitas Fisik</th>
                                    <th class="px-4 py-3">Konsumsi Buah/Sayur</th>
                                    <th class="px-4 py-3">Tanggal Pengecekan</th>
                                    <th class="px-4 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT c.*, p.name as patient_name 
                                         FROM checkups c 
                                         JOIN patients p ON c.patient_id = p.id 
                                         ORDER BY c.created_at DESC";
                                $result = $conn->query($query);
                                
                                $no = 1;
                                while ($row = $result->fetch_assoc()):
                                    $bloodPressure = $row['systolic_pressure'] . '/' . $row['diastolic_pressure'];
                                    $bloodPressureCategory = getBloodPressureCategory($row['systolic_pressure'], $row['diastolic_pressure']);
                                    
                                    $bpClass = '';
                                    if ($bloodPressureCategory == 'Normal') {
                                        $bpClass = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                    } elseif ($bloodPressureCategory == 'Elevated') {
                                        $bpClass = 'bg-yellow-100 text-yellow-700 border border-yellow-200';
                                    } elseif ($bloodPressureCategory == 'Stage 1 Hypertension') {
                                        $bpClass = 'bg-orange-100 text-orange-700 border border-orange-200';
                                    } else {
                                        $bpClass = 'bg-rose-100 text-rose-700 border border-rose-200';
                                    }
                                ?>
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-3"><?php echo $no++; ?></td>
                                    <td class="px-4 py-3 font-medium text-gray-900"><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                    <td class="px-4 py-3">
                                        <span class="text-gray-900 font-medium"><?php echo $bloodPressure; ?> mmHg</span>
                                        <br>
                                        <span class="inline-block mt-1 px-2 py-0.5 rounded text-xs font-medium <?php echo $bpClass; ?>">
                                            <?php echo $bloodPressureCategory; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3"><?php echo $row['blood_sugar']; ?> mg/dL</td>
                                    <td class="px-4 py-3"><?php echo number_format($row['bmi'], 1); ?></td>
                                    <td class="px-4 py-3"><?php echo $row['waist_circumference']; ?> cm</td>
                                    <td class="px-4 py-3">
                                        <span class="badge <?php echo $row['family_history'] != 'Tidak Ada' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                            <?php echo $row['family_history']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge <?php echo $row['personal_history'] != 'Tidak Ada' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                            <?php echo $row['personal_history']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge <?php echo $row['smoking_status'] == 'Merokok' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                            <?php echo $row['smoking_status']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php 
                                        $activityClass = '';
                                        switch ($row['physical_activity']) {
                                            case 'Tidak Aktif': $activityClass = 'bg-red-100 text-red-800'; break;
                                            case 'Ringan': $activityClass = 'bg-yellow-100 text-yellow-800'; break;
                                            case 'Aktif': $activityClass = 'bg-green-100 text-green-800'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $activityClass; ?>">
                                            <?php echo $row['physical_activity']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge <?php echo $row['fruit_vegetable_consumption'] == 'Kurang' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'; ?>">
                                            <?php echo $row['fruit_vegetable_consumption']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3"><?php echo formatDate($row['created_at']); ?></td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-1">
                                            <a href="<?php echo BASE_URL; ?>checkups/view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>checkups/edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0)" onclick="confirmDelete('<?php echo BASE_URL; ?>checkups/delete.php?id=<?php echo $row['id']; ?>')" class="btn btn-sm btn-danger" title="Hapus">
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
        </div>
    </main>

<?php include '../includes/footer.php'; ?>