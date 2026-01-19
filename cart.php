<?php
$pageTitle = 'Shopping Cart - FitSupps';
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/header.php';

requireLogin();

$conn = getDBConnection();
if (!$conn) {
    die("Database connection failed");
}

$user_id = getCurrentUserId();

// Get cart items - simple query
$query = "SELECT c.*, p.name, p.price, p.image, p.stock 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = $user_id AND p.status = 'active' AND p.is_deleted = 0";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$total = 0;
?>

<style>
.cart-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.cart-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 2rem;
    text-align: center;
}

.cart-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.cart-item {
    display: flex;
    gap: 1rem;
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    margin-bottom: 1rem;
    border: 1px solid #f3f4f6;
}

.cart-item-image {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 8px;
    flex-shrink: 0;
}

.cart-item-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.cart-item-name {
    font-size: 1.2rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.cart-item-price {
    color: #059669;
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.cart-item-controls {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #f9fafb;
    border-radius: 8px;
    padding: 0.25rem;
}

.quantity-btn {
    background: white;
    border: 1px solid #e5e7eb;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    min-width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quantity-btn:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.quantity-display {
    padding: 0 1rem;
    font-weight: 600;
    min-width: 40px;
    text-align: center;
}

.remove-btn {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fca5a5;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.remove-btn:hover {
    background: #fecaca;
    border-color: #f87171;
}

.cart-item-total {
    text-align: right;
    align-self: flex-start;
}

.cart-item-total-price {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1f2937;
}

.order-summary {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #f3f4f6;
    height: fit-content;
    position: sticky;
    top: 100px;
}

.summary-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1.5rem;
    text-align: center;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    padding: 0.5rem 0;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
    padding: 1rem 0;
    border-top: 2px solid #e5e7eb;
    font-weight: 700;
}

.summary-total-amount {
    font-size: 1.5rem;
    color: #059669;
}

.checkout-btn {
    width: 100%;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    display: block;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.checkout-btn:hover {
    background: linear-gradient(135deg, #047857 0%, #065f46 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(5, 150, 105, 0.3);
    color: white;
    text-decoration: none;
}

.empty-cart {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.empty-cart-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.empty-cart-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.empty-cart-text {
    color: #6b7280;
    margin-bottom: 2rem;
}

.browse-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.browse-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    color: white;
    text-decoration: none;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .cart-layout {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .cart-item {
        flex-direction: column;
        gap: 1rem;
    }
    
    .cart-item-image {
        width: 100%;
        height: 200px;
        align-self: center;
        max-width: 300px;
    }
    
    .cart-item-details {
        text-align: center;
    }
    
    .cart-item-controls {
        justify-content: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .cart-item-total {
        text-align: center;
        margin-top: 1rem;
    }
    
    .order-summary {
        position: static;
        margin-top: 1rem;
    }
    
    .cart-title {
        font-size: 2rem;
    }
}

@media (max-width: 480px) {
    .cart-container {
        padding: 15px;
    }
    
    .cart-item {
        padding: 1rem;
    }
    
    .cart-item-image {
        height: 150px;
    }
    
    .cart-item-name {
        font-size: 1.1rem;
    }
    
    .cart-item-price {
        font-size: 1.1rem;
    }
    
    .quantity-controls {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .remove-btn {
        width: 100%;
        justify-content: center;
        margin-top: 0.5rem;
    }
    
    .order-summary {
        padding: 1.5rem;
    }
    
    .summary-title {
        font-size: 1.3rem;
    }
    
    .checkout-btn {
        padding: 1.25rem;
        font-size: 1rem;
    }
}
</style>

<div class="cart-container">
    <h1 class="cart-title">Shopping Cart</h1>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="cart-layout">
            <div class="cart-items">
                <?php while ($item = $result->fetch_assoc()): ?>
                    <?php 
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                    ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['image'] ?: '/assets/images/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             class="cart-item-image">
                        
                        <div class="cart-item-details">
                            <h3 class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="cart-item-price">₹<?php echo number_format($item['price'], 2); ?></p>
                            
                            <div class="cart-item-controls">
                                <div class="quantity-controls">
                                    <button onclick="updateCartQuantity(<?php echo $item['id']; ?>, -1)" class="quantity-btn">-</button>
                                    <span class="quantity-display"><?php echo $item['quantity']; ?></span>
                                    <button onclick="updateCartQuantity(<?php echo $item['id']; ?>, 1)" class="quantity-btn">+</button>
                                </div>
                                <button onclick="removeFromCart(<?php echo $item['id']; ?>)" class="remove-btn">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                        
                        <div class="cart-item-total">
                            <p class="cart-item-total-price">₹<?php echo number_format($subtotal, 2); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="order-summary">
                <h2 class="summary-title">Order Summary</h2>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>₹<?php echo number_format($total, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>Free</span>
                </div>
                <div class="summary-total">
                    <strong>Total:</strong>
                    <strong class="summary-total-amount">₹<?php echo number_format($total, 2); ?></strong>
                </div>
                <a href="checkout.php" class="checkout-btn">
                    <i class="fas fa-credit-card" style="margin-right: 8px;"></i>
                    Proceed to Checkout
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart empty-cart-icon"></i>
            <h2 class="empty-cart-title">Your cart is empty</h2>
            <p class="empty-cart-text">Add some products to get started!</p>
            <a href="products.php" class="browse-btn">
                <i class="fas fa-th-large" style="margin-right: 8px;"></i>
                Browse Products
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
function updateCartQuantity(cartId, change) {
    console.log('Updating cart:', cartId, 'Change:', change);
    
    const baseUrl = window.BASE_URL || window.location.origin;
    const apiPath = baseUrl + '/api/cart.php';
    
    fetch(apiPath, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'update', cart_id: cartId, change: change})
    })
    .then(response => response.json())
    .then(data => {
        console.log('Update response:', data);
        if (data.success) {
            location.reload();
        } else {
            showNotification(data.message || 'Failed to update cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error: ' + error.message, 'error');
    });
}

function removeFromCart(cartId) {
    if (confirm('Remove this item from cart?')) {
        console.log('Removing from cart:', cartId);
        
        const baseUrl = window.BASE_URL || window.location.origin;
        const apiPath = baseUrl + '/api/cart.php';
        
        fetch(apiPath, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'remove', cart_id: cartId})
        })
        .then(response => response.json())
        .then(data => {
            console.log('Remove response:', data);
            if (data.success) {
                showNotification('Item removed from cart', 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                showNotification(data.message || 'Failed to remove item', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error: ' + error.message, 'error');
        });
    }
}
</script>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
