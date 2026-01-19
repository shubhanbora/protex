    </main>
    
    <!-- Mobile Bottom Navigation (Flipkart Style) -->
    <?php if (isLoggedIn()): ?>
    <nav class="mobile-bottom-nav">
        <a href="<?php echo $base_url ?? ''; ?>/index.php" class="bottom-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="<?php echo $base_url ?? ''; ?>/products.php" class="bottom-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : ''; ?>">
            <i class="fas fa-store"></i>
            <span>Products</span>
        </a>
        <a href="<?php echo $base_url ?? ''; ?>/cart.php" class="bottom-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'cart.php') ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>Cart</span>
            <?php
            // Get cart count
            $cart_count_query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = " . getCurrentUserId();
            $cart_count_result = mysqli_query($GLOBALS['db_connection'] ?? getDBConnection(), $cart_count_query);
            $cart_count = 0;
            if ($cart_count_result) {
                $cart_data = mysqli_fetch_assoc($cart_count_result);
                $cart_count = $cart_data['total'] ?? 0;
            }
            if ($cart_count > 0):
            ?>
                <span class="bottom-nav-badge"><?php echo $cart_count > 9 ? '9+' : $cart_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="<?php echo $base_url ?? ''; ?>/account/orders.php" class="bottom-nav-item <?php echo (strpos($_SERVER['PHP_SELF'], 'orders.php') !== false) ? 'active' : ''; ?>">
            <i class="fas fa-shopping-bag"></i>
            <span>Orders</span>
        </a>
        <a href="<?php echo $base_url ?? ''; ?>/account/wishlist.php" class="bottom-nav-item <?php echo (strpos($_SERVER['PHP_SELF'], 'wishlist.php') !== false) ? 'active' : ''; ?>">
            <i class="fas fa-heart"></i>
            <span>Wishlist</span>
        </a>
    </nav>
    <?php endif; ?>
    
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p>Your trusted source for premium protein & fitness supplements</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="<?php echo $base_url ?? ''; ?>/index.php">Home</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>/products.php">Products</a></li>
                        <li><a href="<?php echo $base_url ?? ''; ?>/cart.php">Cart</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <p>Email: support@fitsupps.com</p>
                    <p>Phone: +91 1234567890</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> FitSupps. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script src="<?php echo $base_url ?? ''; ?>/assets/js/main.js"></script>
</body>
</html>
