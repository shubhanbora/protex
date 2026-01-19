<?php
// Test cart API functionality
require_once 'config/database.php';
require_once 'config/session.php';

if (!isLoggedIn()) {
    echo "<h2>Please login first</h2>";
    echo "<a href='login.php'>Login</a>";
    exit();
}

$user_id = getCurrentUserId();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cart API Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        button { padding: 10px 15px; margin: 5px; cursor: pointer; }
        #result { margin: 10px 0; padding: 10px; background: #f5f5f5; border-radius: 5px; }
    </style>
</head>
<body>
    <h2>🛒 Cart API Test</h2>
    <p>User ID: <?php echo $user_id; ?></p>
    
    <div class="test-section">
        <h3>Test Add to Cart</h3>
        <button onclick="testAddToCart(1)">Add Product ID 1</button>
        <button onclick="testAddToCart(2)">Add Product ID 2</button>
        <button onclick="testAddToCart(999)">Add Invalid Product</button>
    </div>
    
    <div class="test-section">
        <h3>Current Cart Items</h3>
        <button onclick="loadCartItems()">Load Cart</button>
        <div id="cartItems"></div>
    </div>
    
    <div id="result"></div>

    <script>
    function showResult(message, isSuccess = true) {
        const result = document.getElementById('result');
        result.innerHTML = message;
        result.className = isSuccess ? 'success' : 'error';
    }

    function testAddToCart(productId) {
        showResult('Testing add to cart for product ' + productId + '...');
        
        fetch('api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('API Response:', data);
            if (data.success) {
                showResult('✅ SUCCESS: ' + data.message, true);
            } else {
                showResult('❌ FAILED: ' + data.message, false);
            }
            loadCartItems(); // Refresh cart display
        })
        .catch(error => {
            console.error('Error:', error);
            showResult('❌ NETWORK ERROR: ' + error.message, false);
        });
    }

    function loadCartItems() {
        fetch('get-cart-items.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('cartItems').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('cartItems').innerHTML = 'Error loading cart items';
        });
    }

    // Load cart items on page load
    loadCartItems();
    </script>
</body>
</html>