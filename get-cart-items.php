<?php
// Simple script to get cart items for testing
require_once 'config/database.php';
require_once 'config/session.php';

if (!isLoggedIn()) {
    echo "Not logged in";
    exit();
}

$conn = getDBConnection();
$user_id = getCurrentUserId();

$query = "SELECT c.*, p.name, p.price 
          FROM cart c 
          LEFT JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = $user_id 
          ORDER BY c.created_at DESC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th></tr>";
    
    $total = 0;
    while ($item = $result->fetch_assoc()) {
        $subtotal = $item['price'] * $item['quantity'];
        $total += $subtotal;
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['name'] ?? 'Product ID: ' . $item['product_id']) . "</td>";
        echo "<td>₹" . number_format($item['price'] ?? 0, 2) . "</td>";
        echo "<td>" . $item['quantity'] . "</td>";
        echo "<td>₹" . number_format($subtotal, 2) . "</td>";
        echo "</tr>";
    }
    
    echo "<tr style='font-weight: bold;'>";
    echo "<td colspan='3'>TOTAL</td>";
    echo "<td>₹" . number_format($total, 2) . "</td>";
    echo "</tr>";
    echo "</table>";
} else {
    echo "<p>Cart is empty</p>";
}

$conn->close();
?>