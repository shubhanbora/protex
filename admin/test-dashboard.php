<?php
// Test dashboard functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Admin Dashboard Test</h2>";

// Test 1: Session check
session_start();
echo "<h3>1. Session Test:</h3>";
if (isset($_SESSION['admin_id'])) {
    echo "<p>✅ Admin logged in: " . ($_SESSION['admin_username'] ?? 'Unknown') . "</p>";
} else {
    echo "<p>❌ Admin not logged in</p>";
    echo "<p><a href='login.php'>Login as Admin</a></p>";
    echo "<p><strong>Default credentials:</strong> admin / password</p>";
}

// Test 2: Database connection
echo "<h3>2. Database Test:</h3>";
require_once '../config/database.php';
$conn = getDBConnection();
if ($conn) {
    echo "<p>✅ Database connection successful</p>";
    
    // Test 3: Basic queries
    echo "<h3>3. Query Tests:</h3>";
    try {
        $products_result = $conn->query("SELECT COUNT(*) as count FROM products WHERE is_deleted = 0");
        if ($products_result) {
            $products_count = $products_result->fetch_assoc()['count'];
            echo "<p>✅ Products query: $products_count products found</p>";
        } else {
            echo "<p>❌ Products query failed</p>";
        }
        
        $users_result = $conn->query("SELECT COUNT(*) as count FROM users");
        if ($users_result) {
            $users_count = $users_result->fetch_assoc()['count'];
            echo "<p>✅ Users query: $users_count users found</p>";
        } else {
            echo "<p>❌ Users query failed</p>";
        }
        
        $orders_result = $conn->query("SELECT COUNT(*) as count FROM orders");
        if ($orders_result) {
            $orders_count = $orders_result->fetch_assoc()['count'];
            echo "<p>✅ Orders query: $orders_count orders found</p>";
        } else {
            echo "<p>❌ Orders query failed</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Query error: " . $e->getMessage() . "</p>";
    }
    
    $conn->close();
} else {
    echo "<p>❌ Database connection failed</p>";
}

// Test 4: File existence
echo "<h3>4. File Tests:</h3>";
$files_to_check = [
    'dashboard.php' => 'Main Dashboard',
    'dashboard-simple.php' => 'Simple Dashboard',
    'auth_check.php' => 'Auth Check',
    'login.php' => 'Login Page',
    'includes/header.php' => 'Header Include'
];

foreach ($files_to_check as $file => $description) {
    if (file_exists($file)) {
        echo "<p>✅ $description ($file) exists</p>";
    } else {
        echo "<p>❌ $description ($file) missing</p>";
    }
}

// Test 5: Dashboard links
echo "<h3>5. Dashboard Links:</h3>";
echo "<ul>";
echo "<li><a href='dashboard-simple.php' target='_blank'>Simple Dashboard (Guaranteed Working)</a></li>";
echo "<li><a href='dashboard.php' target='_blank'>Main Dashboard</a></li>";
echo "<li><a href='test-admin.php' target='_blank'>Admin Test Page</a></li>";
echo "<li><a href='login.php' target='_blank'>Admin Login</a></li>";
echo "</ul>";

echo "<h3>6. Troubleshooting:</h3>";
echo "<ul>";
echo "<li>If you get 500 error, use Simple Dashboard instead</li>";
echo "<li>Make sure you're logged in as admin first</li>";
echo "<li>Check that database tables exist</li>";
echo "<li>Verify PHP error logs if issues persist</li>";
echo "</ul>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h2, h3 { color: #333; }
p { margin: 5px 0; }
ul { margin: 10px 0; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>