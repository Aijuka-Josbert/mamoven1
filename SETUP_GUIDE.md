# MamaOven Enhancement Setup Guide

## 🎯 Overview
This document outlines all the enhancements made to the MamaOven food ordering system. The system has been completely redesigned with modern UI, new features, and improved functionality.

---

## ✅ What's Been Completed

### 1. **Database Schema Enhancements** ✓
**File:** `/db/migrations/add_new_tables.sql`

**New Tables Created:**
- `reviews` - Customer reviews with 1-5 star ratings and comments
- `testimonials` - Customer testimonials with approval workflow
- `promo_codes` - Discount codes with expiration and usage limits
- `promo_usage` - Track promo code usage per customer
- `product_images` - Support for multiple images per product
- `email_logs` - Track email notifications

**Existing Tables Modified:**
- `users` - Made phone column NOT NULL (required)
- `orders` - Added promo_code_id, discount_amount, updated_at fields
- `products` - Added stock_quantity, low_stock_threshold fields

### 2. **User Authentication & Registration** ✓
**File:** `/auth/register.php`

**Changes:**
- Phone number is now REQUIRED during registration
- Phone validation supports international formats (+256, (123) 456-7890, etc.)
- Phone field includes helpful placeholder (+256 XXX XXX XXX)
- Phone is stored in user profile for order suggestions

### 3. **Improved Checkout Experience** ✓
**File:** `/cart.php`

**Features:**
- Auto-suggests saved phone number during checkout (can be overridden)
- Auto-suggests saved delivery address during checkout (can be edited)
- Added promo code input field in checkout modal
- Visual order summary with all costs clearly displayed
- "Special Instructions" field for custom delivery notes

### 4. **Enhanced Receipt Printing** ✓
**File:** `/print_receipt.php`

**Improvements:**
- Displays business address from site_settings
- Displays business phone and email from site_settings
- Shows customer's delivery phone number
- Includes special instructions if provided
- Professional layout suitable for printing or emailing

### 5. **Modern UI & Styling** ✓
**File:** `/assets/css/style.css`

**Added:**
- Warm color palette: Deep brown (#5A331F), Soft peach (#F5D1B1), Orange accent (#F39C6A)
- Review cards with 5-star rating display
- Testimonial cards with hover effects
- Product stock badges (In Stock, Low Stock, Out of Stock)
- Promo code input styling
- Customer profile cards
- Order status timeline
- WhatsApp button styling (fixed position, bottom-right)
- Responsive design for all screen sizes
- Print styles for receipts

### 6. **Product Reviews & Ratings System** ✓
**Files:**
- `/api/submit_review.php` - Submit/update product reviews
- `/api/get_reviews.php` - Fetch reviews with average rating

**Features:**
- 1-5 star rating system
- Comments up to 500 characters
- Shows average rating and review count
- Only one review per product per customer (upsert logic)
- Automatic average rating calculation

**Integration:**
- Reviews display on product details page
- Review form for logged-in customers
- AJAX-based submission with real-time updates

### 7. **Product Stock Availability** ✓
**Files:**
- `/products.php` - Product listing page
- `/product-details.php` - Product detail page

**Features:**
- Green "In Stock" badge for stock > 10 units
- Yellow "Low Stock" badge with remaining qty for stock 1-10
- Red "Out of Stock" badge with disable button for stock = 0
- Stock quantity tracked per product
- Out-of-stock products disable add-to-cart button

### 8. **Order Cancellation** ✓
**File:** `/api/cancel_order.php`

**Features:**
- Cancel pending or processing orders only
- Atomic transaction: Restores stock AND updates status together
- Logs cancellation activity for audit trail
- Confirmation dialog before cancellation
- Email notification to admin about cancellation

**Integration:**
- Cancel button on Orders page (pending/processing only)
- Cancel button on Customer Profile page (pending/processing only)
- Prevents cancellation of delivered/completed orders

### 9. **Promo Codes & Discounts** ✓
**File:** `/api/apply_promo.php`

**Features:**
- Validate promo code existence and expiration
- Check usage limits (max_uses per code)
- Verify minimum order amount requirement
- Calculate discount (percentage or fixed amount)
- Prevent duplicate usage by same customer
- Track usage in promo_usage table

**Integration:**
- Promo code input in checkout modal
- Applied via AJAX with instant discount display
- Discount displayed in order confirmation email
- Discount amount stored with order

### 10. **Customer Profile Management** ✓
**File:** `/customer_profile.php`

**Features:**
- View full customer profile (name, email, phone, address)
- Edit profile information (name, phone, address)
- Email cannot be changed (contact support)
- Display recent 5 orders with status
- Cancel buttons for pending/processing orders
- Profile avatar with customer initials
- All changes saved to database

### 11. **Testimonials System** ✓
**Files:**
- `/about.php` - Display approved testimonials
- `/admin/testimonials.php` - Admin management interface

**Admin Features:**
- Three-tab interface: Pending, Approved, Rejected
- Approve testimonials to display on About page
- Reject inappropriate testimonials
- Delete testimonials
- Display star ratings for each testimonial

**Customer Features:**
- See approved testimonials with 5-star ratings on About page
- Testimonials display customer name and message
- Shows newest testimonials first (up to 6)

### 12. **WhatsApp Integration** ✓
**File:** `/includes/footer.php`

**Features:**
- Fixed position WhatsApp button (bottom-right corner)
- Pre-filled message with link to chat
- Green button with WhatsApp icon
- Opens in new tab
- Phone number: +256747686189

### 13. **Navigation Updates** ✓
**File:** `/includes/header.php`

**Changes:**
- Added "My Profile" link in user dropdown menu
- Appears before "My Orders" link
- Links to customer profile page

### 14. **Admin Dashboard Updates** ✓
**File:** `/admin/dashboard.php` and `/admin/includes/header.php`

**Changes:**
- Added "Manage Testimonials" quick action button
- Added Testimonials link in admin sidebar
- Icon: Star (⭐)

### 15. **JavaScript Enhancements** ✓
**File:** `/assets/js/main.js`

**New Functions:**
- `loadProductReviews()` - Fetch reviews for product
- `displayReviews()` - Render reviews on page
- `applyPromoCode()` - Validate and apply promo code
- `cancelOrder()` - Confirm and cancel order
- `updateOrderSummaryWithDiscount()` - Update UI with discount

**Features:**
- AJAX-based promo code application
- Real-time discount calculation and display
- Order cancellation with confirmation dialog
- Review submission with validation
- All functions compatible with existing code

---

## 🚀 Next Steps - CRITICAL

### Step 1: Run Database Migrations
Execute the SQL migration to create new tables:

```bash
mysql -u [username] -p [database_name] < /var/www/html/mamoven1/db/migrations/add_new_tables.sql
```

Replace `[username]` and `[database_name]` with your actual credentials.

### Step 2: Configure Site Settings
Ensure these settings exist in the `site_settings` table:
- `business_address` - Your business address (for receipts)
- `business_phone` - Your business phone (for receipts)
- `business_email` - Your business email (for receipts)
- `delivery_fee` - Default delivery fee in UGX (e.g., 5000)

Insert via SQL if not exist:
```sql
INSERT INTO site_settings (setting_key, setting_value) VALUES
('business_address', 'Kampala, Uganda'),
('business_phone', '+256 747 686189'),
('business_email', 'contact@mamasoven.com'),
('delivery_fee', '5000')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
```

### Step 3: Verify Email Configuration
Email notifications are enabled in:
- `/config/database.php` - SMTP settings (should already be configured)
- `/api/place_order.php` - Uses PHPMailer for order confirmations
- `/orders.php` - Sends cancellation emails to admin

### Step 4: Test Core Functionality
1. **Registration:** Create a new account
   - Verify phone is required
   - Verify phone format validation works
   - Check phone is saved to database

2. **Shopping:** Add products to cart
   - Verify stock badges display correctly
   - Verify out-of-stock disables add-to-cart

3. **Checkout:** Complete an order
   - Verify phone auto-fills (if logged in)
   - Try promo code (if you create one in database)
   - Verify discount displays correctly
   - Complete order and check receipt

4. **Customer Profile:** Edit profile
   - Verify profile can be edited
   - Verify recent orders display
   - Test cancel button on pending order

5. **Reviews:** Leave a product review
   - Click review form on product page
   - Submit 1-5 star rating
   - Verify review appears in list

6. **Testimonials:** Approve testimonials
   - Go to Admin → Testimonials
   - Approve pending testimonials
   - Check About page to see them

---

## 📊 Database Tables Reference

### reviews
```sql
- id (INT, PRIMARY KEY)
- product_id (INT, FK to products)
- user_id (INT, FK to users)
- rating (INT, 1-5)
- comment (TEXT, up to 500 chars)
- created_at (TIMESTAMP)
- UNIQUE(product_id, user_id)
```

### testimonials
```sql
- id (INT, PRIMARY KEY)
- user_id (INT, FK to users, nullable for anonymous)
- name (VARCHAR, required)
- email (VARCHAR, required)
- message (TEXT, required)
- rating (INT, 1-5)
- status ENUM('pending', 'approved', 'rejected')
- created_at (TIMESTAMP)
```

### promo_codes
```sql
- id (INT, PRIMARY KEY)
- code (VARCHAR, UNIQUE)
- discount_type ENUM('percentage', 'fixed')
- discount_value (DECIMAL)
- min_order_amount (DECIMAL)
- max_uses (INT, nullable)
- valid_from (TIMESTAMP, nullable)
- valid_until (TIMESTAMP, nullable)
- status ENUM('active', 'inactive')
```

### product_images
```sql
- id (INT, PRIMARY KEY)
- product_id (INT, FK)
- image (LONGTEXT, base64)
- alt_text (VARCHAR)
- is_primary (BOOLEAN)
- display_order (INT)
```

---

## 🔧 Troubleshooting

### Issue: Promo code not applying
- **Check:** Promo code exists in `promo_codes` table
- **Check:** Status is 'active'
- **Check:** Current date is between valid_from and valid_until
- **Check:** Order amount >= min_order_amount
- **Check:** Usage count < max_uses

### Issue: Reviews not showing
- **Check:** Reviews table exists (run migration)
- **Check:** Product has approved reviews
- **Check:** JavaScript console for errors (F12)

### Issue: Stock not updating
- **Check:** `stock_quantity` column exists in products table
- **Check:** Order placement decreases stock
- **Check:** Order cancellation increases stock

### Issue: Emails not sending
- **Check:** SMTP credentials in config/database.php
- **Check:** Email_logs table for failed attempts
- **Check:** Server error logs

### Issue: Testimonials not showing on About page
- **Check:** Testimonials table exists
- **Check:** At least one testimonial has status='approved'
- **Check:** Load About page and check console for errors

---

## 📁 File Summary

### Created Files (New)
- `/db/migrations/add_new_tables.sql` - Database schema
- `/api/submit_review.php` - Review submission
- `/api/get_reviews.php` - Review retrieval
- `/api/apply_promo.php` - Promo validation
- `/api/cancel_order.php` - Order cancellation
- `/customer_profile.php` - Customer profile page
- `/admin/testimonials.php` - Testimonial management

### Modified Files (Updated)
- `/auth/register.php` - Phone required, validation
- `/cart.php` - Phone auto-fill, promo input
- `/print_receipt.php` - Business info, full details
- `/assets/css/style.css` - Modern UI + 400+ new lines
- `/products.php` - Stock badges display
- `/product-details.php` - Reviews section, ratings, stock
- `/about.php` - Testimonials display
- `/includes/header.php` - Profile link
- `/includes/footer.php` - WhatsApp button
- `/admin/dashboard.php` - Testimonials action
- `/admin/includes/header.php` - Testimonials nav
- `/assets/js/main.js` - Review, promo, cancel functions

---

## 🎨 Color Palette

- **Primary (Deep Brown):** #5A331F
- **Secondary (Soft Peach):** #F5D1B1
- **Background (Light Cream):** #FBF2EA
- **Text (Dark Brown):** #3A2B28
- **Accent (Orange):** #F39C6A

---

## 📞 Support

For issues or questions:
1. Check the Troubleshooting section above
2. Review error messages in browser console (F12)
3. Check server error logs
4. Verify database migrations ran successfully

---

## ✨ Features Ready to Use

- ✅ Required phone number in registration
- ✅ Auto-suggest phone during checkout
- ✅ Modern responsive UI
- ✅ Product reviews with ratings
- ✅ Stock availability display
- ✅ Order cancellation
- ✅ Customer profile editing
- ✅ Testimonials system
- ✅ Promo code support
- ✅ Email notifications
- ✅ WhatsApp chat button
- ✅ Enhanced receipts with business info

**All systems ready for testing and deployment!**
