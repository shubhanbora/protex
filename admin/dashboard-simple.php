<?php
// Simple working admin dashboard
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security Check - Must be logged in as admin
require_once 'auth_check.php';
require_once '../config/database.php';

$conn = getDBConnection();
if (!$conn) {
    die("Database connection failed");
}

// Get statistics with error handling
$total_products = 0;
$total_orders = 0;
$total_users = 0;
$total_revenue = 0;

try {
    // Get total products
    $products_result = $conn->query("SELECT COUNT(*) as count FROM products WHERE is_deleted = 0");
    if ($products_result) {
        $total_products = $products_result->fetch_assoc()['count'];
    }

    // Get total orders
    $orders_result = $conn->query("SELECT COUNT(*) as count FROM orders");
    if ($orders_result) {
        $total_orders = $orders_result->fetch_assoc()['count'];
    }

    // Get total users
    $users_result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($users_result) {
        $total_users = $users_result->fetch_assoc()['count'];
    }

    // Get total revenue
    $revenue_result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE order_status != 'cancelled'");
    if ($revenue_result) {
        $revenue_data = $revenue_result->fetch_assoc();
        $total_revenue = $revenue_data['total'] ?? 0;
    }

    // Get recent orders
    $recent_orders = $conn->query("SELECT o.*, u.full_name as user_name 
                                   FROM orders o 
                                   LEFT JOIN users u ON o.user_id = u.id 
                                   ORDER BY o.created_at DESC 
                                   LIMIT 5");
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FitSupps</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #1a202c;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.blue { background: #3b82f6; }
        .stat-icon.green { background: #10b981; }
        .stat-icon.orange { background: #f59e0b; }
        .stat-icon.red { background: #ef4444; }

        .stat-info h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .table th {
            background: #f8fafc;
            font-weight: 600;
            color: #374151;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .btn-success {
            background: #10b981;
        }

        .btn-success:hover {
            background: #059669;
        }

        .actions {
            display: flex;
            gap: 1rem;
            margin: 2rem 0;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-tachometer-alt"></i> FitSupps Admin Dashboard</h1>
    </div>

    <div class="container">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_products; ?></h3>
                    <p>Total Products</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_orders; ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_users; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>₹<?php echo number_format($total_revenue, 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="actions">
            <a href="products.php" class="btn">
                <i class="fas fa-box"></i> Manage Products
            </a>
            <a href="categories.php" class="btn btn-success">
                <i class="fas fa-tags"></i> Manage Categories
            </a>
            <a href="orders.php" class="btn">
                <i class="fas fa-shopping-cart"></i> View Orders
            </a>
            <a href="product-form.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-clock"></i> Recent Orders</h2>
            </div>
            
            <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['user_name'] ?? 'Unknown'); ?></td>
                                <td>₹<?php echo number_format($order['total_amount'] ?? 0, 2); ?></td>
                                <td><?php echo ucfirst($order['order_status'] ?? 'pending'); ?></td>
                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>No Orders Yet</h3>
                    <p>Orders will appear here once customers start purchasing.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Debug Info -->
        <div style="margin-top: 2rem; padding: 1rem; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #3b82f6;">
            <h4>Debug Information:</h4>
            <p><strong>Products:</strong> <?php echo $total_products; ?></p>
            <p><strong>Orders:</strong> <?php echo $total_orders; ?></p>
            <p><strong>Users:</strong> <?php echo $total_users; ?></p>
            <p><strong>Revenue:</strong> ₹<?php echo number_format($total_revenue, 2); ?></p>
            <p><strong>Admin:</strong> <?php echo $_SESSION['admin_username'] ?? 'Unknown'; ?></p>
        </div>

        <!-- Navigation -->
        <div style="margin-top: 2rem; text-align: center;">
            <a href="../index.php" class="btn">
                <i class="fas fa-store"></i> View Store
            </a>
            <a href="logout.php" class="btn" style="background: #ef4444;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>