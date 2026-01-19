<?php
/**
 * Admin Index - Redirect to login
 * This prevents direct access to admin folder
 */

// Redirect to login page
header('Location: login.php');
exit();
?>
