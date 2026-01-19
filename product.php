<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'config/session.php';

// Require login to view product details
requireLogin();

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header('Location: products.php');
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    die("Database connection failed");
}

// Simple query that will definitely work
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.id = $product_id";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

if ($result->num_rows === 0) {
    echo "<h2>Product not found</h2>";
    echo "<p>Product ID: $product_id</p>";
    echo "<a href='products.php'>Back to Products</a>";
    exit();
}

$product = $result->fetch_assoc();
$pageTitle = $product['name'] . ' - FitSupps';

// Get all product images
$product_images = [];
for ($i = 1; $i <= 5; $i++) {
    if (!empty($product["image_$i"])) {
        $product_images[] = $product["image_$i"];
    }
}
if (empty($product_images) && !empty($product['image'])) {
    $product_images[] = $product['image'];
}
if (empty($product_images)) {
    $product_images[] = 'assets/images/placeholder.jpg';
}

// Get reviews - check if table exists first
$reviews_result = null;
$rating_data = ['avg_rating' => 0, 'total_reviews' => 0];

$check_reviews_table = "SHOW TABLES LIKE 'reviews'";
$table_check = $conn->query($check_reviews_table);

if ($table_check && $table_check->num_rows > 0) {
    // Reviews table exists, get reviews
    $reviews_query = "SELECT r.*, u.full_name as user_name 
                      FROM reviews r 
                      LEFT JOIN users u ON r.user_id = u.id 
                      WHERE r.product_id = $product_id 
                      ORDER BY r.created_at DESC 
                      LIMIT 10";
    $reviews_result = $conn->query($reviews_query);

    // Calculate average rating
    $avg_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                  FROM reviews WHERE product_id = $product_id";
    $avg_result = $conn->query($avg_query);
    $rating_data = $avg_result ? $avg_result->fetch_assoc() : ['avg_rating' => 0, 'total_reviews' => 0];
}

// Get related products - simple query
$related_query = "SELECT * FROM products 
                  WHERE category_id = {$product['category_id']} 
                  AND id != $product_id 
                  AND status = 'active' 
                  AND is_deleted = 0 
                  ORDER BY RAND() 
                  LIMIT 4";
$related_result = $conn->query($related_query);

require_once 'includes/header.php';
?>

<style>
.product-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.product-main {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-bottom: 40px;
}

.product-images {
    position: relative;
}

.main-image {
    width: 100%;
    height: 500px;
    object-fit: cover;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    margin-bottom: 15px;
}

.image-thumbnails {
    display: flex;
    gap: 10px;
    overflow-x: auto;
}

.thumbnail {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.3s ease;
}

.thumbnail:hover,
.thumbnail.active {
    border-color: #667eea;
}

.product-info {
    padding: 20px 0;
}

.product-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 10px;
    line-height: 1.2;
}

.product-category {
    color: #667eea;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 1px;
    margin-bottom: 15px;
}

.rating-section {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.stars {
    color: #fbbf24;
    font-size: 1.2rem;
}

.rating-text {
    color: #6b7280;
    font-size: 0.9rem;
}

.product-price {
    font-size: 3rem;
    font-weight: 800;
    color: #059669;
    margin-bottom: 20px;
}

.product-description {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #4b5563;
    margin-bottom: 25px;
}

.product-details {
    background: #f9fafb;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #e5e7eb;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #374151;
}

.detail-value {
    color: #6b7280;
}

.stock-info {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 25px;
    padding: 15px;
    background: #ecfdf5;
    border-radius: 8px;
    border-left: 4px solid #10b981;
}

.stock-icon {
    color: #10b981;
    font-size: 1.2rem;
}

.action-buttons {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
}

.btn-add-cart {
    flex: 1;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 15px 25px;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-add-cart:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

.btn-wishlist {
    background: #f3f4f6;
    color: #6b7280;
    border: 2px solid #e5e7eb;
    padding: 15px 20px;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-wishlist:hover {
    background: #fee2e2;
    color: #dc2626;
    border-color: #fca5a5;
}

.quantity-selector {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.quantity-label {
    font-weight: 600;
    color: #374151;
}

.quantity-controls {
    display: flex;
    align-items: center;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
}

.quantity-btn {
    background: #f9fafb;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    font-size: 1.2rem;
    font-weight: 600;
    color: #6b7280;
    transition: all 0.3s ease;
}

.quantity-btn:hover {
    background: #e5e7eb;
}

.quantity-input {
    border: none;
    padding: 10px 15px;
    text-align: center;
    font-size: 1.1rem;
    font-weight: 600;
    width: 60px;
    background: white;
}

.tabs-container {
    margin-bottom: 40px;
}

.tabs-nav {
    display: flex;
    border-bottom: 2px solid #e5e7eb;
    margin-bottom: 30px;
}

.tab-btn {
    background: none;
    border: none;
    padding: 15px 25px;
    font-size: 1.1rem;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.tab-btn.active {
    color: #667eea;
    border-bottom-color: #667eea;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.reviews-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.review-item {
    padding: 20px 0;
    border-bottom: 1px solid #e5e7eb;
}

.review-item:last-child {
    border-bottom: none;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.reviewer-name {
    font-weight: 600;
    color: #1f2937;
}

.review-date {
    color: #9ca3af;
    font-size: 0.9rem;
}

.review-text {
    color: #4b5563;
    line-height: 1.6;
}

.related-products {
    margin-top: 60px;
}

.section-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 30px;
    text-align: center;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
}

.product-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.product-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.product-card-content {
    padding: 20px;
}

.product-card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 8px;
}

.product-card-price {
    font-size: 1.3rem;
    font-weight: 700;
    color: #059669;
}

@media (max-width: 768px) {
    .product-main {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .product-title {
        font-size: 2rem;
    }
    
    .product-price {
        font-size: 2.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }
}
</style>

<div class="product-container">
    <!-- Main Product Section -->
    <div class="product-main">
        <!-- Product Images -->
        <div class="product-images">
            <img id="mainImage" src="<?php echo htmlspecialchars($product_images[0]); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 class="main-image">
            
            <?php if (count($product_images) > 1): ?>
                <div class="image-thumbnails">
                    <?php foreach ($product_images as $index => $image): ?>
                        <img src="<?php echo htmlspecialchars($image); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                             onclick="changeMainImage('<?php echo htmlspecialchars($image); ?>', this)">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="product-info">
            <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <?php if ($rating_data['total_reviews'] > 0): ?>
                <div class="rating-section">
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= round($rating_data['avg_rating'])): ?>
                                <i class="fas fa-star"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-text">
                        <?php echo number_format($rating_data['avg_rating'], 1); ?> 
                        (<?php echo $rating_data['total_reviews']; ?> reviews)
                    </span>
                </div>
            <?php endif; ?>
            
            <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
            
            <div class="product-description">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>
            
            <!-- Product Details -->
            <div class="product-details">
                <div class="detail-row">
                    <span class="detail-label">Brand</span>
                    <span class="detail-value">FitSupps</span>
                </div>
                <?php if (!empty($product['weight'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Weight</span>
                    <span class="detail-value"><?php echo htmlspecialchars($product['weight']); ?></span>
                </div>
                <?php endif; ?>
                <div class="detail-row">
                    <span class="detail-label">Category</span>
                    <span class="detail-value"><?php echo htmlspecialchars($product['category_name']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">SKU</span>
                    <span class="detail-value">FS-<?php echo str_pad($product['id'], 4, '0', STR_PAD_LEFT); ?></span>
                </div>
            </div>
            
            <!-- Stock Info -->
            <div class="stock-info">
                <i class="fas fa-check-circle stock-icon"></i>
                <span><strong><?php echo $product['stock']; ?></strong> items in stock</span>
            </div>
            
            <?php if (isLoggedIn()): ?>
                <!-- Quantity Selector -->
                <div class="quantity-selector">
                    <span class="quantity-label">Quantity:</span>
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                        <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        <button class="quantity-btn" onclick="changeQuantity(1)">+</button>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn-add-cart">
                        <i class="fas fa-cart-plus"></i>
                        Add to Cart
                    </button>
                    <button onclick="addToWishlist(<?php echo $product['id']; ?>)" class="btn-wishlist">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>
            <?php else: ?>
                <div style="background: #fef2f2; color: #dc2626; padding: 20px; border-radius: 8px; text-align: center;">
                    <i class="fas fa-lock" style="margin-right: 10px;"></i>
                    Please <a href="login.php" style="color: #dc2626; font-weight: 600;">login</a> to add items to cart
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabs Section -->
    <div class="tabs-container">
        <div class="tabs-nav">
            <button class="tab-btn active" onclick="showTab('description')">Description</button>
            <button class="tab-btn" onclick="showTab('reviews')">Reviews (<?php echo $rating_data['total_reviews']; ?>)</button>
            <button class="tab-btn" onclick="showTab('shipping')">Shipping Info</button>
        </div>

        <div id="description" class="tab-content active">
            <div class="reviews-section">
                <h3 style="margin-bottom: 20px; color: #1f2937;">Product Description</h3>
                <p style="line-height: 1.8; color: #4b5563; font-size: 1.1rem;">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </p>
                
                <h4 style="margin: 30px 0 15px 0; color: #1f2937;">Key Features:</h4>
                <ul style="line-height: 1.8; color: #4b5563;">
                    <li>Premium quality ingredients</li>
                    <li>Lab tested for purity and potency</li>
                    <li>No artificial colors or preservatives</li>
                    <li>Suitable for vegetarians</li>
                    <li>Easy to mix and great taste</li>
                </ul>
            </div>
        </div>

        <div id="reviews" class="tab-content">
            <div class="reviews-section">
                <h3 style="margin-bottom: 20px; color: #1f2937;">Customer Reviews</h3>
                
                <?php if ($reviews_result && $reviews_result->num_rows > 0): ?>
                    <?php while ($review = $reviews_result->fetch_assoc()): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <span class="reviewer-name"><?php echo htmlspecialchars($review['user_name']); ?></span>
                                <div>
                                    <span class="stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $review['rating']): ?>
                                                <i class="fas fa-star"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </span>
                                    <span class="review-date"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></span>
                                </div>
                            </div>
                            <p class="review-text"><?php echo htmlspecialchars($review['comment']); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #6b7280; padding: 40px;">
                        No reviews yet. Be the first to review this product!
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div id="shipping" class="tab-content">
            <div class="reviews-section">
                <h3 style="margin-bottom: 20px; color: #1f2937;">Shipping Information</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div style="padding: 20px; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #0ea5e9;">
                        <h4 style="color: #0c4a6e; margin-bottom: 10px;">
                            <i class="fas fa-shipping-fast" style="margin-right: 8px;"></i>
                            Free Shipping
                        </h4>
                        <p style="color: #075985; margin: 0;">On orders above ₹999</p>
                    </div>
                    
                    <div style="padding: 20px; background: #f0fdf4; border-radius: 8px; border-left: 4px solid #22c55e;">
                        <h4 style="color: #14532d; margin-bottom: 10px;">
                            <i class="fas fa-clock" style="margin-right: 8px;"></i>
                            Fast Delivery
                        </h4>
                        <p style="color: #166534; margin: 0;">2-3 business days</p>
                    </div>
                    
                    <div style="padding: 20px; background: #fefce8; border-radius: 8px; border-left: 4px solid #eab308;">
                        <h4 style="color: #713f12; margin-bottom: 10px;">
                            <i class="fas fa-undo" style="margin-right: 8px;"></i>
                            Easy Returns
                        </h4>
                        <p style="color: #a16207; margin: 0;">30-day return policy</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if ($related_result && $related_result->num_rows > 0): ?>
        <div class="related-products">
            <h2 class="section-title">Related Products</h2>
            <div class="products-grid">
                <?php while ($related = $related_result->fetch_assoc()): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($related['image'] ?: 'assets/images/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($related['name']); ?>">
                        <div class="product-card-content">
                            <h3 class="product-card-title"><?php echo htmlspecialchars($related['name']); ?></h3>
                            <div class="product-card-price">₹<?php echo number_format($related['price'], 2); ?></div>
                            <a href="product.php?id=<?php echo $related['id']; ?>" 
                               style="display: inline-block; margin-top: 10px; color: #667eea; text-decoration: none; font-weight: 600;">
                                View Details →
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
let currentQuantity = 1;

function changeMainImage(imageSrc, thumbnail) {
    document.getElementById('mainImage').src = imageSrc;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
    thumbnail.classList.add('active');
}

function changeQuantity(delta) {
    const quantityInput = document.getElementById('quantity');
    const newQuantity = parseInt(quantityInput.value) + delta;
    const maxStock = parseInt(quantityInput.max);
    
    if (newQuantity >= 1 && newQuantity <= maxStock) {
        quantityInput.value = newQuantity;
        currentQuantity = newQuantity;
    }
}

function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabName).classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}

function addToCart(productId) {
    const quantity = document.getElementById('quantity').value;
    
    fetch('api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'add',
            product_id: productId,
            quantity: parseInt(quantity)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Added to cart successfully!', 'success');
            updateCartCount();
        } else {
            showNotification(data.message || 'Failed to add to cart', 'error');
        }
    })
    .catch(error => {
        showNotification('Error adding to cart', 'error');
    });
}

function addToWishlist(productId) {
    fetch('api/wishlist.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'add', product_id: productId})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Added to wishlist!', 'success');
        } else {
            showNotification(data.message || 'Failed to add to wishlist', 'error');
        }
    });
}

// Update quantity input when user types
document.getElementById('quantity')?.addEventListener('input', function() {
    const value = parseInt(this.value);
    const max = parseInt(this.max);
    
    if (value < 1) this.value = 1;
    if (value > max) this.value = max;
    
    currentQuantity = parseInt(this.value);
});

// Notification function
function showNotification(message, type = 'success') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(n => n.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="notification-close">×</button>
        </div>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease-out;
        max-width: 400px;
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOut 0.3s ease-in';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Add CSS for notifications
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .notification-close {
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        cursor: pointer;
        margin-left: auto;
        padding: 0 5px;
    }
`;
document.head.appendChild(notificationStyles);

// Update cart count function
function updateCartCount() {
    // This would typically fetch cart count from server
    // For now, just a placeholder
    console.log('Cart count updated');
}
</script>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
