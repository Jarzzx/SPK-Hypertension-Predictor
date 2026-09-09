<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

requireLogin();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Delete patient (cascade will delete related checkups and predictions)
    $query = "DELETE FROM patients WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header('Location: index.php?success=' . urlencode('Data pasien berhasil dihapus!'));
    } else {
        header('Location: index.php?error=' . urlencode('Gagal menghapus data pasien!'));
    }
} else {
    header('Location: index.php?error=' . urlencode('ID pasien tidak valid!'));
}
exit();
?>