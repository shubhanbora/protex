<?php
// Security Check - Must be logged in as admin
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Destroy session and logout
session_unset();
session_destroy();
header('Location: login.php');
exit();
?>
