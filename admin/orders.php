<?php
$pageTitle = 'Orders Management';

// Security Check - Must be logged in as admin
require_once 'auth_check.php';

require_once '../config/database.php';
require_once 'includes/header.php';

$conn = getDBConnection();

// Get all orders
$query = "SELECT o.*, u.name as user_name, u.email as user_email 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<h2 style="margin-bottom: 2rem;">Orders List</h2>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Payment Status</th>
                <th>Order Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($order['user_name']); ?></strong><br>
                        <small style="color: #6b7280;"><?php echo htmlspecialchars($order['user_email']); ?></small>
                    </td>
                    <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><?php echo strtoupper($order['payment_method']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $order['payment_status']; ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $order['order_status']; ?>">
                            <?php echo ucfirst($order['order_status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                    <td>
                        <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">
                            View Details
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
