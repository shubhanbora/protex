<?php
$pageTitle = 'My Orders - FitSupps';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/header.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

requireLogin();

$conn = getDBConnection();
if (!$conn) {
    die("Database connection failed");
}

$user_id = getCurrentUserId();

// Get all orders
$query = "SELECT o.*, a.full_name, a.city, a.state 
          FROM orders o 
          LEFT JOIN addresses a ON o.address_id = a.id 
          WHERE o.user_id = $user_id 
          ORDER BY o.created_at DESC";
$result = $conn->query($query);

if (!$result) {
    die("Failed to load orders: " . $conn->error);
}
?>

<div class="container">
    <h1 style="font-size: 2rem; margin-bottom: 2rem;">My Orders</h1>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($order = $result->fetch_assoc()): ?>
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                    <div>
                        <h3>Order #<?php echo $order['id']; ?></h3>
                        <p style="color: #6b7280;">
                            Placed on <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <p style="font-size: 1.3rem; font-weight: bold; color: var(--primary-color);">
                            ₹<?php echo number_format($order['total_amount'], 2); ?>
                        </p>
                        <span class="badge badge-<?php echo $order['order_status']; ?>">
                            <?php echo ucfirst($order['order_status']); ?>
                        </span>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; padding: 1rem 0; border-top: 1px solid var(--border-color);">
                    <div>
                        <strong>Delivery Address:</strong>
                        <p style="color: #6b7280; margin-top: 0.5rem;">
                            <?php echo htmlspecialchars($order['full_name']); ?><br>
                            <?php echo htmlspecialchars($order['city'] . ', ' . $order['state']); ?>
                        </p>
                    </div>
                    <div>
                        <strong>Payment Method:</strong>
                        <p style="color: #6b7280; margin-top: 0.5rem;">
                            <?php echo strtoupper($order['payment_method']); ?>
                        </p>
                    </div>
                    <div>
                        <strong>Payment Status:</strong>
                        <p style="color: #6b7280; margin-top: 0.5rem;">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </p>
                    </div>
                </div>
                
                <div style="margin-top: 1rem;">
                    <a href="/account/order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">
                        View Details
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card" style="text-align: center; padding: 3rem;">
            <i class="fas fa-box" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
            <h2>No orders yet</h2>
            <p style="color: #6b7280; margin: 1rem 0;">Start shopping to see your orders here!</p>
            <a href="/products.php" class="btn btn-primary">Browse Products</a>
        </div>
    <?php endif; ?>
</div>

<style>
.badge {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}
.badge-pending { background: #fef3c7; color: #92400e; }
.badge-approved { background: #dbeafe; color: #1e40af; }
.badge-shipped { background: #e0e7ff; color: #4338ca; }
.badge-delivered { background: #d1fae5; color: #065f46; }
.badge-cancelled { background: #fee2e2; color: #991b1b; }
</style>

<?php
closeDBConnection($conn);
require_once '../includes/footer.php';
?>
