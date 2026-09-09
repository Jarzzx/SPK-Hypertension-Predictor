<?php
// Start session at the very beginning
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Application configuration
define('APP_NAME', 'SPK Hipertensi Predictor');
define('APP_VERSION', '1.5.1');
define('ENVIRONMENT', getenv('APP_ENV') ?: 'development'); // 'development' or 'production'

// Dynamic Base URL Configuration
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_name = $_SERVER['SCRIPT_NAME'];
$base_dir = str_replace('\\', '/', dirname(dirname($script_name)));
if ($base_dir !== '/') $base_dir .= '/';

define('BASE_URL', $protocol . '://' . $host . $base_dir);

// Naive Bayes configuration
define('MIN_CHECKUPS', 3);
define('PREDICTION_CLASSES', [
    'Tidak Berpotensi',
    'Cukup Berpotensi', 
    'Sangat Berpotensi'
]);

// Error reporting based on environment
if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Session configuration - removed ini_set calls to prevent warnings
// Session settings are handled by PHP defaults

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Helper functions
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('auth/login.php');
    }
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

function getStatusClass($status) {
    switch ($status) {
        case 'Tidak Berpotensi':
            return 'badge bg-success';
        case 'Cukup Berpotensi':
            return 'badge bg-warning';
        case 'Sangat Berpotensi':
            return 'badge bg-danger';
        default:
            return 'badge bg-secondary';
    }
}

function calculateBMI($weight, $height) {
    $height_m = $height / 100;
    return $weight / ($height_m * $height_m);
}

function getBloodPressureCategory($systolic, $diastolic) {
    if ($systolic < 120 && $diastolic < 80) {
        return 'Normal';
    } elseif ($systolic < 130 && $diastolic < 80) {
        return 'Elevated';
    } elseif ($systolic < 140 || $diastolic < 90) {
        return 'Stage 1 Hypertension';
    } else {
        return 'Stage 2 Hypertension';
    }
}
