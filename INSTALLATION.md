# Installation Guide

## Step-by-Step Installation

### 1. System Requirements

Before installing, ensure you have:
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache or Nginx web server
- phpMyAdmin (optional, for easier database management)

### 2. Download and Extract

Download the project files and extract them to your web server directory:
- **XAMPP**: `C:\xampp\htdocs\ecommerce`
- **WAMP**: `C:\wamp\www\ecommerce`
- **Linux**: `/var/www/html/ecommerce`

### 3. Create Database

**Option A: Using phpMyAdmin**
1. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
2. Click "New" to create a new database
3. Name it `ecommerce_db`
4. Set collation to `utf8mb4_general_ci`
5. Click "Create"

**Option B: Using MySQL Command Line**
```bash
mysql -u root -p
CREATE DATABASE ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
EXIT;
```

### 4. Import Database Schema

**Option A: Using phpMyAdmin**
1. Select the `ecommerce_db` database
2. Click on "Import" tab
3. Click "Choose File" and select `database/schema.sql`
4. Click "Go" to import

**Option B: Using MySQL Command Line**
```bash
mysql -u root -p ecommerce_db < database/schema.sql
```

### 5. Configure Database Connection

1. Open `config/database.php`
2. Update the following constants with your database credentials:

```php
define('DB_HOST', 'localhost');      // Usually 'localhost'
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', '');               // Your MySQL password
define('DB_NAME', 'ecommerce_db');   // Database name
```

### 6. Configure Web Server

**For Apache (XAMPP/WAMP)**
- The `.htaccess` file is already included
- Ensure `mod_rewrite` is enabled in Apache configuration
- Restart Apache server

**For Nginx**
Add this to your server block:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 7. Set File Permissions (Linux/Mac)

```bash
chmod -R 755 /path/to/ecommerce
chmod -R 777 /path/to/ecommerce/assets/images
```

### 8. Test Installation

1. Open your browser
2. Navigate to `http://localhost/ecommerce/` (adjust path as needed)
3. You should see the homepage

### 9. Access Admin Panel

1. Navigate to `http://localhost/ecommerce/admin/login.php`
2. Login with default credentials:
   - **Username**: admin
   - **Password**: admin123

**IMPORTANT**: Change the admin password immediately after first login!

### 10. Add Sample Products (Optional)

You can add sample products through the admin panel:
1. Login to admin panel
2. Go to "Products" → "Add New Product"
3. Fill in product details
4. Save

## Troubleshooting

### Database Connection Error
- Verify database credentials in `config/database.php`
- Ensure MySQL service is running
- Check if database `ecommerce_db` exists

### Page Not Found (404)
- Check if `.htaccess` file exists
- Ensure `mod_rewrite` is enabled in Apache
- Verify file paths are correct

### Session Issues
- Check PHP session configuration
- Ensure `session.save_path` is writable
- Clear browser cookies and cache

### Images Not Loading
- Check file permissions on `assets/images` folder
- Verify image paths in database
- Ensure images exist in the specified location

### Admin Panel Not Accessible
- Verify admin credentials in database
- Check if `admins` table has data
- Clear browser cache

## Default Admin Password Reset

If you need to reset the admin password:

1. Generate a new password hash:
```php
<?php
echo password_hash('your_new_password', PASSWORD_DEFAULT);
?>
```

2. Update the database:
```sql
UPDATE admins SET password = 'generated_hash' WHERE username = 'admin';
```

## Adding Sample Categories

Run this SQL to add sample categories:
```sql
INSERT INTO categories (name, description) VALUES 
('Electronics', 'Electronic devices and gadgets'),
('Clothing', 'Fashion and apparel'),
('Books', 'Books and literature'),
('Home & Kitchen', 'Home and kitchen essentials'),
('Sports', 'Sports equipment and accessories'),
('Toys', 'Toys and games');
```

## Next Steps

After successful installation:
1. Change admin password
2. Add product categories
3. Add products with images
4. Test user registration and login
5. Test complete purchase flow
6. Configure email settings (if needed)

## Support

If you encounter any issues during installation:
1. Check the error logs (PHP error log, Apache error log)
2. Verify all requirements are met
3. Double-check configuration files
4. Refer to the README.md for additional information

## Security Recommendations

After installation:
1. Change default admin credentials
2. Use strong passwords
3. Keep PHP and MySQL updated
4. Enable HTTPS in production
5. Regular database backups
6. Restrict file permissions appropriately
