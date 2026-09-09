<?php
require_once '../config/config.php';
require_once '../config/database.php';

requireLogin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Semua field harus diisi!';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Password baru dan konfirmasi password tidak cocok!';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password baru minimal 6 karakter!';
    } else {
        // Verify current password
        $user_id = $_SESSION['user_id'];
        $query = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($current_password, $user['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success = 'Password berhasil diubah!';
            } else {
                $error = 'Gagal mengubah password!';
            }
        } else {
            $error = 'Password saat ini salah!';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - SPK Hipertensi Predictor</title>
    <?php include '../includes/header.php'; ?>
</head>
<body class="bg-gray-50">
<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <main class="content">
            <div class="container mx-auto p-4">
                <div class="flex flex-col md:flex-row justify-between items-center mb-4 pb-4 border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">Pengaturan</h1>
                </div>
                
                <?php if ($success): ?>
                    <script>document.addEventListener('DOMContentLoaded',()=>showSuccessAlert('<?php echo addslashes($success); ?>'));</script>
                <?php endif; ?>
                <?php if ($error): ?>
                    <script>document.addEventListener('DOMContentLoaded',()=>showErrorAlert('<?php echo addslashes($error); ?>'));</script>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Change Password -->
                    <div class="lg:col-span-5">
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-4">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <h6 class="m-0 font-bold text-indigo-600">Ganti Password</h6>
                            </div>
                            <div class="p-6">
                                <form method="POST">
                                    <div class="mb-4">
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Password Saat Ini</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Password Baru</label>
                                        <input type="password" name="new_password" class="form-control" required>
                                        <small class="text-gray-500 text-xs mt-1 block">Minimal 6 karakter</small>
                                    </div>
                                    <div class="mb-6">
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Konfirmasi Password Baru</label>
                                        <input type="password" name="confirm_password" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-full">
                                        <i class="fas fa-key me-2"></i>Update Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- System Info -->
                    <div class="lg:col-span-7">
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-4">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <h6 class="m-0 font-bold text-indigo-600">Informasi Sistem</h6>
                            </div>
                            <div class="p-6">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-left text-gray-500">
                                        <tbody class="divide-y divide-gray-200">
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-900 w-1/3">Nama Aplikasi</td>
                                                <td class="px-4 py-3"><?php echo APP_NAME; ?></td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-900">Versi</td>
                                                <td class="px-4 py-3"><?php echo APP_VERSION; ?></td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-900">PHP Version</td>
                                                <td class="px-4 py-3"><?php echo PHP_VERSION; ?></td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-900">Database</td>
                                                <td class="px-4 py-3">MySQL</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-900">User Login</td>
                                                <td class="px-4 py-3"><?php echo $_SESSION['user_name'] ?? 'Operator'; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
</main>
<?php include '../includes/footer.php'; ?> 