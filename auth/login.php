<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect to dashboard
                header('Location: ' . BASE_URL . 'index.php');
                exit();
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Username tidak ditemukan!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-100 via-purple-100 to-pink-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Decorative Background Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob"></div>
        <div class="absolute top-[-10%] right-[-10%] w-96 h-96 bg-indigo-300 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-32 left-20 w-96 h-96 bg-pink-300 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-4000"></div>
    </div>

    <div class="bg-white/60 backdrop-blur-xl border border-white/40 rounded-[2rem] shadow-2xl w-full max-w-[1200px] overflow-hidden flex flex-col md:flex-row min-h-[700px]">
        
        <!-- Left Side: Form -->
        <div class="w-full md:w-1/2 p-8 md:p-16 flex flex-col justify-center relative">
            <!-- Logo -->
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center text-white transform rotate-3">
                    <i class="fas fa-heartbeat text-xl"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800"><?php echo APP_NAME; ?></h2>
            </div>

            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-3">Selamat Datang.</h1>
                <p class="text-gray-500 text-sm">Silakan masuk dengan data yang Anda miliki</p>
            </div>

            <?php if ($error): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r text-sm">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <p><?php echo $error; ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm" class="space-y-6" onsubmit="event.preventDefault(); validateLogin();" novalidate>
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required 
                        class="w-full px-4 py-3 rounded-xl border border-gray-200/60 bg-white/50 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition duration-200">
                </div>
                
                <div class="relative">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required 
                        class="w-full px-4 py-3 rounded-xl border border-gray-200/60 bg-white/50 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition duration-200 pr-12">
                    <button type="button" class="absolute right-4 top-[2.2rem] text-gray-400 hover:text-indigo-600 transition" onclick="togglePassword()">
                        <i id="toggleIcon" class="fas fa-eye"></i>
                    </button>
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white py-3.5 rounded-xl font-semibold hover:bg-indigo-700 active:scale-[0.99] transition duration-200 shadow-lg shadow-indigo-200">
                    Masuk
                </button>

                <div class="flex items-center justify-between mt-4 text-sm">
                    <label class="flex items-center text-gray-500 cursor-pointer hover:text-gray-700">
                        <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                        <span>Ingat saya</span>
                    </label>
                    <!-- <a href="#" class="text-indigo-600 font-medium hover:underline">Lupa password?</a> -->
                </div>
            </form>
        </div>
        
        <!-- Right Side: Visual -->
        <div class="hidden md:flex w-1/2 bg-white/20 p-12 flex-col justify-center items-center relative overflow-hidden">
            <div class="text-center z-10 mb-8">
                <p class="text-gray-600 font-medium mb-2">Senang bertemu Anda kembali</p>
                <h2 class="text-4xl font-bold text-indigo-900 tracking-tight">Selamat Datang Kembali</h2>
            </div>
            
            <div class="w-full max-w-lg z-10 relative">
                <!-- Background Blob -->
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[120%] h-[120%] bg-white/40 blur-3xl rounded-full -z-10"></div>
                
                <lottie-player 
                    src="https://lottie.host/7aa81193-0d58-4ce6-82d0-192f8dc930d0/x3qACec3zv.json" 
                    background="transparent" 
                    speed="1" 
                    style="width: 100%; height: auto;" 
                    loop 
                    autoplay>
                </lottie-player>
            </div>

            <!-- Decorative Circles -->
            <div class="absolute top-10 right-10 w-24 h-24 bg-purple-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
            <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-indigo-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        function validateLogin() {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            if (!username || !password) {
                Swal.fire({ 
                    icon: 'warning', 
                    title: 'Oops...', 
                    text: 'Username dan password harus diisi!',
                    confirmButtonColor: '#4f46e5'
                });
                return false;
            }
            document.getElementById('loginForm').submit();
        }
    </script>
    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
    </style>
</body>
</html>