<?php
$pageTitle = 'Order Details';

// Security Check - Must be logged in as admin
require_once 'auth_check.php';

require_once '../config/database.php';
require_once 'includes/header.php';

$conn = getDBConnection();
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = mysqli_real_escape_string($conn, $_POST['order_status']);
    $query = "UPDATE orders SET order_status = '$new_status' WHERE id = $order_id";
    
    if (mysqli_query($conn, $query)) {
        $success = 'Order status updated successfully';
    }
}

// Get order details
$query = "SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone, a.* 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          JOIN addresses a ON o.address_id = a.id 
          WHERE o.id = $order_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    header('Location: orders.php');
    exit();
}

$order = mysqli_fetch_assoc($result);

// Get order items
$items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);
?>

<a href="orders.php" class="btn" style="background: #6b7280; color: #fff; margin-bottom: 1.5rem;">
    <i class="fas fa-arrow-left"></i> Back to Orders
</a>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
    <div>
        <div class="card">
            <h2 style="margin-bottom: 1.5rem;">Order #<?php echo $order['id']; ?></h2>
            
            <div style="margin-bottom: 2rem;">
                <h3>Customer Information</h3>
                <p>
                    <strong>Name:</strong> <?php echo htmlspecialchars($order['user_name']); ?><br>
                    <strong>Email:</strong> <?php echo htmlspecialchars($order['user_email']); ?><br>
                    <strong>Phone:</strong> <?php echo htmlspecialchars($order['user_phone']); ?>
                </p>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <h3>Delivery Address</h3>
                <p>
                    <?php echo htmlspecialchars($order['full_name']); ?><br>
                    <?php echo htmlspecialchars($order['flat_house']); ?><br>
                    <?php echo htmlspecialchars($order['locality']); ?><br>
                    <?php if ($order['landmark']): ?>
                        Landmark: <?php echo htmlspecialchars($order['landmark']); ?><br>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($order['city'] . ', ' . $order['state'] . ' - ' . $order['pincode']); ?><br>
                    Mobile: <?php echo htmlspecialchars($order['mobile']); ?>
                </p>
            </div>
            
            <div>
                <h3>Order Items</h3>
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
                            <td colspan="3" style="text-align: right;"><strong>Total Amount:</strong></td>
                            <td><strong style="color: var(--primary-color); font-size: 1.2rem;">
                                ₹<?php echo number_format($order['total_amount'], 2); ?>
                            </strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <div>
        <div class="card">
            <h3 style="margin-bottom: 1rem;">Order Information</h3>
            <p>
                <strong>Order Date:</strong><br>
                <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?>
            </p>
            <p>
                <strong>Payment Method:</strong><br>
                <?php echo strtoupper($order['payment_method']); ?>
            </p>
            <p>
                <strong>Payment Status:</strong><br>
                <span class="badge badge-<?php echo $order['payment_status']; ?>">
                    <?php echo ucfirst($order['payment_status']); ?>
                </span>
            </p>
        </div>
        
        <div class="card" style="margin-top: 1.5rem;">
            <h3 style="margin-bottom: 1rem;">Update Order Status</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="order_status">Order Status</label>
                    <select id="order_status" name="order_status" class="form-control" required>
                        <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $order['order_status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="shipped" <?php echo $order['order_status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $order['order_status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Update Status</button>
            </form>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
