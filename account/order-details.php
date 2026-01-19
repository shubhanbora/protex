<?php
$pageTitle = 'Order Details - E-Commerce Store';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/header.php';

requireLogin();

$conn = getDBConnection();
$user_id = getCurrentUserId();
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order details
$query = "SELECT o.*, a.* 
          FROM orders o 
          JOIN addresses a ON o.address_id = a.id 
          WHERE o.id = $order_id AND o.user_id = $user_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    header('Location: /account/orders.php');
    exit();
}

$order = mysqli_fetch_assoc($result);

// Get order items
$items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);

$success = isset($_GET['success']) ? 'Order placed successfully!' : '';
?>

<div class="container">
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <h1 style="font-size: 2rem; margin-bottom: 2rem;">Order Details</h1>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <div>
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 2rem;">
                    <div>
                        <h2>Order #<?php echo $order['id']; ?></h2>
                        <p style="color: #6b7280;">
                            Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?>
                        </p>
                    </div>
                    <span class="badge badge-<?php echo $order['order_status']; ?>">
                        <?php echo ucfirst($order['order_status']); ?>
                    </span>
                </div>
                
                <h3 style="margin-bottom: 1rem;">Order Items</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>₹<?php echo number_format($item['subtotal'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                            <td><strong style="color: var(--primary-color); font-size: 1.2rem;">
                                ₹<?php echo number_format($order['total_amount'], 2); ?>
                            </strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <div>
            <div class="card">
                <h3 style="margin-bottom: 1rem;">Delivery Address</h3>
                <p>
                    <strong><?php echo htmlspecialchars($order['full_name']); ?></strong><br>
                    <?php echo htmlspecialchars($order['flat_house']); ?><br>
                    <?php echo htmlspecialchars($order['locality']); ?><br>
                    <?php if ($order['landmark']): ?>
                        Landmark: <?php echo htmlspecialchars($order['landmark']); ?><br>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($order['city'] . ', ' . $order['state']); ?><br>
                    <?php echo htmlspecialchars($order['pincode']); ?><br>
                    <br>
                    <strong>Mobile:</strong> <?php echo htmlspecialchars($order['mobile']); ?>
                    <?php if ($order['email']): ?>
                        <br><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?>
                    <?php endif; ?>
                </p>
            </div>
            
            <div class="card" style="margin-top: 1.5rem;">
                <h3 style="margin-bottom: 1rem;">Payment Information</h3>
                <p>
                    <strong>Method:</strong> <?php echo strtoupper($order['payment_method']); ?><br>
                    <strong>Status:</strong> <?php echo ucfirst($order['payment_status']); ?>
                </p>
            </div>
            
            <?php if ($order['order_status'] === 'delivered'): ?>
                <div class="card" style="margin-top: 1.5rem;">
                    <h3 style="margin-bottom: 1rem;">Leave a Review</h3>
                    <p style="color: #6b7280; margin-bottom: 1rem;">
                        Share your experience with the products
                    </p>
                    <a href="/product.php?id=<?php echo $items_result[0]['product_id']; ?>" class="btn btn-primary" style="width: 100%; text-align: center;">
                        Write Review
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
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
