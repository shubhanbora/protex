<?php
$pageTitle = 'Order Placed Successfully - FitSupps';
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/header.php';

requireLogin();

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    header('Location: index.php');
    exit();
}

$conn = getDBConnection();
$user_id = getCurrentUserId();

// Get order details
$order_query = "SELECT o.*, a.full_name, a.mobile, a.flat_house, a.locality, a.city, a.state, a.pincode 
                FROM orders o 
                JOIN addresses a ON o.address_id = a.id 
                WHERE o.id = ? AND o.user_id = ?";
$order_result = executeQuery($conn, $order_query, "ii", [$order_id, $user_id]);

if (!$order_result || $order_result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$items_result = executeQuery($conn, $items_query, "i", [$order_id]);
?>

<style>
.success-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 2rem;
}

.success-header {
    text-align: center;
    margin-bottom: 3rem;
    padding: 2rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-radius: 15px;
}

.success-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

.success-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
}

.success-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
}

.order-details {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #374151;
}

.detail-value {
    color: #1f2937;
}

.order-total {
    font-size: 1.5rem;
    font-weight: 800;
    color: #059669;
}

.delivery-info {
    background: #f8fafc;
    padding: 1.5rem;
    border-radius: 10px;
    margin: 1.5rem 0;
}

.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 2rem;
}

.btn {
    padding: 1rem 2rem;
    border-radius: 10px;
    text-decoration: none;
    text-align: center;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.order-items {
    margin-top: 2rem;
}

.item-card {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 10px;
    margin-bottom: 1rem;
}

.item-details {
    flex: 1;
    margin-left: 1rem;
}

.item-name {
    font-weight: 600;
    color: #1f2937;
}

.item-price {
    color: #059669;
    font-weight: 600;
}

@media (max-width: 768px) {
    .success-container {
        padding: 1rem;
    }
    
    .success-title {
        font-size: 2rem;
    }
    
    .detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>

<div class="success-container">
    <div class="success-header">
        <div class="success-icon">🎉</div>
        <h1 class="success-title">Order Placed Successfully!</h1>
        <p class="success-subtitle">Thank you for choosing FitSupps. Your order is being processed.</p>
    </div>
    
    <div class="order-details">
        <h2 style="margin-bottom: 1.5rem; color: #1f2937;">Order Details</h2>
        
        <div class="detail-row">
            <span class="detail-label">Order ID:</span>
            <span class="detail-value">#<?php echo $order['id']; ?></span>
        </div>
        
        <div class="detail-row">
            <span class="detail-label">Order Date:</span>
            <span class="detail-value"><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></span>
        </div>
        
        <div class="detail-row">
            <span class="detail-label">Payment Method:</span>
            <span class="detail-value"><?php echo strtoupper($order['payment_method']); ?></span>
        </div>
        
        <div class="detail-row">
            <span class="detail-label">Payment Status:</span>
            <span class="detail-value" style="color: <?php echo $order['payment_status'] === 'paid' ? '#059669' : '#f59e0b'; ?>">
                <?php echo ucfirst($order['payment_status']); ?>
            </span>
        </div>
        
        <?php if (!empty($order['coupon_code'])): ?>
        <div class="detail-row">
            <span class="detail-label">Coupon Applied:</span>
            <span class="detail-value" style="color: #059669;"><?php echo $order['coupon_code']; ?></span>
        </div>
        <?php endif; ?>
        
        <div class="detail-row">
            <span class="detail-label">Subtotal:</span>
            <span class="detail-value">₹<?php echo number_format($order['subtotal'], 2); ?></span>
        </div>
        
        <?php if ($order['discount'] > 0): ?>
        <div class="detail-row">
            <span class="detail-label">Discount:</span>
            <span class="detail-value" style="color: #059669;">-₹<?php echo number_format($order['discount'], 2); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="detail-row">
            <span class="detail-label">Shipping:</span>
            <span class="detail-value">
                <?php echo $order['shipping_charges'] > 0 ? '₹' . number_format($order['shipping_charges'], 2) : 'FREE'; ?>
            </span>
        </div>
        
        <div class="detail-row">
            <span class="detail-label">Total Amount:</span>
            <span class="detail-value order-total">₹<?php echo number_format($order['total_amount'], 2); ?></span>
        </div>
        
        <div class="delivery-info">
            <h3 style="margin-bottom: 1rem; color: #374151;">Delivery Address</h3>
            <p><strong><?php echo htmlspecialchars($order['full_name']); ?></strong></p>
            <p><?php echo htmlspecialchars($order['mobile']); ?></p>
            <p><?php echo htmlspecialchars($order['flat_house'] . ', ' . $order['locality']); ?></p>
            <p><?php echo htmlspecialchars($order['city'] . ', ' . $order['state'] . ' - ' . $order['pincode']); ?></p>
        </div>
        
        <?php if ($items_result && $items_result->num_rows > 0): ?>
        <div class="order-items">
            <h3 style="margin-bottom: 1rem; color: #374151;">Order Items</h3>
            <?php while ($item = $items_result->fetch_assoc()): ?>
            <div class="item-card">
                <div class="item-details">
                    <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                    <div class="item-price">₹<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?> = ₹<?php echo number_format($item['subtotal'], 2); ?></div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="action-buttons">
        <a href="account/orders.php" class="btn btn-primary">
            <i class="fas fa-list"></i> View All Orders
        </a>
        <a href="products.php" class="btn btn-secondary">
            <i class="fas fa-shopping-bag"></i> Continue Shopping
        </a>
        <a href="index.php" class="btn btn-success">
            <i class="fas fa-home"></i> Back to Home
        </a>
    </div>
</div>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>