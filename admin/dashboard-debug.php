<?php
// Debug admin dashboard
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Admin Dashboard Debug</h2>";

// Security Check - Must be logged in as admin
require_once 'auth_check.php';
echo "<p>✅ Admin authentication passed</p>";

require_once '../config/database.php';
echo "<p>✅ Database config loaded</p>";

$conn = getDBConnection();
if (!$conn) {
    echo "<p>❌ Database connection failed</p>";
    exit();
}
echo "<p>✅ Database connected</p>";

// Check if required tables exist
$required_tables = ['products', 'orders', 'users'];
$missing_tables = [];

foreach ($required_tables as $table) {
    $check_query = "SHOW TABLES LIKE '$table'";
    $result = $conn->query($check_query);
    if (!$result || $result->num_rows === 0) {
        $missing_tables[] = $table;
    }
}

if (!empty($missing_tables)) {
    echo "<p>❌ Missing tables: " . implode(', ', $missing_tables) . "</p>";
    echo "<p><a href='../fix-database.php'>Fix Database</a></p>";
    exit();
}
echo "<p>✅ All required tables exist</p>";

echo "<h3>📊 Statistics Debug:</h3>";

// Get total products
echo "<h4>Products:</h4>";
$products_query = "SELECT COUNT(*) as count FROM products WHERE is_deleted = 0";
$products_result = $conn->query($products_query);
if ($products_result) {
    $total_products = $products_result->fetch_assoc()['count'];
    echo "<p>✅ Total Products: <strong>$total_products</strong></p>";
} else {
    echo "<p>❌ Products query failed: " . $conn->error . "</p>";
}

// Get total orders
echo "<h4>Orders:</h4>";
$orders_query = "SELECT COUNT(*) as count FROM orders";
$orders_result = $conn->query($orders_query);
if ($orders_result) {
    $total_orders = $orders_result->fetch_assoc()['count'];
    echo "<p>✅ Total Orders: <strong>$total_orders</strong></p>";
} else {
    echo "<p>❌ Orders query failed: " . $conn->error . "</p>";
}

// Get total users
echo "<h4>Users:</h4>";
$users_query = "SELECT COUNT(*) as count FROM users";
$users_result = $conn->query($users_query);
if ($users_result) {
    $total_users = $users_result->fetch_assoc()['count'];
    echo "<p>✅ Total Users: <strong>$total_users</strong></p>";
} else {
    echo "<p>❌ Users query failed: " . $conn->error . "</p>";
}

// Get total revenue
echo "<h4>Revenue:</h4>";
$revenue_query = "SELECT SUM(total_amount) as total FROM orders WHERE order_status != 'cancelled'";
$revenue_result = $conn->query($revenue_query);
if ($revenue_result) {
    $revenue_data = $revenue_result->fetch_assoc();
    $total_revenue = $revenue_data['total'] ?? 0;
    echo "<p>✅ Total Revenue: <strong>₹" . number_format($total_revenue, 2) . "</strong></p>";
} else {
    echo "<p>❌ Revenue query failed: " . $conn->error . "</p>";
}

// Check recent orders
echo "<h3>📋 Recent Orders Debug:</h3>";
$recent_orders_query = "SELECT o.*, u.full_name as user_name 
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC 
                        LIMIT 5";
$recent_orders = $conn->query($recent_orders_query);

if ($recent_orders) {
    echo "<p>✅ Recent orders query successful</p>";
    echo "<p>Number of recent orders: " . $recent_orders->num_rows . "</p>";
    
    if ($recent_orders->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Order ID</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr>";
        
        while ($order = $recent_orders->fetch_assoc()) {
            echo "<tr>";
            echo "<td>#" . $order['id'] . "</td>";
            echo "<td>" . htmlspecialchars($order['user_name'] ?? 'Unknown') . "</td>";
            echo "<td>₹" . number_format($order['total_amount'] ?? 0, 2) . "</td>";
            echo "<td>" . ($order['order_status'] ?? 'pending') . "</td>";
            echo "<td>" . date('M j, Y', strtotime($order['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No recent orders found</p>";
    }
} else {
    echo "<p>❌ Recent orders query failed: " . $conn->error . "</p>";
}

// Check admin session
echo "<h3>👤 Admin Session Info:</h3>";
echo "<p>Admin ID: " . ($_SESSION['admin_id'] ?? 'Not set') . "</p>";
echo "<p>Admin Username: " . ($_SESSION['admin_username'] ?? 'Not set') . "</p>";

// Sample data suggestions
if ($total_products == 0) {
    echo "<h3>💡 Suggestions:</h3>";
    echo "<p>⚠️ No products found. Add some products first:</p>";
    echo "<ul>";
    echo "<li><a href='products.php'>Go to Products Management</a></li>";
    echo "<li><a href='product-form.php'>Add New Product</a></li>";
    echo "</ul>";
}

if ($total_users == 0) {
    echo "<p>⚠️ No users found. Register some users first:</p>";
    echo "<ul>";
    echo "<li><a href='../register.php'>Register New User</a></li>";
    echo "</ul>";
}

echo "<br><p><a href='dashboard.php'>Go to Actual Dashboard</a></p>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>