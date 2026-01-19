<?php
$pageTitle = 'FitSupps - Premium Fitness Supplements';
require_once 'config/database.php';
require_once 'config/session.php';

// Require login to access homepage
requireLogin();

$conn = getDBConnection();
if (!$conn) {
    die("Database connection failed");
}

// Fetch featured products - simple query
$featured_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   WHERE p.status = 'active' AND p.is_deleted = 0 
                   ORDER BY p.created_at DESC 
                   LIMIT 8";
$featured_result = $conn->query($featured_query);

// Get categories for quick navigation
$categories_query = "SELECT * FROM categories ORDER BY name LIMIT 6";
$categories_result = $conn->query($categories_query);

// Get specific categories for horizontal section
$specific_categories = [
    'Whey Protein Isolate' => 'fas fa-dumbbell',
    'Whey Protein' => 'fas fa-flask', 
    'Creatine' => 'fas fa-bolt',
    'Gainers' => 'fas fa-weight-hanging',
    'Protein Wafer Bar' => 'fas fa-cookie-bite'
];

$horizontal_categories = [];

// Auto-add categories if they don't exist
foreach ($specific_categories as $cat_name => $icon) {
    $cat_name_escaped = $conn->real_escape_string($cat_name);
    $cat_query = "SELECT id, name FROM categories WHERE name = '$cat_name_escaped' LIMIT 1";
    $cat_result = $conn->query($cat_query);
    
    if ($cat_result && $cat_result->num_rows > 0) {
        $cat_row = $cat_result->fetch_assoc();
        // Category exists
        $horizontal_categories[] = [
            'id' => $cat_row['id'],
            'name' => $cat_row['name'],
            'icon' => $icon
        ];
    } else {
        // Category doesn't exist, create it
        $descriptions = [
            'Whey Protein Isolate' => 'Pure whey protein isolate with 90%+ protein content for lean muscle building',
            'Whey Protein' => 'High-quality whey protein concentrate for muscle growth and recovery',
            'Creatine' => 'Creatine supplements for increased strength, power, and muscle mass',
            'Gainers' => 'Mass gainer supplements for weight gain and muscle building',
            'Protein Wafer Bar' => 'Delicious protein bars and wafers for convenient protein intake'
        ];
        
        $description = $conn->real_escape_string($descriptions[$cat_name]);
        
        $insert_query = "INSERT INTO categories (name, description) VALUES ('$cat_name_escaped', '$description')";
        if ($conn->query($insert_query)) {
            $new_id = $conn->insert_id;
            $horizontal_categories[] = [
                'id' => $new_id,
                'name' => $cat_name,
                'icon' => $icon
            ];
        }
    }
}

require_once 'includes/header.php';
?>

<style>
/* Hero Section with Image Slider */
.hero-section {
    position: relative;
    height: 70vh;
    min-height: 500px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Image Slider */
.hero-slider {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.slider-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    opacity: 0;
    transition: opacity 1s ease-in-out;
}

.slider-image.active {
    opacity: 1;
}

/* Special positioning for protein container visibility */
.slider-image:nth-child(1) {
    background-position: 60% center; /* Show protein container better */
}

.slider-image:nth-child(2) {
    background-position: 65% center;
}

.slider-image:nth-child(3) {
    background-position: 55% center;
}

.slider-image:nth-child(4) {
    background-position: 70% center;
}

/* Default gradient backgrounds if no images */
.slider-image:nth-child(1) {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.slider-image:nth-child(2) {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.slider-image:nth-child(3) {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.slider-image:nth-child(4) {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

/* Overlay for better text readability */
.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    z-index: 2;
}

.hero-content {
    position: relative;
    z-index: 3;
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
    color: white;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 900;
    margin-bottom: 15px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    line-height: 1.1;
    animation: slideInUp 1s ease-out;
}

.hero-subtitle {
    font-size: 1.3rem;
    margin-bottom: 25px;
    opacity: 0.95;
    font-weight: 300;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
    animation: slideInUp 1s ease-out 0.3s both;
}

.hero-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
    animation: slideInUp 1s ease-out 0.6s both;
}



/* Slider Navigation Dots */
.slider-dots {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    z-index: 4;
}

.slider-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: all 0.3s ease;
}

.slider-dot.active {
    background: white;
    transform: scale(1.2);
}



/* Animations */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.btn-hero {
    padding: 14px 28px;
    font-size: 1.05rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-radius: 50px;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border: 2px solid transparent;
}

.btn-hero-primary {
    background: #000000;
    color: white;
    border-color: #000000;
}

.btn-hero-primary:hover {
    background: white;
    color: #000000;
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    text-decoration: none;
}

.btn-hero-secondary {
    background: transparent;
    color: white;
    border-color: white;
}

.btn-hero-secondary:hover {
    background: white;
    color: #333;
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(255, 255, 255, 0.3);
    text-decoration: none;
}



/* Mobile Responsive */
@media (max-width: 768px) {
    .hero-section {
        height: 60vh;
        min-height: 400px;
    }
    
    .hero-title {
        font-size: 2.2rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
    }
    
    .hero-buttons {
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }
    
    .btn-hero {
        padding: 12px 24px;
        font-size: 0.95rem;
        width: 220px;
        justify-content: center;
    }
    
    .slider-dots {
        bottom: 20px;
    }
    
    /* Mobile image positioning for better protein container visibility */
    .slider-image {
        background-position: center right;
    }
}

@media (max-width: 480px) {
    .hero-section {
        height: 50vh;
        min-height: 350px;
    }
    
    .hero-title {
        font-size: 1.8rem;
        margin-bottom: 12px;
    }
    
    .hero-subtitle {
        font-size: 1rem;
        margin-bottom: 18px;
    }
    
    .btn-hero {
        padding: 10px 20px;
        font-size: 0.85rem;
        width: 200px;
    }
    
    .slider-dots {
        bottom: 15px;
    }
    
    /* Small mobile - focus on protein container */
    .slider-image {
        background-position: 70% center;
        background-size: cover;
    }
}




/* Categories Horizontal Section */
.categories-horizontal-section {
    padding: 30px 0;
    background: white;
    border-bottom: 1px solid #f1f5f9;
}

.categories-horizontal-scroll {
    display: flex;
    gap: 30px;
    overflow-x: auto;
    padding: 10px 0 20px 0;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.categories-horizontal-scroll::-webkit-scrollbar {
    display: none;
}

.category-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: #64748b;
    min-width: 85px;
    transition: all 0.3s ease;
}

.category-item:hover {
    color: #007bff;
    text-decoration: none;
    transform: translateY(-2px);
}

.category-icon-circle {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    transition: all 0.3s ease;
    border: 2px solid #e2e8f0;
}

.category-item:hover .category-icon-circle {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border-color: #007bff;
    transform: scale(1.05);
}

.category-icon-circle i {
    font-size: 1.5rem;
    color: #64748b;
    transition: color 0.3s ease;
}

.category-item:hover .category-icon-circle i {
    color: white;
}

.category-label {
    font-size: 0.8rem;
    font-weight: 500;
    text-align: center;
    line-height: 1.2;
    max-width: 85px;
}

/* Features Section */
.features-section {
    padding: 80px 0;
    background: white;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
}

.feature-card {
    text-align: center;
    padding: 40px 20px;
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    font-size: 2rem;
    color: white;
}

.feature-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 15px;
}

.feature-description {
    color: #64748b;
    line-height: 1.6;
}

/* Products Section - Horizontal Carousel */
.products-section {
    padding: 80px 0;
    background: #f8fafc;
}

.products-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.products-tabs {
    display: flex;
    gap: 5px;
    background: #e2e8f0;
    padding: 4px;
    border-radius: 8px;
}

.tab-button {
    background: none;
    border: none;
    padding: 8px 16px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    border-radius: 6px;
    transition: all 0.2s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tab-button.active {
    background: #1a1a1a;
    color: white;
}

.tab-button:hover:not(.active) {
    background: #cbd5e0;
    color: #1a1a1a;
}

.products-carousel-container {
    position: relative;
    overflow: hidden;
}

.products-carousel {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    scroll-behavior: smooth;
    padding: 10px 0 20px 0;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.products-carousel::-webkit-scrollbar {
    display: none;
}

.carousel-product-card {
    flex: 0 0 280px;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid #f1f5f9;
    position: relative;
}

.carousel-product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
}

.carousel-image-container {
    position: relative;
    height: 220px;
    background: #f8fafc;
    overflow: hidden;
}

.carousel-product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.carousel-product-card:hover .carousel-product-image {
    transform: scale(1.05);
}

.carousel-discount-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: #10b981;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 700;
    z-index: 2;
}

.carousel-new-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background: #f59e0b;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 700;
    z-index: 2;
}

.carousel-wishlist-btn {
    position: absolute;
    bottom: 12px;
    right: 12px;
    background: rgba(255, 255, 255, 0.9);
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 2;
}

.carousel-wishlist-btn:hover {
    background: #fee2e2;
    color: #dc2626;
}

.carousel-product-info {
    padding: 20px;
}

.carousel-product-category {
    color: #667eea;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 6px;
}

.carousel-product-name {
    font-size: 1rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.carousel-product-rating {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 12px;
}

.carousel-stars {
    color: #fbbf24;
    font-size: 0.8rem;
}

.carousel-rating-text {
    color: #9ca3af;
    font-size: 0.75rem;
}

.carousel-price-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
}

.carousel-product-price {
    font-size: 1.3rem;
    font-weight: 800;
    color: #059669;
}

.carousel-original-price {
    font-size: 0.9rem;
    color: #9ca3af;
    text-decoration: line-through;
    margin-left: 6px;
}

.carousel-product-actions {
    display: flex;
    gap: 8px;
}

.carousel-btn-view {
    flex: 1;
    background: #000000;
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.carousel-btn-view:hover {
    background: #1a1a1a;
    transform: translateY(-1px);
    color: white;
    text-decoration: none;
}

.carousel-btn-cart {
    background: #10b981;
    color: white;
    border: none;
    padding: 10px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.carousel-btn-cart:hover {
    background: #059669;
    transform: translateY(-1px);
}

.carousel-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 3;
}

.carousel-nav:hover {
    background: rgba(0, 0, 0, 0.9);
    transform: translateY(-50%) scale(1.1);
}

.carousel-nav-prev {
    left: -20px;
}

.carousel-nav-next {
    right: -20px;
}

.view-all-btn {
    background: #1a1a1a;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.view-all-btn:hover {
    background: #000000;
    transform: translateY(-2px);
    text-decoration: none;
    color: white;
}

/* Responsive Design - Mobile First Approach */
@media (max-width: 480px) {
    /* Container */
    .container {
        padding: 0 15px;
    }
    
    /* Hero Section */
    .hero-title {
        font-size: 2rem;
        line-height: 1.1;
        margin-bottom: 15px;
    }
    
    .hero-subtitle {
        font-size: 1rem;
        margin-bottom: 25px;
    }
    
    .hero-buttons {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .btn-hero {
        padding: 14px 24px;
        font-size: 1rem;
        justify-content: center;
    }
    
    /* Categories Section */
    .section-title {
        font-size: 1.8rem;
        margin-bottom: 15px;
    }
    
    .section-subtitle {
        font-size: 1rem;
        margin-bottom: 30px;
    }
    
    /* Categories Horizontal Section - Mobile */
    .categories-horizontal-section {
        padding: 20px 0;
    }
    
    .categories-horizontal-scroll {
        gap: 15px;
        padding: 10px 0 15px 0;
    }
    
    .category-item {
        min-width: 70px;
    }
    
    .category-icon-circle {
        width: 50px;
        height: 50px;
        margin-bottom: 6px;
    }
    
    .category-icon-circle i {
        font-size: 1.2rem;
    }
    
    .category-label {
        font-size: 0.7rem;
        max-width: 70px;
        line-height: 1.1;
    }
    
    /* Features Section */
    .features-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .feature-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
        margin-bottom: 20px;
    }
    
    .feature-title {
        font-size: 1.2rem;
    }
    
    /* Products Section */
    .products-header {
        flex-direction: column;
        gap: 20px;
        align-items: stretch;
        text-align: center;
    }
    
    .products-tabs {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .tab-button {
        padding: 10px 16px;
        font-size: 0.8rem;
    }
    
    .carousel-product-card {
        flex: 0 0 220px;
    }
    
    .carousel-image-container {
        height: 180px;
    }
    
    .carousel-product-info {
        padding: 15px;
    }
    
    .carousel-product-name {
        font-size: 0.9rem;
        margin-bottom: 6px;
    }
    
    .carousel-product-price {
        font-size: 1.1rem;
    }
    
    .carousel-btn-view {
        padding: 8px 12px;
        font-size: 0.75rem;
    }
    
    .carousel-btn-cart {
        padding: 8px 10px;
    }
    
    .carousel-nav {
        display: none;
    }
    
    .view-all-btn {
        padding: 12px 20px;
        font-size: 0.9rem;
    }
    
    /* CTA Section */
    .cta-title {
        font-size: 1.8rem;
        margin-bottom: 15px;
    }
    
    .cta-description {
        font-size: 1rem;
        margin-bottom: 30px;
    }
    
    /* Buttons */
    .btn {
        padding: 10px 20px;
        font-size: 0.85rem;
    }
    
    .btn-sm {
        padding: 6px 12px;
        font-size: 0.75rem;
    }
    
    .btn-lg {
        padding: 14px 28px;
        font-size: 1rem;
    }
    
    /* Forms */
    .form-control {
        padding: 10px 14px;
        font-size: 0.9rem;
    }
    
    /* Cards */
    .card {
        padding: 20px;
        margin-bottom: 20px;
    }
    
    /* Product Cards */
    .product-card {
        margin-bottom: 20px;
    }
    
    .product-info {
        padding: 15px;
    }
    
    .product-name {
        font-size: 1rem;
    }
    
    .product-price {
        font-size: 1.2rem;
    }
}

@media (max-width: 768px) {
    /* Hero Section */
    .hero-section {
        padding: 60px 0;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
    }
    
    .hero-buttons {
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }
    
    /* Stats Section */
    .stats-section {
        padding: 40px 0;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
    }
    
    /* Features Section */
    .features-section {
        padding: 60px 0;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
        gap: 35px;
    }
    
    /* Categories Section - Tablet */
    .categories-horizontal-section {
        padding: 25px 0;
    }
    
    .categories-horizontal-scroll {
        gap: 20px;
        padding: 10px 0 18px 0;
    }
    
    .category-item {
        min-width: 75px;
    }
    
    .category-icon-circle {
        width: 55px;
        height: 55px;
        margin-bottom: 7px;
    }
    
    .category-icon-circle i {
        font-size: 1.3rem;
    }
    
    .category-label {
        font-size: 0.75rem;
        max-width: 75px;
    }
    
    /* Products Section */
    .products-section {
        padding: 60px 0;
    }
    
    .products-header {
        flex-direction: column;
        gap: 25px;
        text-align: center;
    }
    
    .carousel-product-card {
        flex: 0 0 240px;
    }
    
    .carousel-nav {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }
    
    .carousel-nav-prev {
        left: -15px;
    }
    
    .carousel-nav-next {
        right: -15px;
    }
    
    /* CTA Section */
    .cta-section {
        padding: 60px 0;
    }
    
    .cta-title {
        font-size: 2rem;
    }
    
    /* Footer */
    .main-footer {
        padding: 2rem 0 1rem;
    }
    
    .footer-content {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        text-align: center;
    }
}

@media (max-width: 1024px) {
    /* Hero Section */
    .hero-title {
        font-size: 3rem;
    }
    
    /* Stats Section */
    .stats-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
    }
    
    .features-grid {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 35px;
    }
    
    /* Products Section */
    .carousel-product-card {
        flex: 0 0 260px;
    }
}

/* Landscape Phone */
@media (max-width: 667px) and (orientation: landscape) {
    .hero-section {
        padding: 40px 0;
    }
    
    .hero-title {
        font-size: 2.2rem;
    }
    

}

/* Large Screens */
@media (min-width: 1200px) {
    .container {
        max-width: 1200px;
    }
    
    .hero-title {
        font-size: 4.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.5rem;
    }
    
    .carousel-product-card {
        flex: 0 0 300px;
    }
    
    .features-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* Extra Large Screens */
@media (min-width: 1400px) {
    .container {
        max-width: 1400px;
    }
    
    .carousel-product-card {
        flex: 0 0 320px;
    }
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
    .btn:hover {
        transform: none;
    }
    
    .product-card:hover {
        transform: none;
    }
    
    .carousel-product-card:hover {
        transform: none;
    }
    

    
    /* Increase touch targets */
    .btn {
        min-height: 44px;
        min-width: 44px;
    }
    
    .carousel-wishlist-btn {
        width: 44px;
        height: 44px;
    }
    
    .tab-button {
        min-height: 44px;
        padding: 12px 20px;
    }
}

/* High DPI Displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .hero-section::before {
        background-size: 50px 50px;
    }
}

/* Print Styles */
@media print {
    .hero-section,
    .cta-section {
        background: white !important;
        color: black !important;
    }
    
    .btn,
    .carousel-nav,
    .carousel-wishlist-btn {
        display: none !important;
    }
    
    .product-card,
    .carousel-product-card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ccc !important;
    }
}

/* CTA Section */
.cta-section {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d3748 100%);
    color: white;
    padding: 80px 0;
    text-align: center;
}

.cta-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 20px;
}

.cta-description {
    font-size: 1.2rem;
    margin-bottom: 40px;
    opacity: 0.9;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .products-header {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
}
</style>

<!-- Hero Section with Image Slider -->
<section class="hero-section">
    <!-- Image Slider -->
    <div class="hero-slider">
        <div class="slider-image active" 
             style="background-image: url('assets/images/slider/slide1.jpg')"
             data-mobile="assets/images/slider/mobile/slide1.png"></div>
        <div class="slider-image" 
             style="background-image: url('assets/images/slider/slide2.jpg')"
             data-mobile="assets/images/slider/mobile/slide2.png"></div>
        <div class="slider-image" 
             style="background-image: url('assets/images/slider/slide3.jpg')"
             data-mobile="assets/images/slider/mobile/slide3.png"></div>
        <div class="slider-image" 
             style="background-image: url('assets/images/slider/slide4.jpg')"
             data-mobile="assets/images/slider/mobile/slide4.png"></div>
    </div>
    
    <!-- Overlay for better text readability -->
    <div class="hero-overlay"></div>
    
    <!-- Hero Content -->
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">💪 FUEL YOUR FITNESS</h1>
            <p class="hero-subtitle">Premium supplements for peak performance and maximum gains</p>
            <div class="hero-buttons">
                <a href="products.php" class="btn-hero btn-hero-primary">
                    <i class="fas fa-dumbbell"></i>
                    SHOP NOW
                </a>
                <a href="products.php" class="btn-hero btn-hero-secondary">
                    <i class="fas fa-store"></i>
                    EXPLORE PRODUCTS
                </a>
            </div>
        </div>
    </div>
    

    
    <!-- Slider Dots -->
    <div class="slider-dots">
        <span class="slider-dot active" onclick="currentSlide(1)"></span>
        <span class="slider-dot" onclick="currentSlide(2)"></span>
        <span class="slider-dot" onclick="currentSlide(3)"></span>
        <span class="slider-dot" onclick="currentSlide(4)"></span>
    </div>
</section>



<!-- Categories Section - Horizontal Scroll -->
<section class="categories-horizontal-section">
    <div class="container">
        <div class="categories-horizontal-scroll">
            <?php foreach ($horizontal_categories as $category): ?>
                <a href="products.php?category=<?php echo $category['id']; ?>" class="category-item">
                    <div class="category-icon-circle">
                        <i class="<?php echo $category['icon']; ?>"></i>
                    </div>
                    <span class="category-label"><?php echo htmlspecialchars($category['name']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="products-section">
    <div class="container">
        <div class="products-header">
            <div>
                <h2 class="section-title">EXPLORE</h2>
                <p class="section-subtitle">Discover premium fitness supplements for your goals</p>
            </div>
            <div class="products-tabs">
                <button class="tab-button active" onclick="switchTab('new')">NEW</button>
                <button class="tab-button" onclick="switchTab('featured')">FEATURED</button>
                <button class="tab-button" onclick="switchTab('bestsellers')">TOP SELLERS</button>
            </div>
        </div>
        
        <?php if ($featured_result && $featured_result->num_rows > 0): ?>
            <div class="products-carousel-container">
                <button class="carousel-nav carousel-nav-prev" onclick="scrollCarousel('prev')">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="carousel-nav carousel-nav-next" onclick="scrollCarousel('next')">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <div class="products-carousel" id="productsCarousel">
                    <?php 
                    $featured_result->data_seek(0);
                    while ($product = $featured_result->fetch_assoc()): 
                        $discount = rand(15, 35);
                        $original_price = $product['price'] * (1 + $discount/100);
                        $is_new = (strtotime($product['created_at']) > strtotime('-30 days'));
                        $rating = rand(35, 50) / 10;
                    ?>
                        <div class="carousel-product-card">
                            <div class="carousel-image-container">
                                <img src="<?php echo htmlspecialchars($product['image'] ?: 'assets/images/placeholder.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="carousel-product-image">
                                
                                <!-- Discount Badge -->
                                <div class="carousel-discount-badge">-<?php echo $discount; ?>%</div>
                                
                                <!-- New Badge -->
                                <?php if ($is_new): ?>
                                    <div class="carousel-new-badge">NEW & IMPROVED</div>
                                <?php endif; ?>
                                
                                <!-- Wishlist Button -->
                                <button class="carousel-wishlist-btn" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                            
                            <div class="carousel-product-info">
                                <div class="carousel-product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'SUPPLEMENTS'); ?></div>
                                <h3 class="carousel-product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                
                                <!-- Rating -->
                                <div class="carousel-product-rating">
                                    <div class="carousel-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= floor($rating)): ?>
                                                <i class="fas fa-star"></i>
                                            <?php elseif ($i <= ceil($rating)): ?>
                                                <i class="fas fa-star-half-alt"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="carousel-rating-text"><?php echo number_format($rating, 1); ?> (<?php echo rand(10, 200); ?>)</span>
                                </div>
                                
                                <div class="carousel-price-section">
                                    <div>
                                        <span class="carousel-product-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                        <span class="carousel-original-price">₹<?php echo number_format($original_price, 2); ?></span>
                                    </div>
                                </div>
                                
                                <div class="carousel-product-actions">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="carousel-btn-view">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </a>
                                    <?php if (isLoggedIn()): ?>
                                        <button onclick="addToCart(<?php echo $product['id']; ?>)" class="carousel-btn-cart">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 40px;">
                <a href="products.php" class="view-all-btn">
                    <i class="fas fa-th-large"></i>
                    View All Products
                </a>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px; background: white; border-radius: 20px;">
                <i class="fas fa-dumbbell" style="font-size: 4rem; color: #d1d5db; margin-bottom: 20px;"></i>
                <h3>Products Coming Soon</h3>
                <p style="color: #64748b;">We're stocking up on premium supplements for you!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <h2 class="section-title">Why Choose FitSupps?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <h3 class="feature-title">Premium Quality</h3>
                <p class="feature-description">Lab-tested supplements with the highest purity standards and quality assurance.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <h3 class="feature-title">Fast Delivery</h3>
                <p class="feature-description">Quick and secure shipping with tracking. Get your supplements delivered in 2-3 days.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <h3 class="feature-title">Expert Support</h3>
                <p class="feature-description">Professional guidance from certified nutritionists and fitness experts.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2 class="cta-title">Ready to Transform Your Fitness?</h2>
        <p class="cta-description">Join thousands of satisfied customers who trust FitSupps for their supplement needs</p>
        <div class="hero-buttons">
            <a href="products.php" class="btn-hero btn-hero-primary">
                <i class="fas fa-shopping-cart"></i>
                Start Shopping
            </a>
            <a href="register.php" class="btn-hero btn-hero-secondary">
                <i class="fas fa-user-plus"></i>
                Join Community
            </a>
        </div>
    </div>
</section>

<script>
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

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Carousel functionality
function scrollCarousel(direction) {
    const carousel = document.getElementById('productsCarousel');
    const cardWidth = 300; // Card width + gap
    const scrollAmount = cardWidth * 2; // Scroll 2 cards at a time
    
    if (direction === 'prev') {
        carousel.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });
    } else {
        carousel.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    }
}

// Tab switching functionality
function switchTab(tabName) {
    // Update active tab
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Here you can add AJAX functionality to load different products
    console.log('Switched to tab:', tabName);
    
    // For now, just scroll to beginning
    const carousel = document.getElementById('productsCarousel');
    if (carousel) {
        carousel.scrollTo({
            left: 0,
            behavior: 'smooth'
        });
    }
}

// Auto-scroll carousel (optional)
let autoScrollInterval;

function startAutoScroll() {
    autoScrollInterval = setInterval(() => {
        const carousel = document.getElementById('productsCarousel');
        if (carousel) {
            const maxScroll = carousel.scrollWidth - carousel.clientWidth;
            if (carousel.scrollLeft >= maxScroll) {
                carousel.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                scrollCarousel('next');
            }
        }
    }, 5000); // Auto-scroll every 5 seconds
}

function stopAutoScroll() {
    if (autoScrollInterval) {
        clearInterval(autoScrollInterval);
    }
}

// Start auto-scroll when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Uncomment to enable auto-scroll
    // startAutoScroll();
    
    // Stop auto-scroll when user interacts with carousel
    const carousel = document.getElementById('productsCarousel');
    if (carousel) {
        carousel.addEventListener('mouseenter', stopAutoScroll);
        carousel.addEventListener('mouseleave', startAutoScroll);
        carousel.addEventListener('touchstart', stopAutoScroll);
    }
});

// Touch/swipe support for mobile
let startX = 0;
let scrollLeft = 0;

document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('productsCarousel');
    if (carousel) {
        carousel.addEventListener('touchstart', (e) => {
            startX = e.touches[0].pageX - carousel.offsetLeft;
            scrollLeft = carousel.scrollLeft;
        });
        
        carousel.addEventListener('touchmove', (e) => {
            e.preventDefault();
            const x = e.touches[0].pageX - carousel.offsetLeft;
            const walk = (x - startX) * 2;
            carousel.scrollLeft = scrollLeft - walk;
        });
    }
});
</script>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>

<script>
// Image Slider Functionality
document.addEventListener('DOMContentLoaded', function() {
    let currentSlideIndex = 0;
    const slides = document.querySelectorAll('.slider-image');
    const dots = document.querySelectorAll('.slider-dot');
    const totalSlides = slides.length;
    let slideInterval;

    // Function to check if device is mobile
    function isMobile() {
        return window.innerWidth <= 768;
    }

    // Function to load appropriate images based on screen size
    function loadResponsiveImages() {
        slides.forEach(slide => {
            if (isMobile()) {
                const mobileImage = slide.getAttribute('data-mobile');
                if (mobileImage) {
                    slide.style.backgroundImage = `url('${mobileImage}')`;
                    console.log('Loading mobile image:', mobileImage);
                }
            } else {
                // Load desktop image
                const currentBg = slide.style.backgroundImage;
                if (currentBg.includes('/mobile/')) {
                    // Replace mobile path with desktop path
                    const desktopImage = currentBg.replace('/mobile/', '/');
                    slide.style.backgroundImage = desktopImage;
                    console.log('Loading desktop image:', desktopImage);
                }
            }
        });
    }

    // Function to show specific slide
    function showSlide(index) {
        // Remove active class from all slides and dots
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        // Add active class to current slide and dot
        slides[index].classList.add('active');
        dots[index].classList.add('active');
        
        currentSlideIndex = index;
        console.log('Showing slide:', index + 1);
    }

    // Function to go to next slide
    function nextSlide() {
        const nextIndex = (currentSlideIndex + 1) % totalSlides;
        showSlide(nextIndex);
    }

    // Function to go to previous slide
    function prevSlide() {
        const prevIndex = (currentSlideIndex - 1 + totalSlides) % totalSlides;
        showSlide(prevIndex);
    }

    // Auto slide function
    function startAutoSlide() {
        slideInterval = setInterval(nextSlide, 4000); // Change slide every 4 seconds
        console.log('Auto slide started - 4 seconds interval');
    }

    // Stop auto slide
    function stopAutoSlide() {
        clearInterval(slideInterval);
        console.log('Auto slide stopped');
    }

    // Manual slide change function (removed - no controls)
    // Jump to specific slide (removed - no controls)

    // Pause on hover
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        heroSection.addEventListener('mouseenter', stopAutoSlide);
        heroSection.addEventListener('mouseleave', startAutoSlide);
    }

    // Touch/Swipe support for mobile
    let touchStartX = 0;
    let touchEndX = 0;

    heroSection.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });

    heroSection.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });

    function handleSwipe() {
        const swipeThreshold = 50;
        const diff = touchStartX - touchEndX;
        
        if (Math.abs(diff) > swipeThreshold) {
            stopAutoSlide();
            if (diff > 0) {
                // Swipe left - next slide
                nextSlide();
            } else {
                // Swipe right - previous slide
                prevSlide();
            }
            setTimeout(startAutoSlide, 2000);
        }
    }

    // Load responsive images on page load
    loadResponsiveImages();

    // Reload images on window resize
    window.addEventListener('resize', function() {
        loadResponsiveImages();
    });

    // Initialize slider
    console.log('Image slider initialized with', totalSlides, 'slides');
    startAutoSlide();
    
    // Preload images for smooth transitions
    const imageUrls = [
        'assets/images/slider/slide1.jpg',
        'assets/images/slider/slide2.jpg',
        'assets/images/slider/slide3.jpg',
        'assets/images/slider/slide4.jpg',
        'assets/images/slider/mobile/slide1.png',
        'assets/images/slider/mobile/slide2.png',
        'assets/images/slider/mobile/slide3.png',
        'assets/images/slider/mobile/slide4.png'
    ];
    
    imageUrls.forEach(url => {
        const img = new Image();
        img.src = url;
    });
});
</script>
