<?php
/**
 * Database Configuration Example
 * Copy this file to database.php and update with your credentials
 */

// Database connection settings
$host = 'localhost';
$username = 'root';
$password = 'your_password_here';
$database = 'ecommerce_db';
$port = 3306;

// Create connection function
function getDBConnection() {
    global $host, $username, $password, $database, $port;
    
    try {
        $conn = new mysqli($host, $username, $password, $database, $port);
        
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            return false;
        }
        
        // Set charset to UTF-8
        $conn->set_charset("utf8");
        
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return false;
    }
}

// Close connection function
function closeDBConnection($conn) {
    if ($conn && !$conn->connect_error) {
        $conn->close();
    }
}

// Test connection function
function testDBConnection() {
    $conn = getDBConnection();
    if ($conn) {
        closeDBConnection($conn);
        return true;
    }
    return false;
}
?>