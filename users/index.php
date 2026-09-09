<?php
require_once '../config/config.php';
require_once '../config/database.php';
requireLogin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $name = sanitize($_POST['name']);
    $role = sanitize($_POST['role']);
    $password = $_POST['password'];

    if (!$username || !$name || !$role || !$password) {
        $error = 'Semua field wajib diisi';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Username sudah digunakan';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)");
            $ins->bind_param("ssss", $username, $hash, $name, $role);
            if ($ins->execute()) {
                $success = 'User berhasil ditambahkan';
            } else {
                $error = 'Gagal menambahkan user: ' . $conn->error;
            }
            $ins->close();
        }
        $stmt->close();
    }
}

$users = $conn->query("SELECT id, username, name, role, created_at FROM users ORDER BY created_at DESC");
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<main class="content p-6 lg:p-8 bg-gray-50/50 min-h-screen">
    <div class="container mx-auto p-4">
                <div class="flex flex-col md:flex-row justify-between items-center mb-4 pb-4 border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-800 mb-2 md:mb-0">Manajemen User</h1>
                </div>
                <?php if ($success): ?>
                    <script>document.addEventListener('DOMContentLoaded',()=>showSuccessAlert('<?php echo addslashes($success); ?>'));</script>
                <?php endif; ?>
                <?php if ($error): ?>
                    <script>document.addEventListener('DOMContentLoaded',()=>showErrorAlert('<?php echo addslashes($error); ?>'));</script>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-5">
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-4">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <h6 class="m-0 font-bold text-indigo-600">Tambah User</h6>
                            </div>
                            <div class="p-6">
                                <form method="POST">
                                    <div class="mb-4">
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Username</label>
                                        <input type="text" name="username" class="form-control" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                    <div class="mb-6">
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Role</label>
                                        <select name="role" class="form-select" required>
                                            <option value="admin">Admin</option>
                                            <option value="petugas">Petugas</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-full">
                                        <i class="fas fa-save me-2"></i>Simpan User
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-7">
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-4">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <h6 class="m-0 font-bold text-indigo-600">Daftar User</h6>
                            </div>
                            <div class="p-6">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-left text-gray-500" id="dataTable">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3">Username</th>
                                                <th class="px-4 py-3">Nama</th>
                                                <th class="px-4 py-3">Role</th>
                                                <th class="px-4 py-3">Dibuat</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($u = $users->fetch_assoc()): ?>
                                                <tr class="bg-white border-b hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-medium text-gray-900"><?php echo htmlspecialchars($u['username']); ?></td>
                                                    <td class="px-4 py-3"><?php echo htmlspecialchars($u['name']); ?></td>
                                                    <td class="px-4 py-3">
                                                        <span class="badge <?php echo $u['role'] === 'admin' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'; ?>">
                                                            <?php echo ucfirst($u['role']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3"><?php echo date('d/m/Y H:i', strtotime($u['created_at'])); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
