<?php
session_start();

// Clear all session data
session_destroy();
 
// Redirect to login with success message
header('Location: login.php?success=Anda berhasil logout!');
exit();
?>