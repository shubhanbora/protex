<?php
$pageTitle = 'Products Management';

// Security Check - Must be logged in as admin
require_once 'auth_check.php';

require_once '../config/database.php';
require_once 'includes/header.php';

$conn = getDBConnection();

// Get search parameter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Check for success message
$success = '';
if (isset($_SESSION['product_success'])) {
    $success = $_SESSION['product_success'];
    unset($_SESSION['product_success']);
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("UPDATE products SET is_deleted = 1 WHERE id = $id");
    $success = 'Product deleted successfully';
}

// Get all products with search
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          WHERE p.is_deleted = 0";

if (!empty($search)) {
    $query .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%' OR c.name LIKE '%$search%')";
}

$query .= " ORDER BY p.created_at DESC";
$result = $conn->query($query);
$total_products = $result ? $result->num_rows : 0;
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Products List</h2>
    <div style="display: flex; gap: 1rem;">
        <a href="test_db.php" class="btn" style="background: #f59e0b; color: #fff;">
            <i class="fas fa-bug"></i> Test Database
        </a>
        <a href="product-form.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Product
        </a>
    </div>
</div>

<!-- Admin Search Bar -->
<div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem;">
    <form method="GET" style="display: flex; gap: 15px; align-items: center;">
        <div style="flex: 1; position: relative;">
            <input type="text" 
                   name="search" 
                   placeholder="Search products by name, description, or category..." 
                   value="<?php echo htmlspecialchars($search); ?>"
                   style="width: 100%; padding: 12px 45px 12px 15px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 1rem;">
            <i class="fas fa-search" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if (!empty($search)): ?>
            <a href="products.php" class="btn" style="background: #ef4444; color: white;">
                <i class="fas fa-times"></i> Clear
            </a>
        <?php endif; ?>
    </form>
    
    <?php if (!empty($search)): ?>
        <div style="margin-top: 15px; padding: 10px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; color: #0c4a6e;">
            <i class="fas fa-info-circle"></i>
            <strong><?php echo $total_products; ?></strong> product<?php echo $total_products != 1 ? 's' : ''; ?> found for "<strong><?php echo htmlspecialchars($search); ?></strong>"
        </div>
    <?php endif; ?>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="card">
    <?php if ($result && $result->num_rows > 0): ?>
        <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($product = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $product['id']; ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <img src="<?php echo htmlspecialchars($product['image'] ?: '/assets/images/placeholder.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                            <span><?php echo htmlspecialchars($product['name']); ?></span>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                    <td>₹<?php echo number_format($product['price'], 2); ?></td>
                    <td><?php echo $product['stock']; ?></td>
                    <td>
                        <span class="badge badge-<?php echo $product['status']; ?>">
                            <?php echo ucfirst($product['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($product['created_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="product-form.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo $product['id']; ?>" 
                               onclick="return confirm('Delete this product?')" 
                               class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div style="text-align: center; padding: 3rem;">
            <i class="fas fa-box-open" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
            <h3>No Products Found</h3>
            <p style="color: #6b7280; margin: 1rem 0;">Add your first product to get started!</p>
            <a href="product-form.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
            </a>
            <a href="test_db.php" class="btn" style="background: #f59e0b; color: #fff; margin-left: 1rem;">
                <i class="fas fa-bug"></i> Test Database
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
