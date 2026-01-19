<?php
// Modern Admin Dashboard
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = 'Dashboard';

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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background: #f1f5f9; 
            color: #1e293b; 
            line-height: 1.6;
        }
        
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            background: #1e293b;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid #334155;
        }
        
        .sidebar-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .sidebar-header .logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.5rem;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .nav-item:hover, .nav-item.active {
            background: #334155;
            color: white;
            border-left-color: #3b82f6;
        }
        
        .nav-item i {
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            background: #f1f5f9;
        }
        
        .top-bar {
            background: white;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .top-bar h2 {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .content-area {
            padding: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-color);
        }
        
        .stat-card.blue { --accent-color: #3b82f6; }
        .stat-card.green { --accent-color: #10b981; }
        .stat-card.orange { --accent-color: #f59e0b; }
        .stat-card.red { --accent-color: #ef4444; }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            background: var(--accent-color);
        }
        
        .stat-value {
            font-size: 2.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            background: white;
            color: #374151;
            text-decoration: none;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
            font-weight: 500;
        }
        
        .action-btn:hover {
            background: #f8fafc;
            border-color: #3b82f6;
            color: #3b82f6;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .action-btn i {
            width: 20px;
            text-align: center;
        }
        
        .logout-btn {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fecaca;
        }
        
        .logout-btn:hover {
            background: #fecaca;
            border-color: #dc2626;
            color: #dc2626;
        }
        
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .top-bar {
                padding: 1rem;
            }
            
            .content-area {
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>
                    <div class="logo">💪</div>
                    FitSupps
                </h1>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-chart-line"></i>
                    Dashboard
                </a>
                <a href="products.php" class="nav-item">
                    <i class="fas fa-box"></i>
                    Products
                </a>
                <a href="categories.php" class="nav-item">
                    <i class="fas fa-tags"></i>
                    Categories
                </a>
                <a href="orders.php" class="nav-item">
                    <i class="fas fa-shopping-cart"></i>
                    Orders
                </a>
                <a href="../index.php" class="nav-item" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    View Store
                </a>
                <a href="logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="top-bar">
                <h2>Dashboard</h2>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></span>
                </div>
            </div>
            
            <div class="content-area">
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card blue">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value"><?php echo $total_products; ?></div>
                                <div class="stat-label">Total Products</div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card green">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value"><?php echo $total_orders; ?></div>
                                <div class="stat-label">Total Orders</div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card orange">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value"><?php echo $total_users; ?></div>
                                <div class="stat-label">Total Users</div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card red">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value">₹<?php echo number_format($total_revenue, 0); ?></div>
                                <div class="stat-label">Total Revenue</div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="actions-grid">
                    <a href="products.php" class="action-btn">
                        <i class="fas fa-box"></i>
                        Manage Products
                    </a>
                    <a href="categories.php" class="action-btn">
                        <i class="fas fa-tags"></i>
                        Manage Categories
                    </a>
                    <a href="orders.php" class="action-btn">
                        <i class="fas fa-shopping-cart"></i>
                        View Orders
                    </a>
                    <a href="product-form.php" class="action-btn">
                        <i class="fas fa-plus"></i>
                        Add Product
                    </a>
                    <a href="../index.php" class="action-btn" target="_blank">
                        <i class="fas fa-store"></i>
                        View Store
                    </a>
                    <a href="logout.php" class="action-btn logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

<?php $conn->close(); ?>