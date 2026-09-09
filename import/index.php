<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

requireLogin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == 0) {
        $file = $_FILES['excel_file'];
        $filename = $file['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Check file type
        $allowed_types = ['xlsx', 'xls', 'csv'];
        if (!in_array(strtolower($filetype), $allowed_types)) {
            $error = 'File harus berformat Excel (XLSX/XLS) atau CSV!';
        } else {
            try {
                // Load file using PhpSpreadsheet
                $spreadsheet = IOFactory::load($file['tmp_name']);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();
                
                $rowNum = 0;
                $imported = 0;
                $errors = [];
                
                foreach ($rows as $data) {
                    $rowNum++;
                    
                    // Skip header row
                    if ($rowNum == 1) continue;
                    
                    // Skip empty rows
                    if (empty($data[0]) && empty($data[1])) continue;
                    
                    // Validate data length/structure
                    if (count($data) < 4) { // At least Name, Age, Gender, Address
                        $errors[] = "Baris $rowNum: Data tidak lengkap";
                        continue;
                    }
                    
                    $name = sanitize($data[0] ?? '');
                    $age = (int)($data[1] ?? 0);
                    $gender = sanitize($data[2] ?? '');
                    $address = sanitize($data[3] ?? '');
                    $phone = sanitize($data[4] ?? '');
                    
                    // Validation
                    if (empty($name) || empty($age) || empty($gender) || empty($address)) {
                        $errors[] = "Baris $rowNum: Data wajib tidak boleh kosong (Nama, Umur, JK, Alamat)";
                        continue;
                    }
                    
                    if ($age < 1 || $age > 150) {
                        $errors[] = "Baris $rowNum: Umur harus antara 1-150 tahun";
                        continue;
                    }
                    
                    // Normalize Gender
                    $gender = strtoupper($gender);
                    if ($gender == 'LAKI-LAKI') $gender = 'L';
                    if ($gender == 'PEREMPUAN') $gender = 'P';
                    
                    if (!in_array($gender, ['L', 'P'])) {
                        $errors[] = "Baris $rowNum: Jenis kelamin harus L atau P";
                        continue;
                    }
                    
                    // Insert patient
                    $query = "INSERT INTO patients (name, age, gender, address, phone) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("sisss", $name, $age, $gender, $address, $phone);
                    
                    if ($stmt->execute()) {
                        $imported++;
                    } else {
                        $errors[] = "Baris $rowNum: Gagal menyimpan data (" . $stmt->error . ")";
                    }
                }
                
                if ($imported > 0) {
                    $success = "Berhasil mengimpor $imported data pasien!";
                    if (!empty($errors)) {
                        $success .= " Terdapat " . count($errors) . " error.";
                    }
                } else {
                    $error = "Tidak ada data yang berhasil diimpor!";
                }
                
                if (!empty($errors)) {
                    $error .= " Detail error: " . implode(", ", array_slice($errors, 0, 5));
                    if (count($errors) > 5) {
                        $error .= " dan " . (count($errors) - 5) . " error lainnya";
                    }
                }
                
            } catch (Exception $e) {
                $error = 'Gagal membaca file: ' . $e->getMessage();
            }
        }
    } else {
        $error = 'Pilih file Excel terlebih dahulu!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data - SPK Hipertensi Predictor</title>
    <?php include '../includes/header.php'; ?>
</head>
<body class="bg-gray-50">
<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main">
        <main class="content p-6">
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Import Data Pasien</h1>
                    <p class="text-gray-500 text-sm mt-1">Upload data pasien via Excel</p>
                </div>
                <div class="flex flex-col gap-2">
                    <a href="<?php echo BASE_URL; ?>import/generate_template.php" class="btn bg-green-600 hover:bg-green-700 text-white shadow-sm hover:shadow-md transition-all" download>
                        <i class="fas fa-file-excel me-2"></i>Download Template Excel
                    </a>
                    <a href="<?php echo BASE_URL; ?>import/generate_template_csv.php" class="btn bg-blue-600 hover:bg-blue-700 text-white shadow-sm hover:shadow-md transition-all" download>
                        <i class="fas fa-file-csv me-2"></i>Download Template CSV
                    </a>
                </div>
            </div>
            
            <?php if ($success): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: '<?php echo addslashes($success); ?>',
                        timer: 3000,
                        showConfirmButton: false
                    });
                });
            </script>
            <?php endif; ?>

            <?php if ($error): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: '<?php echo addslashes($error); ?>'
                    });
                });
            </script>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Form Import -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h6 class="font-bold text-gray-700">Form Import Excel/CSV</h6>
                        </div>
                        <div class="p-6">
                            <form method="POST" action="" enctype="multipart/form-data" id="importForm">
                                <div class="mb-6">
                                    <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-2">Pilih File <span class="text-red-500">*</span></label>
                                    <div class="flex items-center justify-center w-full">
                                        <label for="excel_file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                                <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Klik untuk upload</span> atau drag and drop</p>
                                                <p class="text-xs text-gray-500">XLSX, XLS, CSV</p>
                                            </div>
                                            <input type="file" id="excel_file" name="excel_file" accept=".xlsx, .xls, .csv" required class="hidden" onchange="updateFileName(this)">
                                        </label>
                                    </div>
                                    <div id="file-name" class="mt-2 text-sm text-gray-600 text-center hidden"></div>
                                </div>
                                
                                <div class="flex justify-end gap-3">
                                    <button type="button" onclick="resetForm()" class="btn btn-secondary">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </button>
                                    <button type="button" onclick="submitImport()" class="btn btn-primary">
                                        <i class="fas fa-upload me-2"></i>Import Data
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Panduan -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h6 class="font-bold text-gray-700">Panduan Import (Excel & CSV)</h6>
                        </div>
                        <div class="p-6">
                            <div class="mb-4 text-sm text-gray-600 bg-blue-50 p-3 rounded-lg border border-blue-100">
                                <i class="fas fa-info-circle text-blue-500 me-2"></i>
                                Support format <strong>.xlsx</strong>, <strong>.xls</strong>, dan <strong>.csv</strong>
                            </div>
                            
                            <h6 class="font-semibold text-gray-700 mb-3">Struktur Kolom:</h6>
                            <ul class="space-y-2 text-sm text-gray-600 mb-6">
                                <li class="flex items-start">
                                    <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-indigo-100 text-indigo-800 text-xs font-bold mr-2 mt-0.5">1</span>
                                    <span><strong>Nama Lengkap</strong> (Teks)</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-indigo-100 text-indigo-800 text-xs font-bold mr-2 mt-0.5">2</span>
                                    <span><strong>Umur</strong> (Angka 1-150)</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-indigo-100 text-indigo-800 text-xs font-bold mr-2 mt-0.5">3</span>
                                    <span><strong>Jenis Kelamin</strong> (L/P)</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-indigo-100 text-indigo-800 text-xs font-bold mr-2 mt-0.5">4</span>
                                    <span><strong>Alamat</strong> (Teks)</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-indigo-100 text-indigo-800 text-xs font-bold mr-2 mt-0.5">5</span>
                                    <span><strong>Telepon</strong> (Opsional)</span>
                                </li>
                            </ul>
                            
                            <hr class="border-gray-100 my-4">
                            
                            <h6 class="font-semibold text-gray-700 mb-2">Catatan:</h6>
                            <p class="text-sm text-gray-600">
                                Gunakan template yang disediakan agar format data sesuai. Pastikan tidak ada kolom yang kosong untuk data wajib.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    function updateFileName(input) {
        const fileNameDiv = document.getElementById('file-name');
        if (input.files && input.files.length > 0) {
            fileNameDiv.textContent = 'File terpilih: ' + input.files[0].name;
            fileNameDiv.classList.remove('hidden');
        } else {
            fileNameDiv.classList.add('hidden');
        }
    }

    function resetForm() {
        document.getElementById('importForm').reset();
        document.getElementById('file-name').classList.add('hidden');
    }

    function submitImport() {
        const fileInput = document.getElementById('excel_file');
        if (!fileInput.files || fileInput.files.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Silakan pilih file terlebih dahulu!'
            });
            return;
        }
        
        Swal.fire({
            title: 'Import Data?',
            text: "Pastikan format file sudah sesuai!",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#667eea',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Import!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('importForm').submit();
            }
        });
    }
</script>

<?php include '../includes/footer.php'; ?>