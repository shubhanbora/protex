<?php
// Automatic database fix script
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

echo "<h2>🔧 Database Fix Script</h2>";

$conn = getDBConnection();
if (!$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error());
}

echo "<p>✅ Database connected successfully</p>";

// Function to check if table exists
function tableExists($conn, $tableName) {
    $query = "SHOW TABLES LIKE '$tableName'";
    $result = $conn->query($query);
    return $result && $result->num_rows > 0;
}

// Function to check if column exists
function columnExists($conn, $tableName, $columnName) {
    $query = "SHOW COLUMNS FROM $tableName LIKE '$columnName'";
    $result = $conn->query($query);
    return $result && $result->num_rows > 0;
}

echo "<h3>📋 Checking Required Tables...</h3>";

// Check and create reviews table
if (!tableExists($conn, 'reviews')) {
    echo "<p>⚠️ Reviews table missing. Creating...</p>";
    $reviews_sql = "CREATE TABLE reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($reviews_sql)) {
        echo "<p>✅ Reviews table created successfully</p>";
    } else {
        echo "<p>❌ Error creating reviews table: " . $conn->error . "</p>";
    }
} else {
    echo "<p>✅ Reviews table exists</p>";
}

// Check and create addresses table
if (!tableExists($conn, 'addresses')) {
    echo "<p>⚠️ Addresses table missing. Creating...</p>";
    $addresses_sql = "CREATE TABLE addresses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        mobile VARCHAR(15) NOT NULL,
        flat_house VARCHAR(255) NOT NULL,
        locality VARCHAR(255) NOT NULL,
        landmark VARCHAR(255),
        pincode VARCHAR(10) NOT NULL,
        city VARCHAR(100) NOT NULL,
        state VARCHAR(100) NOT NULL,
        is_default BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($addresses_sql)) {
        echo "<p>✅ Addresses table created successfully</p>";
    } else {
        echo "<p>❌ Error creating addresses table: " . $conn->error . "</p>";
    }
} else {
    echo "<p>✅ Addresses table exists</p>";
}

// Check and update orders table
echo "<h3>🔄 Updating Orders Table...</h3>";
$orders_columns = [
    'address_id' => 'INT',
    'subtotal' => 'DECIMAL(10,2) DEFAULT 0',
    'discount' => 'DECIMAL(10,2) DEFAULT 0',
    'shipping_charges' => 'DECIMAL(10,2) DEFAULT 0',
    'coupon_code' => 'VARCHAR(50)',
    'order_status' => 'VARCHAR(50) DEFAULT "pending"'
];

foreach ($orders_columns as $column => $definition) {
    if (!columnExists($conn, 'orders', $column)) {
        echo "<p>⚠️ Adding column '$column' to orders table...</p>";
        $sql = "ALTER TABLE orders ADD COLUMN $column $definition";
        if ($conn->query($sql)) {
            echo "<p>✅ Column '$column' added successfully</p>";
        } else {
            echo "<p>❌ Error adding column '$column': " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✅ Column '$column' exists in orders table</p>";
    }
}

// Check and update order_items table
echo "<h3>🔄 Updating Order Items Table...</h3>";
$order_items_columns = [
    'product_name' => 'VARCHAR(255)',
    'subtotal' => 'DECIMAL(10,2)'
];

foreach ($order_items_columns as $column => $definition) {
    if (!columnExists($conn, 'order_items', $column)) {
        echo "<p>⚠️ Adding column '$column' to order_items table...</p>";
        $sql = "ALTER TABLE order_items ADD COLUMN $column $definition";
        if ($conn->query($sql)) {
            echo "<p>✅ Column '$column' added successfully</p>";
        } else {
            echo "<p>❌ Error adding column '$column': " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✅ Column '$column' exists in order_items table</p>";
    }
}

// Add sample reviews if none exist
echo "<h3>📝 Adding Sample Reviews...</h3>";
$reviews_count = $conn->query("SELECT COUNT(*) as count FROM reviews")->fetch_assoc()['count'];

if ($reviews_count == 0) {
    echo "<p>⚠️ No reviews found. Adding sample reviews...</p>";
    
    // Get first user and first few products
    $user_result = $conn->query("SELECT id FROM users LIMIT 1");
    $product_result = $conn->query("SELECT id, name FROM products WHERE status = 'active' LIMIT 3");
    
    if ($user_result && $user_result->num_rows > 0 && $product_result && $product_result->num_rows > 0) {
        $user_id = $user_result->fetch_assoc()['id'];
        
        $sample_reviews = [
            ['rating' => 5, 'comment' => 'Excellent product! Highly recommended for muscle building.'],
            ['rating' => 4, 'comment' => 'Good quality protein with great taste and mixability.'],
            ['rating' => 5, 'comment' => 'Best supplement for strength gains! Will buy again.']
        ];
        
        $i = 0;
        while ($product = $product_result->fetch_assoc() && $i < count($sample_reviews)) {
            $review = $sample_reviews[$i];
            $sql = "INSERT INTO reviews (user_id, product_id, rating, comment) VALUES ($user_id, {$product['id']}, {$review['rating']}, '{$review['comment']}')";
            
            if ($conn->query($sql)) {
                echo "<p>✅ Added review for product: " . htmlspecialchars($product['name']) . "</p>";
            } else {
                echo "<p>❌ Error adding review: " . $conn->error . "</p>";
            }
            $i++;
        }
    } else {
        echo "<p>⚠️ No users or products found to add sample reviews</p>";
    }
} else {
    echo "<p>✅ Reviews already exist ($reviews_count reviews found)</p>";
}

echo "<h3>🎉 Database Fix Complete!</h3>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li><a href='test-products.php'>Test Products Database</a></li>";
echo "<li><a href='product.php?id=1'>Test Product Page</a></li>";
echo "<li><a href='products.php'>View All Products</a></li>";
echo "</ul>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h2, h3 { color: #333; }
p { margin: 5px 0; }
ul { margin: 10px 0; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>