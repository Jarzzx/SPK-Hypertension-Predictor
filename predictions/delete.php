<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

requireLogin();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Delete prediction
    $query = "DELETE FROM predictions WHERE patient_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header('Location: index.php?success=' . urlencode('Data prediksi berhasil dihapus!'));
    } else {
        header('Location: index.php?error=' . urlencode('Gagal menghapus data prediksi!'));
    }
} else {
    header('Location: index.php?error=' . urlencode('ID prediksi tidak valid!'));
}
exit();
?>