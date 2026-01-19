<?php
/**
 * Admin Authentication Check
 * Include this file at the top of every admin page (except login.php)
 * This ensures no one can access admin pages without logging in
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    // Not logged in - redirect to login page
    header('Location: login.php?error=unauthorized');
    exit();
}

// Check if session is expired (optional - 30 minutes timeout)
$timeout_duration = 1800; // 30 minutes in seconds

if (isset($_SESSION['admin_last_activity'])) {
    $elapsed_time = time() - $_SESSION['admin_last_activity'];
    
    if ($elapsed_time > $timeout_duration) {
        // Session expired
        session_unset();
        session_destroy();
        header('Location: login.php?error=session_expired');
        exit();
    }
}

// Update last activity time
$_SESSION['admin_last_activity'] = time();

// Verify admin exists in database (extra security)
require_once __DIR__ . '/../config/database.php';
$conn = getDBConnection();
if ($conn) {
    $admin_id = intval($_SESSION['admin_id']);
    $verify_result = $conn->query("SELECT id FROM admins WHERE id = $admin_id");
    
    if (!$verify_result || $verify_result->num_rows === 0) {
        // Admin not found in database - logout
        session_unset();
        session_destroy();
        $conn->close();
        header('Location: login.php?error=invalid_session');
        exit();
    }
    $conn->close();
}

// All checks passed - admin is authenticated
?>
