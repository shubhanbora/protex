<?php
require_once __DIR__ . '/../config/session.php';

// Get the base URL dynamically - fixed for subdirectories
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// Get the document root and current script path
$doc_root = $_SERVER['DOCUMENT_ROOT'];
$script_path = dirname($_SERVER['SCRIPT_FILENAME']);

// Calculate base path relative to document root
$base_path = str_replace($doc_root, '', $script_path);
$base_path = str_replace('\\', '/', $base_path);

// Remove /account, /admin, etc. to get the root
$base_path = preg_replace('#/(account|admin|api|includes).*$#', '', $base_path);

// Clean up the path
$base_path = rtrim($base_path, '/');

$base_url = $protocol . '://' . $host . $base_path;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'E-Commerce Store'; ?></title>
    <script>
        // Make base URL available to JavaScript
        window.BASE_URL = '<?php echo $base_url; ?>';
    </script>
    <style>
        /* MOBILE NAVIGATION - STRONGEST OVERRIDE */
        body .main-header {
            background: white !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1) !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 1000 !important;
        }
        
        body .navbar {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 1rem !important;
            height: 60px !important;
            max-width: 1200px !important;
            margin: 0 auto !important;
            background: white !important;
            flex-direction: row !important;
            gap: 0 !important;
        }
        
        body .logo a {
            font-size: 1.4rem !important;
            font-weight: bold !important;
            color: #2563eb !important;
            text-decoration: none !important;
        }
        
        body .mobile-menu-toggle {
            display: none !important;
            background: none !important;
            border: none !important;
            font-size: 1.5rem !important;
            cursor: pointer !important;
            color: #333 !important;
        }
        
        /* Mobile Navigation Icons */
        .mobile-nav-icons {
            display: none !important;
            gap: 15px !important;
            align-items: center !important;
        }
        
        .mobile-search-btn {
            background: none !important;
            border: none !important;
            font-size: 1.3rem !important;
            color: #333 !important;
            cursor: pointer !important;
            padding: 8px !important;
            border-radius: 50% !important;
            transition: all 0.3s ease !important;
        }
        
        .mobile-search-btn:hover {
            background: #f8f9fa !important;
            color: #007bff !important;
        }
        
        body .nav-menu {
            display: flex !important;
            list-style: none !important;
            gap: 2rem !important;
            align-items: center !important;
            margin: 0 !important;
            padding: 0 !important;
            flex-direction: row !important;
            width: auto !important;
            text-align: left !important;
        }
        
        body .nav-menu a {
            text-decoration: none !important;
            color: #333 !important;
            font-weight: 500 !important;
            padding: 0 !important;
            border-bottom: none !important;
        }
        
        body .nav-menu li {
            margin: 0 !important;
            padding: 0 !important;
            border-bottom: none !important;
            width: auto !important;
            text-align: left !important;
        }
        
        /* Desktop vs Mobile visibility */
        .mobile-only {
            display: none !important;
        }
        
        .desktop-only {
            display: block !important;
        }
        
        /* Profile Dropdown - Modern Design */
        .profile-dropdown {
            position: relative !important;
        }
        
        .profile-icon {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 8px 16px !important;
            background: #f8f9fa !important;
            border-radius: 25px !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            text-decoration: none !important;
            color: #333 !important;
            border: 1px solid #e9ecef !important;
        }
        
        .profile-icon:hover {
            background: #e9ecef !important;
            color: #333 !important;
        }
        
        .profile-avatar {
            width: 32px !important;
            height: 32px !important;
            background: #007bff !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 14px !important;
            color: white !important;
        }
        
        .profile-avatar::before {
            content: "\f007" !important;
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
        }
        
        .profile-text {
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #333 !important;
        }
        
        .profile-arrow {
            font-size: 12px !important;
            color: #666 !important;
            transition: transform 0.3s ease !important;
        }
        
        .profile-dropdown.active .profile-arrow {
            transform: rotate(180deg) !important;
        }
        
        .dropdown-menu {
            display: none !important;
            position: absolute !important;
            top: calc(100% + 8px) !important;
            right: 0 !important;
            background: white !important;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
            border-radius: 12px !important;
            min-width: 280px !important;
            z-index: 1000 !important;
            overflow: hidden !important;
            border: 1px solid #e9ecef !important;
        }
        
        .profile-dropdown:hover .dropdown-menu,
        .profile-dropdown.active .dropdown-menu {
            display: block !important;
        }
        
        .dropdown-menu a {
            display: flex !important;
            align-items: center !important;
            gap: 16px !important;
            padding: 16px 20px !important;
            color: #333 !important;
            border-bottom: 1px solid #f1f3f4 !important;
            transition: all 0.3s ease !important;
            font-size: 14px !important;
            text-decoration: none !important;
        }
        
        .dropdown-menu a:last-child {
            border-bottom: none !important;
        }
        
        .dropdown-menu a:hover {
            background: #f8f9fa !important;
        }
        
        .menu-icon-box {
            width: 40px !important;
            height: 40px !important;
            background: #f8f9fa !important;
            border-radius: 8px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 16px !important;
            color: #495057 !important;
        }
        
        .menu-icon-box i {
            font-size: 16px !important;
            color: #495057 !important;
        }
        
        .menu-text {
            font-weight: 500 !important;
            color: #333 !important;
        }
        
        .dropdown-divider {
            height: 1px !important;
            background: #e9ecef !important;
            margin: 8px 0 !important;
        }
        
        .logout-item:hover {
            background: #fff5f5 !important;
        }
        
        .logout-item .menu-icon-box {
            background: #fee2e2 !important;
        }
        
        /* Mobile Styles - SIDE NAVIGATION DRAWER */
        @media (max-width: 768px) {
            .mobile-nav-icons {
                display: flex !important;
            }
            
            body .mobile-menu-toggle {
                display: block !important;
            }
            
            /* Side Navigation Overlay */
            .nav-overlay {
                display: none !important;
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 100% !important;
                background: rgba(0, 0, 0, 0.5) !important;
                z-index: 1999 !important;
            }
            
            .nav-overlay.active {
                display: block !important;
            }
            
            /* Side Navigation Menu */
            body .nav-menu {
                display: block !important;
                position: fixed !important;
                top: 0 !important;
                right: -100% !important;
                width: 320px !important;
                height: 100vh !important;
                background: white !important;
                flex-direction: column !important;
                box-shadow: -5px 0 15px rgba(0,0,0,0.1) !important;
                padding: 0 !important;
                gap: 0 !important;
                margin: 0 !important;
                z-index: 2000 !important;
                transition: right 0.3s ease !important;
                overflow-y: auto !important;
            }
            
            body .nav-menu.active {
                right: 0 !important;
            }
            
            /* Mobile Menu Header */
            .mobile-menu-header {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                padding: 20px !important;
                border-bottom: 1px solid #eee !important;
                background: #f8f9fa !important;
            }
            
            .mobile-menu-close {
                background: none !important;
                border: none !important;
                font-size: 24px !important;
                cursor: pointer !important;
                color: #666 !important;
            }
            
            /* Mobile Menu Items */
            body .nav-menu li {
                width: 100% !important;
                text-align: left !important;
                border-bottom: 1px solid #f1f3f4 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            body .nav-menu li:last-child {
                border-bottom: none !important;
            }
            
            body .nav-menu a {
                display: flex !important;
                align-items: center !important;
                gap: 16px !important;
                padding: 16px 20px !important;
                width: 100% !important;
                border-bottom: none !important;
                font-size: 16px !important;
                color: #333 !important;
                text-decoration: none !important;
            }
            
            body .nav-menu a i {
                font-size: 16px !important;
                color: #495057 !important;
                width: 20px !important;
                text-align: center !important;
            }
            
            body .nav-menu a:hover {
                background: #f8f9fa !important;
            }
            
            /* Profile Section in Mobile */
            .mobile-profile-section {
                padding: 20px !important;
                background: #f8f9fa !important;
                border-bottom: 1px solid #eee !important;
            }
            
            .mobile-profile-info {
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
            }
            
            .mobile-profile-avatar {
                width: 48px !important;
                height: 48px !important;
                background: #007bff !important;
                border-radius: 50% !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                font-size: 18px !important;
                color: white !important;
            }
            
            .mobile-profile-avatar::before {
                content: "\f007" !important;
                font-family: "Font Awesome 6 Free" !important;
                font-weight: 900 !important;
            }
            
            .mobile-profile-text {
                font-size: 16px !important;
                font-weight: 600 !important;
                color: #333 !important;
            }
            
            /* Hide desktop profile dropdown on mobile */
            .profile-dropdown {
                display: none !important;
            }
            
            /* Show mobile-only items */
            .mobile-only {
                display: block !important;
            }
            
            /* Hide desktop-only items */
            .desktop-only {
                display: none !important;
            }
        }
        
        /* Desktop Search Styles */
        .search-container {
            width: 300px !important;
            padding: 0 !important;
            border-bottom: none !important;
        }
        
        .search-form {
            width: 100% !important;
        }
        
        .search-input-group {
            display: flex !important;
            width: 100% !important;
            background: #f8f9fa !important;
            border-radius: 20px !important;
            overflow: hidden !important;
            border: 1px solid #e9ecef !important;
            transition: all 0.3s ease !important;
        }
        
        .search-input-group:focus-within {
            border-color: #007bff !important;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25) !important;
        }
        
        .search-input {
            flex: 1 !important;
            padding: 8px 40px 8px 15px !important;
            border: none !important;
            background: transparent !important;
            font-size: 13px !important;
            outline: none !important;
        }
        
        .search-btn {
            background: #007bff !important;
            color: white !important;
            border: none !important;
            padding: 8px 12px !important;
            cursor: pointer !important;
            transition: background 0.3s ease !important;
        }
        
        /* Desktop Search Bar */
        .desktop-search-bar {
            flex: 0 0 300px;
            margin: 0 20px;
        }
        
        .desktop-search-form {
            width: 100%;
        }
        
        .desktop-search-input-group {
            display: flex;
            width: 100%;
            background: #f8f9fa;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .desktop-search-input-group:focus-within {
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        
        .desktop-search-input {
            flex: 1;
            padding: 8px 15px;
            border: none;
            background: transparent;
            font-size: 13px;
            outline: none;
        }
        
        .desktop-search-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .desktop-search-btn:hover {
            background: #0056b3;
        }
        }
    </style>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="navbar">
            <div class="logo">
                <a href="<?php echo $base_url; ?>/index.php">💪 FitSupps</a>
            </div>
            
            <!-- Mobile Icons (Search + Hamburger) -->
            <div class="mobile-nav-icons">
                <button class="mobile-search-btn" onclick="window.location.href='<?php echo $base_url; ?>/products.php'">
                    <i class="fas fa-search"></i>
                </button>
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                    <span>≡</span>
                </button>
            </div>
            
            <!-- Desktop Search Bar -->
            <div class="desktop-search-bar desktop-only">
                <form action="<?php echo $base_url; ?>/products.php" method="GET" class="desktop-search-form">
                    <div class="desktop-search-input-group">
                        <input type="text" 
                               name="search" 
                               placeholder="Search products..." 
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                               class="desktop-search-input">
                        <button type="submit" class="desktop-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Mobile Navigation Overlay -->
            <div class="nav-overlay" id="navOverlay" onclick="closeMobileMenu()"></div>
            
            <ul class="nav-menu" id="navMenu">
                <!-- Mobile Menu Header -->
                <li class="mobile-menu-header" style="display: none;">
                    <div class="logo">💪 FitSupps</div>
                    <button class="mobile-menu-close" onclick="closeMobileMenu()">✕</button>
                </li>
                
                <!-- Mobile Profile Section -->
                <?php if (isLoggedIn()): ?>
                <li class="mobile-profile-section" style="display: none;">
                    <div class="mobile-profile-info">
                        <div class="mobile-profile-avatar"></div>
                        <div class="mobile-profile-text">Hello, User</div>
                    </div>
                </li>
                <?php endif; ?>
                
                <!-- Navigation Items -->
                <li class="desktop-only"><a href="<?php echo $base_url; ?>/index.php"><i class="fas fa-home"></i> Home</a></li>
                <li class="desktop-only"><a href="<?php echo $base_url; ?>/products.php"><i class="fas fa-store"></i> Products</a></li>
                <?php if (isLoggedIn()): ?>
                    <li class="desktop-only">
                        <a href="<?php echo $base_url; ?>/cart.php">
                            <i class="fas fa-shopping-cart"></i> Cart
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
                                <span style="background: #ef4444; color: white; border-radius: 50%; padding: 2px 8px; font-size: 12px; margin-left: 8px;">
                                    <?php echo $cart_count > 9 ? '9+' : $cart_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <!-- Desktop Profile Dropdown -->
                    <li class="profile-dropdown desktop-only">
                        <a href="#" class="profile-icon" onclick="toggleProfileDropdown(event)">
                            <span class="profile-avatar"></span>
                            <span class="profile-text">Hello, User</span>
                            <span class="profile-arrow">▼</span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="<?php echo $base_url; ?>/account/profile.php">
                                <div class="menu-icon-box">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span class="menu-text">My Profile</span>
                            </a>
                            <a href="<?php echo $base_url; ?>/account/orders.php">
                                <div class="menu-icon-box">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <span class="menu-text">My Orders</span>
                            </a>
                            <a href="<?php echo $base_url; ?>/account/addresses.php">
                                <div class="menu-icon-box">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <span class="menu-text">My Address</span>
                            </a>
                            <a href="<?php echo $base_url; ?>/account/referral.php">
                                <div class="menu-icon-box">
                                    <i class="fas fa-gift"></i>
                                </div>
                                <span class="menu-text">Refer & Earn</span>
                            </a>
                            <a href="<?php echo $base_url; ?>/account/wishlist.php">
                                <div class="menu-icon-box">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <span class="menu-text">Wishlist</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo $base_url; ?>/logout.php" class="logout-item">
                                <div class="menu-icon-box">
                                    <i class="fas fa-sign-out-alt"></i>
                                </div>
                                <span class="menu-text">Logout</span>
                            </a>
                        </div>
                    </li>
                    
                    <!-- Mobile Profile Items (hidden on desktop) -->
                    <li class="mobile-only"><a href="<?php echo $base_url; ?>/account/profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li class="mobile-only"><a href="<?php echo $base_url; ?>/account/addresses.php"><i class="fas fa-map-marker-alt"></i> My Address</a></li>
                    <li class="mobile-only"><a href="<?php echo $base_url; ?>/account/referral.php"><i class="fas fa-gift"></i> Refer & Earn</a></li>
                    <li class="mobile-only"><a href="<?php echo $base_url; ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo $base_url; ?>/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>
    <main class="main-content">
