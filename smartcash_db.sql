-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 02, 2025 at 01:53 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smartcash_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'admin', '$2y$10$tJ9f9xYk8Y9fL7S9fC8jA.R3lA7lA7P8lA7fA9gN9iH5pL7kG9hJ9oP4', '2025-08-02 13:52:00');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `full_name_on_request` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `request_status` enum('pending','approved','denied','completed') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_by_admin_at` timestamp NULL DEFAULT NULL,
  `denied_by_admin_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reset_token` (`reset_token`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `buying_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT '0.00',
  `current_stock` int DEFAULT '0',
  `min_stock_level` int DEFAULT '10',
  `supplier` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `product_name` (`product_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category`, `buying_price`, `selling_price`, `discount`, `current_stock`, `min_stock_level`, `supplier`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Coca-Cola 500ml', 'Beverages', 60.00, 75.00, 5.00, 100, 20, NULL, NULL, '2025-08-02 13:07:27', '2025-08-02 13:12:13'),
(2, 'Munchee Biscuits (Large)', 'Snacks', 150.00, 180.00, 0.00, 50, 10, NULL, NULL, '2025-08-02 13:07:27', '2025-08-02 13:12:33'),
(3, 'Anchor Milk Powder 400g', 'Dairy', 500.00, 650.00, 25.00, 65, 10, NULL, NULL, '2025-08-02 13:07:27', '2025-08-02 13:13:03'),
(4, 'Lifebuoy Soap', 'Personal Care', 100.00, 120.00, 0.00, 200, 10, NULL, NULL, '2025-08-02 13:07:27', '2025-08-02 13:13:42'),
(5, 'Samaposha 200g', 'Breakfast Cereals', 100.00, 140.00, 10.00, 30, 10, NULL, NULL, '2025-08-02 13:07:27', '2025-08-02 13:14:04'),
(6, 'Sunlight Dishwash Liquid', 'Household', 190.00, 220.00, 0.00, 20, 0, NULL, NULL, '2025-08-02 13:07:27', '2025-08-02 13:14:28'),
(7, 'Nestomalt 400g', 'Beverages', 480.00, 550.00, 20.00, 82, 10, NULL, NULL, '2025-08-02 13:07:27', '2025-08-02 13:14:58'),
(8, 'Kist Mango Jam 200g', 'Spreads', 225.00, 250.00, 0.00, 34, 0, NULL, NULL, '2025-08-02 13:07:27', '2025-08-02 13:15:29');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `transaction_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Cash',
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `change_given` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`transaction_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `transaction_date`, `total_amount`, `payment_method`, `amount_paid`, `change_given`) VALUES
(21, 100, '2025-06-02 03:35:00', 270.00, 'Cash', NULL, NULL),
(22, 100, '2025-06-02 03:40:00', 320.00, 'Card', NULL, NULL),
(23, 100, '2025-06-02 03:45:00', 650.00, 'Cash', NULL, NULL),
(24, 100, '2025-06-02 03:50:00', 1090.00, 'Card', NULL, NULL),
(25, 100, '2025-06-02 03:55:00', 580.00, 'Cash', NULL, NULL),
(26, 100, '2025-06-02 04:00:00', 500.00, 'Card', NULL, NULL),
(27, 100, '2025-06-02 04:05:00', 875.00, 'Cash', NULL, NULL),
(28, 100, '2025-06-02 04:10:00', 850.00, 'Card', NULL, NULL),
(29, 100, '2025-06-02 04:15:00', 1300.00, 'Cash', NULL, NULL),
(30, 100, '2025-06-02 04:20:00', 940.00, 'Card', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transaction_items`
--

DROP TABLE IF EXISTS `transaction_items`;
CREATE TABLE IF NOT EXISTS `transaction_items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `transaction_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `selling_price_at_sale` decimal(10,2) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_items`
--

INSERT INTO `transaction_items` (`item_id`, `transaction_id`, `product_id`, `quantity`, `selling_price_at_sale`) VALUES
(1, 21, 1, 2, 75.00),
(2, 21, 2, 1, 120.00),
(3, 22, 2, 1, 180.00),
(4, 22, 5, 1, 140.00),
(5, 23, 3, 1, 650.00),
(6, 24, 7, 2, 550.00),
(7, 25, 4, 3, 120.00),
(8, 25, 6, 1, 220.00),
(9, 26, 2, 2, 180.00),
(10, 26, 5, 1, 140.00),
(11, 27, 1, 3, 75.00),
(12, 27, 3, 1, 650.00),
(13, 28, 4, 5, 120.00),
(14, 28, 8, 1, 250.00),
(15, 29, 7, 1, 550.00),
(16, 29, 8, 3, 250.00),
(17, 30, 1, 1, 75.00),
(18, 30, 6, 2, 220.00),
(19, 30, 7, 1, 550.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','cashier','inventory_manager') COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `contact_info` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `role`, `status`, `contact_info`, `address`, `created_at`, `updated_at`) VALUES
(1, 'admin', '49', 'System Administrator', 'admin', 'active', NULL, NULL, '2025-06-01 15:01:12', '2025-08-02 13:21:30'),
(2, 'cashier1', 'YOUR_GENERATED_HASH_FOR_123456', 'Alice Cashier', 'cashier', 'active', NULL, NULL, '2025-06-01 15:01:12', '2025-06-01 15:01:12'),
(3, 'inv_manager1', 'YOUR_GENERATED_HASH_FOR_123456', 'Bob Inventory', 'inventory_manager', 'active', NULL, NULL, '2025-06-01 15:01:12', '2025-06-01 15:01:12'),
(100, 'cashier_user', '', '', 'cashier', 'active', NULL, NULL, '2025-08-02 12:37:56', '2025-08-02 12:37:56');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD CONSTRAINT `transaction_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `transaction_items_ibfk_3` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`),
  ADD CONSTRAINT `transaction_items_ibfk_4` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
