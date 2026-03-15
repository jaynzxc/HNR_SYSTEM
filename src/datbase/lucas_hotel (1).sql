-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 13, 2026 at 07:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lucas_hotel`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `customer_code` varchar(20) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `loyalty_points` int(11) DEFAULT 0,
  `membership_level` enum('regular','silver','gold','platinum') DEFAULT 'regular',
  `total_visits` int(11) DEFAULT 0,
  `total_spent` decimal(10,2) DEFAULT 0.00,
  `last_visit` datetime DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `anniversary` date DEFAULT NULL,
  `dietary_preferences` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `customer_code`, `first_name`, `last_name`, `email`, `phone`, `address`, `city`, `postal_code`, `loyalty_points`, `membership_level`, `total_visits`, `total_spent`, `last_visit`, `birthday`, `anniversary`, `dietary_preferences`, `allergies`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'CUST001', 'Michael', 'Cruz', 'michael.cruz@email.com', '09181234567', NULL, NULL, NULL, 0, 'gold', 0, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(2, 'CUST002', 'Jiyeon', 'Kim', 'jiyeon.kim@email.com', '09182345678', NULL, NULL, NULL, 0, 'silver', 0, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(3, 'CUST003', 'Paolo', 'Reyes', 'paolo.reyes@email.com', '09183456789', NULL, NULL, NULL, 0, 'regular', 0, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(4, 'CUST004', 'Michelle', 'Tan', 'michelle.tan@email.com', '09184567890', NULL, NULL, NULL, 0, 'gold', 0, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(5, 'CUST005', 'Anna', 'Santos', 'anna.santos@email.com', '09185678901', NULL, NULL, NULL, 0, 'platinum', 0, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(6, 'CUST006', 'Luis', 'Garcia', 'luis.garcia@email.com', '09186789012', NULL, NULL, NULL, 0, 'silver', 0, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(7, 'CUST007', 'Jose', 'Rivera', 'jose.rivera@email.com', '09187890123', NULL, NULL, NULL, 0, 'regular', 0, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-13 06:46:03', '2026-03-13 06:46:03');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(20) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `service_charge` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `balance_due` decimal(10,2) DEFAULT 0.00,
  `status` enum('draft','issued','paid','void') DEFAULT 'draft',
  `issued_date` datetime DEFAULT current_timestamp(),
  `due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `order_id`, `customer_id`, `subtotal`, `tax_amount`, `service_charge`, `discount_amount`, `total_amount`, `amount_paid`, `balance_due`, `status`, `issued_date`, `due_date`, `notes`, `created_at`) VALUES
(1, 'INV1001', 3, 4, 1250.00, 150.00, 0.00, 0.00, 1400.00, 1400.00, 0.00, 'paid', '2026-03-13 06:56:34', NULL, NULL, '2026-03-13 06:56:34');

-- --------------------------------------------------------

--
-- Table structure for table `kitchen_stations`
--

CREATE TABLE `kitchen_stations` (
  `id` int(11) NOT NULL,
  `station_name` varchar(100) NOT NULL,
  `station_code` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kitchen_stations`
--

INSERT INTO `kitchen_stations` (`id`, `station_name`, `station_code`, `description`, `is_active`) VALUES
(1, 'Grill Station', 'GRL', 'Grilled items and meats', 1),
(2, 'Hot Kitchen', 'HOT', 'Main courses and hot dishes', 1),
(3, 'Cold Kitchen', 'CLD', 'Salads and cold appetizers', 1),
(4, 'Dessert Station', 'DES', 'Desserts and pastries', 1),
(5, 'Beverage Station', 'BEV', 'Drinks and beverages', 1),
(6, 'Preparation Station', 'PREP', 'Food preparation', 1);

-- --------------------------------------------------------

--
-- Table structure for table `kitchen_tickets`
--

CREATE TABLE `kitchen_tickets` (
  `id` int(11) NOT NULL,
  `ticket_number` varchar(20) NOT NULL,
  `order_id` int(11) NOT NULL,
  `station_id` int(11) DEFAULT NULL,
  `priority` enum('normal','high','urgent') DEFAULT 'normal',
  `status` enum('pending','preparing','ready','served','cancelled') DEFAULT 'pending',
  `prepared_by` int(11) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `completion_time` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kitchen_tickets`
--

INSERT INTO `kitchen_tickets` (`id`, `ticket_number`, `order_id`, `station_id`, `priority`, `status`, `prepared_by`, `start_time`, `completion_time`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'KOT1001', 1, 2, 'normal', 'preparing', NULL, NULL, NULL, NULL, '2026-03-13 06:56:34', '2026-03-13 06:56:34'),
(2, 'KOT1002', 2, 2, 'normal', 'preparing', NULL, NULL, NULL, NULL, '2026-03-13 06:56:34', '2026-03-13 06:56:34'),
(3, 'KOT1003', 4, 2, 'normal', 'preparing', NULL, NULL, NULL, NULL, '2026-03-13 06:56:34', '2026-03-13 06:56:34'),
(4, 'KOT1004', 4, 4, 'normal', 'preparing', NULL, NULL, NULL, NULL, '2026-03-13 06:56:34', '2026-03-13 06:56:34');

-- --------------------------------------------------------

--
-- Table structure for table `kitchen_ticket_items`
--

CREATE TABLE `kitchen_ticket_items` (
  `id` int(11) NOT NULL,
  `kitchen_ticket_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `menu_item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `status` enum('pending','preparing','ready','cancelled') DEFAULT 'pending',
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_categories`
--

CREATE TABLE `menu_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `item_code` varchar(20) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) DEFAULT 0.00,
  `stock_quantity` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 5,
  `unit` varchar(20) DEFAULT 'piece',
  `image_url` varchar(500) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `is_special` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `preparation_time` int(11) DEFAULT NULL COMMENT 'in minutes',
  `calories` int(11) DEFAULT NULL,
  `allergens` text DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_item_variations`
--

CREATE TABLE `menu_item_variations` (
  `id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `variation_name` varchar(100) NOT NULL,
  `price_adjustment` decimal(10,2) DEFAULT 0.00,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `order_type_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `table_id` int(11) DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL,
  `guest_count` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL COMMENT 'staff ID who took the order',
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `service_charge` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `discount_type` enum('percentage','fixed') DEFAULT NULL,
  `discount_value` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('pending','partial','paid','refunded') DEFAULT 'pending',
  `order_status` enum('draft','pending','confirmed','preparing','ready','served','completed','cancelled') DEFAULT 'pending',
  `kitchen_status` enum('pending','preparing','ready','served') DEFAULT 'pending',
  `special_instructions` text DEFAULT NULL,
  `source` enum('pos','website','app','phone') DEFAULT 'pos',
  `void_reason` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `order_type_id`, `customer_id`, `table_id`, `guest_name`, `guest_count`, `server_id`, `subtotal`, `tax_amount`, `service_charge`, `discount_amount`, `discount_type`, `discount_value`, `total_amount`, `payment_status`, `order_status`, `kitchen_status`, `special_instructions`, `source`, `void_reason`, `created_by`, `created_at`, `updated_at`, `completed_at`) VALUES
(1, 'OR1001', 1, 1, 3, 'Michael Cruz', 2, 1, 850.00, 102.00, 0.00, 0.00, NULL, 0.00, 952.00, 'pending', 'preparing', 'preparing', NULL, 'pos', NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03', NULL),
(2, 'OR1002', 2, 2, NULL, 'Jiyeon Kim', 1, 2, 540.00, 64.80, 0.00, 0.00, NULL, 0.00, 604.80, 'pending', 'preparing', 'preparing', NULL, 'website', NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03', NULL),
(3, 'OR1003', 1, 4, 12, 'Michelle Tan', 2, 3, 1250.00, 150.00, 0.00, 0.00, NULL, 0.00, 1400.00, 'pending', 'served', 'served', NULL, 'pos', NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03', NULL),
(4, 'OR1004', 3, 5, NULL, 'Anna Santos', 2, 4, 890.00, 106.80, 0.00, 0.00, NULL, 0.00, 996.80, 'pending', 'preparing', 'preparing', NULL, 'app', NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03', NULL),
(5, 'OR1005', 2, NULL, NULL, 'Walk-in Customer', 1, 5, 390.00, 46.80, 0.00, 0.00, NULL, 0.00, 436.80, 'pending', 'pending', 'pending', NULL, 'pos', NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `variation_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `item_status` enum('pending','preparing','ready','served','cancelled') DEFAULT 'pending',
  `preparation_time` int(11) DEFAULT NULL,
  `kitchen_notes` text DEFAULT NULL,
  `is_complimentary` tinyint(1) DEFAULT 0,
  `void_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_item_modifiers`
--

CREATE TABLE `order_item_modifiers` (
  `id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `modifier_name` varchar(100) NOT NULL,
  `modifier_price` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_types`
--

CREATE TABLE `order_types` (
  `id` int(11) NOT NULL,
  `type_name` enum('dine_in','takeaway','delivery','room_service') NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_types`
--

INSERT INTO `order_types` (`id`, `type_name`, `description`, `is_active`) VALUES
(1, 'dine_in', 'Customer dining in the restaurant', 1),
(2, 'takeaway', 'Customer taking food away', 1),
(3, 'delivery', 'Food delivered to customer', 1),
(4, 'room_service', 'Delivery to hotel room', 1),
(5, 'dine_in', 'Customer dining in the restaurant', 1),
(6, 'takeaway', 'Customer taking food away', 1),
(7, 'delivery', 'Food delivered to customer', 1),
(8, 'room_service', 'Delivery to hotel room', 1),
(9, 'dine_in', 'Customer dining in the restaurant', 1),
(10, 'takeaway', 'Customer taking food away', 1),
(11, 'delivery', 'Food delivered to customer', 1),
(12, 'room_service', 'Delivery to hotel room', 1),
(13, 'dine_in', 'Customer dining in the restaurant', 1),
(14, 'takeaway', 'Customer taking food away', 1),
(15, 'delivery', 'Food delivered to customer', 1),
(16, 'room_service', 'Delivery to hotel room', 1),
(17, 'dine_in', 'Customer dining in the restaurant', 1),
(18, 'takeaway', 'Customer taking food away', 1),
(19, 'delivery', 'Food delivered to customer', 1),
(20, 'room_service', 'Delivery to hotel room', 1),
(21, 'dine_in', 'Customer dining in the restaurant', 1),
(22, 'takeaway', 'Customer taking food away', 1),
(23, 'delivery', 'Food delivered to customer', 1),
(24, 'room_service', 'Delivery to hotel room', 1),
(25, 'dine_in', 'Customer dining in the restaurant', 1),
(26, 'takeaway', 'Customer taking food away', 1),
(27, 'delivery', 'Food delivered to customer', 1),
(28, 'room_service', 'Delivery to hotel room', 1),
(29, 'dine_in', 'Customer dining in the restaurant', 1),
(30, 'takeaway', 'Customer taking food away', 1),
(31, 'delivery', 'Food delivered to customer', 1),
(32, 'room_service', 'Delivery to hotel room', 1),
(33, 'dine_in', 'Customer dining in the restaurant', 1),
(34, 'takeaway', 'Customer taking food away', 1),
(35, 'delivery', 'Food delivered to customer', 1),
(36, 'room_service', 'Delivery to hotel room', 1),
(37, 'dine_in', 'Customer dining in the restaurant', 1),
(38, 'takeaway', 'Customer taking food away', 1),
(39, 'delivery', 'Food delivered to customer', 1),
(40, 'room_service', 'Delivery to hotel room', 1);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `payment_number` varchar(20) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `tip_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `reference_number` varchar(100) DEFAULT NULL,
  `card_last_four` varchar(4) DEFAULT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `processed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `payment_number`, `order_id`, `payment_method_id`, `amount`, `tip_amount`, `status`, `reference_number`, `card_last_four`, `payment_date`, `processed_by`, `notes`, `created_at`) VALUES
(1, 'PAY1001', 3, 2, 1400.00, 0.00, 'completed', NULL, NULL, '2026-03-13 06:56:34', NULL, NULL, '2026-03-13 06:56:34');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `method_name` varchar(50) NOT NULL,
  `method_code` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `method_name`, `method_code`, `is_active`) VALUES
(1, 'Cash', 'CASH', 1),
(2, 'Credit Card', 'CC', 1),
(3, 'Debit Card', 'DC', 1),
(4, 'GCash', 'GCSH', 1),
(5, 'PayMaya', 'MAYA', 1),
(6, 'Hotel Bill', 'HBILL', 1),
(7, 'GrabPay', 'GRAB', 1);

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_tables`
--

CREATE TABLE `restaurant_tables` (
  `id` int(11) NOT NULL,
  `table_number` varchar(10) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `capacity` int(11) NOT NULL,
  `section` enum('main','outdoor','private','bar','vip') DEFAULT 'main',
  `status` enum('available','occupied','reserved','cleaning','maintenance') DEFAULT 'available',
  `is_active` tinyint(1) DEFAULT 1,
  `x_position` int(11) DEFAULT NULL COMMENT 'for floor plan visualization',
  `y_position` int(11) DEFAULT NULL COMMENT 'for floor plan visualization',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurant_tables`
--

INSERT INTO `restaurant_tables` (`id`, `table_number`, `table_name`, `capacity`, `section`, `status`, `is_active`, `x_position`, `y_position`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'T1', 'Table 1', 2, 'main', 'available', 1, 100, 100, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(2, 'T2', 'Table 2', 4, 'main', 'available', 1, 200, 100, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(3, 'T3', 'Table 3', 2, 'main', 'occupied', 1, 300, 100, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(4, 'T4', 'Table 4', 2, 'main', 'available', 1, 400, 100, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(5, 'T5', 'Table 5', 2, 'main', 'reserved', 1, 500, 100, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(6, 'T6', 'Table 6', 4, 'main', 'available', 1, 600, 100, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(7, 'T7', 'Table 7', 4, 'main', 'reserved', 1, 100, 200, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(8, 'T8', 'Table 8', 6, 'outdoor', 'available', 1, 200, 200, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(9, 'T9', 'Table 9', 6, 'outdoor', 'occupied', 1, 300, 200, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(10, 'T10', 'Table 10', 2, 'bar', 'available', 1, 400, 200, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(11, 'T11', 'Table 11', 8, 'private', 'available', 1, 500, 200, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(12, 'T12', 'Table 12', 2, 'main', 'occupied', 1, 600, 200, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(13, 'VIP1', 'VIP Room 1', 10, 'vip', 'available', 1, 100, 300, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(14, 'VIP2', 'VIP Room 2', 12, 'vip', 'reserved', 1, 300, 300, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03');

-- --------------------------------------------------------

--
-- Table structure for table `staff_attendance`
--

CREATE TABLE `staff_attendance` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in_time` datetime DEFAULT NULL,
  `check_out_time` datetime DEFAULT NULL,
  `status` enum('present','absent','late','half_day','holiday','leave') DEFAULT 'present',
  `late_minutes` int(11) DEFAULT 0,
  `early_departure_minutes` int(11) DEFAULT 0,
  `overtime_minutes` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_members`
--

CREATE TABLE `staff_members` (
  `id` int(11) NOT NULL,
  `staff_code` varchar(20) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive','on_leave','terminated') DEFAULT 'active',
  `employment_type` enum('full_time','part_time','casual') DEFAULT 'full_time',
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `profile_image` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_members`
--

INSERT INTO `staff_members` (`id`, `staff_code`, `first_name`, `last_name`, `role_id`, `email`, `phone`, `address`, `hire_date`, `emergency_contact_name`, `emergency_contact_phone`, `status`, `employment_type`, `hourly_rate`, `profile_image`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'STF001', 'John', 'Doe', 2, 'john.doe@lucas.com', '09171234567', NULL, NULL, NULL, NULL, 'active', 'full_time', NULL, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(2, 'STF002', 'Jane', 'Smith', 3, 'jane.smith@lucas.com', '09172345678', NULL, NULL, NULL, NULL, 'active', 'full_time', NULL, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(3, 'STF003', 'Maria', 'Cruz', 1, 'maria.cruz@lucas.com', '09173456789', NULL, NULL, NULL, NULL, 'active', 'full_time', NULL, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(4, 'STF004', 'Antonio', 'Reyes', 3, 'antonio.reyes@lucas.com', '09174567890', NULL, NULL, NULL, NULL, 'active', 'full_time', NULL, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(5, 'STF005', 'Lisa', 'Garcia', 3, 'lisa.garcia@lucas.com', '09175678901', NULL, NULL, NULL, NULL, 'active', 'full_time', NULL, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(6, 'STF006', 'Mike', 'Tan', 6, 'mike.tan@lucas.com', '09176789012', NULL, NULL, NULL, NULL, 'active', 'full_time', NULL, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(7, 'STF007', 'Anna', 'Santos', 7, 'anna.santos@lucas.com', '09177890123', NULL, NULL, NULL, NULL, 'active', 'full_time', NULL, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(8, 'STF008', 'David', 'Kim', 4, 'david.kim@lucas.com', '09178901234', NULL, NULL, NULL, NULL, 'active', 'full_time', NULL, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03');

-- --------------------------------------------------------

--
-- Table structure for table `staff_performance`
--

CREATE TABLE `staff_performance` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `rating_date` date NOT NULL,
  `rated_by` int(11) DEFAULT NULL,
  `customer_service_rating` decimal(3,2) DEFAULT NULL,
  `speed_rating` decimal(3,2) DEFAULT NULL,
  `accuracy_rating` decimal(3,2) DEFAULT NULL,
  `overall_rating` decimal(3,2) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_performance`
--

INSERT INTO `staff_performance` (`id`, `staff_id`, `rating_date`, `rated_by`, `customer_service_rating`, `speed_rating`, `accuracy_rating`, `overall_rating`, `comments`, `created_at`) VALUES
(1, 1, '2026-03-13', NULL, 5.00, 5.00, 5.00, 5.00, 'Excellent service, very attentive', '2026-03-13 06:56:34'),
(2, 2, '2026-03-13', NULL, 4.50, 4.50, 4.50, 4.50, 'Good service, friendly', '2026-03-13 06:56:34'),
(3, 3, '2026-03-13', NULL, 5.00, 5.00, 5.00, 5.00, 'Outstanding performance', '2026-03-13 06:56:34'),
(4, 5, '2026-03-13', NULL, 4.80, 4.80, 4.80, 4.80, 'Very efficient and polite', '2026-03-13 06:56:34');

-- --------------------------------------------------------

--
-- Table structure for table `staff_roles`
--

CREATE TABLE `staff_roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `base_salary` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_roles`
--

INSERT INTO `staff_roles` (`id`, `role_name`, `description`, `base_salary`, `is_active`) VALUES
(1, 'Head Waiter', 'Supervises wait staff and handles VIP tables', NULL, 1),
(2, 'Senior Waiter', 'Experienced waiter with section responsibility', NULL, 1),
(3, 'Waiter', 'General waiting staff', NULL, 1),
(4, 'Trainee', 'Staff in training', NULL, 1),
(5, 'Captain', 'Section supervisor', NULL, 1),
(6, 'Food Runner', 'Delivers food from kitchen to tables', NULL, 1),
(7, 'Host/Hostess', 'Greets and seats guests', NULL, 1),
(8, 'Bartender', 'Prepares drinks at the bar', NULL, 1),
(9, 'Head Waiter', 'Supervises wait staff and handles VIP tables', NULL, 1),
(10, 'Senior Waiter', 'Experienced waiter with section responsibility', NULL, 1),
(11, 'Waiter', 'General waiting staff', NULL, 1),
(12, 'Trainee', 'Staff in training', NULL, 1),
(13, 'Captain', 'Section supervisor', NULL, 1),
(14, 'Food Runner', 'Delivers food from kitchen to tables', NULL, 1),
(15, 'Host/Hostess', 'Greets and seats guests', NULL, 1),
(16, 'Bartender', 'Prepares drinks at the bar', NULL, 1),
(17, 'Head Waiter', 'Supervises wait staff and handles VIP tables', NULL, 1),
(18, 'Senior Waiter', 'Experienced waiter with section responsibility', NULL, 1),
(19, 'Waiter', 'General waiting staff', NULL, 1),
(20, 'Trainee', 'Staff in training', NULL, 1),
(21, 'Captain', 'Section supervisor', NULL, 1),
(22, 'Food Runner', 'Delivers food from kitchen to tables', NULL, 1),
(23, 'Host/Hostess', 'Greets and seats guests', NULL, 1),
(24, 'Bartender', 'Prepares drinks at the bar', NULL, 1),
(25, 'Head Waiter', 'Supervises wait staff and handles VIP tables', NULL, 1),
(26, 'Senior Waiter', 'Experienced waiter with section responsibility', NULL, 1),
(27, 'Waiter', 'General waiting staff', NULL, 1),
(28, 'Trainee', 'Staff in training', NULL, 1),
(29, 'Captain', 'Section supervisor', NULL, 1),
(30, 'Food Runner', 'Delivers food from kitchen to tables', NULL, 1),
(31, 'Host/Hostess', 'Greets and seats guests', NULL, 1),
(32, 'Bartender', 'Prepares drinks at the bar', NULL, 1),
(33, 'Head Waiter', 'Supervises wait staff and handles VIP tables', NULL, 1),
(34, 'Senior Waiter', 'Experienced waiter with section responsibility', NULL, 1),
(35, 'Waiter', 'General waiting staff', NULL, 1),
(36, 'Trainee', 'Staff in training', NULL, 1),
(37, 'Captain', 'Section supervisor', NULL, 1),
(38, 'Food Runner', 'Delivers food from kitchen to tables', NULL, 1),
(39, 'Host/Hostess', 'Greets and seats guests', NULL, 1),
(40, 'Bartender', 'Prepares drinks at the bar', NULL, 1),
(41, 'Head Waiter', 'Supervises wait staff and handles VIP tables', NULL, 1),
(42, 'Senior Waiter', 'Experienced waiter with section responsibility', NULL, 1),
(43, 'Waiter', 'General waiting staff', NULL, 1),
(44, 'Trainee', 'Staff in training', NULL, 1),
(45, 'Captain', 'Section supervisor', NULL, 1),
(46, 'Food Runner', 'Delivers food from kitchen to tables', NULL, 1),
(47, 'Host/Hostess', 'Greets and seats guests', NULL, 1),
(48, 'Bartender', 'Prepares drinks at the bar', NULL, 1),
(49, 'Head Waiter', 'Supervises wait staff and handles VIP tables', NULL, 1),
(50, 'Senior Waiter', 'Experienced waiter with section responsibility', NULL, 1),
(51, 'Waiter', 'General waiting staff', NULL, 1),
(52, 'Trainee', 'Staff in training', NULL, 1),
(53, 'Captain', 'Section supervisor', NULL, 1),
(54, 'Food Runner', 'Delivers food from kitchen to tables', NULL, 1),
(55, 'Host/Hostess', 'Greets and seats guests', NULL, 1),
(56, 'Bartender', 'Prepares drinks at the bar', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `staff_schedule`
--

CREATE TABLE `staff_schedule` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `shift_id` int(11) DEFAULT NULL,
  `schedule_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `status` enum('scheduled','present','absent','late','on_break','completed') DEFAULT 'scheduled',
  `check_in_time` datetime DEFAULT NULL,
  `check_out_time` datetime DEFAULT NULL,
  `overtime_minutes` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_schedule`
--

INSERT INTO `staff_schedule` (`id`, `staff_id`, `shift_id`, `schedule_date`, `start_time`, `end_time`, `break_start`, `break_end`, `status`, `check_in_time`, `check_out_time`, `overtime_minutes`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-03-13', '07:00:00', '16:00:00', NULL, NULL, 'scheduled', NULL, NULL, 0, NULL, '2026-03-13 06:56:34', '2026-03-13 06:56:34'),
(2, 2, 1, '2026-03-13', '07:00:00', '16:00:00', NULL, NULL, 'scheduled', NULL, NULL, 0, NULL, '2026-03-13 06:56:34', '2026-03-13 06:56:34'),
(3, 3, 2, '2026-03-13', '12:00:00', '21:00:00', NULL, NULL, 'scheduled', NULL, NULL, 0, NULL, '2026-03-13 06:56:34', '2026-03-13 06:56:34'),
(4, 4, 2, '2026-03-13', '12:00:00', '21:00:00', NULL, NULL, 'scheduled', NULL, NULL, 0, NULL, '2026-03-13 06:56:34', '2026-03-13 06:56:34'),
(5, 5, 3, '2026-03-13', '16:00:00', '23:00:00', NULL, NULL, 'scheduled', NULL, NULL, 0, NULL, '2026-03-13 06:56:34', '2026-03-13 06:56:34'),
(6, 6, 3, '2026-03-13', '16:00:00', '23:00:00', NULL, NULL, 'scheduled', NULL, NULL, 0, NULL, '2026-03-13 06:56:34', '2026-03-13 06:56:34'),
(7, 7, 2, '2026-03-13', '12:00:00', '21:00:00', NULL, NULL, 'scheduled', NULL, NULL, 0, NULL, '2026-03-13 06:56:34', '2026-03-13 06:56:34'),
(8, 8, 1, '2026-03-13', '07:00:00', '16:00:00', NULL, NULL, 'scheduled', NULL, NULL, 0, NULL, '2026-03-13 06:56:34', '2026-03-13 06:56:34');

-- --------------------------------------------------------

--
-- Table structure for table `staff_shifts`
--

CREATE TABLE `staff_shifts` (
  `id` int(11) NOT NULL,
  `shift_name` varchar(100) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_shifts`
--

INSERT INTO `staff_shifts` (`id`, `shift_name`, `start_time`, `end_time`, `description`) VALUES
(1, 'Morning Shift', '07:00:00', '16:00:00', 'Morning shift covers breakfast and lunch'),
(2, 'Afternoon Shift', '12:00:00', '21:00:00', 'Afternoon shift covers lunch and dinner'),
(3, 'Evening Shift', '16:00:00', '01:00:00', 'Evening shift covers dinner and late night'),
(4, 'Split Shift', '07:00:00', '21:00:00', 'Split shift with break in between'),
(5, 'Morning Shift', '07:00:00', '16:00:00', 'Morning shift covers breakfast and lunch'),
(6, 'Afternoon Shift', '12:00:00', '21:00:00', 'Afternoon shift covers lunch and dinner'),
(7, 'Evening Shift', '16:00:00', '01:00:00', 'Evening shift covers dinner and late night'),
(8, 'Split Shift', '07:00:00', '21:00:00', 'Split shift with break in between'),
(9, 'Morning Shift', '07:00:00', '16:00:00', 'Morning shift covers breakfast and lunch'),
(10, 'Afternoon Shift', '12:00:00', '21:00:00', 'Afternoon shift covers lunch and dinner'),
(11, 'Evening Shift', '16:00:00', '01:00:00', 'Evening shift covers dinner and late night'),
(12, 'Split Shift', '07:00:00', '21:00:00', 'Split shift with break in between'),
(13, 'Morning Shift', '07:00:00', '16:00:00', 'Morning shift covers breakfast and lunch'),
(14, 'Afternoon Shift', '12:00:00', '21:00:00', 'Afternoon shift covers lunch and dinner'),
(15, 'Evening Shift', '16:00:00', '01:00:00', 'Evening shift covers dinner and late night'),
(16, 'Split Shift', '07:00:00', '21:00:00', 'Split shift with break in between'),
(17, 'Morning Shift', '07:00:00', '16:00:00', 'Morning shift covers breakfast and lunch'),
(18, 'Afternoon Shift', '12:00:00', '21:00:00', 'Afternoon shift covers lunch and dinner'),
(19, 'Evening Shift', '16:00:00', '01:00:00', 'Evening shift covers dinner and late night'),
(20, 'Split Shift', '07:00:00', '21:00:00', 'Split shift with break in between'),
(21, 'Morning Shift', '07:00:00', '16:00:00', 'Morning shift covers breakfast and lunch'),
(22, 'Afternoon Shift', '12:00:00', '21:00:00', 'Afternoon shift covers lunch and dinner'),
(23, 'Evening Shift', '16:00:00', '01:00:00', 'Evening shift covers dinner and late night'),
(24, 'Split Shift', '07:00:00', '21:00:00', 'Split shift with break in between'),
(25, 'Morning Shift', '07:00:00', '16:00:00', 'Morning shift covers breakfast and lunch'),
(26, 'Afternoon Shift', '12:00:00', '21:00:00', 'Afternoon shift covers lunch and dinner'),
(27, 'Evening Shift', '16:00:00', '01:00:00', 'Evening shift covers dinner and late night'),
(28, 'Split Shift', '07:00:00', '21:00:00', 'Split shift with break in between');

-- --------------------------------------------------------

--
-- Table structure for table `table_assignments`
--

CREATE TABLE `table_assignments` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `assignment_date` date NOT NULL,
  `shift_id` int(11) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `table_assignments`
--

INSERT INTO `table_assignments` (`id`, `staff_id`, `table_id`, `assignment_date`, `shift_id`, `start_time`, `end_time`, `is_active`, `created_at`) VALUES
(1, 1, 1, '2026-03-13', NULL, NULL, NULL, 1, '2026-03-13 06:56:34'),
(2, 1, 2, '2026-03-13', NULL, NULL, NULL, 1, '2026-03-13 06:56:34'),
(3, 1, 3, '2026-03-13', NULL, NULL, NULL, 1, '2026-03-13 06:56:34'),
(4, 1, 4, '2026-03-13', NULL, NULL, NULL, 1, '2026-03-13 06:56:34'),
(5, 2, 5, '2026-03-13', NULL, NULL, NULL, 1, '2026-03-13 06:56:34'),
(6, 2, 6, '2026-03-13', NULL, NULL, NULL, 1, '2026-03-13 06:56:34'),
(7, 2, 7, '2026-03-13', NULL, NULL, NULL, 1, '2026-03-13 06:56:34'),
(8, 2, 8, '2026-03-13', NULL, NULL, NULL, 1, '2026-03-13 06:56:34'),
(9, 3, 9, '2026-03-13', NULL, NULL, NULL, 1, '2026-03-13 06:56:34'),
(10, 3, 10, '2026-03-13', NULL, NULL, NULL, 1, '2026-03-13 06:56:34'),
(11, 3, 11, '2026-03-13', NULL, NULL, NULL, 1, '2026-03-13 06:56:34'),
(12, 4, 12, '2026-03-13', NULL, NULL, NULL, 1, '2026-03-13 06:56:34'),
(13, 5, 13, '2026-03-13', NULL, NULL, NULL, 1, '2026-03-13 06:56:34'),
(14, 5, 14, '2026-03-13', NULL, NULL, NULL, 1, '2026-03-13 06:56:34');

-- --------------------------------------------------------

--
-- Table structure for table `table_reservations`
--

CREATE TABLE `table_reservations` (
  `id` int(11) NOT NULL,
  `reservation_number` varchar(20) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_phone` varchar(20) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `table_id` int(11) DEFAULT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `end_time` time DEFAULT NULL,
  `number_of_guests` int(11) NOT NULL,
  `status` enum('pending','confirmed','seated','completed','cancelled','no_show') DEFAULT 'pending',
  `source` enum('website','phone','walk_in','app','hotel_guest') DEFAULT 'phone',
  `special_requests` text DEFAULT NULL,
  `occasion` enum('birthday','anniversary','business','date','other') DEFAULT NULL,
  `is_walk_in` tinyint(1) DEFAULT 0,
  `waitlist_position` int(11) DEFAULT NULL,
  `estimated_wait_time` int(11) DEFAULT NULL COMMENT 'in minutes',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `table_reservations`
--

INSERT INTO `table_reservations` (`id`, `reservation_number`, `customer_id`, `guest_name`, `guest_phone`, `guest_email`, `table_id`, `reservation_date`, `reservation_time`, `end_time`, `number_of_guests`, `status`, `source`, `special_requests`, `occasion`, `is_walk_in`, `waitlist_position`, `estimated_wait_time`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'RES20250521001', 1, 'Michael Cruz', '09181234567', NULL, 5, '2026-03-13', '18:00:00', NULL, 2, 'confirmed', 'phone', 'Window seat preferred', NULL, 0, NULL, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(2, 'RES20250521002', 2, 'Jiyeon Kim', '09182345678', NULL, 7, '2026-03-13', '18:30:00', NULL, 6, 'confirmed', 'website', 'Birthday celebration', NULL, 0, NULL, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(3, 'RES20250521003', 3, 'Paolo Reyes', '09183456789', NULL, 2, '2026-03-13', '19:00:00', NULL, 4, 'pending', 'website', 'High chair needed', NULL, 0, NULL, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(4, 'RES20250521004', 4, 'Michelle Tan', '09184567890', NULL, 12, '2026-03-13', '19:30:00', NULL, 2, 'confirmed', 'phone', 'Anniversary', NULL, 0, NULL, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(5, 'RES20250521005', 6, 'Luis Garcia', '09186789012', NULL, 5, '2026-03-13', '20:30:00', NULL, 2, 'confirmed', 'website', 'Quiet area', NULL, 0, NULL, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(6, 'RES20250521006', 7, 'Jose Rivera', '09187890123', NULL, 11, '2026-03-13', '21:00:00', NULL, 8, '', 'phone', NULL, NULL, 0, NULL, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03');

-- --------------------------------------------------------

--
-- Table structure for table `waitlist`
--

CREATE TABLE `waitlist` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_phone` varchar(20) NOT NULL,
  `number_of_guests` int(11) NOT NULL,
  `requested_section` enum('main','outdoor','private','bar','any') DEFAULT 'any',
  `check_in_time` datetime NOT NULL,
  `estimated_wait_time` int(11) DEFAULT NULL,
  `status` enum('waiting','notified','seated','cancelled') DEFAULT 'waiting',
  `notification_sent` tinyint(1) DEFAULT 0,
  `notified_at` datetime DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `waitlist`
--

INSERT INTO `waitlist` (`id`, `customer_id`, `guest_name`, `guest_phone`, `number_of_guests`, `requested_section`, `check_in_time`, `estimated_wait_time`, `status`, `notification_sent`, `notified_at`, `special_requests`, `created_at`, `updated_at`) VALUES
(1, 7, 'Jose Rivera', '09187890123', 8, 'any', '2026-03-13 06:46:03', 20, 'waiting', 0, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(2, NULL, 'Maria Lopez', '09188901234', 4, 'any', '2026-03-13 06:46:03', 15, 'waiting', 0, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(3, NULL, 'David Tan', '09189012345', 2, 'any', '2026-03-13 06:46:03', 10, 'waiting', 0, NULL, NULL, '2026-03-13 06:46:03', '2026-03-13 06:46:03'),
(4, 7, 'Jose Rivera', '09187890123', 8, 'any', '2026-03-13 06:54:38', 20, 'waiting', 0, NULL, NULL, '2026-03-13 06:54:38', '2026-03-13 06:54:38'),
(5, NULL, 'Maria Lopez', '09188901234', 4, 'any', '2026-03-13 06:54:38', 15, 'waiting', 0, NULL, NULL, '2026-03-13 06:54:38', '2026-03-13 06:54:38'),
(6, NULL, 'David Tan', '09189012345', 2, 'any', '2026-03-13 06:54:38', 10, 'waiting', 0, NULL, NULL, '2026-03-13 06:54:38', '2026-03-13 06:54:38'),
(7, 7, 'Jose Rivera', '09187890123', 8, 'any', '2026-03-13 06:56:34', 20, 'waiting', 0, NULL, NULL, '2026-03-13 06:56:34', '2026-03-13 06:56:34'),
(8, NULL, 'Maria Lopez', '09188901234', 4, 'any', '2026-03-13 06:56:34', 15, 'waiting', 0, NULL, NULL, '2026-03-13 06:56:34', '2026-03-13 06:56:34'),
(9, NULL, 'David Tan', '09189012345', 2, 'any', '2026-03-13 06:56:34', 10, 'waiting', 0, NULL, NULL, '2026-03-13 06:56:34', '2026-03-13 06:56:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_code` (`customer_code`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_membership` (`membership_level`);
ALTER TABLE `customers` ADD FULLTEXT KEY `idx_customer_search` (`first_name`,`last_name`,`email`,`phone`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_invoice_number` (`invoice_number`);

--
-- Indexes for table `kitchen_stations`
--
ALTER TABLE `kitchen_stations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `station_code` (`station_code`);

--
-- Indexes for table `kitchen_tickets`
--
ALTER TABLE `kitchen_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `station_id` (`station_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_ticket_number` (`ticket_number`);

--
-- Indexes for table `kitchen_ticket_items`
--
ALTER TABLE `kitchen_ticket_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kitchen_ticket_id` (`kitchen_ticket_id`),
  ADD KEY `order_item_id` (`order_item_id`);

--
-- Indexes for table `menu_categories`
--
ALTER TABLE `menu_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_category_name` (`category_name`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_code` (`item_code`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_availability` (`is_available`),
  ADD KEY `idx_special` (`is_special`);
ALTER TABLE `menu_items` ADD FULLTEXT KEY `idx_search` (`item_name`,`description`);

--
-- Indexes for table `menu_item_variations`
--
ALTER TABLE `menu_item_variations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `order_type_id` (`order_type_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `table_id` (`table_id`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_status` (`order_status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_payment_status` (`payment_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_item_id` (`menu_item_id`),
  ADD KEY `variation_id` (`variation_id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_status` (`item_status`);

--
-- Indexes for table `order_item_modifiers`
--
ALTER TABLE `order_item_modifiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_item_id` (`order_item_id`);

--
-- Indexes for table `order_types`
--
ALTER TABLE `order_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_number` (`payment_number`),
  ADD KEY `payment_method_id` (`payment_method_id`),
  ADD KEY `processed_by` (`processed_by`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_payment_date` (`payment_date`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `method_code` (`method_code`);

--
-- Indexes for table `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `table_number` (`table_number`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_section` (`section`);

--
-- Indexes for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`staff_id`,`attendance_date`),
  ADD KEY `idx_date` (`attendance_date`);

--
-- Indexes for table `staff_members`
--
ALTER TABLE `staff_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_code` (`staff_code`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `idx_status` (`status`);
ALTER TABLE `staff_members` ADD FULLTEXT KEY `idx_staff_name` (`first_name`,`last_name`,`email`);

--
-- Indexes for table `staff_performance`
--
ALTER TABLE `staff_performance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `rated_by` (`rated_by`),
  ADD KEY `idx_date` (`rating_date`);

--
-- Indexes for table `staff_roles`
--
ALTER TABLE `staff_roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff_schedule`
--
ALTER TABLE `staff_schedule`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_staff_schedule` (`staff_id`,`schedule_date`),
  ADD KEY `shift_id` (`shift_id`),
  ADD KEY `idx_date` (`schedule_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `staff_shifts`
--
ALTER TABLE `staff_shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `table_assignments`
--
ALTER TABLE `table_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `table_id` (`table_id`),
  ADD KEY `shift_id` (`shift_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `table_reservations`
--
ALTER TABLE `table_reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reservation_number` (`reservation_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `table_id` (`table_id`),
  ADD KEY `idx_date` (`reservation_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_phone` (`guest_phone`);

--
-- Indexes for table `waitlist`
--
ALTER TABLE `waitlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_check_in` (`check_in_time`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kitchen_stations`
--
ALTER TABLE `kitchen_stations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `kitchen_tickets`
--
ALTER TABLE `kitchen_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kitchen_ticket_items`
--
ALTER TABLE `kitchen_ticket_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `menu_categories`
--
ALTER TABLE `menu_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu_item_variations`
--
ALTER TABLE `menu_item_variations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `order_item_modifiers`
--
ALTER TABLE `order_item_modifiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_types`
--
ALTER TABLE `order_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_members`
--
ALTER TABLE `staff_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `staff_performance`
--
ALTER TABLE `staff_performance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff_roles`
--
ALTER TABLE `staff_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `staff_schedule`
--
ALTER TABLE `staff_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `staff_shifts`
--
ALTER TABLE `staff_shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `table_assignments`
--
ALTER TABLE `table_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `table_reservations`
--
ALTER TABLE `table_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `waitlist`
--
ALTER TABLE `waitlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `kitchen_tickets`
--
ALTER TABLE `kitchen_tickets`
  ADD CONSTRAINT `kitchen_tickets_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kitchen_tickets_ibfk_2` FOREIGN KEY (`station_id`) REFERENCES `kitchen_stations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `kitchen_ticket_items`
--
ALTER TABLE `kitchen_ticket_items`
  ADD CONSTRAINT `kitchen_ticket_items_ibfk_1` FOREIGN KEY (`kitchen_ticket_id`) REFERENCES `kitchen_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kitchen_ticket_items_ibfk_2` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `menu_item_variations`
--
ALTER TABLE `menu_item_variations`
  ADD CONSTRAINT `menu_item_variations_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`order_type_id`) REFERENCES `order_types` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`variation_id`) REFERENCES `menu_item_variations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_item_modifiers`
--
ALTER TABLE `order_item_modifiers`
  ADD CONSTRAINT `order_item_modifiers_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `staff_members` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  ADD CONSTRAINT `staff_attendance_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff_members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_members`
--
ALTER TABLE `staff_members`
  ADD CONSTRAINT `staff_members_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `staff_roles` (`id`);

--
-- Constraints for table `staff_performance`
--
ALTER TABLE `staff_performance`
  ADD CONSTRAINT `staff_performance_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff_members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_performance_ibfk_2` FOREIGN KEY (`rated_by`) REFERENCES `staff_members` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `staff_schedule`
--
ALTER TABLE `staff_schedule`
  ADD CONSTRAINT `staff_schedule_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff_members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_schedule_ibfk_2` FOREIGN KEY (`shift_id`) REFERENCES `staff_shifts` (`id`);

--
-- Constraints for table `table_assignments`
--
ALTER TABLE `table_assignments`
  ADD CONSTRAINT `table_assignments_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff_members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `table_assignments_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `table_assignments_ibfk_3` FOREIGN KEY (`shift_id`) REFERENCES `staff_shifts` (`id`);

--
-- Constraints for table `table_reservations`
--
ALTER TABLE `table_reservations`
  ADD CONSTRAINT `table_reservations_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `table_reservations_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `waitlist`
--
ALTER TABLE `waitlist`
  ADD CONSTRAINT `waitlist_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
