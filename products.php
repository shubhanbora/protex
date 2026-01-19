<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'config/session.php';

// Require login to access products
requireLogin();

$conn = getDBConnection();
if (!$conn) {
    die("Database connection failed");
}

// Get filter parameters with basic sanitization
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$sort = isset($_GET['sort']) ? htmlspecialchars($_GET['sort']) : 'newest';

// Build query - simple version that works
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active' AND p.is_deleted = 0";

if ($category_id > 0) {
    $query .= " AND p.category_id = $category_id";
}

if (!empty($search)) {
    $search_escaped = $conn->real_escape_string($search);
    $query .= " AND (p.name LIKE '%$search_escaped%' OR p.description LIKE '%$search_escaped%')";
}

// Add sorting
switch ($sort) {
    case 'price_low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name':
        $query .= " ORDER BY p.name ASC";
        break;
    default:
        $query .= " ORDER BY p.created_at DESC";
}

$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Get categories for filter
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);

$pageTitle = 'Products - FitSupps';
require_once 'includes/header.php';
?>

<style>
.products-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    text-align: center;
    margin-bottom: 40px;
}

.page-title {
    font-size: 3rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-subtitle {
    font-size: 1.2rem;
    color: #6b7280;
    margin-bottom: 30px;
}

.filters-section {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    margin-bottom: 40px;
}

.filters-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 20px;
    align-items: center;
}

.search-box {
    position: relative;
}

.search-input-main {
    width: 100%;
    padding: 12px 50px 12px 15px;
    border: 2px solid #e5e7eb;
    border-radius: 25px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f9fafb;
}

.search-input-main:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-icon {
    position: absolute;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 1.1rem;
    pointer-events: none;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 5px;
}

.filter-select {
    padding: 10px 15px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.95rem;
    background: white;
    transition: border-color 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #667eea;
}

.clear-filters {
    background: #ef4444;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.clear-filters:hover {
    background: #dc2626;
    text-decoration: none;
    color: white;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .filters-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .search-input-main {
        padding: 15px 55px 15px 20px;
        font-size: 1.1rem;
    }
    
    .search-icon {
        right: 22px;
        font-size: 1.2rem;
    }
    
    .filter-select {
        padding: 12px 15px;
        font-size: 1rem;
    }
    
    .clear-filters {
        padding: 12px 20px;
        font-size: 1rem;
        justify-content: center;
    }
}

.active-filter {
    background: #667eea;
    color: white;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 500;
}

/* Search Results Info */
.search-results-info {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    color: #0c4a6e;
}

.search-results-info i {
    color: #0284c7;
    margin-right: 8px;
}

.search-input {
    width: 100%;
    padding: 12px 20px 12px 50px;
    border: 2px solid #e5e7eb;
    border-radius: 25px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f9fafb;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.search-icon {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 1.1rem;
}

.filter-select {
    padding: 12px 20px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 1rem;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 180px;
}

.filter-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.tabs-nav {
    display: flex;
    gap: 5px;
    background: #f3f4f6;
    padding: 5px;
    border-radius: 12px;
    margin-bottom: 40px;
}

.tab-btn {
    background: none;
    border: none;
    padding: 12px 25px;
    font-size: 1rem;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.tab-btn.active {
    background: white;
    color: #667eea;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.product-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    position: relative;
    border: 1px solid #f3f4f6;
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.product-image-container {
    position: relative;
    overflow: hidden;
    height: 250px;
    background: #f9fafb;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

.discount-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: #10b981;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    z-index: 2;
}

.new-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #f59e0b;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    z-index: 2;
}

.wishlist-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(255, 255, 255, 0.9);
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 2;
}

.wishlist-btn:hover {
    background: #fee2e2;
    color: #dc2626;
}

.product-info {
    padding: 20px;
}

.product-category {
    color: #667eea;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
}

.product-name {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-description {
    color: #6b7280;
    font-size: 0.9rem;
    line-height: 1.4;
    margin-bottom: 15px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 15px;
}

.stars {
    color: #fbbf24;
    font-size: 0.9rem;
}

.rating-text {
    color: #9ca3af;
    font-size: 0.85rem;
}

.product-price-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
}

.product-price {
    font-size: 1.5rem;
    font-weight: 800;
    color: #059669;
}

.original-price {
    font-size: 1rem;
    color: #9ca3af;
    text-decoration: line-through;
    margin-left: 8px;
}

.product-actions {
    display: flex;
    gap: 10px;
}

.btn-view-details {
    flex: 1;
    background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.9rem;
}

.btn-view-details:hover {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    color: white;
    text-decoration: none;
}

.btn-add-cart {
    background: #10b981;
    color: white;
    border: none;
    padding: 12px 15px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-add-cart:hover {
    background: #059669;
    transform: translateY(-2px);
}

.no-products {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.no-products-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .filters-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .tabs-nav {
        flex-wrap: wrap;
    }
    
    .page-title {
        font-size: 2.5rem;
    }
}

@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .product-actions {
        flex-direction: column;
    }
}
</style>

<div class="products-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">EXPLORE</h1>
        <p class="page-subtitle">Discover premium fitness supplements for your goals</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="tabs-nav">
        <button class="tab-btn active" onclick="filterProducts('all')">NEW</button>
        <button class="tab-btn" onclick="filterProducts('featured')">FEATURED</button>
        <button class="tab-btn" onclick="filterProducts('bestsellers')">TOP SELLERS</button>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
        <div class="filters-row">
            <!-- Enhanced Search Box -->
            <div class="search-box">
                <form method="GET" style="position: relative;">
                    <input type="text" 
                           name="search" 
                           class="search-input-main"
                           placeholder="Search products by name, description, or category..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           autocomplete="off">
                    <i class="fas fa-search search-icon"></i>
                    <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                    <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                </form>
            </div>
            
            <!-- Category Filter -->
            <div class="filter-group">
                <label class="filter-label">Category</label>
                <select onchange="window.location.href='?category=' + this.value + '&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>'" class="filter-select">
                    <option value="0">All Categories</option>
                    <?php 
                    $categories_result->data_seek(0);
                    while ($cat = $categories_result->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <!-- Sort Filter -->
            <div class="filter-group">
                <label class="filter-label">Sort By</label>
                <select onchange="window.location.href='?sort=' + this.value + '&category=<?php echo $category_id; ?>&search=<?php echo urlencode($search); ?>'" class="filter-select">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                </select>
            </div>
        </div>
        
        <!-- Active Filters & Clear Button -->
        <?php if (!empty($search) || $category_id > 0 || $sort != 'newest'): ?>
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <span style="font-weight: 600; color: #374151;">Active Filters:</span>
                    
                    <?php if (!empty($search)): ?>
                        <span class="active-filter">Search: "<?php echo htmlspecialchars($search); ?>"</span>
                    <?php endif; ?>
                    
                    <?php if ($category_id > 0): ?>
                        <?php
                        $categories_result->data_seek(0);
                        while ($cat = $categories_result->fetch_assoc()) {
                            if ($cat['id'] == $category_id) {
                                echo '<span class="active-filter">Category: ' . htmlspecialchars($cat['name']) . '</span>';
                                break;
                            }
                        }
                        ?>
                    <?php endif; ?>
                    
                    <?php if ($sort != 'newest'): ?>
                        <span class="active-filter">Sort: <?php 
                            switch($sort) {
                                case 'price_low': echo 'Price Low to High'; break;
                                case 'price_high': echo 'Price High to Low'; break;
                                case 'name': echo 'Name A-Z'; break;
                            }
                        ?></span>
                    <?php endif; ?>
                    
                    <a href="products.php" class="clear-filters">
                        <i class="fas fa-times"></i>
                        Clear All
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Search Results Info -->
    <?php 
    $total_results = mysqli_num_rows($result);
    if (!empty($search) || $category_id > 0): 
    ?>
        <div class="search-results-info">
            <i class="fas fa-info-circle"></i>
            <strong><?php echo $total_results; ?></strong> product<?php echo $total_results != 1 ? 's' : ''; ?> found
            <?php if (!empty($search)): ?>
                for "<strong><?php echo htmlspecialchars($search); ?></strong>"
            <?php endif; ?>
            <?php if ($category_id > 0): ?>
                <?php
                $categories_result->data_seek(0);
                while ($cat = $categories_result->fetch_assoc()) {
                    if ($cat['id'] == $category_id) {
                        echo ' in <strong>' . htmlspecialchars($cat['name']) . '</strong>';
                        break;
                    }
                }
                ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Products Grid -->
    <div class="products-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php 
            $product_count = 0;
            while ($product = $result->fetch_assoc()): 
                $product_count++;
                
                // Calculate discount percentage (random for demo)
                $discount = rand(10, 50);
                $original_price = $product['price'] * (1 + $discount/100);
                
                // Determine if product is new (created within last 30 days)
                $is_new = (strtotime($product['created_at']) > strtotime('-30 days'));
            ?>
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="<?php echo htmlspecialchars($product['image'] ?: 'assets/images/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="product-image">
                        
                        <!-- Discount Badge -->
                        <div class="discount-badge">-<?php echo $discount; ?>%</div>
                        
                        <!-- New Badge -->
                        <?php if ($is_new): ?>
                            <div class="new-badge">NEW</div>
                        <?php endif; ?>
                        
                        <!-- Wishlist Button -->
                        <button class="wishlist-btn" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                    
                    <div class="product-info">
                        <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        
                        <div class="product-description">
                            <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                        </div>
                        
                        <!-- Rating (demo data) -->
                        <div class="product-rating">
                            <div class="stars">
                                <?php 
                                $rating = rand(35, 50) / 10; // Random rating between 3.5-5.0
                                for ($i = 1; $i <= 5; $i++): 
                                ?>
                                    <?php if ($i <= floor($rating)): ?>
                                        <i class="fas fa-star"></i>
                                    <?php elseif ($i <= ceil($rating)): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-text"><?php echo number_format($rating, 1); ?> (<?php echo rand(10, 200); ?>)</span>
                        </div>
                        
                        <div class="product-price-section">
                            <div>
                                <span class="product-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                <span class="original-price">₹<?php echo number_format($original_price, 2); ?></span>
                            </div>
                        </div>
                        
                        <div class="product-actions">
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-view-details">
                                <i class="fas fa-eye"></i>
                                View Details
                            </a>
                            <?php if (isLoggedIn()): ?>
                                <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn-add-cart">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-products">
                <div class="no-products-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>No products found</h3>
                <p>Try adjusting your search or filter criteria</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function filterProducts(type) {
    // Update active tab
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Here you can add AJAX filtering logic
    console.log('Filtering by:', type);
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

// Auto-submit search form on input
document.querySelector('.search-input').addEventListener('input', function() {
    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => {
        this.form.submit();
    }, 500);
});
</script>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
