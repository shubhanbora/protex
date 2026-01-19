-- =====================================================
-- Missing Tables for FitSupps E-Commerce
-- Run this in phpMyAdmin to create missing tables
-- =====================================================

USE ecommerce_db;

-- =====================================================
-- REVIEWS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_review (user_id, product_id)
);

-- =====================================================
-- ADDRESSES TABLE (for multiple delivery addresses)
-- =====================================================
CREATE TABLE IF NOT EXISTS addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    mobile VARCHAR(15) NOT NULL,
    flat_house VARCHAR(255) NOT NULL,
    locality VARCHAR(255) NOT NULL,
    landmark VARCHAR(255),
    pincode VARCHAR(10) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- UPDATE ORDERS TABLE (add missing columns)
-- =====================================================
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS address_id INT,
ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS discount DECIMAL(10,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS shipping_charges DECIMAL(10,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS coupon_code VARCHAR(50),
ADD COLUMN IF NOT EXISTS order_status VARCHAR(50) DEFAULT 'pending';

-- Add foreign key for address if not exists
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'ecommerce_db' 
                  AND TABLE_NAME = 'orders' 
                  AND CONSTRAINT_NAME = 'fk_orders_address');

SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE orders ADD CONSTRAINT fk_orders_address FOREIGN KEY (address_id) REFERENCES addresses(id)', 
    'SELECT "Foreign key already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- UPDATE ORDER_ITEMS TABLE (add missing columns)
-- =====================================================
ALTER TABLE order_items 
ADD COLUMN IF NOT EXISTS product_name VARCHAR(255),
ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10,2);

-- =====================================================
-- SAMPLE REVIEWS DATA
-- =====================================================
-- Insert sample reviews (only if products exist)
INSERT IGNORE INTO reviews (user_id, product_id, rating, comment) 
SELECT 1, p.id, 5, 'Excellent product! Highly recommended for muscle building.'
FROM products p 
WHERE p.name LIKE '%Gold Standard%' 
LIMIT 1;

INSERT IGNORE INTO reviews (user_id, product_id, rating, comment) 
SELECT 1, p.id, 4, 'Good quality protein with great taste and mixability.'
FROM products p 
WHERE p.name LIKE '%MuscleBlaze%' 
LIMIT 1;

INSERT IGNORE INTO reviews (user_id, product_id, rating, comment) 
SELECT 1, p.id, 5, 'Best creatine supplement for strength gains!'
FROM products p 
WHERE p.name LIKE '%Creatine%' 
LIMIT 1;

-- =====================================================
-- SUCCESS MESSAGE
-- =====================================================
SELECT 'Missing tables created successfully!' as message;