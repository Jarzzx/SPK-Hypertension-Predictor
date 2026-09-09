<?php
require_once '../config/config.php';
require_once '../config/database.php';

requireLogin();

$success = '';
$error = '';

// Handle tambah pasien dari modal
if (isset($_POST['modal_add']) && $_POST['modal_add'] == '1') {
    $name = trim($_POST['name']);
    $age = (int)$_POST['age'];
    $gender = trim($_POST['gender']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    
    // Debug: Log the received data
    error_log("Received patient data: " . json_encode($_POST));
    
    if (empty($name) || empty($age) || empty($gender) || empty($address)) {
        $error = 'Semua field wajib diisi!';
    } elseif ($age < 1 || $age > 150) {
        $error = 'Umur harus antara 1-150 tahun!';
    } else {
        $query = "INSERT INTO patients (name, age, gender, address, phone, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sisss", $name, $age, $gender, $address, $phone);
        
        if ($stmt->execute()) {
            $success = 'Data pasien berhasil ditambahkan!';
            // Debug: Log success
            error_log("Patient added successfully. ID: " . $stmt->insert_id);
        } else {
            $error = 'Gagal menambahkan data pasien! Error: ' . $stmt->error;
            // Debug: Log error
            error_log("Failed to add patient. Error: " . $stmt->error);
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM patients WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success = 'Data pasien berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus data pasien!';
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pasien - SPK Hipertensi Predictor</title>
    <?php include '../includes/header.php'; ?>
</head>
<body class="bg-gray-50">
<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main">
        <main class="content">
            <div class="container mx-auto p-4">

            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 pb-4 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">Data Pasien</h1>
                <div class="flex gap-2">
                    <a href="<?php echo BASE_URL; ?>reports/export_patients_pdf.php" target="_blank" class="btn btn-secondary">
                        <i class="fas fa-print me-2"></i>Cetak Laporan
                    </a>
                    <button type="button" class="btn btn-primary" onclick="toggleModal('addPatientModal')">
                        <i class="fas fa-plus me-2"></i>Tambah Pasien
                    </button>
                </div>
            </div>
            
            <!-- Modal Tambah Pasien (Tailwind) -->
            <div id="addPatientModal-backdrop" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 hidden" onclick="toggleModal('addPatientModal')"></div>
            <div id="addPatientModal" class="fixed inset-0 z-50 hidden items-center justify-center overflow-auto outline-none focus:outline-none">
                <div class="relative w-full max-w-2xl mx-auto my-6 p-4">
                    <!-- content -->
                    <div class="relative flex flex-col w-full bg-white border-0 rounded-lg shadow-lg outline-none focus:outline-none">
                        <!-- Header -->
                        <div class="flex items-start justify-between p-5 border-b border-solid border-gray-200 rounded-t bg-gray-50">
                            <h3 class="text-xl font-semibold text-indigo-600">
                                <i class="fas fa-user-plus me-2"></i>Tambah Data Pasien Baru
                            </h3>
                            <button class="p-1 ml-auto bg-transparent border-0 text-gray-400 hover:text-gray-600 float-right text-3xl leading-none font-semibold outline-none focus:outline-none transition-colors" onclick="toggleModal('addPatientModal')">
                                <span class="bg-transparent h-6 w-6 text-2xl block outline-none focus:outline-none">
                                    ×
                                </span>
                            </button>
                        </div>
                        <!-- Body -->
                        <form method="POST" id="addPatientForm">
                            <div class="p-6 relative flex-auto">
                                <input type="hidden" name="modal_add" value="1">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div class="mb-3">
                                        <label for="name" class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama lengkap pasien" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="age" class="block text-sm font-bold text-gray-700 mb-2">Umur <span class="text-red-500">*</span></label>
                                        <input type="number" class="form-control" id="age" name="age" min="1" max="150" placeholder="Masukkan umur" required>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div class="mb-3">
                                        <label for="gender" class="block text-sm font-bold text-gray-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                                        <select class="form-select" id="gender" name="gender" required>
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone" class="block text-sm font-bold text-gray-700 mb-2">Telepon</label>
                                        <input type="text" class="form-control" id="phone" name="phone" placeholder="Masukkan nomor telepon">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="block text-sm font-bold text-gray-700 mb-2">Alamat <span class="text-red-500">*</span></label>
                                    <textarea class="form-control" id="address" name="address" rows="4" placeholder="Masukkan alamat lengkap pasien" required></textarea>
                                </div>
                            </div>
                            <!-- Footer -->
                            <div class="flex items-center justify-end p-6 border-t border-solid border-gray-200 rounded-b gap-2">
                                <button type="button" onclick="resetForm('addPatientForm', 'Reset Form Pasien')" class="btn btn-warning">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="toggleModal('addPatientModal')">
                                    <i class="fas fa-times me-2"></i>Batal
                                </button>
                                <button type="button" onclick="saveData('addPatientForm', 'Simpan Pasien')" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Pasien
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- End Modal Tambah Pasien -->
            
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h6 class="m-0 font-bold text-indigo-600">Daftar Pasien</h6>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500" id="dataTable" width="100%" cellspacing="0">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Umur</th>
                                    <th class="px-4 py-3">Jenis Kelamin</th>
                                    <th class="px-4 py-3">Alamat</th>
                                    <th class="px-4 py-3">Telepon</th>
                                    <th class="px-4 py-3">Jumlah Pengecekan</th>
                                    <th class="px-4 py-3">Status Prediksi</th>
                                    <th class="px-4 py-3">Tanggal Daftar</th>
                                    <th class="px-4 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Custom Sort Order requested by User
                                $custom_order = [
                                    'RUKMINI', 'NETI', 'MEGA', 'SULASTRI', 'JUHANATI', 
                                    'AINI', 'SUMARNI', 'ROSMALIA', 'SATINEM', 'NINING', 
                                    'ROSNAINI', 'RAJA PARIDA', 'RULI ERPINA', 'IDA ROYANI', 'DONA'
                                ];
                                $order_string = "'" . implode("','", $custom_order) . "'";

                                $query = "SELECT p.*, 
                                         (SELECT COUNT(*) FROM checkups c WHERE c.patient_id = p.id) as checkup_count,
                                         (SELECT status FROM predictions pr WHERE pr.patient_id = p.id ORDER BY created_at DESC LIMIT 1) as prediction_status
                                         FROM patients p 
                                         ORDER BY (FIELD(p.name, $order_string) = 0), FIELD(p.name, $order_string)";
                                $result = $conn->query($query);
                                
                                $no = 1;
                                while ($row = $result->fetch_assoc()):
                                    $status = '';
                                    $statusClass = '';
                                    
                                    if ($row['checkup_count'] < 3) {
                                        $status = 'Menunggu Data';
                                        $statusClass = 'badge bg-yellow-100 text-yellow-800';
                                    } else {
                                        switch ($row['prediction_status']) {
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
                                                $status = 'Siap Prediksi';
                                                break;
                                        }
                                        $status = $row['prediction_status'] ?? 'Siap Prediksi';
                                    }
                                ?>
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-3"><?php echo $no++; ?></td>
                                    <td class="px-4 py-3 font-medium text-gray-900"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="px-4 py-3"><?php echo $row['age']; ?> tahun</td>
                                    <td class="px-4 py-3"><?php echo $row['gender'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-indigo-100 bg-indigo-600 rounded-full">
                                            <?php echo $row['checkup_count']; ?>/3
                                        </span>
                                    </td>
                                    <td class="px-4 py-3"><span class="<?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                                    <td class="px-4 py-3"><?php echo formatDate($row['created_at']); ?></td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-1">
                                            <a href="<?php echo BASE_URL; ?>reports/export_patients_pdf.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-sm btn-secondary" title="Cetak Kartu">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>patients/view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>patients/edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>checkups/create.php?patient_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" title="Checkup">
                                                <i class="fas fa-stethoscope"></i>
                                            </a>
                                            <a href="javascript:void(0)" onclick="confirmDelete('<?php echo BASE_URL; ?>patients/delete.php?id=<?php echo $row['id']; ?>')" class="btn btn-sm btn-danger" title="Hapus">
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