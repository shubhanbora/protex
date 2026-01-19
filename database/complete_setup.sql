-- =====================================================
-- FitSupps E-Commerce Complete Database Setup
-- =====================================================
-- This file contains everything needed to setup the complete database
-- Run this once in phpMyAdmin or MySQL command line

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS ecommerce_db;
USE ecommerce_db;

-- =====================================================
-- 1. USERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    pincode VARCHAR(10),
    referral_code VARCHAR(10),
    referred_by INT,
    reward_points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================================================
-- 2. ADMINS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Insert default admin (username: admin, password: password)
INSERT INTO admins (username, password, email, full_name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@fitsupps.com', 'FitSupps Administrator')
ON DUPLICATE KEY UPDATE password = VALUES(password);

-- =====================================================
-- 3. CATEGORIES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert FitSupps Categories (Only Required 5 Categories)
INSERT INTO categories (name, description) VALUES 
('Whey Protein Isolate', 'Pure whey protein isolate with 90%+ protein content for lean muscle building'),
('Whey Protein', 'High-quality whey protein concentrate for muscle growth and recovery'),
('Creatine', 'Creatine supplements for increased strength, power, and muscle mass'),
('Gainers', 'Mass gainer supplements for weight gain and muscle building'),
('Protein Wafer Bar', 'Delicious protein bars and wafers for convenient protein intake')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- =====================================================
-- 4. PRODUCTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    weight VARCHAR(50),
    category_id INT,
    image VARCHAR(255),
    image_2 VARCHAR(255),
    image_3 VARCHAR(255),
    image_4 VARCHAR(255),
    image_5 VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    is_deleted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- =====================================================
-- 5. SAMPLE PRODUCTS DATA
-- =====================================================

-- Get category IDs for products (Only 5 Required Categories)
SET @whey_isolate_id = (SELECT id FROM categories WHERE name = 'Whey Protein Isolate' LIMIT 1);
SET @whey_protein_id = (SELECT id FROM categories WHERE name = 'Whey Protein' LIMIT 1);
SET @creatine_id = (SELECT id FROM categories WHERE name = 'Creatine' LIMIT 1);
SET @gainers_id = (SELECT id FROM categories WHERE name = 'Gainers' LIMIT 1);
SET @protein_bar_id = (SELECT id FROM categories WHERE name = 'Protein Wafer Bar' LIMIT 1);

-- Insert Sample Products
INSERT INTO products (name, description, price, stock, weight, category_id, image, status) VALUES 

-- Whey Protein Isolate Products
('Gold Standard 100% Whey Isolate', 'Premium whey protein isolate with 25g protein per serving. Fast absorption and muscle building.', 3499.00, 50, '2 lbs', @whey_isolate_id, 'assets/images/placeholder.jpg', 'active'),
('Dymatize ISO100 Hydrolyzed', 'Hydrolyzed whey protein isolate for fastest absorption. Perfect for post-workout recovery.', 4299.00, 30, '1.6 lbs', @whey_isolate_id, 'assets/images/placeholder.jpg', 'active'),
('Ultimate Nutrition Iso Sensation', 'Ultra-pure whey protein isolate with added digestive enzymes for better absorption.', 3899.00, 25, '2 lbs', @whey_isolate_id, 'assets/images/placeholder.jpg', 'active'),

-- Whey Protein Products  
('Optimum Nutrition Gold Standard Whey', 'The world most popular whey protein with 24g protein per serving. Trusted by athletes worldwide.', 2999.00, 75, '2 lbs', @whey_protein_id, 'assets/images/placeholder.jpg', 'active'),
('MuscleBlaze Whey Protein', 'High-quality whey protein concentrate with great taste and mixability. Made in India.', 1899.00, 100, '2 lbs', @whey_protein_id, 'assets/images/placeholder.jpg', 'active'),
('BSN Syntha-6 Protein', 'Multi-source protein blend with amazing taste. Perfect for any time protein supplementation.', 3299.00, 40, '2.27 lbs', @whey_protein_id, 'assets/images/placeholder.jpg', 'active'),

-- Creatine Products
('Optimum Nutrition Creatine Monohydrate', 'Pure creatine monohydrate for increased strength and power. Unflavored and micronized.', 1299.00, 80, '300g', @creatine_id, 'assets/images/placeholder.jpg', 'active'),
('MuscleBlaze Creatine Monohydrate', 'Premium quality creatine for enhanced performance and muscle growth. Lab tested for purity.', 899.00, 60, '250g', @creatine_id, 'assets/images/placeholder.jpg', 'active'),
('Universal Nutrition Creatine', 'Pharmaceutical grade creatine monohydrate. Increases muscle strength and size.', 1599.00, 45, '500g', @creatine_id, 'assets/images/placeholder.jpg', 'active'),

-- Mass Gainers
('Optimum Nutrition Serious Mass', 'High-calorie mass gainer with 50g protein and 1250 calories per serving. Perfect for hard gainers.', 4599.00, 35, '6 lbs', @gainers_id, 'assets/images/placeholder.jpg', 'active'),
('MuscleBlaze Mass Gainer XXL', 'Indian mass gainer with complex carbs and high protein. Great taste and value for money.', 2799.00, 55, '3 kg', @gainers_id, 'assets/images/placeholder.jpg', 'active'),
('Dymatize Super Mass Gainer', 'Premium mass gainer with digestive enzymes and vitamins. Clean weight gain formula.', 3899.00, 25, '6 lbs', @gainers_id, 'assets/images/placeholder.jpg', 'active'),

-- Protein Bars
('Quest Nutrition Protein Bar', 'Low-carb protein bar with 20g protein and 4g net carbs. Perfect on-the-go snack.', 199.00, 200, '60g', @protein_bar_id, 'assets/images/placeholder.jpg', 'active'),
('MuscleBlaze Protein Bar', 'Indian protein bar with 22g protein and great taste. No added sugar and high fiber.', 149.00, 150, '75g', @protein_bar_id, 'assets/images/placeholder.jpg', 'active'),
('ONE Protein Bar', 'Delicious protein bar with 20g protein and birthday cake flavor. Gluten-free formula.', 179.00, 180, '60g', @protein_bar_id, 'assets/images/placeholder.jpg', 'active');

-- =====================================================
-- 6. ORDERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT,
    billing_address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- 7. ORDER ITEMS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- =====================================================
-- 8. CART TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- =====================================================
-- 9. WISHLIST TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- =====================================================
-- 10. OTP TABLE (for email verification)
-- =====================================================
CREATE TABLE IF NOT EXISTS otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    purpose ENUM('login', 'register', 'reset_password') NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_purpose (email, purpose),
    INDEX idx_expires_at (expires_at)
);

-- =====================================================
-- 11. USER ADDRESSES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS user_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('home', 'work', 'other') DEFAULT 'home',
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address_line_1 VARCHAR(255) NOT NULL,
    address_line_2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    pincode VARCHAR(10) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- 12. INDEXES FOR BETTER PERFORMANCE
-- =====================================================
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_created ON products(created_at);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_cart_user ON cart(user_id);
CREATE INDEX idx_wishlist_user ON wishlist(user_id);

-- =====================================================
-- SETUP COMPLETE MESSAGE
-- =====================================================
SELECT 'FitSupps Database Setup Complete!' as message,
       (SELECT COUNT(*) FROM categories) as categories_count,
       (SELECT COUNT(*) FROM products) as products_count,
       (SELECT COUNT(*) FROM admins) as admin_accounts;

-- Show all categories
SELECT 'Categories Created:' as info;
SELECT id, name, description FROM categories ORDER BY name;

-- Show sample products
SELECT 'Sample Products Created:' as info;
SELECT p.id, p.name, c.name as category, p.price, p.weight, p.stock 
FROM products p 
LEFT JOIN categories c ON p.category_id = c.id 
ORDER BY c.name, p.name 
LIMIT 10;

-- =====================================================
-- ADMIN LOGIN CREDENTIALS
-- =====================================================
SELECT 'ADMIN LOGIN CREDENTIALS:' as info;
SELECT 'Username: admin' as username, 'Password: password' as password;
SELECT 'Login URL: http://localhost/client/admin/login.php' as login_url;