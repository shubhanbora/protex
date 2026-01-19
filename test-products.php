<?php
// Simple test to check products in database
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

echo "<h2>Database Products Test</h2>";

$conn = getDBConnection();
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "<p>✅ Database connected successfully</p>";

// Check if products table exists
$tables_query = "SHOW TABLES LIKE 'products'";
$tables_result = $conn->query($tables_query);
if ($tables_result->num_rows === 0) {
    echo "<p>❌ Products table does not exist!</p>";
    exit();
}
echo "<p>✅ Products table exists</p>";

// Count total products
$count_query = "SELECT COUNT(*) as total FROM products";
$count_result = $conn->query($count_query);
$total_products = $count_result->fetch_assoc()['total'];
echo "<p>📊 Total products in database: <strong>$total_products</strong></p>";

if ($total_products == 0) {
    echo "<p>❌ No products found in database!</p>";
    echo "<p>You need to add some products first through admin panel.</p>";
    exit();
}

// Count active products
$active_query = "SELECT COUNT(*) as active FROM products WHERE status = 'active' AND is_deleted = 0";
$active_result = $conn->query($active_query);
$active_products = $active_result->fetch_assoc()['active'];
echo "<p>✅ Active products: <strong>$active_products</strong></p>";

// Show first 5 products
echo "<h3>Sample Products:</h3>";
$sample_query = "SELECT id, name, price, status, is_deleted FROM products LIMIT 5";
$sample_result = $conn->query($sample_query);

if ($sample_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Status</th><th>Deleted</th><th>Action</th></tr>";
    
    while ($row = $sample_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>₹" . $row['price'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . ($row['is_deleted'] ? 'Yes' : 'No') . "</td>";
        echo "<td><a href='product-debug.php?id=" . $row['id'] . "'>Debug</a> | <a href='product.php?id=" . $row['id'] . "'>View</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No products to display</p>";
}

// Check categories
echo "<h3>Categories:</h3>";
$cat_query = "SELECT id, name FROM categories LIMIT 5";
$cat_result = $conn->query($cat_query);

if ($cat_result->num_rows > 0) {
    echo "<ul>";
    while ($cat = $cat_result->fetch_assoc()) {
        echo "<li>ID: " . $cat['id'] . " - " . htmlspecialchars($cat['name']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No categories found</p>";
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>