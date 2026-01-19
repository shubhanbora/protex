# FitSupps - E-Commerce Website 💪

A complete protein powder e-commerce website built with PHP, MySQL, and modern responsive design.

## 🚀 Features

### **Customer Features**
- 🛍️ Product catalog with search and filtering
- 🛒 Shopping cart with quantity management
- 👤 User registration and login system
- 📍 Address management for delivery
- 💳 Multiple payment options (COD, Online, Card)
- 🎟️ Coupon system with discounts
- 📧 Order confirmation emails
- 📱 Fully responsive mobile design

### **Admin Features**
- 📊 Modern admin dashboard with statistics
- 📦 Product management (add, edit, delete)
- 🏷️ Category management
- 📋 Order management and tracking
- 👥 User management
- 📈 Sales analytics and reporting

### **Security Features**
- 🔐 Secure user authentication
- 🛡️ SQL injection prevention
- 🔒 Password hashing
- 🚫 CSRF protection
- ✅ Input validation and sanitization

## 🛠️ Technology Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Icons:** Font Awesome 6.4.0
- **Responsive:** CSS Grid & Flexbox

## 📋 Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP (for local development)

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/yourusername/fitsupp.git
cd fitsupp
```

### 2. Database Setup
1. Create a new MySQL database named `ecommerce_db`
2. Import the database structure:
```bash
mysql -u root -p ecommerce_db < database/complete_setup.sql
```

### 3. Configure Database Connection
Edit `config/database.php` with your database credentials:
```php
$host = 'localhost';
$username = 'root';
$password = 'your_password';
$database = 'ecommerce_db';
```

### 4. Set Permissions
Make sure the following directories are writable:
```bash
chmod 755 uploads/products/
chmod 755 assets/images/
```

### 5. Access the Website
- **Frontend:** `http://localhost/fitsupp/`
- **Admin Panel:** `http://localhost/fitsupp/admin/`

## 🔑 Default Login Credentials

### Admin Access
- **URL:** `http://localhost/fitsupp/admin/login.php`
- **Username:** `admin`
- **Password:** `password`

### Test User Account
- **Email:** `test@example.com`
- **Password:** `password123`

## 📁 Project Structure

```
fitsupp/
├── admin/                  # Admin panel
│   ├── dashboard.php      # Admin dashboard
│   ├── products.php       # Product management
│   ├── orders.php         # Order management
│   └── login.php          # Admin login
├── account/               # User account pages
│   ├── profile.php        # User profile
│   ├── orders.php         # Order history
│   └── addresses.php      # Address management
├── api/                   # API endpoints
│   ├── cart.php           # Cart operations
│   └── wishlist.php       # Wishlist operations
├── assets/                # Static assets
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   └── images/            # Images
├── config/                # Configuration files
│   ├── database.php       # Database connection
│   ├── session.php        # Session management
│   └── security.php       # Security functions
├── database/              # Database files
│   └── complete_setup.sql # Database structure
├── includes/              # Common includes
│   ├── header.php         # Site header
│   └── footer.php         # Site footer
├── uploads/               # User uploads
│   └── products/          # Product images
├── index.php              # Homepage
├── products.php           # Product listing
├── product.php            # Product details
├── cart.php               # Shopping cart
├── checkout.php           # Checkout process
├── login.php              # User login
└── register.php           # User registration
```

## 🧪 Testing

### Test Pages Available
- `test-products.php` - Test product functionality
- `test-cart-api.php` - Test cart operations
- `test-checkout.php` - Test checkout process
- `admin/test-admin.php` - Test admin functionality

### Sample Data
The database includes sample products and categories to get you started.

## 🎯 Key Features Explained

### **Shopping Cart**
- Add/remove products
- Update quantities
- Persistent cart (saved in database)
- Real-time price calculations

### **Checkout Process**
1. Select delivery address
2. Choose payment method
3. Apply coupon codes
4. Review order summary
5. Place order

### **Order Management**
- Order tracking
- Status updates
- Email notifications
- Invoice generation

### **Admin Dashboard**
- Sales statistics
- Product management
- Order processing
- User management

## 🎟️ Available Coupons

- **SAVE10** - 10% discount on any order
- **FREESHIP** - Free shipping (can be added)

## 📱 Mobile Responsive

The website is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones
- All screen sizes

## 🔧 Customization

### Adding New Products
1. Login to admin panel
2. Go to Products → Add Product
3. Fill in product details
4. Upload product images
5. Set pricing and stock

### Modifying Design
- Edit CSS files in `assets/css/`
- Modify templates in `includes/`
- Update colors and fonts as needed

### Adding Payment Gateways
- Edit `config/payment.php`
- Add payment processing logic
- Update checkout flow

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error:**
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database exists

**Permission Errors:**
- Set proper file permissions
- Check Apache/PHP user permissions
- Ensure uploads directory is writable

**Admin Login Issues:**
- Use default credentials: admin/password
- Check if admin user exists in database
- Clear browser cache and cookies

## 📈 Performance

### Optimization Tips
- Enable PHP OPcache
- Use MySQL query optimization
- Compress images
- Enable GZIP compression
- Use CDN for static assets

## 🔒 Security

### Security Measures Implemented
- Prepared statements for SQL queries
- Password hashing with PHP's password_hash()
- Input validation and sanitization
- CSRF token protection
- Session security

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 📞 Support

For support and questions:
- Create an issue on GitHub
- Check the troubleshooting section
- Review the test pages for debugging

## 🎉 Acknowledgments

- Font Awesome for icons
- Modern CSS techniques for responsive design
- PHP community for best practices

---

**Made with ❤️ for fitness enthusiasts and protein powder lovers!** 💪

## 🚀 Live Demo

[Add your live demo URL here when deployed]

## 📸 Screenshots

[Add screenshots of your website here]

---

### Quick Start Commands

```bash
# Clone and setup
git clone https://github.com/yourusername/fitsupp.git
cd fitsupp

# Import database
mysql -u root -p ecommerce_db < database/complete_setup.sql

# Start local server (if using PHP built-in server)
php -S localhost:8000
```

**Happy Coding! 🚀**