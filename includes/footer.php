    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- DataTables (jQuery) -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Global function for Tailwind Pagination styling
        function applyTailwindPagination(settings) {
            // Tailwind Pagination Styling via JS (fixes "susah di klik")
            var wrapper = $(this).closest('.dataTables_wrapper');
            var pagination = wrapper.find('.dataTables_paginate');
            
            // Container styling
            pagination.addClass('flex justify-end gap-1 mt-4 pt-4 border-t border-gray-100');
            
            // Button styling
            var buttons = wrapper.find('.dataTables_paginate .paginate_button');
            buttons.addClass('px-3 py-1.5 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 cursor-pointer transition-colors duration-200 mx-1');
            
            // Active button styling
            var current = wrapper.find('.dataTables_paginate .paginate_button.current');
            current.addClass('!bg-indigo-600 !text-white !border-indigo-600 hover:!bg-indigo-700');
            current.removeClass('text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700');
            
            // Disabled button styling
            var disabled = wrapper.find('.dataTables_paginate .paginate_button.disabled');
            disabled.addClass('opacity-50 cursor-not-allowed hover:bg-white');
        }

        $(document).ready(function() {
            // Initialize DataTables
            var table = $('#dataTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                responsive: true,
                drawCallback: applyTailwindPagination
            });
            
            // Check for search parameter in URL
            const urlParams = new URLSearchParams(window.location.search);
            const searchTerm = urlParams.get('search');
            if (searchTerm) {
                table.search(searchTerm).draw();
            }
            
            // Show alerts from URL parameters
            const success = urlParams.get('success');
            const error = urlParams.get('error');
            const warning = urlParams.get('warning');
            const info = urlParams.get('info');
            
            if (success) {
                showSuccessAlert(decodeURIComponent(success));
            }
            if (error) {
                showErrorAlert(decodeURIComponent(error));
            }
            if (warning) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: decodeURIComponent(warning),
                    confirmButtonColor: '#ffc107',
                    confirmButtonText: 'OK'
                });
            }
            if (info) {
                Swal.fire({
                    icon: 'info',
                    title: 'Informasi!',
                    text: decodeURIComponent(info),
                    confirmButtonColor: '#0dcaf0',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            }
            
            // Simple form validation for CRUD
            $('form[method="POST"]').on('submit', function(e) {
                const requiredFields = $(this).find('[required]');
                let isValid = true;
                
                requiredFields.each(function() {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).addClass('border-red-500'); // Tailwind error class
                    } else {
                        $(this).removeClass('border-red-500');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    showErrorAlert('Mohon lengkapi semua field yang wajib diisi!', 'Validasi Gagal');
                    return false;
                }
            });
            
            // Sidebar state management (Handled in header.php now)
            // const sidebarState = localStorage.getItem('sidebarCollapsed');
            // if (sidebarState === 'true') {
            //     $('body').addClass('sidebar-collapsed');
            // }
            
            // Sidebar toggle (Handled in header.php now)
            // $('#sidebarToggle').on('click', function() {
            //     $('body').toggleClass('sidebar-collapsed');
            //     localStorage.setItem('sidebarCollapsed', $('body').hasClass('sidebar-collapsed'));
            // });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
        
        // UI Logic (Modal, Dropdown, Sidebar) is now centralized in header.php to prevent conflicts
        
        // SweetAlert Helper Functions
        function showSuccessAlert(message, title = 'Berhasil!') {
            Swal.fire({
                icon: 'success',
                title: title,
                text: message,
                confirmButtonColor: '#198754',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        }
        
        function showErrorAlert(message, title = 'Gagal!') {
            Swal.fire({
                icon: 'error',
                title: title,
                text: message,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'OK'
            });
        }
        
        function confirmDelete(url, message = 'Apakah Anda yakin ingin menghapus data ini?') {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
        
        function confirmLogout() {
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Apakah Anda yakin ingin keluar dari sistem?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Logout!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?php echo BASE_URL; ?>auth/logout.php';
                }
            });
        }
        
        function saveData(formId, title = 'Simpan Data') {
            Swal.fire({
                title: title,
                text: 'Simpan data ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
        
        function viewDetail(url, title = 'Detail Data') {
            Swal.fire({
                title: title,
                text: 'Membuka halaman detail...',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#0dcaf0',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Buka!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }

        function editData(url, title = 'Edit Data') {
            Swal.fire({
                title: title,
                text: 'Membuka halaman edit...',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Edit!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }

        function addCheckup(url, patientName = 'Pasien') {
            Swal.fire({
                title: 'Tambah Pengecekan',
                text: 'Tambah pengecekan untuk ' + patientName + '?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tambah!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
        
        function confirmPrediction(patientId) {
            Swal.fire({
                title: 'Konfirmasi Prediksi',
                text: 'Hitung prediksi untuk pasien ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hitung!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?predict=' + patientId;
                }
            });
        }
        
        function validateLogin() {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                showErrorAlert('Username dan password harus diisi!', 'Validasi Gagal');
                return false;
            }
            
            document.getElementById('loginForm').submit();
        }
        
        function resetForm(formId, title = 'Reset Form') {
            Swal.fire({
                title: title,
                text: 'Reset semua data form?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Reset!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).reset();
                    showSuccessAlert('Form berhasil direset!', 'Berhasil');
                }
            });
        }
        
        // Show loading spinner
        function showLoading() {
            $('#loadingSpinner').show();
        }
        
        // Hide loading spinner
        function hideLoading() {
            $('#loadingSpinner').hide();
        }
    </script>
    
    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 hidden z-50">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
    </div>
</body>
</html>

<?php
function getPredictionStats() {
    global $conn;
    $stats = [0, 0, 0];
    
    $query = "SELECT status, COUNT(*) as count FROM predictions GROUP BY status";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        switch ($row['status']) {
            case 'Tidak Berpotensi':
                $stats[0] = $row['count'];
                break;
            case 'Cukup Berpotensi':
                $stats[1] = $row['count'];
                break;
            case 'Sangat Berpotensi':
                $stats[2] = $row['count'];
                break;
        }
    }
    
    return implode(',', $stats);
}
?>