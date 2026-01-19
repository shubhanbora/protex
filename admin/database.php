<?php
$pageTitle = 'Database Management';

// Security Check - Must be logged in as admin
require_once 'auth_check.php';

require_once '../config/database.php';
require_once 'includes/header.php';

$conn = getDBConnection();

// Get database statistics
$tables = [
    'users' => 'Users',
    'products' => 'Products',
    'orders' => 'Orders',
    'categories' => 'Categories',
    'addresses' => 'Addresses',
    'cart' => 'Cart Items',
    'wishlist' => 'Wishlist Items',
    'reviews' => 'Reviews'
];

$stats = [];
foreach ($tables as $table => $label) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM $table");
    $stats[$table] = mysqli_fetch_assoc($result)['count'];
}
?>

<h2 style="margin-bottom: 2rem;">Database Overview</h2>

<div class="stats-grid">
    <?php foreach ($tables as $table => $label): ?>
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-database"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats[$table]; ?></h3>
                <p><?php echo $label; ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <h3 style="margin-bottom: 1.5rem;">Database Tables</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Table Name</th>
                <th>Records</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tables as $table => $label): ?>
                <tr>
                    <td><code><?php echo $table; ?></code></td>
                    <td><?php echo $stats[$table]; ?></td>
                    <td><?php echo $label; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card" style="margin-top: 2rem;">
    <h3 style="margin-bottom: 1rem;">Database Information</h3>
    <p><strong>Database Name:</strong> <?php echo DB_NAME; ?></p>
    <p><strong>Host:</strong> <?php echo DB_HOST; ?></p>
    <p style="color: #6b7280; margin-top: 1rem;">
        <i class="fas fa-info-circle"></i> 
        To manage the database structure, use the schema.sql file located in the database folder.
    </p>
</div>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
