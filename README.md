# Mama's Oven Uganda - Bakery E-Commerce Platform

A full-featured bakery e-commerce website built with PHP, MySQL, HTML, CSS, JavaScript, Bootstrap, and AJAX. This platform enables customers to browse bakery products, manage shopping carts, place orders with cash-on-delivery, and track order history. Administrators can manage products, orders, customers, and categories through a dedicated admin panel.

## 🎯 Features

### Customer Features
- **Product Browsing**: View all bakery products with category filtering and search functionality
- **Product Details**: Comprehensive product pages with descriptions, flavors, ingredients, and pricing
- **User Authentication**: Secure registration and login system with password recovery
- **Shopping Cart**: Real-time cart management with AJAX (add, update quantities, remove items)
- **Order Placement**: Streamlined checkout with delivery address and special instructions
- **Order Tracking**: View complete order history with detailed receipts
- **Printable Receipts**: Professional receipt generation for all orders
- **Email Notifications**: Automated order confirmations and welcome emails
- **Responsive Design**: Mobile-first design using Bootstrap 5
- **Contact Form**: Direct messaging with admin via email

### Admin Features
- **Dashboard Analytics**: Real-time statistics on products, customers, orders, and revenue
- **Product Management**: Full CRUD operations for products with base64 image encoding
- **Order Management**: View, update order status, and print receipts
- **Customer Management**: View and manage customer accounts
- **Category Management**: Organize products into categories
- **Secure Access**: Role-based authentication (admin/customer)

### Technical Features
- **Base64 Image Storage**: Products images stored directly in database as base64 strings
- **AJAX-Powered Cart**: Dynamic cart updates without page reloads
- **PHPMailer Integration**: Professional email notifications using SMTP
- **Session Management**: Secure session handling across the platform
- **SQL Injection Protection**: All queries use prepared statements
- **XSS Prevention**: Output escaping with `htmlspecialchars()`
- **Password Security**: Bcrypt hashing for all passwords
- **Environment Variables**: `.env` support for sensitive configuration

## 🛠️ Technology Stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 7.4+ |
| **Database** | MySQL 5.7+ / MariaDB |
| **Frontend** | HTML5, CSS3, JavaScript (ES6) |
| **CSS Framework** | Bootstrap 5.3 |
| **JavaScript Libraries** | jQuery 3.7, SweetAlert2 |
| **Email Service** | PHPMailer 6.x |
| **Icons** | Font Awesome 6.4 |
| **Server** | Apache 2.4+ / Nginx |

## 📦 Installation

### Prerequisites
- PHP 7.4 or higher (with extensions: `pdo_mysql`, `mbstring`, `openssl`)
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server
- Composer (optional, for PHPMailer)

### Step 1: Clone the Repository
```bash
# Clone to your web server directory
# XAMPP: C:\xampp\htdocs\mamoven1
# LAMP: /var/www/html/mamoven1
cd /var/www/html
git clone <repository-url> mamoven1
cd mamoven1
```

### Step 2: Install Dependencies
```bash
# Install PHPMailer via Composer
composer install
```

### Step 3: Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE mamaove CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p mamaove < database/schema.sql
```

### Step 4: Environment Configuration
```bash
# Copy example environment file
cp .env.example .env

# Edit .env with your credentials
nano .env
```

Configure the following in `.env`:
```env
# Database Configuration
DB_HOST=127.0.0.1
DB_NAME=mamaove
DB_USER=root
DB_PASS=your_secure_password
DB_CHARSET=utf8mb4

# Application Settings
BASE_URL=http://localhost/mamoven1
SITE_NAME=Mama's Oven

# SMTP Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
SMTP_SECURE=tls
ADMIN_EMAIL=admin@mamasoven.com
```

### Step 5: Set Permissions (Linux/macOS)
```bash
# Set directory permissions
chmod 755 -R /var/www/html/mamoven1/
chmod 644 /var/www/html/mamoven1/.env

# Set ownership (if needed)
sudo chown -R www-data:www-data /var/www/html/mamoven1/
```

### Step 6: Access the Application
Navigate to:
- **Frontend**: `http://localhost/mamoven1/`
- **Admin Panel**: `http://localhost/mamoven1/admin/dashboard.php`

## 🔐 Default Credentials

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`
- **Access**: Full admin panel

### Creating Customer Accounts
Customers can register via: `http://localhost/mamoven1/auth/register.php`

## 📁 Project Structure

```
mamoven1/
├── admin/                          # Admin panel
│   ├── dashboard.php              # Analytics dashboard
│   ├── products.php               # Product management
│   ├── add_product.php            # Add new products
│   ├── edit_product.php           # Edit products
│   ├── orders.php                 # Order management
│   ├── order_details.php          # Order details view
│   ├── customers.php              # Customer management
│   ├── edit_customer.php          # Edit customers
│   ├── categories.php             # Category management
│   └── includes/
│       ├── header.php             # Admin header
│       └── footer.php             # Admin footer
├── api/                           # AJAX API endpoints
│   ├── add_to_cart.php           # Add items to cart
│   ├── get_cart_count.php        # Get cart item count
│   ├── get_cart_items.php        # Fetch cart contents
│   ├── update_cart.php           # Update quantities
│   ├── remove_from_cart.php      # Remove items
│   ├── clear_cart.php            # Empty cart
│   ├── place_order.php           # Process checkout
│   └── get_products.php          # Fetch product list
├── assets/                        # Static assets
│   ├── css/
│   │   └── style.css             # Custom styles
│   ├── js/
│   │   └── main.js               # JavaScript functions
│   ├── images/                    # Logo, placeholders
│   └── image2/                    # Additional images
├── auth/                          # Authentication
│   ├── login.php                 # User login
│   ├── register.php              # Registration
│   ├── logout.php                # Logout handler
│   ├── forgot_password.php       # Password reset request
│   └── reset_password.php        # Password reset form
├── config/                        # Configuration
│   └── database.php              # DB connection & constants
├── database/                      # Database files
│   └── schema.sql                # Database structure
├── footer_pages/                  # Footer links
│   ├── faq.php                   # FAQ page
│   ├── privacy_policy.php        # Privacy policy
│   ├── shipping_returns.php      # Shipping & returns
│   └── terms_of_service.php      # Terms of service
├── includes/                      # Reusable components
│   ├── header.php                # Site header/nav
│   └── footer.php                # Site footer
├── vendor/                        # Composer dependencies
├── .env                          # Environment variables (gitignored)
├── .env.example                  # Environment template
├── .gitignore                    # Git ignore rules
├── index.php                     # Homepage
├── products.php                  # Products listing
├── product-details.php           # Single product page
├── cart.php                      # Shopping cart page
├── orders.php                    # Order history
├── print_receipt.php             # Printable receipt
├── about.php                     # About us page
├── contact.php                   # Contact form
├── services.php                  # Services page
├── composer.json                 # Composer dependencies
└── README.md                     # This file
```

## 🔧 Configuration

### Site Settings
Edit constants in [`config/database.php`](config/database.php):
```php
define('BASE_URL', 'http://localhost/mamoven1');
define('SITE_NAME', "Mama's Oven");
define('ADMIN_EMAIL', 'admin@mamasoven.com');
```

### SMTP Email Setup

#### Gmail Configuration
1. Enable 2-Factor Authentication
2. Generate App Password: [Google Account Security](https://myaccount.google.com/security)
3. Add to `.env`:
```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-16-char-app-password
SMTP_SECURE=tls
```

### Image Storage
Products use **base64 encoded images** stored directly in the database:
- Automatic conversion during upload in [`admin/add_product.php`](admin/add_product.php)
- Images limited to 2MB
- Supported formats: JPG, PNG, GIF, WEBP
- Fallback to [`assets/images/placeholder.jpg`](assets/images/placeholder.jpg)

## 🔒 Security Features

| Feature | Implementation |
|---------|----------------|
| **Password Hashing** | Bcrypt via `password_hash()` |
| **SQL Injection Prevention** | PDO prepared statements |
| **XSS Protection** | `htmlspecialchars()` on all output |
| **CSRF Protection** | Session-based verification |
| **Session Security** | `session_regenerate_id()` after login |
| **Role-Based Access** | Admin/customer role checking |
| **Input Validation** | Server-side validation on all forms |
| **Environment Variables** | `.env` for sensitive data |

## 🎨 Customization

### Branding & Colors
Edit CSS variables in [`assets/css/style.css`](assets/css/style.css):
```css
:root {
    --color-primary: #5A331F;    /* Deep warm brown */
    --color-secondary: #F5D1B1;  /* Soft peach */
    --color-accent: #F39C6A;     /* Orange accent */
    --font-heading: 'Playfair Display', serif;
    --font-body: 'Lato', sans-serif;
}
```

### Logo
Replace [`assets/images/logo.jpeg`](assets/images/logo.jpeg) with your logo (recommended: 200x80px)

### Adding Features
1. **New Page**: Create PHP file, include [`includes/header.php`](includes/header.php) and [`includes/footer.php`](includes/footer.php)
2. **API Endpoint**: Add to [`api/`](api/) directory with JSON response
3. **Admin Page**: Add to [`admin/`](admin/) directory, include [`admin/includes/header.php`](admin/includes/header.php)

## 🐛 Troubleshooting

### Database Connection Error
```bash
# Check credentials in .env
cat .env | grep DB_

# Test connection
php -r "require 'config/database.php'; echo 'DB OK';"

# Verify MySQL is running
sudo systemctl status mysql
```

### SMTP Email Failures
```bash
# Check SMTP credentials
cat .env | grep SMTP_

# Enable debug in contact.php temporarily
$mail->SMTPDebug = 2;

# Check error logs
tail -f /var/log/apache2/error.log
```

### Cart Not Working
1. Check browser console for JavaScript errors
2. Verify [`assets/js/main.js`](assets/js/main.js) is loaded
3. Ensure user is logged in
4. Check session configuration in `php.ini`

### Image Upload Issues
```bash
# Check file permissions
ls -la admin/add_product.php

# Verify image conversion
# Images are base64 encoded, not uploaded to filesystem
# Check database column type is LONGTEXT
```

### Session Issues
```bash
# Check session directory permissions
ls -ld /var/lib/php/sessions

# Set correct permissions
sudo chmod 1733 /var/lib/php/sessions

# Restart web server
sudo systemctl restart apache2
```

## 🚀 Deployment

### Production Checklist
- [ ] Change default admin password
- [ ] Update `.env` with production credentials
- [ ] Set `display_errors = Off` in `php.ini`
- [ ] Enable HTTPS/SSL
- [ ] Configure firewall rules
- [ ] Set up database backups
- [ ] Configure email service (SendGrid/Mailgun recommended)
- [ ] Optimize images (use WebP format)
- [ ] Enable PHP OPcache
- [ ] Set up monitoring (logs, uptime)

### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName mamasoven.com
    DocumentRoot /var/www/html/mamoven1
    
    <Directory /var/www/html/mamoven1>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/mamasoven_error.log
    CustomLog ${APACHE_LOG_DIR}/mamasoven_access.log combined
</VirtualHost>
```

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name mamasoven.com;
    root /var/www/html/mamoven1;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.env {
        deny all;
    }
}
```

## 🔐 Protecting Secrets

### Never Commit These Files
```gitignore
.env
vendor/
node_modules/
.DS_Store
*.log
```

### If Secrets Were Exposed
1. **Rotate credentials immediately**
   - Database password
   - SMTP app password
   - Admin account password

2. **Clean Git history**
```bash
# Install git-filter-repo
pip install git-filter-repo

# Remove sensitive data
git filter-repo --path .env --invert-paths
git filter-repo --replace-text <(echo 'EXPOSED_PASSWORD==>***REDACTED***')

# Force push
git push --force --all
git push --force --tags
```

3. **Use environment variables in production**
   - GitHub Actions: Repository Secrets
   - Server: Export in `.bashrc` or use secret manager

## 📊 Database Schema Highlights

### Products Table
- `image` (LONGTEXT): Base64 encoded images
- `flavours` (TEXT): Comma-separated flavor options
- `ingredients` (TEXT): Product ingredients
- `featured` (TINYINT): Featured product flag

### Orders Table
- `order_number` (VARCHAR): Unique order identifier
- `delivery_address` (TEXT): Customer delivery location
- `delivery_phone` (VARCHAR): Contact number
- `special_instructions` (TEXT): Customer notes

## 🌐 Browser Support

| Browser | Version |
|---------|---------|
| Chrome | 90+ |
| Firefox | 88+ |
| Safari | 14+ |
| Edge | 90+ |
| Mobile Safari | iOS 14+ |
| Chrome Mobile | Latest |

## 📈 Performance Tips

- **Database Indexing**: Products, orders, and users tables have indexes
- **Image Optimization**: Compress images before upload (max 2MB)
- **Caching**: Enable browser caching for static assets
- **CDN**: Use CDN for Bootstrap/jQuery in production
- **Minification**: Minify CSS/JS before deployment
- **PHP OPcache**: Enable for faster PHP execution

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is proprietary software developed for Mama's Oven Uganda.

## 📞 Support

For questions or issues:
- **Email**: josbertaijuka15@gmail.com
- **Phone**: +256 759 420168
- **GitHub Issues**: [Create an issue](https://github.com/yourusername/mamoven1/issues)

## 🙏 Acknowledgments

- Bootstrap 5 for responsive framework
- Font Awesome for icons
- SweetAlert2 for beautiful alerts
- PHPMailer for email functionality
- jQuery for AJAX operations

---

**Built with ❤️ for Mama's Oven Uganda** | Serving freshly baked delights across Kampala
