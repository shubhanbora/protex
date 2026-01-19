<?php
$pageTitle = 'My Wishlist - E-Commerce Store';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/header.php';

requireLogin();

$conn = getDBConnection();
$user_id = getCurrentUserId();

// Get wishlist items
$query = "SELECT w.*, p.name, p.price, p.image, p.stock, c.name as category_name 
          FROM wishlist w 
          JOIN products p ON w.product_id = p.id 
          JOIN categories c ON p.category_id = c.id 
          WHERE w.user_id = $user_id AND p.status = 'active' AND p.is_deleted = 0 
          ORDER BY w.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container">
    <h1 style="font-size: 2rem; margin-bottom: 2rem;">My Wishlist</h1>
    
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="product-grid">
            <?php while ($item = mysqli_fetch_assoc($result)): ?>
                <div class="product-card">
                    <img src="<?php echo htmlspecialchars($item['image'] ?: '/assets/images/placeholder.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                         class="product-image">
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p style="color: #6b7280; font-size: 0.9rem;">
                            <?php echo htmlspecialchars($item['category_name']); ?>
                        </p>
                        <p class="product-price">₹<?php echo number_format($item['price'], 2); ?></p>
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="../product.php?id=<?php echo $item['product_id']; ?>" class="btn btn-primary" style="flex: 1; text-align: center;">
                                View
                            </a>
                            <button onclick="addToCart(<?php echo $item['product_id']; ?>)" class="btn btn-success">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                            <button onclick="removeFromWishlist(<?php echo $item['id']; ?>)" class="btn btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="card" style="text-align: center; padding: 3rem;">
            <i class="fas fa-heart" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
            <h2>Your wishlist is empty</h2>
            <p style="color: #6b7280; margin: 1rem 0;">Save your favorite products here!</p>
            <a href="../products.php" class="btn btn-primary">Browse Products</a>
        </div>
    <?php endif; ?>
</div>

<script>
function removeFromWishlist(wishlistId) {
    if (confirm('Remove from wishlist?')) {
        fetch('../api/wishlist.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'remove', wishlist_id: wishlistId})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showNotification(data.message || 'Failed to remove item', 'error');
            }
        });
    }
}
</script>

<?php
closeDBConnection($conn);
require_once '../includes/footer.php';
?>
