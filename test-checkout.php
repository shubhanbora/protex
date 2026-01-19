<?php
// Test checkout functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🛒 Checkout Test</h2>";

// Test 1: Database connection
require_once 'config/database.php';
$conn = getDBConnection();
if ($conn) {
    echo "<p>✅ Database connection successful</p>";
} else {
    echo "<p>❌ Database connection failed</p>";
    exit;
}

// Test 2: Check required tables
$tables_to_check = ['cart', 'products', 'addresses', 'orders', 'order_items', 'users'];
foreach ($tables_to_check as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p>✅ Table '$table' exists</p>";
    } else {
        echo "<p>❌ Table '$table' missing</p>";
    }
}

// Test 3: Check if user is logged in
session_start();
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    echo "<p>✅ User logged in: ID $user_id</p>";
    
    // Test 4: Check cart items
    $cart_query = "SELECT c.*, p.name, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?";
    $cart_stmt = $conn->prepare($cart_query);
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();
    
    if ($cart_result && $cart_result->num_rows > 0) {
        echo "<p>✅ Cart has " . $cart_result->num_rows . " items</p>";
        echo "<h4>Cart Items:</h4>";
        while ($item = $cart_result->fetch_assoc()) {
            echo "<p>- " . htmlspecialchars($item['name']) . " (Qty: " . $item['quantity'] . ", Price: ₹" . $item['price'] . ")</p>";
        }
    } else {
        echo "<p>❌ Cart is empty</p>";
    }
    
    // Test 5: Check addresses
    $addr_query = "SELECT * FROM addresses WHERE user_id = ?";
    $addr_stmt = $conn->prepare($addr_query);
    $addr_stmt->bind_param("i", $user_id);
    $addr_stmt->execute();
    $addr_result = $addr_stmt->get_result();
    
    if ($addr_result && $addr_result->num_rows > 0) {
        echo "<p>✅ User has " . $addr_result->num_rows . " address(es)</p>";
    } else {
        echo "<p>❌ No addresses found</p>";
    }
    
} else {
    echo "<p>❌ User not logged in</p>";
}

// Test 6: Check config files
$config_files = [
    'config/session.php' => 'Session Config',
    'config/security.php' => 'Security Config',
    'config/payment.php' => 'Payment Config',
    'config/email.php' => 'Email Config'
];

foreach ($config_files as $file => $description) {
    if (file_exists($file)) {
        echo "<p>✅ $description exists</p>";
    } else {
        echo "<p>❌ $description missing</p>";
    }
}

echo "<h3>🔗 Test Links:</h3>";
echo "<ul>";
echo "<li><a href='checkout.php' target='_blank'>Test Checkout Page</a></li>";
echo "<li><a href='cart.php' target='_blank'>View Cart</a></li>";
echo "<li><a href='account/addresses.php' target='_blank'>Manage Addresses</a></li>";
echo "<li><a href='login.php' target='_blank'>Login</a></li>";
echo "</ul>";

echo "<h3>🔧 Troubleshooting:</h3>";
echo "<ul>";
echo "<li>Make sure you're logged in as a user (not admin)</li>";
echo "<li>Add some products to your cart first</li>";
echo "<li>Add at least one delivery address</li>";
echo "<li>Check that all required tables exist in database</li>";
echo "</ul>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h2, h3, h4 { color: #333; }
p { margin: 5px 0; }
ul { margin: 10px 0; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>