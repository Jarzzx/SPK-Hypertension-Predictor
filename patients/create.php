<?php
require_once '../config/config.php';
require_once '../config/database.php';

requireLogin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $age = (int)$_POST['age'];
    $gender = sanitize($_POST['gender']);
    $address = sanitize($_POST['address']);
    $phone = sanitize($_POST['phone']);
    
    // Validation
    if (empty($name) || empty($age) || empty($gender) || empty($address)) {
        $error = 'Semua field wajib diisi!';
    } elseif ($age < 1 || $age > 150) {
        $error = 'Umur harus antara 1-150 tahun!';
    } else {
        $query = "INSERT INTO patients (name, age, gender, address, phone) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sisss", $name, $age, $gender, $address, $phone);
        
        if ($stmt->execute()) {
            $success = 'Data pasien berhasil ditambahkan!';
            // Redirect after 2 seconds
            header("refresh:2;url=index.php");
        } else {
            $error = 'Gagal menambahkan data pasien!';
        }
    }
}

?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<main class="content p-6 lg:p-8 bg-gray-50/50 min-h-screen">
    <div class="container mx-auto p-4">
            <div class="flex justify-between items-center pt-3 pb-2 mb-3 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-800">Tambah Pasien Baru</h1>
                <div class="flex gap-2">
                    <a href="javascript:void(0)" onclick="goBack('<?php echo BASE_URL; ?>patients/index.php', 'Kembali ke Data Pasien')" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
            
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
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Data Pasien</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="patientForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="age" class="form-label">Umur <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="age" name="age" min="1" max="150" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gender" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Nomor Telepon</label>
                                    <input type="text" class="form-control" id="phone" name="phone">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="button" onclick="resetForm('patientForm', 'Reset Form Pasien')" class="btn btn-warning me-2">
                                <i class="fas fa-undo me-2"></i>Reset
                            </button>
                            <button type="button" onclick="saveData('patientForm', 'Simpan Pasien')" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Pasien
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

<?php include '../includes/footer.php'; ?> 