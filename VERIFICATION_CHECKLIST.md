# MamaOven - Quick Verification Checklist

Use this checklist to verify all features are working correctly after setup.

## 🗄️ Database Setup Verification

- [ ] Run migration SQL file
- [ ] Verify all new tables created: reviews, testimonials, promo_codes, promo_usage, product_images, email_logs
- [ ] Verify altered tables: users (phone NOT NULL), orders (promo_code_id, discount_amount, updated_at), products (stock_quantity, low_stock_threshold)
- [ ] Populate site_settings with business_address, business_phone, business_email, delivery_fee
- [ ] Verify no SQL errors in error logs

## 👤 User Registration & Authentication

- [ ] Navigate to `/auth/register.php`
- [ ] Verify phone field displays with placeholder "+256 XXX XXX XXX"
- [ ] Try to register WITHOUT phone → Should show "Phone number is required" error
- [ ] Try invalid phone formats → Should show validation error
- [ ] Register with valid phone (+256 700 000000) → Should succeed
- [ ] Login and verify phone appears in customer profile
- [ ] Verify phone saved to database (SELECT * FROM users WHERE id=[user_id];)

## 🛒 Shopping & Cart

- [ ] Add product to cart
- [ ] Navigate to `/cart.php`
- [ ] Verify stock badges display:
  - Green "In Stock" badge for products with >10 stock
  - Yellow "Low Stock" badge for 1-10 stock
  - Red "Out of Stock" badge for 0 stock
  - Out-of-stock button should be disabled
- [ ] Verify order summary shows subtotal, delivery fee, total
- [ ] Add product to cart and view `/cart.php` with logged-in user

## 🏠 Checkout Process

- [ ] Click "Proceed to Checkout"
- [ ] Verify checkout modal appears
- [ ] Verify delivery address is pre-filled (or empty if first order)
- [ ] Verify delivery phone is pre-filled with saved phone (or empty)
- [ ] Edit phone/address and verify fields accept changes
- [ ] Test promo code:
  - [ ] Leave blank, apply → Should show "Please enter a promo code" error
  - [ ] Enter invalid code, apply → Should show "Invalid code" or similar
  - [ ] If promo code exists in DB, enter it → Should apply and show discount
  - [ ] Verify discount displays in order summary
- [ ] Verify "Special Instructions" textarea is present and optional
- [ ] Complete order without promo, verify order creates successfully

## 📋 Order Management

- [ ] Navigate to `/orders.php`
- [ ] Verify recent order appears with:
  - [ ] Order number
  - [ ] Date
  - [ ] Amount
  - [ ] Status badge
- [ ] Click "Print Receipt" → Verify receipt displays with:
  - [ ] Business address (from site_settings)
  - [ ] Business phone (from site_settings)
  - [ ] Customer phone and address
  - [ ] Order items with prices
  - [ ] Total amount
- [ ] If order status is pending/confirmed:
  - [ ] "Cancel Order" button should appear
  - [ ] Click cancel → Confirmation dialog appears
  - [ ] Confirm cancellation → Order status changes to cancelled
  - [ ] Verify products stock quantity increased (stock restored)
- [ ] If order status is completed/delivered:
  - [ ] "Cancel Order" button should NOT appear

## 👤 Customer Profile

- [ ] Navigate to `/customer_profile.php`
- [ ] Verify profile displays:
  - [ ] Profile avatar with initials
  - [ ] Full name
  - [ ] Email (read-only)
  - [ ] Phone number
  - [ ] Address
- [ ] Click "Edit Profile" button
- [ ] Verify form fields are editable
- [ ] Try to submit without phone → Should show error
- [ ] Update profile with new phone → Should save successfully
- [ ] Verify "Recent Orders" section displays last 5 orders
- [ ] Verify cancel buttons appear for pending/processing orders

## ⭐ Product Reviews

- [ ] Navigate to any product page (`/product-details.php?id=X`)
- [ ] Scroll down to "Customer Reviews" section
- [ ] Verify "Loading reviews..." message initially appears
- [ ] If reviews exist, verify they display with:
  - [ ] Star rating
  - [ ] Customer name
  - [ ] Date
  - [ ] Comment text
- [ ] If logged in, verify review form appears:
  - [ ] Star rating selector (1-5 stars)
  - [ ] Comment textarea
  - [ ] Submit button
- [ ] Test review submission:
  - [ ] Leave rating blank, try submit → Should show error
  - [ ] Select 4 stars, add comment → Submit
  - [ ] Verify success message displays
  - [ ] Verify review appears in list immediately
  - [ ] Verify average rating updates in product header
  - [ ] Edit review (select different rating) → Should update not duplicate
- [ ] Verify average rating displays in product header (e.g., "4.5 out of 5 stars (3 reviews)")

## 🌟 Testimonials

- [ ] As admin, navigate to `/admin/testimonials.php`
- [ ] Verify three tabs visible: Pending, Approved, Rejected
- [ ] If pending testimonials exist:
  - [ ] Click "Approve" button → Testimonial moves to Approved tab
  - [ ] Click "Reject" button → Testimonial moves to Rejected tab
- [ ] Navigate to `/about.php`
- [ ] Verify "Customer Testimonials" section displays
- [ ] If approved testimonials exist, verify they show:
  - [ ] Customer name
  - [ ] Star rating (1-5 stars displayed)
  - [ ] Testimonial message
  - [ ] Only approved testimonials display (not pending/rejected)

## 🎨 UI & Styling

- [ ] Navigate to `/products.php`
- [ ] Verify products display with modern styling:
  - [ ] Product card with image
  - [ ] Product name and price
  - [ ] Stock badge (colored appropriately)
  - [ ] "Add to Cart" button
- [ ] Navigate to `/cart.php`
- [ ] Verify modern styling with:
  - [ ] Warm color palette (browns and peaches)
  - [ ] Clear order summary
  - [ ] Responsive layout (test on mobile)
- [ ] Scroll to footer, verify WhatsApp button:
  - [ ] Button visible in bottom-right corner (fixed position)
  - [ ] Green color with WhatsApp icon
  - [ ] Click opens WhatsApp chat in new tab

## 📧 Email Notifications

- [ ] Complete an order
- [ ] Check email inbox for order confirmation
- [ ] Verify email contains:
  - [ ] Order number
  - [ ] Order items list with prices
  - [ ] Delivery address and phone
  - [ ] Total amount
  - [ ] If promo applied: discount amount shown
  - [ ] Link to order history
- [ ] Check admin email for order notification (may be same email)
- [ ] Cancel an order
- [ ] Verify admin receives cancellation notification email

## 🔗 Navigation & Links

- [ ] When logged in, verify dropdown menu shows:
  - [ ] "My Profile" link
  - [ ] "My Orders" link
  - [ ] "Admin Dashboard" link (if admin role)
  - [ ] "Logout" link
- [ ] Click "My Profile" → Should navigate to customer_profile.php
- [ ] Click "My Orders" → Should navigate to orders.php
- [ ] Verify all links work correctly

## 💳 Promo Code Flow (Complete)

- [ ] Create promo code in database:
  ```sql
  INSERT INTO promo_codes (code, discount_type, discount_value, min_order_amount, valid_from, valid_until, status)
  VALUES ('WELCOME10', 'percentage', 10, 50000, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'active');
  ```
- [ ] Add products to cart (total > 50000)
- [ ] Go to checkout
- [ ] Enter promo code "WELCOME10"
- [ ] Verify discount calculates correctly (10% off subtotal)
- [ ] Verify promo code field gets populated in hidden form field
- [ ] Complete order
- [ ] Verify promo usage recorded in database (promo_usage table)
- [ ] Try to use promo code again → Should succeed (if max_uses not reached)
- [ ] Verify order has discount_amount stored
- [ ] Verify order email shows discount line

## 🚀 Performance & Error Handling

- [ ] Check browser console (F12) for JavaScript errors
- [ ] Check server error logs for PHP errors
- [ ] Test with various browsers (Chrome, Firefox, Safari)
- [ ] Test responsive design on mobile (375px width)
- [ ] Verify all AJAX calls complete successfully
- [ ] Test with slow internet (Throttle in DevTools) to verify loading states

## 📝 Final Checks

- [ ] All new database tables exist and have correct structure
- [ ] All new files are readable (check permissions)
- [ ] All modified files have correct syntax (no PHP errors)
- [ ] CSS loads correctly (no 404 errors in console)
- [ ] JavaScript functions available and working
- [ ] Email configuration is correct
- [ ] Admin testimonials page accessible only to admins
- [ ] Order cancellation works atomically (stock + status together)
- [ ] All URLs use BASE_URL for proper asset linking

---

## ✅ All Verified?

If all checks pass, the system is ready for production use!

If any check fails:
1. Note the failing check
2. Check the error in browser console (F12)
3. Check server error logs
4. Refer to SETUP_GUIDE.md Troubleshooting section
5. Verify database migrations ran correctly

---

## 📊 Expected Database State After Setup

```
Database: [database_name]
Tables:
  - users (with phone NOT NULL)
  - products (with stock_quantity, low_stock_threshold)
  - orders (with promo_code_id, discount_amount, updated_at)
  - order_items
  - cart
  - categories
  - reviews (new)
  - testimonials (new)
  - promo_codes (new)
  - promo_usage (new)
  - product_images (new)
  - email_logs (new)
  - activity_logs
  - password_resets
  - contact_messages
  - site_settings
```

Sample site_settings rows:
- business_address | Kampala, Uganda
- business_phone | +256 747 686189
- business_email | contact@mamasoven.com
- delivery_fee | 5000

---

Last Updated: [Current Date]
Status: ✅ Ready for Testing
