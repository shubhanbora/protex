<?php
/**
 * Database Test Page
 * Check if database and tables are working
 */

require_once 'auth_check.php';
require_once '../config/database.php';

$conn = getDBConnection();

echo "<h1>Database Test Results</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style>";

// Test 1: Database Connection
echo "<h2>1. Database Connection</h2>";
if ($conn) {
    echo "<p class='success'>✅ Connected to database: " . DB_NAME . "</p>";
} else {
    echo "<p class='error'>❌ Failed to connect to database</p>";
    exit();
}

// Test 2: Check Tables
echo "<h2>2. Tables Check</h2>";
$tables = ['categories', 'products', 'users', 'orders', 'admins'];
foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM $table"))['count'];
        echo "<p class='success'>✅ Table '$table' exists - $count records</p>";
    } else {
        echo "<p class='error'>❌ Table '$table' NOT found</p>";
    }
}

// Test 3: Check Categories
echo "<h2>3. Categories</h2>";
$cat_result = mysqli_query($conn, "SELECT * FROM categories");
if (mysqli_num_rows($cat_result) > 0) {
    echo "<table><tr><th>ID</th><th>Name</th><th>Description</th></tr>";
    while ($cat = mysqli_fetch_assoc($cat_result)) {
        echo "<tr><td>{$cat['id']}</td><td>{$cat['name']}</td><td>{$cat['description']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ No categories found! Please add categories first.</p>";
    echo "<p>Run this SQL in phpMyAdmin:</p>";
    echo "<pre>INSERT INTO categories (name, description) VALUES 
('Whey Protein', 'High-quality whey protein supplements'),
('Mass Gainers', 'Weight and muscle mass gainers'),
('Pre-Workout', 'Energy and performance boosters'),
('Vitamins & Minerals', 'Essential vitamins and supplements'),
('Amino Acids', 'BCAAs and essential amino acids'),
('Fat Burners', 'Weight loss and fat burning supplements');</pre>";
}

// Test 4: Check Products
echo "<h2>4. Products</h2>";
$prod_result = mysqli_query($conn, "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_deleted = 0");
if (mysqli_num_rows($prod_result) > 0) {
    echo "<table><tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th></tr>";
    while ($prod = mysqli_fetch_assoc($prod_result)) {
        echo "<tr><td>{$prod['id']}</td><td>{$prod['name']}</td><td>{$prod['category_name']}</td><td>\${$prod['price']}</td><td>{$prod['stock']}</td><td>{$prod['status']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ No products found! Add products from admin panel.</p>";
}

// Test 5: Check Product Table Structure
echo "<h2>5. Products Table Structure</h2>";
$structure = mysqli_query($conn, "DESCRIBE products");
echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($field = mysqli_fetch_assoc($structure)) {
    echo "<tr><td>{$field['Field']}</td><td>{$field['Type']}</td><td>{$field['Null']}</td><td>{$field['Key']}</td><td>{$field['Default']}</td></tr>";
}
echo "</table>";

// Check if weight and image columns exist
$columns = [];
mysqli_data_seek($structure, 0);
while ($field = mysqli_fetch_assoc($structure)) {
    $columns[] = $field['Field'];
}

if (!in_array('weight', $columns)) {
    echo "<p class='error'>❌ 'weight' column missing! Run: ALTER TABLE products ADD COLUMN weight VARCHAR(50) AFTER stock;</p>";
}
if (!in_array('image_2', $columns)) {
    echo "<p class='error'>❌ Additional image columns missing! Run database/add_product_fields.sql</p>";
}

// Test 6: Admin Session
echo "<h2>6. Admin Session</h2>";
echo "<p class='success'>✅ Admin ID: " . $_SESSION['admin_id'] . "</p>";
echo "<p class='success'>✅ Admin Username: " . $_SESSION['admin_username'] . "</p>";

closeDBConnection($conn);

echo "<hr><p><a href='dashboard.php'>← Back to Dashboard</a> | <a href='products.php'>View Products</a> | <a href='product-form.php'>Add Product</a></p>";
?>
