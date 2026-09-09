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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $age = (int)$_POST['age'];
    $gender = $_POST['gender'];
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Nama harus diisi";
    }
    
    if ($age < 1 || $age > 150) {
        $errors[] = "Umur harus antara 1-150 tahun";
    }
    
    if (empty($gender)) {
        $errors[] = "Jenis kelamin harus dipilih";
    }
    
    if (empty($address)) {
        $errors[] = "Alamat harus diisi";
    }
    
    if (empty($phone)) {
        $errors[] = "Telepon harus diisi";
    }
    
    if (empty($errors)) {
        // Update patient
        $stmt = $conn->prepare("UPDATE patients SET name = ?, age = ?, gender = ?, address = ?, phone = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("sisssi", $name, $age, $gender, $address, $phone, $patient_id);
        
        if ($stmt->execute()) {
            header('Location: ' . BASE_URL . 'patients/index.php?success=Data pasien berhasil diperbarui!');
            exit();
        } else {
            $errors[] = "Gagal memperbarui data pasien";
        }
    }
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<main class="content p-6 lg:p-8 bg-gray-50/50 min-h-screen">
    <div class="container mx-auto px-4 py-6">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">
                            <i class="fas fa-edit me-2"></i>Edit Pasien
                        </h1>
                        <a href="<?php echo BASE_URL; ?>patients/index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4" role="alert">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">
                                        <?php foreach ($errors as $error): ?>
                                            <span class="block"><?php echo $error; ?></span>
                                        <?php endforeach; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h6 class="m-0 font-bold text-indigo-600">Form Edit Data Pasien</h6>
                        </div>
                        <div class="p-6">
                            <form method="POST" action="" id="editPatientForm">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($patient['name']); ?>" required>
                                    </div>
                                    <div>
                                        <label for="age" class="block text-sm font-medium text-gray-700 mb-2">Umur <span class="text-red-500">*</span></label>
                                        <input type="number" class="form-control" id="age" name="age" min="1" max="150" value="<?php echo $patient['age']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                                        <select class="form-select" id="gender" name="gender" required>
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="L" <?php echo ($patient['gender'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                            <option value="P" <?php echo ($patient['gender'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($patient['phone']); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-6">
                                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Alamat <span class="text-red-500">*</span></label>
                                    <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($patient['address']); ?></textarea>
                                </div>
                                
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="resetForm('editPatientForm', 'Reset Form Edit')" class="btn btn-warning">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </button>
                                    <button type="button" onclick="saveData('editPatientForm', 'Simpan Perubahan')" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
    
    <?php include '../includes/footer.php'; ?>