<?php
require_once '../config/config.php';
require_once '../config/database.php';

requireLogin();

$success = '';
$error = '';
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $systolic_pressure = (int)$_POST['systolic_pressure'];
    $diastolic_pressure = (int)$_POST['diastolic_pressure'];
    $blood_sugar = (float)$_POST['blood_sugar'];
    $height = (float)$_POST['height'];
    $weight = (float)$_POST['weight'];
    $waist_circumference = (float)$_POST['waist_circumference'];
    $family_history = trim($_POST['family_history']);
    $personal_history = trim($_POST['personal_history']);
    $smoking_status = trim($_POST['smoking_status']);
    $physical_activity = trim($_POST['physical_activity']);
    $fruit_vegetable_consumption = trim($_POST['fruit_vegetable_consumption']);
    
    // Validate required fields
    if (empty($family_history) || empty($personal_history) || empty($smoking_status) || empty($physical_activity) || empty($fruit_vegetable_consumption)) {
        $error = 'Semua field harus diisi!';
    }
    
    // Validate ENUM values
    $valid_family_history = ['Tidak Ada', 'Hipertensi', 'Diabetes Melitus', 'Hipertensi dan Diabetes Melitus'];
    $valid_personal_history = ['Tidak Ada', 'Hipertensi', 'Diabetes Melitus', 'Hipertensi dan Diabetes Melitus'];
    $valid_smoking_status = ['Tidak Merokok', 'Merokok'];
    $valid_physical_activity = ['Tidak Aktif', 'Ringan', 'Aktif'];
    $valid_fruit_vegetable_consumption = ['Kurang', 'Cukup'];
    
    // Set default values if empty or invalid
    if (empty($family_history) || !in_array($family_history, $valid_family_history)) $family_history = 'Tidak Ada';
    if (empty($personal_history) || !in_array($personal_history, $valid_personal_history)) $personal_history = 'Tidak Ada';
    if (empty($smoking_status) || !in_array($smoking_status, $valid_smoking_status)) $smoking_status = 'Tidak Merokok';
    if (empty($physical_activity) || !in_array($physical_activity, $valid_physical_activity)) $physical_activity = 'Tidak Aktif';
    if (empty($fruit_vegetable_consumption) || !in_array($fruit_vegetable_consumption, $valid_fruit_vegetable_consumption)) $fruit_vegetable_consumption = 'Cukup';

    // Calculate BMI
    $bmi = 0;
    if ($height > 0) {
        $bmi = $weight / pow($height / 100, 2);
    }
    
    // Validation ranges
    if ($systolic_pressure < 70 || $systolic_pressure > 300) {
        $error = 'Tekanan darah sistolik harus antara 70-300 mmHg!';
    } elseif ($diastolic_pressure < 40 || $diastolic_pressure > 200) {
        $error = 'Tekanan darah diastolik harus antara 40-200 mmHg!';
    } elseif ($blood_sugar < 50 || $blood_sugar > 1000) {
        $error = 'Gula darah harus antara 50-1000 mg/dL!';
    } elseif ($height < 100 || $height > 250) {
        $error = 'Tinggi badan harus antara 100-250 cm!';
    } elseif ($weight < 20 || $weight > 300) {
        $error = 'Berat badan harus antara 20-300 kg!';
    } elseif ($waist_circumference < 50 || $waist_circumference > 200) {
        $error = 'Lingkar perut harus antara 50-200 cm!';
    } else {
        try {
            $query = "UPDATE checkups SET 
                systolic_pressure = ?, 
                diastolic_pressure = ?, 
                blood_sugar = ?, 
                height = ?, 
                weight = ?, 
                bmi = ?, 
                waist_circumference = ?, 
                family_history = ?, 
                personal_history = ?, 
                smoking_status = ?, 
                physical_activity = ?, 
                fruit_vegetable_consumption = ? 
                WHERE id = ?";
                
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iidddddsssssi", 
                $systolic_pressure, 
                $diastolic_pressure, 
                $blood_sugar, 
                $height, 
                $weight, 
                $bmi, 
                $waist_circumference, 
                $family_history, 
                $personal_history, 
                $smoking_status, 
                $physical_activity, 
                $fruit_vegetable_consumption,
                $checkup_id
            );
            
            if ($stmt->execute()) {
                $success = 'Data pengecekan berhasil diperbarui!';
                // Redirect after 2 seconds
                header("refresh:2;url=index.php");
            } else {
                $error = 'Gagal memperbarui data pengecekan! Error: ' . $conn->error;
            }
        } catch (Exception $e) {
            $error = 'Error sistem: ' . $e->getMessage();
            error_log("Checkup update error: " . $e->getMessage());
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<main class="content p-6 lg:p-8 bg-gray-50/50 min-h-screen">
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 pb-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">Edit Pengecekan</h1>
            <div class="flex gap-2">
                <a href="<?php echo BASE_URL; ?>checkups/index.php" class="btn btn-secondary">
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
        
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h6 class="m-0 font-bold text-indigo-600">Form Edit Pengecekan</h6>
            </div>
            <div class="p-6">
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Pasien:</strong> <?php echo htmlspecialchars($checkup['patient_name']); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="" id="editCheckupForm">
                    <h5 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Pengukuran Fisik</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="mb-3">
                            <label for="systolic_pressure" class="block text-sm font-bold text-gray-700 mb-2">Tekanan Darah Sistolik <span class="text-red-500">*</span></label>
                            <input type="number" class="form-control" id="systolic_pressure" name="systolic_pressure" value="<?php echo $checkup['systolic_pressure']; ?>" min="70" max="300" required>
                            <small class="text-gray-500 mt-1 block">mmHg (70-300)</small>
                        </div>
                        <div class="mb-3">
                            <label for="diastolic_pressure" class="block text-sm font-bold text-gray-700 mb-2">Tekanan Darah Diastolik <span class="text-red-500">*</span></label>
                            <input type="number" class="form-control" id="diastolic_pressure" name="diastolic_pressure" value="<?php echo $checkup['diastolic_pressure']; ?>" min="40" max="200" required>
                            <small class="text-gray-500 mt-1 block">mmHg (40-200)</small>
                        </div>
                        <div class="mb-3">
                            <label for="blood_sugar" class="block text-sm font-bold text-gray-700 mb-2">Gula Darah <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" class="form-control" id="blood_sugar" name="blood_sugar" value="<?php echo $checkup['blood_sugar']; ?>" min="50" max="1000" required>
                            <small class="text-gray-500 mt-1 block">mg/dL (50-1000)</small>
                        </div>
                        <div class="mb-3">
                            <label for="height" class="block text-sm font-bold text-gray-700 mb-2">Tinggi Badan <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" class="form-control" id="height" name="height" value="<?php echo $checkup['height'] ?? ''; ?>" min="100" max="250" required>
                            <small class="text-gray-500 mt-1 block">cm (100-250)</small>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="mb-3">
                            <label for="weight" class="block text-sm font-bold text-gray-700 mb-2">Berat Badan <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" class="form-control" id="weight" name="weight" value="<?php echo $checkup['weight'] ?? ''; ?>" min="20" max="300" required>
                            <small class="text-gray-500 mt-1 block">kg (20-300)</small>
                        </div>
                        <div class="mb-3">
                            <label for="waist_circumference" class="block text-sm font-bold text-gray-700 mb-2">Lingkar Perut <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" class="form-control" id="waist_circumference" name="waist_circumference" value="<?php echo $checkup['waist_circumference'] ?? ''; ?>" min="50" max="200" required>
                            <small class="text-gray-500 mt-1 block">cm (50-200)</small>
                        </div>
                        <div class="mb-3">
                            <label for="bmi" class="block text-sm font-bold text-gray-700 mb-2">BMI (Otomatis)</label>
                            <input type="text" class="form-control bg-gray-100" id="bmi" value="<?php echo $checkup['bmi']; ?>" readonly>
                            <small class="text-gray-500 mt-1 block">Akan dihitung otomatis</small>
                        </div>
                    </div>
                    
                    <h5 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Faktor Risiko</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="mb-3">
                            <label for="family_history" class="block text-sm font-bold text-gray-700 mb-2">Riwayat Keluarga <span class="text-red-500">*</span></label>
                            <select class="form-select w-full" id="family_history" name="family_history" required>
                                <option value="">Pilih Status</option>
                                <option value="Tidak Ada" <?php echo ($checkup['family_history'] == 'Tidak Ada') ? 'selected' : ''; ?>>Tidak Ada</option>
                                <option value="Hipertensi" <?php echo ($checkup['family_history'] == 'Hipertensi') ? 'selected' : ''; ?>>Hipertensi</option>
                                <option value="Diabetes Melitus" <?php echo ($checkup['family_history'] == 'Diabetes Melitus') ? 'selected' : ''; ?>>Diabetes Melitus</option>
                                <option value="Hipertensi dan Diabetes Melitus" <?php echo ($checkup['family_history'] == 'Hipertensi dan Diabetes Melitus') ? 'selected' : ''; ?>>Hipertensi dan Diabetes Melitus</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="personal_history" class="block text-sm font-bold text-gray-700 mb-2">Riwayat Pribadi <span class="text-red-500">*</span></label>
                            <select class="form-select w-full" id="personal_history" name="personal_history" required>
                                <option value="">Pilih Status</option>
                                <option value="Tidak Ada" <?php echo (($checkup['personal_history'] ?? '') == 'Tidak Ada') ? 'selected' : ''; ?>>Tidak Ada</option>
                                <option value="Hipertensi" <?php echo (($checkup['personal_history'] ?? '') == 'Hipertensi') ? 'selected' : ''; ?>>Hipertensi</option>
                                <option value="Diabetes Melitus" <?php echo (($checkup['personal_history'] ?? '') == 'Diabetes Melitus') ? 'selected' : ''; ?>>Diabetes Melitus</option>
                                <option value="Hipertensi dan Diabetes Melitus" <?php echo (($checkup['personal_history'] ?? '') == 'Hipertensi dan Diabetes Melitus') ? 'selected' : ''; ?>>Hipertensi dan Diabetes Melitus</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="mb-3">
                            <label for="smoking_status" class="block text-sm font-bold text-gray-700 mb-2">Status Merokok <span class="text-red-500">*</span></label>
                            <select class="form-select w-full" id="smoking_status" name="smoking_status" required>
                                <option value="">Pilih Status</option>
                                <option value="Tidak Merokok" <?php echo ($checkup['smoking_status'] == 'Tidak Merokok') ? 'selected' : ''; ?>>Tidak Merokok</option>
                                <option value="Merokok" <?php echo ($checkup['smoking_status'] == 'Merokok') ? 'selected' : ''; ?>>Merokok</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="physical_activity" class="block text-sm font-bold text-gray-700 mb-2">Aktivitas Fisik <span class="text-red-500">*</span></label>
                            <select class="form-select w-full" id="physical_activity" name="physical_activity" required>
                                <option value="">Pilih Level</option>
                                <option value="Tidak Aktif" <?php echo ($checkup['physical_activity'] == 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                                <option value="Ringan" <?php echo ($checkup['physical_activity'] == 'Ringan') ? 'selected' : ''; ?>>Ringan</option>
                                <option value="Aktif" <?php echo ($checkup['physical_activity'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="fruit_vegetable_consumption" class="block text-sm font-bold text-gray-700 mb-2">Konsumsi Buah/Sayur <span class="text-red-500">*</span></label>
                            <select class="form-select w-full" id="fruit_vegetable_consumption" name="fruit_vegetable_consumption" required>
                                <option value="">Pilih Level</option>
                                <option value="Kurang" <?php echo (($checkup['fruit_vegetable_consumption'] ?? '') == 'Kurang') ? 'selected' : ''; ?>>Kurang</option>
                                <option value="Cukup" <?php echo (($checkup['fruit_vegetable_consumption'] ?? '') == 'Cukup') ? 'selected' : ''; ?>>Cukup</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                        <button type="button" onclick="resetForm('editCheckupForm', 'Reset Form')" class="btn btn-warning">
                            <i class="fas fa-undo me-2"></i>Reset
                        </button>
                        <a href="<?php echo BASE_URL; ?>checkups/index.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Batal
                        </a>
                        <button type="button" onclick="saveData('editCheckupForm', 'Simpan Perubahan')" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

<script>
// Auto calculate BMI when weight or height changes
document.getElementById('weight').addEventListener('input', calculateBMI);
document.getElementById('height').addEventListener('input', calculateBMI);

function calculateBMI() {
    const weight = parseFloat(document.getElementById('weight').value);
    const height = parseFloat(document.getElementById('height').value);
    
    if (weight && height && height > 0) {
        const bmi = weight / Math.pow(height / 100, 2);
        document.getElementById('bmi').value = bmi.toFixed(2);
    } else {
        document.getElementById('bmi').value = '';
    }
}
</script>