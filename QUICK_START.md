# Quick Start Guide

## 5-Minute Setup

### Step 1: Database Setup (2 minutes)
```bash
# Create database
mysql -u root -p
CREATE DATABASE ecommerce_db;
EXIT;

# Import schema
mysql -u root -p ecommerce_db < database/schema.sql
```

### Step 2: Configure (1 minute)
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Your MySQL password
define('DB_NAME', 'ecommerce_db');
```

### Step 3: Start Server (1 minute)
```bash
# If using XAMPP/WAMP
# Just start Apache and MySQL services

# Or use PHP built-in server
php -S localhost:8000
```

### Step 4: Access (1 minute)
- **Website**: http://localhost:8000/
- **Admin Panel**: http://localhost:8000/admin/login.php
  - Username: `admin`
  - Password: `admin123`

## Test the System

### Test User Flow:
1. Register a new user
2. Browse products
3. Add products to cart
4. Add delivery address
5. Place an order

### Test Admin Flow:
1. Login to admin panel
2. Add a new product
3. View orders
4. Update order status

## Default Data

The database comes with:
- 1 Admin account (admin/admin123)
- 4 Sample categories
- No products (add via admin panel)

## Common Issues

**Can't connect to database?**
- Check MySQL is running
- Verify credentials in config/database.php

**Admin login not working?**
- Username: admin
- Password: admin123
- Clear browser cache

**Products not showing?**
- Add products via admin panel first
- Check product status is "active"

## Next Steps

1. Change admin password
2. Add product categories (or use existing)
3. Add products with details
4. Test complete purchase flow
5. Customize design as needed

## File Structure Overview

```
├── index.php              # Homepage
├── products.php           # Product listing
├── cart.php              # Shopping cart
├── checkout.php          # Checkout page
├── admin/                # Admin panel
│   ├── login.php        # Admin login
│   ├── dashboard.php    # Dashboard
│   ├── products.php     # Manage products
│   └── orders.php       # Manage orders
├── account/             # User account
│   ├── profile.php      # User profile
│   ├── orders.php       # Order history
│   └── addresses.php    # Address management
└── config/              # Configuration
    └── database.php     # DB config
```

## Features Checklist

### User Features ✓
- [x] Registration & Login
- [x] Product Browsing
- [x] Shopping Cart
- [x] Wishlist
- [x] Multiple Addresses
- [x] Order Placement
- [x] Order Tracking
- [x] Referral System
- [x] Product Reviews

### Admin Features ✓
- [x] Secure Login
- [x] Dashboard
- [x] Product Management
- [x] Order Management
- [x] Database Overview
- [x] Order Status Updates

### Technical Features ✓
- [x] PHP Backend
- [x] MySQL Database
- [x] Session Authentication
- [x] SQL Injection Protection
- [x] Password Hashing
- [x] Responsive Design
- [x] Anime.js Animations
- [x] Clean UI/UX

## Production Deployment

Before going live:
1. Change admin password
2. Update database credentials
3. Enable HTTPS
4. Set proper file permissions
5. Disable error display
6. Enable error logging
7. Regular backups
8. Security audit

## Support

Need help? Check:
- README.md - Full documentation
- INSTALLATION.md - Detailed setup
- Database schema - database/schema.sql

Happy coding! 🚀
