-- Create database and use it
drop database if exists `mamaove`;
CREATE DATABASE IF NOT EXISTS `mamaove`;
USE `mamaove`;

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--login Username: admin (not the email)
--Password: Mama2023!
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('admin', 'admin@mamasovenug.com', '$2y$10$kUY/zK2N46tELCQqq.1ekeVArJebnxv1xMy17RfpZ06NDnrlFW/H6', 'Administrator', 'admin');

--
-- Table structure for table `categories`
--
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--
INSERT INTO `categories` (`name`, `description`) VALUES
('Cakes', 'Delicious homemade cakes for all occasions'),
('Pastries', 'Fresh pastries and baked goods'),
('Snacks', 'Tasty snacks and finger foods'),
('Breads', 'Fresh breads and rolls'),
('Cookies', 'Sweet and savory cookies'),
('Custom Orders', 'Special custom-made items');

--
-- Table structure for table `products`
--
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `flavours` text DEFAULT NULL,
  `ingredients` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `featured` tinyint(1) DEFAULT 0,
  `stock_quantity` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--
INSERT INTO `products` (`name`, `description`, `price`, `category_id`, `image`, `flavours`, `ingredients`, `featured`, `stock_quantity`) VALUES
('Chocolate Birthday Cake', 'Rich chocolate cake perfect for birthdays. Moist, decadent, and topped with a creamy chocolate ganache.', '45000.00', 1, 'assets/images/Untitled.jpeg', 'Chocolate, Vanilla, Strawberry', 'Flour, Sugar, Eggs, Cocoa, Butter', 1, 10),
('Vanilla Cupcakes', 'A dozen soft vanilla cupcakes with a light and fluffy buttercream frosting. Perfect for any celebration.', '30000.00', 1, 'assets/images/caketwo.jpeg', 'Vanilla, Chocolate, Red Velvet', 'Flour, Sugar, Eggs, Vanilla, Butter', 1, 24),
('Savory Meat Pies', 'A pack of 6 savory meat pies with a perfectly seasoned beef filling in a flaky pastry crust.', '30000.00', 2, 'assets/images/meatp.jpeg', 'Beef, Chicken, Vegetable', 'Pastry, Beef, Onions, Spices', 1, 20),
('Classic Samosas', 'A set of 10 crispy samosas with a delicious spiced filling. A perfect snack for any time of day.', '20000.00', 3, 'assets/images/samosa.jpeg', 'Beef, Chicken, Vegetable', 'Pastry, Meat, Vegetables, Spices', 0, 30),
('Fresh White Bread', 'A fresh, soft loaf of classic white bread, perfect for sandwiches or toast.', '8000.00', 4, 'assets/images/products/bread1.jpeg', 'White, Brown, Whole Wheat', 'Flour, Yeast, Salt, Sugar', 0, 15);

--
-- Table structure for table `product_images`
--
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `orders`
--
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','processing','ready','delivered','cancelled') DEFAULT 'pending',
  `delivery_address` text DEFAULT NULL,
  `delivery_phone` varchar(20) DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `order_items`
--
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `special_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cart`
--
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `contact_messages`
--
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `site_settings`
--
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `description`) VALUES
('site_name', 'Mama\'s Oven Uganda', 'Website name'),
('contact_email', 'info@mamasovenug.com', 'Contact email'),
('contact_phone', '+256 700 123456', 'Contact phone number'),
('address', 'Kampala, Uganda', 'Business address'),
('delivery_fee', '5000', 'Standard delivery fee'),
('min_order_amount', '20000', 'Minimum order amount for delivery');