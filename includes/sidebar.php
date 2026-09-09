<!-- Sidebar Desktop -->
<nav class="hidden lg:flex flex-col fixed top-0 bottom-0 left-0 bg-white sidebar overflow-hidden" id="sidebarMenu">
    <!-- Sidebar Header -->
    <div class="sidebar-header h-20 flex items-center justify-between px-6 border-b border-gray-50 shrink-0">
        <a class="sidebar-logo flex items-center text-indigo-600 text-xl font-bold truncate" href="<?php echo BASE_URL; ?>index.php">
            <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center mr-3 shrink-0">
                <i class="fas fa-heartbeat"></i>
            </div>
            <span class="sidebar-text tracking-tight"><?php echo APP_NAME; ?></span>
        </a>
        <button id="sidebarToggle" class="text-gray-400 hover:text-indigo-600 transition-colors p-1 rounded-md hover:bg-indigo-50 hidden lg:block">
            <i class="fas fa-stream"></i>
        </button>
    </div>

    <!-- Sidebar Content -->
    <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
        <ul class="flex flex-col space-y-1">
            <li>
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-text mb-1">Menu</div>
            </li>
            <li>
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/patients/') === false && strpos($_SERVER['PHP_SELF'], '/checkups/') === false && strpos($_SERVER['PHP_SELF'], '/predictions/') === false && strpos($_SERVER['PHP_SELF'], '/reports/') === false && strpos($_SERVER['PHP_SELF'], '/import/') === false && strpos($_SERVER['PHP_SELF'], '/users/') === false && strpos($_SERVER['PHP_SELF'], '/settings/') === false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php">
                    <i class="fas fa-tachometer-alt w-6 text-center"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/patients/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>patients/index.php">
                    <i class="fas fa-users w-6 text-center"></i>
                    <span class="sidebar-text">Data Pasien</span>
                </a>
            </li>
            <li>
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/checkups/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>checkups/index.php">
                    <i class="fas fa-stethoscope w-6 text-center"></i>
                    <span class="sidebar-text">Pengecekan</span>
                </a>
            </li>
            <li>
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/predictions/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>predictions/index.php">
                    <i class="fas fa-chart-line w-6 text-center"></i>
                    <span class="sidebar-text">Prediksi</span>
                </a>
            </li>
            <li>
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/reports/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>reports/index.php">
                    <i class="fas fa-chart-bar w-6 text-center"></i>
                    <span class="sidebar-text">Laporan</span>
                </a>
            </li>
            <li>
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/import/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>import/index.php">
                    <i class="fas fa-file-import w-6 text-center"></i>
                    <span class="sidebar-text">Import Data</span>
                </a>
            </li>
        </ul>
        <div class="px-4 py-2 mt-6 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-text mb-1">
            <span class="sidebar-text">Pengaturan</span>
        </div>
        <ul class="flex flex-col space-y-1 mb-4">
            <li>
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/users/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>users/index.php">
                    <i class="fas fa-user-cog w-6 text-center"></i>
                    <span class="sidebar-text">Manajemen User</span>
                </a>
            </li>
            <li>
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/settings/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>settings/index.php">
                    <i class="fas fa-cog w-6 text-center"></i>
                    <span class="sidebar-text">Pengaturan Sistem</span>
                </a>
            </li>
            <li class="mt-4">
                <button class="nav-link w-full text-left" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt w-6 text-center"></i>
                    <span class="sidebar-text">Logout</span>
                </button>
            </li>
        </ul>
    </div>
</nav>

<!-- Mobile Sidebar Overlay & Menu -->
<div id="mobile-sidebar-backdrop" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 hidden lg:hidden" onclick="toggleMobileSidebar()"></div>
<nav id="sidebarMenuMobile" class="fixed inset-y-0 left-0 w-64 bg-white shadow-xl z-50 transform -translate-x-full transition-transform duration-300 ease-in-out lg:hidden">
    <div class="flex items-center justify-between p-4 border-b">
        <h5 class="text-lg font-bold text-gray-800">Menu</h5>
        <button type="button" class="text-gray-500 hover:text-gray-700 focus:outline-none" onclick="toggleMobileSidebar()">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>
    <div class="p-4 overflow-y-auto h-full pb-20">
        <ul class="flex flex-col space-y-1">
            <!-- Items duplicated for mobile for simplicity, or could use include if extracted -->
            <li>
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], '/patients/') === false && strpos($_SERVER['PHP_SELF'], '/checkups/') === false && strpos($_SERVER['PHP_SELF'], '/predictions/') === false && strpos($_SERVER['PHP_SELF'], '/reports/') === false && strpos($_SERVER['PHP_SELF'], '/import/') === false && strpos($_SERVER['PHP_SELF'], '/users/') === false && strpos($_SERVER['PHP_SELF'], '/settings/') === false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php">
                    <i class="fas fa-tachometer-alt w-6 text-center"></i> Dashboard
                </a>
            </li>
            <li>
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/patients/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>patients/index.php">
                    <i class="fas fa-users w-6 text-center"></i> Data Pasien
                </a>
            </li>
            <li>
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/checkups/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>checkups/index.php">
                    <i class="fas fa-stethoscope w-6 text-center"></i> Pengecekan
                </a>
            </li>
            <li>
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/predictions/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>predictions/index.php">
                    <i class="fas fa-chart-line w-6 text-center"></i> Prediksi
                </a>
            </li>
            <li>
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/reports/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>reports/index.php">
                    <i class="fas fa-chart-bar w-6 text-center"></i> Laporan
                </a>
            </li>
            <li>
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/import/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>import/index.php">
                    <i class="fas fa-file-import w-6 text-center"></i> Import Data
                </a>
            </li>
             <h6 class="flex items-center px-3 mt-8 mb-2 text-xs font-bold text-gray-400 uppercase tracking-wider">
                <span class="sidebar-text">Pengaturan</span>
            </h6>
            <li>
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/users/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>users/index.php">
                    <i class="fas fa-user-cog w-6 text-center"></i> Manajemen User
                </a>
            </li>
            <li>
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/settings/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>settings/index.php">
                    <i class="fas fa-cog w-6 text-center"></i> Pengaturan Sistem
                </a>
            </li>
            <li class="mt-4">
                <button class="nav-link w-full text-left" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt w-6 text-center"></i> Logout
                </button>
            </li>
        </ul>
    </div>
</nav>