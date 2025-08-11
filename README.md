# Mama's Oven Uganda - Bakery Website

A complete bakery e-commerce website built with PHP, MySQL, HTML, CSS, JavaScript, Bootstrap, and AJAX. This website allows customers to browse products, add items to cart, place orders, and provides admin functionality for managing products, orders, and customers.

## Features

### Customer Features
- **Browse Products**: View all bakery products with filtering by category and search
- **Product Details**: Detailed product information including flavors, ingredients, and pricing
- **User Registration & Login**: Secure user authentication system
- **Shopping Cart**: Add products to cart, modify quantities, and remove items
- **Order Placement**: Complete checkout process with delivery information
- **Order History**: View past orders and order status
- **Responsive Design**: Mobile-friendly interface using Bootstrap

### Admin Features
- **Admin Dashboard**: Overview of orders, products, customers, and revenue
- **Product Management**: Add, edit, delete, and manage product inventory
- **Order Management**: View orders, update order status, print receipts
- **Customer Management**: View and manage customer accounts
- **Category Management**: Organize products into categories

### Additional Features
- **Contact Form**: Customer inquiries and feedback
- **About Page**: Company information and team details
- **Services Page**: Description of bakery services
- **Email Notifications**: Order confirmations and updates
- **Print-friendly Order Receipts**: Printable order details

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6)
- **CSS Framework**: Bootstrap 5.3
- **JavaScript Libraries**: jQuery 3.6, SweetAlert2
- **Icons**: Font Awesome 6
- **AJAX**: For dynamic content loading and cart management

## Installation Instructions

### Prerequisites
- Web server (Apache/Nginx)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

### Step 1: Clone/Download the Project
```bash
# Clone the repository or download the files to your web server directory
# For XAMPP: C:\xampp\htdocs\
# For LAMP: /var/www/html/
```

### Step 2: Database Setup
1. Create a MySQL database named `mamaove`
2. Import the database schema:
   ```sql
   mysql -u root -p mamaove < database/schema.sql
   ```
   Or manually run the SQL commands in `database/schema.sql`

### Step 3: Configure Database Connection
1. Open `config/database.php`
2. Update the database credentials:
   ```php
   $host = 'localhost';
   $dbname = 'mamaove';
   $username = 'root';          // Your MySQL username
   $password = '!Log19tan88';   // Your MySQL password
   ```

### Step 4: Set Permissions (Linux/Mac)
```bash
chmod 755 -R /var/www/html/mamoven1/
chmod 777 -R /var/www/html/mamoven1/uploads/  # If you create an uploads folder
```

### Step 5: Access the Website
- Open your web browser
- Navigate to: `http://localhost/mamoven1/` or `http://your-domain.com/`

## Default Login Credentials

### Admin Account
- **Username**: admin
- **Password**: admin123
- **Access**: Full admin panel access

### Customer Account
- Register a new customer account through the registration page
- Or create one manually in the database

## File Structure

```
mamoven1/
├── admin/                      # Admin panel files
│   ├── dashboard.php          # Admin dashboard
│   ├── products.php           # Product management
│   ├── orders.php            # Order management
│   └── customers.php         # Customer management
├── api/                       # AJAX API endpoints
│   ├── add_to_cart.php       # Add items to cart
│   ├── get_products.php      # Fetch products
│   ├── place_order.php       # Process orders
│   └── ...                   # Other API endpoints
├── assets/                    # Static assets
│   ├── css/
│   │   └── style.css         # Custom styles
│   ├── js/
│   │   └── main.js           # JavaScript functions
│   └── images/               # Website images
├── auth/                      # Authentication files
│   ├── login.php             # User login
│   ├── register.php          # User registration
│   └── logout.php            # User logout
├── config/                    # Configuration files
│   └── database.php          # Database connection
├── database/                  # Database files
│   └── schema.sql            # Database structure
├── includes/                  # Reusable components
│   ├── header.php            # Site header
│   └── footer.php            # Site footer
├── index.php                 # Homepage
├── products.php              # Products listing
├── cart.php                  # Shopping cart
├── about.php                 # About page
├── contact.php               # Contact page
├── services.php              # Services page
└── README.md                 # This file
```

## Configuration

### Site Settings
You can modify site settings in the `site_settings` table:
- Site name
- Contact information
- Delivery fees
- Minimum order amounts

### Email Configuration
To enable email notifications, configure SMTP settings in your PHP configuration or use a mail service.

### Image Upload
To enable product image uploads:
1. Create an `uploads/` directory
2. Set proper permissions
3. Implement image upload functionality in admin panel

## Security Features

- **Password Hashing**: All passwords are hashed using PHP's `password_hash()`
- **SQL Injection Prevention**: All database queries use prepared statements
- **Session Management**: Secure session handling for user authentication
- **Input Validation**: Server-side validation for all user inputs
- **XSS Prevention**: Output escaping using `htmlspecialchars()`

## Customization

### Colors and Branding
- Modify CSS variables in `assets/css/style.css`
- Update logo and images in `assets/images/`
- Change site name in `config/database.php`

### Adding New Features
- Create new API endpoints in the `api/` directory
- Add new admin pages in the `admin/` directory
- Extend the database schema as needed

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check MySQL credentials in `config/database.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **Permission Denied Errors**
   - Check file permissions (755 for directories, 644 for files)
   - Ensure web server has read access

3. **AJAX Requests Failing**
   - Check browser console for JavaScript errors
   - Verify API endpoint URLs are correct
   - Ensure user is logged in for protected endpoints

4. **Session Issues**
   - Check PHP session configuration
   - Ensure cookies are enabled in browser
   - Verify session directory permissions

## Browser Support

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance Optimization

- **Database Indexing**: Key columns are indexed for better performance
- **Image Optimization**: Compress images before uploading
- **CSS/JS Minification**: Consider minifying assets for production
- **Caching**: Implement caching strategies for better performance

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Support

For support and questions:
- Email: info@mamasovenug.com
- Phone: +256 700 123456

## License

This project is open-source and available under the MIT License.

---

Built with ❤️ for Mama's Oven Uganda
