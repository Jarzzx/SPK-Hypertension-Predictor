<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#667eea',
                        secondary: '#764ba2'
                    },
                    fontFamily: {
                        poppins: ['Poppins', 'Arial', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Fallback FontAwesome jika CDN gagal -->
    <script>
        // Check if FontAwesome loaded
        setTimeout(function() {
            if (!document.querySelector('.fas')) {
                console.log('FontAwesome not loaded, trying alternative CDN');
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://use.fontawesome.com/releases/v6.4.0/css/all.min.css';
                document.head.appendChild(link);
            }
        }, 1000);
    </script>
    
    <!-- DataTables (Tailwind) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css">
    
    <!-- Custom CSS -->
    <style type="text/tailwindcss">
        /* Global Scrollbar Hide */
        html, body {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        html::-webkit-scrollbar, body::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }

        body { 
            font-family: 'Poppins', Arial, sans-serif; 
            background-color: #f3f4f6; 
            overflow-x: hidden; /* Prevent horizontal scroll */
        }
        
        /* Layout & Sidebar */
        .sidebar {
            box-shadow: none;
            border-right: 1px solid #e5e7eb;
            z-index: 50; /* Higher than content, same as nav if needed */
            transition: all 0.3s ease-in-out;
            width: 260px; /* Slightly wider */
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #ffffff;
            /* Hide scrollbar visually but allow scrolling */
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        
        .sidebar::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }
        
        .sidebar-collapsed .sidebar {
            width: 4.5rem; /* Collapsed width */
        }

        /* Fix for Sidebar Header when collapsed */
        .sidebar-collapsed .sidebar-header {
            padding-left: 0 !important;
            padding-right: 0 !important;
            justify-content: center !important;
            width: 4.5rem; /* Ensure header matches sidebar width */
        }
        .sidebar-collapsed .sidebar-logo {
            display: none !important;
        }
        
        .sidebar .nav-link {
            display: flex;
            align-items: center;
            border-radius: 0.75rem; /* More rounded */
            transition: all .2s ease-in-out;
            padding: 0.8rem 1rem;
            margin-bottom: 0.5rem;
            color: #64748b; /* Slate 500 */
            font-weight: 500;
            font-size: 0.95rem;
            white-space: nowrap;
            overflow: hidden;
            position: relative;
        }

        .sidebar-collapsed .sidebar .nav-link {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
            width: 100%;
        }

        .sidebar-collapsed .sidebar .sidebar-text,
        .sidebar-collapsed .sidebar .nav-link.active .sidebar-text {
            display: none !important;
        }

        .sidebar-collapsed .sidebar .fa,
        .sidebar-collapsed .sidebar .fas {
            margin-right: 0 !important;
        }
        
        .sidebar .nav-link:hover {
            background-color: #f8fafc;
            color: #4f46e5;
        }
        
        /* Icon animation on hover */
        .sidebar .nav-link:hover i {
            transform: scale(1.2);
            transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .sidebar .nav-link i {
            transition: transform 0.2s ease;
            margin-right: 1rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-collapsed .sidebar .nav-link:hover {
            transform: none;
        }
        
        .sidebar .nav-link.active {
            background-color: #4f46e5;
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2), 0 2px 4px -1px rgba(79, 70, 229, 0.1);
        }

        /* Override border-left from previous design */
        .sidebar .nav-link.active {
            border-left: none;
        }
        
        /* Content Area */
        .content {
            padding-top: 8rem !important; /* Increased top padding to prevent header overlap */
            min-height: 100vh;
            transition: all 0.3s ease-in-out;
            margin-left: 0; /* Mobile first */
        }

        @media (min-width: 1024px) {
            .content {
                margin-left: 260px !important;
            }
            
            .sidebar-collapsed .content {
                margin-left: 5rem !important; /* Adjusted to 5rem to provide safe gap */
            }
        }
        
        /* Ensure FontAwesome icons are visible */
        .fas, .fa {
            font-family: 'Font Awesome 6 Free' !important;
            font-weight: 900 !important;
        }

        /* Modal Transitions */
        .modal {
            transition: opacity 0.25s ease;
        }
        body.modal-active {
            overflow-x: hidden;
            overflow-y: hidden !important;
        }
        
        /* DataTables Customization */
        .dataTables_wrapper .dataTables_length select {
            padding-right: 2rem;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem; /* rounded-lg */
            font-weight: 500;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* shadow-sm */
        }
        .btn:active {
            transform: translateY(1px);
        }
        
        .btn-primary { @apply bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500; }
        .btn-secondary { @apply bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500; }
        .btn-success { @apply bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500; }
        .btn-danger { @apply bg-rose-600 text-white hover:bg-rose-700 focus:ring-2 focus:ring-offset-2 focus:ring-rose-500; }
        .btn-warning { @apply bg-amber-500 text-white hover:bg-amber-600 focus:ring-2 focus:ring-offset-2 focus:ring-amber-500; }
        .btn-info { @apply bg-sky-500 text-white hover:bg-sky-600 focus:ring-2 focus:ring-offset-2 focus:ring-sky-500; }
        
        .btn-sm {
            padding: 0.35rem 0.65rem; /* Slightly larger padding */
            font-size: 0.8rem;
            line-height: 1rem;
            border-radius: 0.375rem;
            @apply border shadow-sm transition-all duration-200;
        }
        .btn-sm i {
            font-size: 0.75rem;
        }

        /* Modern Soft Buttons */
        .btn-primary.btn-sm { @apply bg-indigo-50 text-indigo-600 border-indigo-200 hover:bg-indigo-100 hover:border-indigo-300; }
        .btn-secondary.btn-sm { @apply bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100 hover:border-gray-300; }
        .btn-success.btn-sm { @apply bg-emerald-50 text-emerald-600 border-emerald-200 hover:bg-emerald-100 hover:border-emerald-300; }
        .btn-danger.btn-sm { @apply bg-rose-50 text-rose-600 border-rose-200 hover:bg-rose-100 hover:border-rose-300; }
        .btn-warning.btn-sm { @apply bg-amber-50 text-amber-600 border-amber-200 hover:bg-amber-100 hover:border-amber-300; }
        .btn-info.btn-sm { @apply bg-sky-50 text-sky-600 border-sky-200 hover:bg-sky-100 hover:border-sky-300; }
        
        /* Form Inputs */
        .form-control, .form-select {
            @apply w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2 transition-colors duration-200;
        }

        /* Badges */
        .badge {
            @apply inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold border whitespace-nowrap shadow-sm uppercase tracking-wide;
        }
        .bg-primary { @apply bg-indigo-50 text-indigo-600 border-indigo-200; }
        .bg-secondary { @apply bg-gray-50 text-gray-600 border-gray-200; }
        .bg-success { @apply bg-emerald-50 text-emerald-600 border-emerald-200; }
        .bg-danger { @apply bg-rose-50 text-rose-600 border-rose-200; }
        .bg-warning { @apply bg-amber-50 text-amber-600 border-amber-200; }
        .bg-info { @apply bg-sky-50 text-sky-600 border-sky-200; }
        
        /* Mobile Overlay */
        #mobile-sidebar-backdrop {
            z-index: 30;
        }

        #sidebarMenuMobile {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        #sidebarMenuMobile::-webkit-scrollbar {
            display: none;
        }

        /* Sidebar toggle button styling - Moved to utility classes in sidebar.php */
        /* #sidebarToggle {
            background-color: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        #sidebarToggle:hover {
            background-color: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: scale(1.05);
        }
        
        #sidebarToggle i {
            font-size: 1.1rem;
            color: white;
        } */

        /* Mobile Main Content - Handled by .content class */
        /* main {
            padding-top: 5rem; 
            min-height: 100vh;
            transition: margin-left 0.3s ease-in-out;
        } */
    </style>
    <style>
        /* Additional Navbar Styles for Layout */
        .top-navbar {
            left: 0;
            width: 100%;
            transition: all 0.3s ease-in-out;
            z-index: 30; /* Lower than sidebar (50) and backdrop (40) */
        }
        @media (min-width: 1024px) {
            .top-navbar {
                left: 260px !important;
                width: calc(100% - 260px) !important;
            }
            .sidebar-collapsed .top-navbar {
                left: 5rem !important;
                width: calc(100% - 5rem) !important;
            }
        }
    </style>
</head>
<body>
    <nav class="top-navbar fixed top-0 right-0 h-20 bg-white border-b border-gray-100 flex items-center px-8 transition-all duration-300">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center flex-1">
                <button class="mr-4 lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors" type="button" onclick="toggleMobileSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                
                <!-- Search Bar -->
                <form action="<?php echo BASE_URL; ?>patients/index.php" method="GET" class="hidden md:flex items-center w-full max-w-md bg-gray-50 rounded-xl px-4 py-2.5 border border-transparent focus-within:border-indigo-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-indigo-200 transition-all duration-200">
                    <button type="submit" class="bg-transparent border-none cursor-pointer p-0">
                        <i class="fas fa-search text-gray-400 mr-3"></i>
                    </button>
                    <input type="text" name="search" placeholder="Cari Data Pasien..." class="bg-transparent border-none outline-none w-full text-sm text-gray-600 placeholder-gray-400 focus:ring-0" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </form>
            </div>
            
            <div class="flex items-center ml-auto gap-4">
                
                <div class="h-8 w-[1px] bg-gray-200 mx-2"></div>

                <div class="relative">
                    <a class="dropdown-toggle flex items-center cursor-pointer p-1 rounded-xl hover:bg-gray-50 transition-all" href="javascript:void(0)" onclick="toggleDropdown('userDropdownMenu')">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 mr-3 overflow-hidden">
                            <!-- Use an image if available, else icon -->
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name'] ?? 'Admin'); ?>&background=random" alt="User" class="w-full h-full object-cover">
                        </div>
                        <div class="hidden md:block text-left mr-2">
                            <strong class="block text-sm font-semibold text-gray-800"><?php echo $_SESSION['user_name'] ?? 'Dr. Robert Fox'; ?></strong>
                            <small class="block text-xs text-gray-500 font-medium"><?php echo ucfirst($_SESSION['user_role'] ?? 'Admin'); ?></small>
                        </div>
                        <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                    </a>
                    <ul id="userDropdownMenu" class="dropdown-menu hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] py-2 z-50 border border-gray-100 overflow-hidden">
                        <li class="px-4 py-3 border-b border-gray-50">
                            <p class="text-sm font-semibold text-gray-800">Signed in as</p>
                            <p class="text-xs text-gray-500 truncate"><?php echo $_SESSION['user_name'] ?? 'User'; ?></p>
                        </li>
                        <li><a class="block px-4 py-2.5 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" href="<?php echo BASE_URL; ?>settings/index.php"><i class="fas fa-cog mr-2 w-5 text-center"></i> Settings</a></li>
                        <li><a class="block px-4 py-2.5 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" href="javascript:void(0)" onclick="confirmLogout()"><i class="fas fa-sign-out-alt mr-2 w-5 text-center text-red-500"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // --- Global UI Logic ---

        // 1. Mobile Sidebar Toggle
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebarMenuMobile');
            const backdrop = document.getElementById('mobile-sidebar-backdrop');
            
            if (sidebar && backdrop) {
                sidebar.classList.toggle('-translate-x-full');
                backdrop.classList.toggle('hidden');
                document.body.style.overflow = backdrop.classList.contains('hidden') ? '' : 'hidden';
            }
        }

        // 2. Dropdown Toggle
        function toggleDropdown(dropdownID) {
            const dropdown = document.getElementById(dropdownID);
            if (dropdown) {
                // Close other dropdowns first
                const allDropdowns = document.querySelectorAll('.dropdown-menu');
                allDropdowns.forEach(d => {
                    if (d.id !== dropdownID && !d.classList.contains('hidden')) {
                        d.classList.add('hidden');
                    }
                });
                
                dropdown.classList.toggle('hidden');
            }
        }
        
        // 3. Modal Toggle (Global)
        function toggleModal(modalID) {
            const modal = document.getElementById(modalID);
            const backdrop = document.getElementById(modalID + '-backdrop');
            
            if (modal) {
                modal.classList.toggle('hidden');
                modal.classList.toggle('flex');
            }
            if (backdrop) {
                backdrop.classList.toggle('hidden');
                backdrop.classList.toggle('flex');
            }
            document.body.classList.toggle('modal-active');
        }

        // Initialize UI Events
        document.addEventListener('DOMContentLoaded', function() {
            // Desktop Sidebar Toggle
            const sidebarToggleBtn = document.getElementById('sidebarToggle');
            if (sidebarToggleBtn) {
                sidebarToggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.body.classList.toggle('sidebar-collapsed');
                    localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
                });
            }

            // Restore Sidebar State
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }

            // Close dropdowns when clicking outside
            window.addEventListener('click', function(event) {
                if (!event.target.closest('.dropdown-toggle') && !event.target.closest('.dropdown-menu')) {
                    document.querySelectorAll('.dropdown-menu').forEach(menu => {
                        if (!menu.classList.contains('hidden')) {
                            menu.classList.add('hidden');
                        }
                    });
                }
            });
        });
    </script>