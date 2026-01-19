<?php
// Debug version to check what's happening
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting product debug...<br>";

require_once 'config/database.php';
echo "Database config loaded<br>";

require_once 'config/session.php';
echo "Session config loaded<br>";

// Check if user is logged in
if (!isLoggedIn()) {
    echo "User not logged in, redirecting...<br>";
    header('Location: login.php');
    exit();
}
echo "User is logged in<br>";

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
echo "Product ID: " . $product_id . "<br>";

if ($product_id <= 0) {
    echo "Invalid product ID<br>";
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    echo "Database connection failed<br>";
    exit();
}
echo "Database connected successfully<br>";

// Simple query without prepared statement for debugging
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.id = $product_id";
echo "Query: " . $query . "<br>";

$result = $conn->query($query);
if (!$result) {
    echo "Query failed: " . $conn->error . "<br>";
    exit();
}

echo "Query executed successfully<br>";
echo "Number of rows: " . $result->num_rows . "<br>";

if ($result->num_rows === 0) {
    echo "No product found with ID: " . $product_id . "<br>";
    
    // Check if any products exist
    $check_query = "SELECT COUNT(*) as count FROM products";
    $check_result = $conn->query($check_query);
    $count = $check_result->fetch_assoc()['count'];
    echo "Total products in database: " . $count . "<br>";
    
    // Show all products
    $all_query = "SELECT id, name, status, is_deleted FROM products LIMIT 5";
    $all_result = $conn->query($all_query);
    echo "<h3>Sample products:</h3>";
    while ($row = $all_result->fetch_assoc()) {
        echo "ID: " . $row['id'] . ", Name: " . $row['name'] . ", Status: " . $row['status'] . ", Deleted: " . $row['is_deleted'] . "<br>";
    }
    exit();
}

$product = $result->fetch_assoc();
echo "<h2>Product found:</h2>";
echo "Name: " . $product['name'] . "<br>";
echo "Price: " . $product['price'] . "<br>";
echo "Status: " . $product['status'] . "<br>";
echo "Category: " . ($product['category_name'] ?? 'No category') . "<br>";

echo "<br><a href='product.php?id=" . $product_id . "'>Go to actual product page</a>";
?>