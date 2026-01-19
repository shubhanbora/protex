<?php
// Test admin access
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Admin Access Test</h2>";

session_start();

echo "<h3>📋 Session Information:</h3>";
echo "<p><strong>Admin ID:</strong> " . ($_SESSION['admin_id'] ?? 'Not set') . "</p>";
echo "<p><strong>Admin Username:</strong> " . ($_SESSION['admin_username'] ?? 'Not set') . "</p>";
echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "</p>";

if (!isset($_SESSION['admin_id'])) {
    echo "<div style='background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>❌ Not Logged In as Admin</h4>";
    echo "<p>You need to login as admin first.</p>";
    echo "<p><a href='login.php' style='background: #dc2626; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login as Admin</a></p>";
    echo "</div>";
    
    echo "<h4>Default Admin Credentials:</h4>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> password</p>";
} else {
    echo "<div style='background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>✅ Admin Login Successful</h4>";
    echo "<p>You are logged in as admin.</p>";
    echo "</div>";
    
    // Test database connection
    require_once '../config/database.php';
    $conn = getDBConnection();
    
    if ($conn) {
        echo "<p>✅ Database connection successful</p>";
        
        // Test basic queries
        $products_count = 0;
        $users_count = 0;
        $orders_count = 0;
        
        try {
            $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE is_deleted = 0");
            if ($result) {
                $products_count = $result->fetch_assoc()['count'];
            }
            
            $result = $conn->query("SELECT COUNT(*) as count FROM users");
            if ($result) {
                $users_count = $result->fetch_assoc()['count'];
            }
            
            $result = $conn->query("SELECT COUNT(*) as count FROM orders");
            if ($result) {
                $orders_count = $result->fetch_assoc()['count'];
            }
            
            echo "<h4>📊 Database Statistics:</h4>";
            echo "<p>Products: $products_count</p>";
            echo "<p>Users: $users_count</p>";
            echo "<p>Orders: $orders_count</p>";
            
        } catch (Exception $e) {
            echo "<p>❌ Database query error: " . $e->getMessage() . "</p>";
        }
        
        $conn->close();
    } else {
        echo "<p>❌ Database connection failed</p>";
    }
    
    echo "<h4>🔗 Admin Panel Links:</h4>";
    echo "<ul>";
    echo "<li><a href='dashboard-simple.php'>Simple Dashboard (Guaranteed Working)</a></li>";
    echo "<li><a href='dashboard.php'>Main Dashboard</a></li>";
    echo "<li><a href='products.php'>Products Management</a></li>";
    echo "<li><a href='categories.php'>Categories Management</a></li>";
    echo "<li><a href='orders.php'>Orders Management</a></li>";
    echo "</ul>";
}

echo "<h4>🔧 Troubleshooting:</h4>";
echo "<ul>";
echo "<li>If dashboard shows 500 error, use <a href='dashboard-simple.php'>Simple Dashboard</a></li>";
echo "<li>If login fails, check database for admin user</li>";
echo "<li>Default admin password is 'password' (hashed)</li>";
echo "</ul>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h2, h3, h4 { color: #333; }
ul { margin: 10px 0; }
a { color: #007bff; }
</style>