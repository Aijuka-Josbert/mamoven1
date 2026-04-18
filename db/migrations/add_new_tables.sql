-- MamaOven Database Schema Enhancements
-- Run these migrations to add new features

-- 0. Disable foreign key checks to handle constraints cleanly
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Product Reviews & Ratings Table
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `product_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `rating` int(1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
    `comment` TEXT,
    `is_verified_purchase` tinyint(1) DEFAULT 0,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_review` (`product_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Product Images Gallery Table
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `product_id` int(11) NOT NULL,
    `image` LONGTEXT NOT NULL,
    `alt_text` varchar(255),
    `is_primary` tinyint(1) DEFAULT 0,
    `display_order` int(11) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    INDEX `product_id` (`product_id`),
    INDEX `is_primary` (`is_primary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Testimonials Table
DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE `testimonials` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` int(11),
    `name` varchar(100) NOT NULL,
    `email` varchar(100),
    `message` TEXT NOT NULL,
    `rating` int(1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
    `status` enum('pending','approved','rejected') DEFAULT 'pending',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Promo Codes Table (create fresh)
DROP TABLE IF EXISTS `promo_codes`;
CREATE TABLE `promo_codes` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `code` varchar(50) NOT NULL UNIQUE,
    `description` varchar(255),
    `discount_type` enum('percentage','fixed') NOT NULL,
    `discount_value` decimal(10,2) NOT NULL,
    `min_order_amount` decimal(10,2) DEFAULT 0,
    `max_uses` int(11),
    `used_count` int(11) DEFAULT 0,
    `valid_from` datetime,
    `valid_until` datetime,
    `status` enum('active','inactive') DEFAULT 'active',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    INDEX `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Promo Usage Tracking
DROP TABLE IF EXISTS `promo_usage`;
CREATE TABLE `promo_usage` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `promo_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `order_id` int(11),
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`promo_id`) REFERENCES `promo_codes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- 6. Enhance Products Table with new fields
ALTER TABLE `products` 
    ADD COLUMN IF NOT EXISTS `stock_quantity` int(11) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `low_stock_threshold` int(11) DEFAULT 10;

-- 7. Enhance Orders Table - add promo columns if not exist
ALTER TABLE `orders` 
    ADD COLUMN IF NOT EXISTS `discount_amount` decimal(10,2) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ADD COLUMN IF NOT EXISTS `review_reminder_sent` tinyint(1) DEFAULT 0;

-- 8. Add promo_code_id column and foreign key to orders if not exist
ALTER TABLE `orders` 
    ADD COLUMN IF NOT EXISTS `promo_code_id` int(11);

-- 9. Drop existing foreign key if it exists, then add it fresh
SET FOREIGN_KEY_CHECKS = 0;
ALTER TABLE `orders` DROP FOREIGN KEY IF EXISTS `orders_ibfk_promo_code`;
ALTER TABLE `orders` DROP FOREIGN KEY IF EXISTS `orders_ibfk_2`;
ALTER TABLE `orders` 
    ADD CONSTRAINT `orders_ibfk_promo_code` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes`(`id`) ON DELETE SET NULL;
SET FOREIGN_KEY_CHECKS = 1;

-- 10. Update Users table - make phone required
ALTER TABLE `users` 
    MODIFY COLUMN `phone` varchar(30) NOT NULL DEFAULT '';

-- 11. Email Logs Table
DROP TABLE IF EXISTS `email_logs`;
CREATE TABLE `email_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` int(11),
    `email_to` varchar(255) NOT NULL,
    `email_subject` varchar(255) NOT NULL,
    `email_type` varchar(50) NOT NULL,
    `related_order_id` int(11),
    `sent_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `status` enum('sent','failed') DEFAULT 'sent',
    INDEX `user_id` (`user_id`),
    INDEX `email_type` (`email_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Update site_settings with business info
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('business_address', 'Kampala, Uganda'),
('business_phone', '+256 XXX XXX XXX'),
('business_email', 'info@mamasoven.com'),
('whatsapp_number', '+256 XXX XXX XXX'),
('low_stock_threshold', '10'),
('delivery_fee', '5000');

-- 13. Add performance indexes
ALTER TABLE `reviews` ADD INDEX IF NOT EXISTS `idx_reviews_product_rating` (`product_id`, `rating`);
ALTER TABLE `promo_codes` ADD INDEX IF NOT EXISTS `idx_promo_code_status` (`code`, `status`);
ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_orders_user_status` (`user_id`, `status`);

-- Complete: All tables and schema enhancements applied successfully

