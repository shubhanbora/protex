# 🚀 FitSupps Deployment Guide

This guide will help you deploy your FitSupps e-commerce website to various platforms.

## 📋 Pre-Deployment Checklist

### ✅ Before Pushing to GitHub
- [ ] Remove sensitive data from config files
- [ ] Update database credentials to use environment variables
- [ ] Test all functionality locally
- [ ] Ensure all images and uploads are working
- [ ] Verify admin panel access
- [ ] Test checkout process end-to-end

### ✅ Files to Check
- [ ] `config/database.php` - Use example file for GitHub
- [ ] `uploads/products/` - Only include sample images
- [ ] Remove any test files with sensitive data
- [ ] Check `.gitignore` is properly configured

## 🌐 Deployment Options

### 1. 📱 GitHub Pages (Static Demo)
**Note:** GitHub Pages only supports static files, not PHP. Use for showcasing design only.

```bash
# Create a static version for demo
git checkout -b gh-pages
# Convert PHP to HTML for demo purposes
git push origin gh-pages
```

### 2. 🔥 Firebase Hosting (Static Demo)
```bash
npm install -g firebase-tools
firebase login
firebase init hosting
firebase deploy
```

### 3. ☁️ Heroku (Full PHP App)
```bash
# Install Heroku CLI
# Create Procfile
echo "web: vendor/bin/heroku-php-apache2 public/" > Procfile

# Create composer.json
echo '{"require":{"php":"^7.4.0"}}' > composer.json

# Deploy
heroku create your-app-name
git push heroku main
```

### 4. 🌊 DigitalOcean Droplet
1. Create a droplet with LAMP stack
2. Upload files via SFTP
3. Configure Apache virtual host
4. Import database
5. Set file permissions

### 5. 📦 Shared Hosting (cPanel)
1. Upload files to public_html
2. Create MySQL database via cPanel
3. Import database via phpMyAdmin
4. Update config files
5. Set folder permissions

## 🗄️ Database Deployment

### For Production Deployment

1. **Export your current database:**
```bash
mysqldump -u root -p ecommerce_db > fitsupp_production.sql
```

2. **Clean sensitive data:**
```sql
-- Remove test users (keep admin)
DELETE FROM users WHERE email != 'admin@fitsupp.com';

-- Clear test orders
DELETE FROM orders WHERE id > 0;
DELETE FROM order_items WHERE id > 0;

-- Reset cart
DELETE FROM cart WHERE id > 0;

-- Keep sample products and categories
```

3. **Create production database script:**
```bash
# Create clean production database
mysql -u root -p -e "CREATE DATABASE fitsupp_production;"
mysql -u root -p fitsupp_production < database/complete_setup.sql
```

### Environment Variables Setup

Create `.env` file (don't commit to GitHub):
```env
DB_HOST=localhost
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_DATABASE=ecommerce_db
DB_PORT=3306

ADMIN_EMAIL=admin@yoursite.com
SITE_URL=https://yoursite.com
```

Update `config/database.php` to use environment variables:
```php
$host = $_ENV['DB_HOST'] ?? 'localhost';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';
$database = $_ENV['DB_DATABASE'] ?? 'ecommerce_db';
```

## 🔧 Server Configuration

### Apache .htaccess (Already included)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name yoursite.com;
    root /var/www/fitsupp;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🔒 Security for Production

### 1. Update Default Credentials
```sql
-- Change admin password
UPDATE admins SET password = '$2y$10$newhashedpassword' WHERE username = 'admin';

-- Create new admin user
INSERT INTO admins (username, email, password) VALUES 
('youradmin', 'admin@yoursite.com', '$2y$10$hashedpassword');
```

### 2. File Permissions
```bash
# Set proper permissions
chmod 755 /var/www/fitsupp
chmod 644 /var/www/fitsupp/*.php
chmod 755 /var/www/fitsupp/uploads
chmod 644 /var/www/fitsupp/uploads/products/*
```

### 3. Hide Sensitive Files
```apache
# In .htaccess
<Files "config/*.php">
    Order allow,deny
    Deny from all
</Files>

<Files "*.sql">
    Order allow,deny
    Deny from all
</Files>
```

## 📊 Performance Optimization

### 1. Enable PHP OPcache
```ini
; In php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
```

### 2. Database Optimization
```sql
-- Add indexes for better performance
ALTER TABLE products ADD INDEX idx_status_deleted (status, is_deleted);
ALTER TABLE orders ADD INDEX idx_user_status (user_id, order_status);
ALTER TABLE cart ADD INDEX idx_user_product (user_id, product_id);
```

### 3. Image Optimization
```bash
# Compress images
find uploads/products -name "*.jpg" -exec jpegoptim --max=85 {} \;
find uploads/products -name "*.png" -exec optipng -o2 {} \;
```

## 🧪 Testing Production

### 1. Functionality Tests
- [ ] User registration and login
- [ ] Product browsing and search
- [ ] Add to cart functionality
- [ ] Checkout process
- [ ] Order placement
- [ ] Admin panel access
- [ ] Product management
- [ ] Order management

### 2. Performance Tests
- [ ] Page load times
- [ ] Database query performance
- [ ] Image loading speed
- [ ] Mobile responsiveness

### 3. Security Tests
- [ ] SQL injection attempts
- [ ] XSS protection
- [ ] File upload security
- [ ] Admin access protection

## 🚀 GitHub Repository Setup

### 1. Create Repository
```bash
# Initialize git (if not already done)
git init

# Add all files
git add .

# Commit
git commit -m "Initial commit: FitSupps E-commerce Website"

# Add remote
git remote add origin https://github.com/yourusername/fitsupp.git

# Push to GitHub
git push -u origin main
```

### 2. Repository Settings
- [ ] Add description: "Complete PHP e-commerce website for protein powder sales"
- [ ] Add topics: `php`, `mysql`, `ecommerce`, `responsive`, `shopping-cart`
- [ ] Enable Issues for bug reports
- [ ] Add license (MIT recommended)
- [ ] Create releases for versions

### 3. Documentation
- [ ] Complete README.md
- [ ] Add screenshots to repository
- [ ] Create CHANGELOG.md for updates
- [ ] Add API documentation if needed

## 📱 Mobile App Integration (Future)

### API Endpoints Ready
- `/api/cart.php` - Cart operations
- `/api/wishlist.php` - Wishlist operations
- Can be extended for mobile app

### JSON Responses
All API endpoints return JSON for easy mobile integration.

## 🔄 Continuous Deployment

### GitHub Actions (Optional)
Create `.github/workflows/deploy.yml`:
```yaml
name: Deploy to Production
on:
  push:
    branches: [ main ]
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v2
    - name: Deploy to server
      run: |
        # Add deployment script here
```

## 📈 Monitoring

### 1. Error Logging
```php
// In config/database.php
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php_errors.log');
```

### 2. Analytics
- Add Google Analytics
- Monitor user behavior
- Track conversion rates

## 🎯 Post-Deployment

### 1. SEO Optimization
- [ ] Add meta descriptions
- [ ] Optimize images with alt tags
- [ ] Create sitemap.xml
- [ ] Submit to search engines

### 2. Marketing
- [ ] Set up social media links
- [ ] Create email marketing campaigns
- [ ] Add customer reviews system
- [ ] Implement referral program

---

**Your FitSupps website is ready for the world! 🌍💪**

Need help with deployment? Check the troubleshooting section or create an issue on GitHub.