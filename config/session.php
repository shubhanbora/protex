<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current admin ID
function getCurrentAdminId() {
    return $_SESSION['admin_id'] ?? null;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        // Get the current script path to determine redirect location
        $current_path = $_SERVER['PHP_SELF'];
        
        // If in account folder, redirect to ../login.php
        if (strpos($current_path, '/account/') !== false) {
            header('Location: ../login.php?redirect=' . urlencode($current_path));
        } else {
            header('Location: login.php?redirect=' . urlencode($current_path));
        }
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ../admin/login.php');
        exit();
    }
}
?>
