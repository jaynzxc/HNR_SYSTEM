-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 13, 2026 at 07:02 PM
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
-- Database: `lucas_customer_portal`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `active_bookings_view`
-- (See below for the actual view)
--
CREATE TABLE `active_bookings_view` (
`booking_id` int(11)
,`booking_reference` varchar(20)
,`user_id` int(11)
,`guest_name` varchar(201)
,`room_number` varchar(10)
,`room_type` enum('deluxe_twin','ocean_suite','executive_suite','presidential_suite','standard_room')
,`check_in_date` date
,`check_out_date` date
,`number_of_guests` int(11)
,`total_amount` decimal(10,2)
,`booking_status` enum('pending','confirmed','checked_in','checked_out','cancelled','no_show')
,`payment_status` enum('pending','partial','paid','refunded')
);

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(100) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `food_orders`
--

CREATE TABLE `food_orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_reference` varchar(20) NOT NULL,
  `order_type` enum('dine_in','takeaway','room_delivery') NOT NULL,
  `order_status` enum('pending','confirmed','preparing','ready','delivered','cancelled') DEFAULT 'pending',
  `delivery_address` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `points_earned` int(11) DEFAULT 0,
  `points_used` int(11) DEFAULT 0,
  `delivery_room_number` varchar(10) DEFAULT NULL,
  `delivery_table_number` varchar(10) DEFAULT NULL,
  `estimated_ready_time` timestamp NULL DEFAULT NULL,
  `actual_delivery_time` timestamp NULL DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_orders`
--

INSERT INTO `food_orders` (`order_id`, `user_id`, `order_reference`, `order_type`, `order_status`, `delivery_address`, `total_amount`, `points_earned`, `points_used`, `delivery_room_number`, `delivery_table_number`, `estimated_ready_time`, `actual_delivery_time`, `special_instructions`, `created_at`, `updated_at`) VALUES
(1, 1, 'ORD20240317001', 'dine_in', '', NULL, 660.00, 33, 0, NULL, NULL, NULL, NULL, 'Extra spicy sisig please', '2024-03-17 12:15:00', '2026-03-12 07:21:17'),
(2, 1, 'ORD20240318001', 'room_delivery', '', NULL, 280.00, 14, 0, '101', NULL, NULL, NULL, 'No ice in drinks', '2024-03-18 14:30:00', '2026-03-12 07:21:17'),
(3, 2, 'ORD20231111001', 'dine_in', '', NULL, 450.00, 22, 0, NULL, NULL, NULL, NULL, 'Well-done salmon please', '2023-11-11 11:00:00', '2026-03-12 07:21:17'),
(4, 4, 'ORD20230606001', 'takeaway', '', NULL, 980.00, 49, 0, NULL, NULL, NULL, NULL, 'Separate containers requested', '2023-06-06 12:00:00', '2026-03-12 07:21:17'),
(1000, 1, 'FO20260313F095F0', 'takeaway', 'confirmed', '123 Test Street, Test City', 880.00, 0, 0, NULL, NULL, NULL, NULL, 'Test order please', '2026-03-13 06:29:35', '2026-03-13 06:29:35'),
(1001, 104, 'FO20260313AE79CE', 'takeaway', 'confirmed', '201', 1820.00, 0, 0, NULL, NULL, NULL, NULL, '', '2026-03-13 06:31:06', '2026-03-13 06:31:06'),
(1002, 104, 'FO20260313879967', 'takeaway', 'confirmed', '201', 500.00, 0, 0, NULL, NULL, NULL, NULL, '', '2026-03-13 06:54:00', '2026-03-13 06:54:00'),
(1003, 104, 'FO20260313A7D7C3', 'takeaway', 'confirmed', '201', 560.00, 0, 0, NULL, NULL, NULL, NULL, '', '2026-03-13 08:16:26', '2026-03-13 08:16:26'),
(1004, 104, 'FO2026031309303A', 'takeaway', 'confirmed', '201', 280.00, 0, 0, NULL, NULL, NULL, NULL, '', '2026-03-13 08:28:32', '2026-03-13 08:28:32');

-- --------------------------------------------------------

--
-- Table structure for table `food_order_items`
--

CREATE TABLE `food_order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(8,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `item_status` enum('ordered','preparing','ready','served','cancelled') DEFAULT 'ordered',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_order_items`
--

INSERT INTO `food_order_items` (`order_item_id`, `order_id`, `item_id`, `quantity`, `unit_price`, `subtotal`, `special_instructions`, `item_status`, `created_at`) VALUES
(1, 1, 8, 1, 320.00, 320.00, NULL, 'served', '2026-03-12 07:21:17'),
(2, 1, 9, 1, 290.00, 290.00, 'Extra spicy', 'served', '2026-03-12 07:21:17'),
(3, 1, 11, 1, 50.00, 50.00, NULL, 'served', '2026-03-12 07:21:17'),
(4, 2, 7, 1, 280.00, 280.00, NULL, 'served', '2026-03-12 07:21:17'),
(5, 3, 12, 1, 680.00, 680.00, 'Well-done', 'served', '2026-03-12 07:21:17'),
(6, 3, 18, 1, 180.00, 180.00, NULL, 'served', '2026-03-12 07:21:17'),
(7, 4, 8, 2, 320.00, 640.00, NULL, 'served', '2026-03-12 07:21:17'),
(8, 4, 9, 1, 290.00, 290.00, NULL, 'served', '2026-03-12 07:21:17'),
(9, 4, 15, 1, 50.00, 50.00, NULL, 'served', '2026-03-12 07:21:17'),
(1000, 1000, 1, 2, 280.00, 560.00, NULL, 'ordered', '2026-03-13 06:29:35'),
(1001, 1000, 4, 1, 320.00, 320.00, NULL, 'ordered', '2026-03-13 06:29:35'),
(1002, 1001, 18, 2, 280.00, 560.00, NULL, 'ordered', '2026-03-13 06:31:06'),
(1003, 1001, 12, 2, 180.00, 360.00, NULL, 'ordered', '2026-03-13 06:31:06'),
(1004, 1001, 9, 2, 450.00, 900.00, NULL, 'ordered', '2026-03-13 06:31:06'),
(1005, 1002, 17, 2, 250.00, 500.00, NULL, 'ordered', '2026-03-13 06:54:00'),
(1006, 1003, 18, 2, 280.00, 560.00, NULL, 'ordered', '2026-03-13 08:16:26'),
(1007, 1004, 18, 1, 280.00, 280.00, NULL, 'ordered', '2026-03-13 08:28:32');

-- --------------------------------------------------------

--
-- Table structure for table `hotel_bookings`
--

CREATE TABLE `hotel_bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `booking_reference` varchar(20) NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `number_of_guests` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `deposit_amount` decimal(10,2) DEFAULT 0.00,
  `booking_status` enum('pending','confirmed','checked_in','checked_out','cancelled','no_show') DEFAULT 'pending',
  `payment_status` enum('pending','partial','paid','refunded') DEFAULT 'pending',
  `special_requests` text DEFAULT NULL,
  `guest_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotel_bookings`
--

INSERT INTO `hotel_bookings` (`booking_id`, `user_id`, `room_id`, `booking_reference`, `check_in_date`, `check_out_date`, `number_of_guests`, `total_amount`, `deposit_amount`, `booking_status`, `payment_status`, `special_requests`, `guest_notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'HBK20240315001', '2024-03-15', '2024-03-17', 2, 8400.00, 2000.00, '', 'paid', 'Late check-in requested', NULL, '2026-03-12 07:21:17', '2026-03-12 07:21:17'),
(2, 1, 2, 'HBK20240410001', '2024-04-10', '2024-04-12', 2, 8400.00, 0.00, 'confirmed', 'paid', 'Early check-in if possible', NULL, '2026-03-12 07:21:17', '2026-03-12 07:21:17'),
(3, 2, 3, 'HBK20231110001', '2023-11-10', '2023-11-12', 3, 13800.00, 3000.00, '', 'paid', 'Anniversary celebration', NULL, '2026-03-12 07:21:17', '2026-03-12 07:21:17'),
(4, 4, 6, 'HBK20230605001', '2023-06-05', '2023-06-07', 6, 30000.00, 5000.00, '', 'paid', 'Business meeting arrangement', NULL, '2026-03-12 07:21:17', '2026-03-12 07:21:17'),
(1000, 104, 1, 'HB202603136D94DF', '2026-03-13', '2026-03-13', 2, 0.00, 0.00, 'confirmed', 'pending', '', NULL, '2026-03-13 04:46:30', '2026-03-13 04:46:30'),
(1001, 104, 1, 'HB20260313466184', '2026-03-13', '2026-03-13', 2, 0.00, 0.00, 'confirmed', 'pending', '', NULL, '2026-03-13 04:53:56', '2026-03-13 04:53:56'),
(1002, 104, 1, 'HB20260313D3FF6A', '2026-03-13', '2026-03-13', 2, 0.00, 0.00, 'confirmed', 'pending', '', NULL, '2026-03-13 05:25:33', '2026-03-13 05:25:33');

-- --------------------------------------------------------

--
-- Table structure for table `hotel_rooms`
--

CREATE TABLE `hotel_rooms` (
  `room_id` int(11) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `room_type` enum('deluxe_twin','ocean_suite','executive_suite','presidential_suite','standard_room') NOT NULL,
  `base_price_per_night` decimal(10,2) NOT NULL,
  `max_occupancy` int(11) NOT NULL,
  `bed_configuration` varchar(100) DEFAULT NULL,
  `amenities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`amenities`)),
  `room_status` enum('available','occupied','maintenance','out_of_order') DEFAULT 'available',
  `floor_number` int(11) DEFAULT NULL,
  `square_meters` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotel_rooms`
--

INSERT INTO `hotel_rooms` (`room_id`, `room_number`, `room_type`, `base_price_per_night`, `max_occupancy`, `bed_configuration`, `amenities`, `room_status`, `floor_number`, `square_meters`, `description`, `created_at`, `updated_at`) VALUES
(1, '101', 'deluxe_twin', 4200.00, 2, '2 Twin Beds', '[\"mini_bar\", \"air_conditioning\", \"work_desk\", \"safety_box\"]', 'available', 1, 28, 'Comfortable deluxe room with twin beds, perfect for business travelers.', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(2, '102', 'deluxe_twin', 4200.00, 2, '2 Twin Beds', '[\"mini_bar\", \"air_conditioning\", \"work_desk\", \"safety_box\", \"city_view\"]', 'available', 1, 28, 'Deluxe twin room with city view, ideal for friends or colleagues.', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(3, '201', 'ocean_suite', 6900.00, 3, '1 King Bed + 1 Sofa Bed', '[\"mini_bar\", \"air_conditioning\", \"balcony\", \"ocean_view\", \"living_area\", \"safety_box\"]', 'available', 2, 45, 'Spacious ocean suite with stunning sea views and separate living area.', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(4, '202', 'ocean_suite', 6900.00, 3, '1 King Bed + 1 Sofa Bed', '[\"mini_bar\", \"air_conditioning\", \"balcony\", \"ocean_view\", \"living_area\", \"safety_box\", \"jacuzzi\"]', 'available', 2, 48, 'Luxurious ocean suite with private jacuzzi and panoramic ocean views.', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(5, '301', 'executive_suite', 8500.00, 4, '1 King Bed + 2 Twin Beds', '[\"mini_bar\", \"air_conditioning\", \"balcony\", \"ocean_view\", \"living_area\", \"dining_area\", \"kitchenette\", \"safety_box\"]', 'available', 3, 65, 'Executive suite with full amenities, perfect for extended stays.', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(6, '401', 'presidential_suite', 15000.00, 6, '2 King Beds + 2 Queen Beds', '[\"mini_bar\", \"air_conditioning\", \"balcony\", \"ocean_view\", \"living_area\", \"dining_area\", \"full_kitchen\", \"safety_box\", \"jacuzzi\", \"butler_service\"]', 'available', 4, 120, 'Ultimate luxury presidential suite with butler service and full kitchen.', '2026-03-12 07:21:16', '2026-03-12 07:21:16');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_redemptions`
--

CREATE TABLE `loyalty_redemptions` (
  `redemption_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reward_id` int(11) NOT NULL,
  `redemption_reference` varchar(20) NOT NULL,
  `points_used` int(11) NOT NULL,
  `redemption_status` enum('pending','confirmed','used','expired','cancelled') DEFAULT 'pending',
  `redemption_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_date` timestamp NULL DEFAULT NULL,
  `usage_date` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_rewards`
--

CREATE TABLE `loyalty_rewards` (
  `reward_id` int(11) NOT NULL,
  `reward_name` varchar(200) NOT NULL,
  `reward_description` text DEFAULT NULL,
  `url_image` varchar(255) DEFAULT NULL,
  `reward_type` enum('free_item','discount','upgrade','service','experience') NOT NULL,
  `points_cost` int(11) NOT NULL,
  `monetary_value` decimal(8,2) DEFAULT NULL,
  `tier_requirement` enum('member','silver','gold','platinum') DEFAULT 'member',
  `reward_status` enum('available','unavailable','seasonal') DEFAULT 'available',
  `is_active` tinyint(1) DEFAULT 1,
  `redemption_instructions` text DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `usage_limit_per_user` int(11) DEFAULT NULL,
  `total_usage_limit` int(11) DEFAULT NULL,
  `current_usage_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loyalty_rewards`
--

INSERT INTO `loyalty_rewards` (`reward_id`, `reward_name`, `reward_description`, `url_image`, `reward_type`, `points_cost`, `monetary_value`, `tier_requirement`, `reward_status`, `is_active`, `redemption_instructions`, `terms_conditions`, `valid_from`, `valid_until`, `usage_limit_per_user`, `total_usage_limit`, `current_usage_count`, `created_at`, `updated_at`) VALUES
(1, 'Free Coffee / Tea', 'Any hot beverage at Azure Lounge', 'Menu Pics/Brewed Coffee.jpeg', 'free_item', 240, 120.00, 'member', 'available', 1, 'Present this reward at Azure Lounge to claim your free hot beverage.', 'Valid for any regular coffee or tea. Not applicable for specialty drinks.', '2024-01-01', '2025-12-31', 5, 1000, 0, '2026-03-12 07:21:16', '2026-03-13 08:48:45'),
(2, 'Complimentary Breakfast', 'For one person at Azure Restaurant', 'Loyalty/Complimentary_Breakfast.jpg', 'free_item', 480, 350.00, 'member', 'available', 1, 'Show this reward at Azure Restaurant breakfast buffet.', 'Valid for breakfast buffet only. Cannot be combined with other promotions.', '2024-01-01', '2025-12-31', 3, 500, 0, '2026-03-12 07:21:16', '2026-03-13 09:09:28'),
(4, 'Welcome Drink (2 pax)', 'Signature cocktail or mocktail', 'Loyalty/Welcome_drinks.jpg', 'free_item', 360, 280.00, 'member', 'available', 1, 'Present at Azure Restaurant or bar to claim welcome drinks.', 'Valid for any signature cocktail or mocktail. Alcoholic options for 18+ only.', '2024-01-01', '2025-12-31', 4, 800, 0, '2026-03-12 07:21:16', '2026-03-13 09:09:28'),
(5, 'Room Upgrade (next stay)', 'Deluxe to suite (subject to availability)', NULL, 'upgrade', 1200, 0.00, 'gold', 'available', 1, 'Request upgrade when making next booking.', 'Upgrade from deluxe room to suite, subject to availability. One category upgrade only.', '2024-01-01', '2025-12-31', 1, 100, 0, '2026-03-12 07:21:16', '2026-03-13 09:12:25'),
(6, '₱500 Discount', 'On any hotel booking', 'Loyalty/Discount.jpg', 'discount', 800, 500.00, 'silver', 'available', 1, 'Apply discount code at checkout when booking hotel room.', 'Valid for minimum booking of ₱2,000. Cannot be combined with other discounts.', '2024-01-01', '2025-12-31', 3, 300, 0, '2026-03-12 07:21:16', '2026-03-13 09:09:28'),
(7, 'Free Halo-Halo', 'Signature Filipino dessert', 'Loyalty/Halohalo.jpg', 'free_item', 150, 150.00, 'member', 'available', 1, 'Present this reward at Azure Restaurant to claim free Halo-Halo.', 'Valid for regular Halo-Halo only. No substitutions allowed.', '2024-01-01', '2025-12-31', 10, 2000, 0, '2026-03-12 07:21:16', '2026-03-13 09:09:28');

-- --------------------------------------------------------

--
-- Table structure for table `menu_categories`
--

CREATE TABLE `menu_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_categories`
--

INSERT INTO `menu_categories` (`category_id`, `category_name`, `category_description`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'Appetizers', 'Start your meal with our delicious appetizers', 1, 1, '2026-03-12 07:21:16'),
(2, 'Mains', 'Main course dishes featuring local and international cuisine', 2, 1, '2026-03-12 07:21:16'),
(3, 'Desserts', 'Sweet endings to your perfect meal', 3, 1, '2026-03-12 07:21:16'),
(4, 'Beverages', 'Refreshing drinks and beverages', 4, 1, '2026-03-12 07:21:16'),
(5, 'Soups', 'Warm and comforting soups', 5, 1, '2026-03-12 07:21:16'),
(6, 'Salads', 'Fresh and healthy salad options', 6, 1, '2026-03-12 07:21:16');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `item_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `item_name` varchar(200) NOT NULL,
  `item_description` text DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `item_status` enum('available','unavailable','seasonal') DEFAULT 'available',
  `preparation_time_minutes` int(11) DEFAULT NULL,
  `allergen_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`allergen_info`)),
  `dietary_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dietary_info`)),
  `image_url` varchar(500) DEFAULT NULL,
  `spicy_level` enum('none','mild','medium','hot','extra_hot') DEFAULT 'none',
  `is_signature` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`item_id`, `category_id`, `item_name`, `item_description`, `price`, `item_status`, `preparation_time_minutes`, `allergen_info`, `dietary_info`, `image_url`, `spicy_level`, `is_signature`, `created_at`, `updated_at`) VALUES
(1, 1, 'Calamares', 'Deep-fried squid rings with garlic aioli', 280.00, 'available', 15, '[\"seafood\", \"gluten\"]', '[\"gluten_free\"]', 'Menu Pics/Calamares.jpeg', 'none', 0, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(2, 1, 'Lumpiang Shanghai', 'Filipino spring rolls with sweet and sour sauce', 220.00, 'available', 12, '[\"meat\", \"gluten\"]', '[]', 'Menu Pics/Lumping Shanghai.jpeg', 'none', 1, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(3, 1, 'Cheese Platter', 'Selection of local and imported cheeses', 380.00, 'available', 10, '[\"dairy\"]', '[\"vegetarian\"]', 'Menu Pics/Cheese Platter.jpeg', 'none', 0, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(4, 2, 'Sinigang na Baboy', 'Tamarind soup with pork and vegetables', 320.00, 'available', 25, '[]', '[\"gluten_free\"]', 'Menu Pics/Sinigang na Baboy.jpeg', 'none', 1, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(5, 2, 'Sizzling Sisig', 'Sizzling chopped pork with onion and egg', 290.00, 'available', 20, '[\"meat\"]', '[]', 'Menu Pics/Sizzling Sisig.jpeg', 'medium', 1, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(6, 2, 'Crispy Pata', 'Deep-fried pork knuckle with soy-vinegar dip', 550.00, 'available', 30, '[\"meat\"]', '[]', 'Menu Pics/Crispy Pata.jpeg', 'none', 1, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(7, 2, 'Garlic Rice', 'Fried rice with garlic, plain', 50.00, 'available', 8, '[\"gluten\"]', '[\"vegetarian\"]', 'Menu Pics/Garlic Rice.jpeg', 'none', 0, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(8, 2, 'Grilled Salmon', 'Atlantic salmon with lemon butter sauce', 680.00, 'available', 22, '[\"fish\"]', '[\"gluten_free\"]', 'Menu Pics/Grilled Salmon.jpeg', 'none', 0, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(9, 2, 'Beef Steak', 'Filipino-style beef steak with onions', 450.00, 'available', 25, '[\"meat\"]', '[]', 'Menu Pics/Iced Tea.jpeg', 'none', 0, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(10, 3, 'Halo-Halo', 'Shaved ice with mixed fruits, leche flan, and ube', 150.00, 'available', 10, '[\"dairy\"]', '[\"vegetarian\"]', 'Menu Pics/Halo-Halo.jpeg', 'none', 1, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(11, 3, 'Leche Flan', 'Creamy caramel custard', 120.00, 'available', 5, '[\"dairy\", \"egg\"]', '[\"vegetarian\"]', 'Menu Pics/Leche Flan.jpeg', 'none', 1, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(12, 3, 'Chocolate Cake', 'Rich chocolate cake with ganache', 180.00, 'available', 8, '[\"dairy\", \"gluten\", \"egg\"]', '[]', 'Menu Pics/Chocolate Cake.jpeg', 'none', 0, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(13, 4, 'Fresh Buko Juice', 'Fresh coconut juice with pulp', 90.00, 'available', 5, '[]', '[\"vegan\", \"gluten_free\"]', 'Menu Pics/Buko Juice.jpeg', 'none', 0, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(14, 4, 'Brewed Coffee', 'Locally brewed coffee', 120.00, 'available', 3, '[]', '[\"vegan\", \"gluten_free\"]', 'Menu Pics/Brewed Coffee.jpeg', 'none', 0, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(15, 4, 'Iced Tea', 'Freshly brewed iced tea', 80.00, 'available', 3, '[]', '[\"vegan\", \"gluten_free\"]', 'Menu Pics/Iced Tea.jpeg', 'none', 0, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(16, 4, 'Mango Shake', 'Fresh mango smoothie', 140.00, 'available', 5, '[\"dairy\"]', '[\"vegetarian\"]', 'Menu Pics/Mango Shake.jpeg', 'none', 0, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(17, 5, 'Chicken Tinola', 'Chicken soup with ginger and vegetables', 250.00, 'available', 20, '[]', '[\"gluten_free\"]', 'Menu Pics/Chicken Tinola.jpeg', 'none', 0, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(18, 5, 'Beef Nilaga', 'Beef soup with vegetables', 280.00, 'available', 25, '[\"meat\"]', '[\"gluten_free\"]', 'Menu Pics/Beef Nilaga.jpeg', 'none', 0, '2026-03-12 07:21:16', '2026-03-13 08:11:59'),
(19, 6, 'Garden Salad', 'Mixed greens with vinaigrette dressing', 180.00, 'available', 10, '[]', '[\"vegan\", \"gluten_free\"]', 'Menu Pics/Garden Salad.jpeg', 'none', 0, '2026-03-12 07:21:16', '2026-03-13 08:15:43'),
(20, 6, 'Caesar Salad', 'Romaine lettuce with Caesar dressing and croutons', 220.00, 'available', 12, '[\"dairy\", \"gluten\", \"egg\"]', '[\"vegetarian\"]', 'Menu Pics/Caesar Salad.jpeg', 'none', 0, '2026-03-12 07:21:16', '2026-03-13 08:15:43');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `notification_type` enum('booking','payment','loyalty','promo','system','reminder','review') NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `priority_level` enum('low','medium','high','urgent') DEFAULT 'medium',
  `action_url` varchar(500) DEFAULT NULL,
  `action_text` varchar(100) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `sent_via` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sent_via`)),
  `related_entity_type` enum('hotel_booking','restaurant_reservation','food_order','loyalty_reward','payment') DEFAULT NULL,
  `related_entity_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `notification_type`, `title`, `message`, `is_read`, `priority_level`, `action_url`, `action_text`, `expires_at`, `sent_via`, `related_entity_type`, `related_entity_id`, `created_at`) VALUES
(1, 1, 'booking', 'Hotel Booking Confirmed', 'Your hotel booking HBK20240315001 has been confirmed. Check-in: March 15, 2024', 0, 'high', 'my_reservation.html', 'View Booking', NULL, NULL, 'hotel_booking', 1, '2024-03-15 02:35:00'),
(2, 1, 'loyalty', 'Points Earned', 'You earned 33 points from your recent food order!', 0, 'medium', 'loyalty_rewards.html', 'View Rewards', NULL, NULL, 'food_order', 1, '2024-03-17 12:20:00'),
(3, 1, 'promo', 'Special Weekend Offer', 'Get 20% off on all weekend dining reservations this month!', 0, 'low', 'restaurant_reservation.html', 'Book Now', NULL, NULL, NULL, NULL, '2024-03-18 01:00:00'),
(4, 2, 'booking', 'Ocean Suite Available', 'Your preferred ocean suite is available for your dates!', 1, 'medium', 'hotel_booking.html', 'Book Now', NULL, NULL, NULL, NULL, '2023-11-09 06:00:00'),
(5, 2, 'payment', 'Payment Successful', 'Your payment of ₱6,900 for booking HBK20231110001 has been processed.', 1, 'high', 'payments.html', 'View Receipt', NULL, NULL, 'hotel_booking', 2, '2023-11-10 06:25:00'),
(6, 3, 'reminder', 'Reservation Tomorrow', 'Don\'t forget about your restaurant reservation tomorrow at 6:30 PM.', 0, 'medium', 'restaurant_reservation.html', 'View Details', NULL, NULL, 'restaurant_reservation', 2, '2024-01-19 10:00:00'),
(7, 3, 'loyalty', 'Welcome to Lùcas', 'Welcome! You earned 50 bonus points for joining our loyalty program.', 1, 'high', 'loyalty_rewards.html', 'View Points', NULL, NULL, NULL, NULL, '2024-01-20 01:00:00'),
(8, 4, 'system', 'Platinum Benefits', 'As a Platinum member, enjoy exclusive benefits and priority service.', 1, 'medium', 'loyalty_rewards.html', 'Learn More', NULL, NULL, NULL, NULL, '2023-06-05 08:50:00'),
(9, 4, 'promo', 'VIP Event Invitation', 'You\'re invited to our exclusive wine tasting event this Friday.', 0, 'high', NULL, 'RSVP Now', NULL, NULL, NULL, NULL, '2023-06-06 02:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_reference` varchar(50) NOT NULL,
  `payment_type` enum('hotel_booking','restaurant_reservation','food_order','loyalty_reward') NOT NULL,
  `related_entity_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method_id` int(11) DEFAULT NULL,
  `status` enum('pending','processing','completed','failed','cancelled','refunded') DEFAULT 'pending',
  `payment_gateway` varchar(50) DEFAULT NULL,
  `gateway_transaction_id` varchar(100) DEFAULT NULL,
  `processing_fee` decimal(8,2) DEFAULT 0.00,
  `discount_amount` decimal(8,2) DEFAULT 0.00,
  `tax_amount` decimal(8,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'PHP',
  `due_date` timestamp NULL DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `payment_description` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `payment_method_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `method_type` enum('credit_card','debit_card','gcash','maya','bank_transfer','cash') NOT NULL,
  `method_nickname` varchar(100) DEFAULT NULL,
  `provider_name` varchar(100) DEFAULT NULL,
  `account_number_encrypted` varchar(255) DEFAULT NULL,
  `expiry_date` varchar(10) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`payment_method_id`, `user_id`, `method_type`, `method_nickname`, `provider_name`, `account_number_encrypted`, `expiry_date`, `is_default`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'gcash', 'Personal GCash', 'GCash', '****1234', NULL, 1, 1, '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(2, 1, 'credit_card', 'Company Credit Card', 'Visa', '****5678', '12/25', 0, 1, '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(3, 2, 'gcash', 'Main GCash', 'GCash', '****9876', NULL, 1, 1, '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(4, 2, 'maya', 'Maya Wallet', 'Maya', '****5432', NULL, 0, 1, '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(5, 3, 'cash', 'Cash Payment', 'Cash', NULL, NULL, 1, 1, '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(6, 4, 'credit_card', 'Premium Credit Card', 'Mastercard', '****1111', '09/26', 1, 1, '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(7, 4, 'gcash', 'Backup GCash', 'GCash', '****2222', NULL, 0, 1, '2026-03-12 07:21:16', '2026-03-12 07:21:16');

-- --------------------------------------------------------

--
-- Table structure for table `points_history`
--

CREATE TABLE `points_history` (
  `history_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `points_change` int(11) NOT NULL,
  `points_balance_after` int(11) NOT NULL,
  `transaction_type` enum('earn','redeem','expire','adjust') NOT NULL,
  `source_type` enum('hotel_stay','dining','promo','reward_redemption','manual_adjust','signup_bonus') NOT NULL,
  `source_id` int(11) DEFAULT NULL,
  `description` varchar(500) NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `points_history`
--

INSERT INTO `points_history` (`history_id`, `user_id`, `points_change`, `points_balance_after`, `transaction_type`, `source_type`, `source_id`, `description`, `expires_at`, `created_at`) VALUES
(1, 1, 50, 50, 'earn', 'signup_bonus', NULL, 'Welcome bonus points for joining', NULL, '2024-03-15 01:00:00'),
(2, 1, 210, 260, 'earn', 'hotel_stay', 1, 'Points earned from hotel booking', NULL, '2024-03-15 02:30:00'),
(3, 1, 10, 270, 'earn', 'dining', 1, 'Points earned from restaurant reservation', NULL, '2024-03-16 11:45:00'),
(4, 1, 33, 303, 'earn', 'dining', 1, 'Points earned from food order', NULL, '2024-03-17 12:15:00'),
(5, 1, -240, 63, 'redeem', 'reward_redemption', 1, 'Points used for Free Coffee reward', NULL, '2024-03-19 07:30:00'),
(6, 1, 1177, 1240, 'earn', 'manual_adjust', NULL, 'Points adjustment for loyalty tier upgrade', NULL, '2024-03-20 03:00:00'),
(7, 2, 25, 25, 'earn', 'signup_bonus', NULL, 'Welcome bonus points for joining', NULL, '2023-11-10 01:00:00'),
(8, 2, 345, 370, 'earn', 'hotel_stay', 2, 'Points earned from hotel booking', NULL, '2023-11-10 06:20:00'),
(9, 2, 22, 392, 'earn', 'dining', 2, 'Points earned from food order', NULL, '2023-11-11 11:00:00'),
(10, 2, 288, 680, 'earn', 'promo', NULL, 'Promotional bonus points', NULL, '2023-11-15 02:00:00'),
(11, 3, 50, 50, 'earn', 'signup_bonus', NULL, 'Welcome bonus points for joining', NULL, '2024-01-20 00:00:00'),
(12, 3, 7, 57, 'earn', 'dining', 2, 'Points earned from restaurant reservation', NULL, '2024-01-20 10:30:00'),
(13, 3, 93, 150, 'earn', 'manual_adjust', NULL, 'Welcome bonus adjustment', NULL, '2024-01-21 01:00:00'),
(14, 2, 100, 100, 'earn', 'signup_bonus', NULL, 'Welcome bonus points for joining', NULL, '2023-06-05 00:00:00'),
(15, 4, 750, 850, 'earn', 'hotel_stay', 3, 'Points earned from presidential suite booking', NULL, '2023-06-05 08:45:00'),
(16, 4, 49, 899, 'earn', 'dining', 3, 'Points earned from food order', NULL, '2023-06-06 12:00:00'),
(17, 4, 1251, 2150, 'earn', 'manual_adjust', NULL, 'Platinum tier bonus points', NULL, '2023-06-10 03:00:00'),
(1000, 100, 50, 50, 'earn', 'signup_bonus', NULL, 'Welcome bonus points for joining', NULL, '2026-03-12 07:49:51');

--
-- Triggers `points_history`
--
DELIMITER $$
CREATE TRIGGER `update_user_points_after_history` AFTER INSERT ON `points_history` FOR EACH ROW BEGIN
    UPDATE users 
    SET loyalty_points = NEW.points_balance_after 
    WHERE user_id = NEW.user_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_reservations`
--

CREATE TABLE `restaurant_reservations` (
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `table_id` int(11) DEFAULT NULL,
  `reservation_reference` varchar(20) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `number_of_guests` int(11) NOT NULL,
  `reservation_status` enum('pending','confirmed','seated','completed','cancelled','no_show') DEFAULT 'pending',
  `special_requests` text DEFAULT NULL,
  `occasion_type` varchar(50) DEFAULT NULL,
  `deposit_amount` decimal(10,2) DEFAULT 0.00,
  `deposit_paid` tinyint(1) DEFAULT 0,
  `points_earned` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurant_reservations`
--

INSERT INTO `restaurant_reservations` (`reservation_id`, `user_id`, `table_id`, `reservation_reference`, `reservation_date`, `reservation_time`, `number_of_guests`, `reservation_status`, `special_requests`, `occasion_type`, `deposit_amount`, `deposit_paid`, `points_earned`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 'RSV20240316001', '2024-03-16', '19:30:00', 4, 'completed', 'Birthday celebration', NULL, 200.00, 1, 10, '2026-03-12 07:21:17', '2026-03-12 07:21:17'),
(2, 1, NULL, 'RSV20240320001', '2024-03-20', '20:00:00', 2, 'confirmed', 'Anniversary dinner', NULL, 0.00, 0, 0, '2026-03-12 07:21:17', '2026-03-12 07:21:17'),
(3, 2, 4, 'RSV20231111001', '2023-11-11', '19:00:00', 2, 'completed', 'Business dinner', NULL, 150.00, 1, 7, '2026-03-12 07:21:17', '2026-03-12 07:21:17'),
(4, 3, NULL, 'RSV20240120001', '2024-01-20', '18:30:00', 3, 'completed', 'Family dinner', NULL, 150.00, 1, 7, '2026-03-12 07:21:17', '2026-03-12 07:21:17'),
(1000, 104, NULL, '', '2026-03-13', '12:00:00', 5, 'confirmed', '', NULL, 0.00, 0, 0, '2026-03-13 04:17:35', '2026-03-13 05:27:57'),
(1002, 104, NULL, 'RS20260313898B7C', '2026-03-13', '11:30:00', 2, 'confirmed', '', NULL, 0.00, 0, 0, '2026-03-13 05:32:56', '2026-03-13 05:32:56'),
(1003, 104, NULL, 'RS202603136BE277', '2026-03-13', '11:00:00', 2, 'confirmed', '', NULL, 0.00, 0, 0, '2026-03-13 08:44:54', '2026-03-13 08:44:54'),
(1004, 104, NULL, 'RS20260313FC626D', '2026-03-13', '19:00:00', 2, 'confirmed', '', NULL, 0.00, 0, 0, '2026-03-13 08:50:07', '2026-03-13 08:50:07'),
(1006, 104, NULL, 'RS20260313171690', '2026-03-13', '11:30:00', 2, 'confirmed', '', NULL, 0.00, 0, 0, '2026-03-13 08:53:21', '2026-03-13 08:53:21'),
(1009, 104, NULL, 'RS20260313396DF3', '2026-03-13', '11:30:00', 2, 'confirmed', '', NULL, 0.00, 0, 0, '2026-03-13 08:57:07', '2026-03-13 08:57:07'),
(1011, 104, NULL, 'RS20260313CB1D63', '2026-03-13', '11:00:00', 2, 'confirmed', '', NULL, 0.00, 0, 0, '2026-03-13 09:01:48', '2026-03-13 09:01:48'),
(1012, 104, NULL, 'RS202603137D6712', '2026-03-13', '11:00:00', 2, 'confirmed', '', NULL, 0.00, 0, 0, '2026-03-13 09:04:55', '2026-03-13 09:04:55'),
(1013, 104, NULL, 'RS20260313F4EA7C', '2026-03-13', '11:30:00', 2, 'confirmed', '', NULL, 0.00, 0, 0, '2026-03-13 09:06:55', '2026-03-13 09:06:55');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_tables`
--

CREATE TABLE `restaurant_tables` (
  `table_id` int(11) NOT NULL,
  `table_number` varchar(10) NOT NULL,
  `table_type` enum('2_person','4_person','6_person','8_person','booth','private') NOT NULL,
  `max_capacity` int(11) NOT NULL,
  `table_status` enum('available','reserved','occupied','maintenance') DEFAULT 'available',
  `location_area` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurant_tables`
--

INSERT INTO `restaurant_tables` (`table_id`, `table_number`, `table_type`, `max_capacity`, `table_status`, `location_area`, `created_at`, `updated_at`) VALUES
(1, 'T1', '2_person', 2, 'available', 'main_dining', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(2, 'T2', '2_person', 2, 'available', 'main_dining', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(3, 'T3', '4_person', 4, 'available', 'main_dining', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(4, 'T4', '4_person', 4, 'available', 'main_dining', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(5, 'T5', '4_person', 4, 'available', 'main_dining', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(6, 'T6', '6_person', 6, 'available', 'main_dining', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(7, 'T7', '6_person', 6, 'available', 'main_dining', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(8, 'T8', '8_person', 8, 'available', 'main_dining', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(9, 'B1', 'booth', 4, 'available', 'terrace', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(10, 'B2', 'booth', 4, 'available', 'terrace', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(11, 'P1', 'private', 12, 'available', 'private_room', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(12, 'P2', 'private', 20, 'available', 'private_room', '2026-03-12 07:21:16', '2026-03-12 07:21:16');

-- --------------------------------------------------------

--
-- Stand-in structure for view `today_reservations_view`
-- (See below for the actual view)
--
CREATE TABLE `today_reservations_view` (
`reservation_id` int(11)
,`reservation_reference` varchar(20)
,`user_id` int(11)
,`guest_name` varchar(201)
,`reservation_date` date
,`reservation_time` time
,`number_of_guests` int(11)
,`table_number` varchar(10)
,`reservation_status` enum('pending','confirmed','seated','completed','cancelled','no_show')
,`special_requests` text
);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_reference` varchar(30) NOT NULL,
  `transaction_type` enum('payment','refund','deposit','points_earn','points_redeem') NOT NULL,
  `related_entity_type` enum('hotel_booking','restaurant_reservation','food_order','loyalty_reward','payment') DEFAULT NULL,
  `related_entity_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method_id` int(11) DEFAULT NULL,
  `transaction_status` enum('pending','processing','completed','failed','cancelled','refunded') DEFAULT 'pending',
  `points_earned` int(11) DEFAULT 0,
  `points_used` int(11) DEFAULT 0,
  `processing_fee` decimal(8,2) DEFAULT 0.00,
  `transaction_description` text DEFAULT NULL,
  `external_transaction_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `transaction_reference`, `transaction_type`, `related_entity_type`, `related_entity_id`, `amount`, `payment_method_id`, `transaction_status`, `points_earned`, `points_used`, `processing_fee`, `transaction_description`, `external_transaction_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'TXN20240315001', 'payment', 'hotel_booking', 1, 4200.00, 1, 'completed', 210, 0, 0.00, 'Hotel booking payment - Room 101', NULL, '2024-03-15 02:30:00', '2026-03-12 07:21:16'),
(2, 1, 'TXN20240316001', 'payment', 'restaurant_reservation', 1, 200.00, 1, 'completed', 10, 0, 0.00, 'Restaurant reservation deposit', NULL, '2024-03-16 11:45:00', '2026-03-12 07:21:16'),
(3, 1, 'TXN20240317001', 'payment', 'food_order', 1, 660.00, 1, 'completed', 33, 0, 0.00, 'Food order - Sinigang, Sisig, Rice', NULL, '2024-03-17 12:15:00', '2026-03-12 07:21:16'),
(4, 1, 'TXN20240318001', 'points_earn', NULL, NULL, 0.00, NULL, 'completed', 0, 0, 0.00, 'Welcome bonus points', NULL, '2024-03-18 01:00:00', '2026-03-12 07:21:16'),
(5, 1, 'TXN20240319001', 'points_redeem', 'loyalty_reward', 1, 0.00, NULL, 'completed', 0, 0, 0.00, 'Redeemed Free Coffee', NULL, '2024-03-19 07:30:00', '2026-03-12 07:21:16'),
(6, 2, 'TXN20231110001', 'payment', 'hotel_booking', 2, 6900.00, 3, 'completed', 345, 0, 0.00, 'Hotel booking payment - Ocean Suite', NULL, '2023-11-10 06:20:00', '2026-03-12 07:21:16'),
(7, 2, 'TXN20231111001', 'payment', 'food_order', 2, 450.00, 3, 'completed', 22, 0, 0.00, 'Food order - Grilled Salmon, Salad', NULL, '2023-11-11 11:00:00', '2026-03-12 07:21:16'),
(8, 3, 'TXN20240120001', 'payment', 'restaurant_reservation', 2, 150.00, 5, 'completed', 7, 0, 0.00, 'Restaurant reservation deposit', NULL, '2024-01-20 10:30:00', '2026-03-12 07:21:16'),
(9, 4, 'TXN20230605001', 'payment', 'hotel_booking', 3, 15000.00, 6, 'completed', 750, 0, 0.00, 'Hotel booking payment - Presidential Suite', NULL, '2023-06-05 08:45:00', '2026-03-12 07:21:16'),
(10, 4, 'TXN20230606001', 'payment', 'food_order', 3, 980.00, 6, 'completed', 49, 0, 0.00, 'Food order - multiple items', NULL, '2023-06-06 12:00:00', '2026-03-12 07:21:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `alternative_phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','prefer_not_to_say') DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `street_address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `preferred_language` varchar(50) DEFAULT 'English',
  `email_verified` tinyint(1) DEFAULT 0,
  `phone_verified` tinyint(1) DEFAULT 0,
  `user_role` enum('customer','admin','restaurant_manager','hotel_manager') DEFAULT 'customer',
  `membership_tier` enum('member','silver','gold','platinum') DEFAULT 'member',
  `loyalty_points` int(11) DEFAULT 0,
  `join_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `profile_photo_url` text DEFAULT NULL,
  `account_status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `phone`, `alternative_phone`, `password_hash`, `date_of_birth`, `gender`, `nationality`, `street_address`, `city`, `postal_code`, `country`, `preferred_language`, `email_verified`, `phone_verified`, `user_role`, `membership_tier`, `loyalty_points`, `join_date`, `last_login`, `profile_photo_url`, `account_status`, `created_at`, `updated_at`) VALUES
(1, 'Mia', 'Cruz', 'mia.cruz@email.com', '+63 917 555 1234', NULL, '$2y$10$bHfxOUmryjUDI/qnGLZdTe5wu8zRy9gEFjLPFvoT6oYuF1zmsC3Cu', '1994-05-12', 'female', 'Filipino', '15 B. Gonzales St., Barangay San Antonio', 'Makati', '1203', 'Philippines', 'English', 1, 1, 'customer', 'gold', 1240, '2024-03-14 16:00:00', '2026-03-12 14:06:22', NULL, 'active', '2026-03-12 07:21:15', '2026-03-12 14:06:22'),
(2, 'Admin', 'User', 'admin@lucas.stay', '+63 912 345 6789', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'English', 1, 1, 'admin', 'platinum', 100, '2023-12-31 16:00:00', NULL, NULL, 'active', '2026-03-12 07:21:16', '2026-03-12 07:21:17'),
(3, 'Restaurant', 'Manager', 'manager@lucas.stay', '+63 923 456 7890', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'English', 1, 1, 'restaurant_manager', 'gold', 150, '2023-12-31 16:00:00', NULL, NULL, 'active', '2026-03-12 07:21:16', '2026-03-12 07:21:17'),
(4, 'Hotel', 'Manager', 'hotel@lucas.stay', '+63 934 567 8901', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'English', 1, 1, 'hotel_manager', 'gold', 2150, '2023-12-31 16:00:00', NULL, NULL, 'active', '2026-03-12 07:21:16', '2026-03-12 07:21:17'),
(5, 'Juan', 'Mateo', 'juan.mateo@email.com', '+63 912 345 6789', NULL, '', '1985-08-22', 'male', 'Filipino', NULL, 'Quezon City', NULL, 'Philippines', 'English', 1, 1, 'customer', 'silver', 680, '2023-11-09 16:00:00', NULL, NULL, 'active', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(6, 'Sofia', 'Reyes', 'sofia.reyes@email.com', '+63 923 456 7890', NULL, '', '1992-03-15', 'female', 'Filipino', NULL, 'Manila', NULL, 'Philippines', 'English', 1, 0, 'customer', 'member', 150, '2024-01-19 16:00:00', NULL, NULL, 'active', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(7, 'Carlos', 'Santos', 'carlos.santos@email.com', '+63 934 567 8901', NULL, '', '1988-11-30', 'male', 'Filipino', NULL, 'Cebu City', NULL, 'Philippines', 'English', 1, 1, 'customer', 'platinum', 2150, '2023-06-04 16:00:00', NULL, NULL, 'active', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(100, 'roldan', 'tiu', 'roldantiu89@gmail.com', '09204381084', '03875659597', '$2y$10$UjG33GhvFHxdZt5Ht2IaG.16xY.xP0YZwdxWuavPu/3YHLAKq7fXi', '2004-08-28', 'male', 'filipino', '75 republic avenue', 'quezon city', '1118', 'Philippines', 'English', 0, 0, 'customer', 'member', 50, '2026-03-12 07:49:51', '2026-03-13 09:13:25', NULL, 'active', '2026-03-12 07:49:51', '2026-03-13 09:13:25'),
(104, 'test', 'test', 'test@example.com', '09204381084', '09204381084', '$2y$10$zAot8NnS.8ZL5PEHyn4ytOoXJzLsIg/ET6ArWGM0YM7i7UWoEsb1i', '2004-08-28', 'male', 'filipino', '75 republic ave', 'quezon city', '1118', 'Philippines', 'English', 1, 0, 'customer', 'platinum', 500247, '2026-03-13 03:14:04', '2026-03-13 07:50:09', NULL, 'active', '2026-03-13 03:14:04', '2026-03-13 09:06:55');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `update_users_timestamp` BEFORE UPDATE ON `users` FOR EACH ROW BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_dashboard_view`
-- (See below for the actual view)
--
CREATE TABLE `user_dashboard_view` (
`user_id` int(11)
,`full_name` varchar(201)
,`email` varchar(255)
,`membership_tier` enum('member','silver','gold','platinum')
,`loyalty_points` int(11)
,`profile_photo_url` text
,`total_hotel_bookings` bigint(21)
,`total_restaurant_reservations` bigint(21)
,`total_food_orders` bigint(21)
,`unread_notifications` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `user_notification_preferences`
--

CREATE TABLE `user_notification_preferences` (
  `preference_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_category` enum('booking_confirmations','reservation_reminders','payment_updates','loyalty_updates','promotional_offers','system_announcements') NOT NULL,
  `email_enabled` tinyint(1) DEFAULT 1,
  `sms_enabled` tinyint(1) DEFAULT 1,
  `in_app_enabled` tinyint(1) DEFAULT 1,
  `frequency_preference` enum('immediate','daily_digest','weekly_digest','never') DEFAULT 'immediate',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_notification_preferences`
--

INSERT INTO `user_notification_preferences` (`preference_id`, `user_id`, `notification_category`, `email_enabled`, `sms_enabled`, `in_app_enabled`, `frequency_preference`, `created_at`, `updated_at`) VALUES
(1, 1, 'booking_confirmations', 1, 1, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(2, 1, 'reservation_reminders', 1, 1, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(3, 1, 'payment_updates', 1, 0, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(4, 1, 'loyalty_updates', 1, 1, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(5, 1, 'promotional_offers', 0, 0, 1, 'weekly_digest', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(6, 1, 'system_announcements', 1, 0, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(7, 2, 'booking_confirmations', 1, 1, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(8, 2, 'reservation_reminders', 1, 1, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(9, 2, 'payment_updates', 1, 0, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(10, 2, 'loyalty_updates', 1, 1, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(11, 2, 'promotional_offers', 1, 0, 1, 'daily_digest', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(12, 2, 'system_announcements', 1, 0, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(13, 3, 'booking_confirmations', 1, 1, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(14, 3, 'reservation_reminders', 1, 1, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(15, 3, 'payment_updates', 1, 0, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(16, 3, 'loyalty_updates', 0, 0, 1, 'weekly_digest', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(17, 3, 'promotional_offers', 0, 0, 1, 'never', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(18, 3, 'system_announcements', 1, 0, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(19, 4, 'booking_confirmations', 1, 1, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(20, 4, 'reservation_reminders', 1, 1, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(21, 4, 'payment_updates', 1, 1, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(22, 4, 'loyalty_updates', 1, 1, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(23, 4, 'promotional_offers', 1, 1, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16'),
(24, 4, 'system_announcements', 1, 1, 1, 'immediate', '2026-03-12 07:21:16', '2026-03-12 07:21:16');

-- --------------------------------------------------------

--
-- Table structure for table `user_reviews`
--

CREATE TABLE `user_reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `review_type` enum('hotel_stay','dining_experience','food_item','service') NOT NULL,
  `related_entity_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_title` varchar(200) DEFAULT NULL,
  `review_text` text DEFAULT NULL,
  `review_status` enum('pending','approved','rejected','hidden') DEFAULT 'pending',
  `helpful_count` int(11) DEFAULT 0,
  `staff_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_reviews`
--

INSERT INTO `user_reviews` (`review_id`, `user_id`, `review_type`, `related_entity_id`, `rating`, `review_title`, `review_text`, `review_status`, `helpful_count`, `staff_response`, `created_at`, `updated_at`) VALUES
(1, 1, 'hotel_stay', 1, 5, 'Excellent Stay!', 'The deluxe twin room was perfect for our weekend getaway. Clean, comfortable, and great service!', 'approved', 0, NULL, '2024-03-17 03:00:00', '2026-03-12 07:21:17'),
(2, 1, 'dining_experience', 1, 4, 'Great Food', 'Sinigang was authentic and delicious. Sisig was perfectly spicy. Will definitely come back!', 'approved', 0, NULL, '2024-03-18 02:30:00', '2026-03-12 07:21:17'),
(3, 2, 'hotel_stay', 2, 5, 'Luxurious Experience', 'The ocean suite exceeded our expectations. The view was breathtaking and the amenities were top-notch.', 'approved', 0, NULL, '2023-11-12 01:00:00', '2026-03-12 07:21:17'),
(4, 2, 'dining_experience', 2, 5, 'Perfect Dinner', 'Grilled salmon was cooked to perfection. Great ambiance for our business dinner.', 'approved', 0, NULL, '2023-11-12 10:00:00', '2026-03-12 07:21:17'),
(5, 4, 'hotel_stay', 3, 5, 'Ultimate Luxury', 'Presidential suite was absolutely amazing! Butler service was impeccable. Worth every penny!', 'approved', 0, NULL, '2023-06-07 02:00:00', '2026-03-12 07:21:17'),
(6, 4, 'food_item', 8, 5, 'Best Sinigang Ever!', 'Authentic Filipino sinigang that reminds me of home. Perfect sourness and generous servings.', 'approved', 0, NULL, '2023-06-08 06:00:00', '2026-03-12 07:21:17');

-- --------------------------------------------------------

--
-- Table structure for table `user_reward_redemptions`
--

CREATE TABLE `user_reward_redemptions` (
  `redemption_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reward_id` int(11) NOT NULL,
  `redemption_reference` varchar(20) NOT NULL,
  `points_used` int(11) NOT NULL,
  `redemption_status` enum('pending','confirmed','used','expired','cancelled') DEFAULT 'pending',
  `redemption_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_date` timestamp NULL DEFAULT NULL,
  `usage_date` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_reward_redemptions`
--

INSERT INTO `user_reward_redemptions` (`redemption_id`, `user_id`, `reward_id`, `redemption_reference`, `points_used`, `redemption_status`, `redemption_date`, `expiry_date`, `usage_date`, `notes`, `created_at`) VALUES
(1, 1, 1, 'RDM20240319001', 240, 'used', '2026-03-12 07:21:17', NULL, '2024-03-19 07:30:00', 'Claimed free coffee at Azure Lounge', '2026-03-12 07:21:17'),
(2, 1, 7, 'RDM20240320001', 150, 'pending', '2026-03-12 07:21:17', NULL, NULL, 'Free Halo-Halo - pending redemption', '2026-03-12 07:21:17'),
(3, 2, 4, 'RDM20231115001', 360, 'used', '2026-03-12 07:21:17', NULL, '2023-11-15 11:00:00', 'Used welcome drinks for anniversary dinner', '2026-03-12 07:21:17'),
(4, 4, 5, 'RDM20230608001', 1200, 'used', '2026-03-12 07:21:17', NULL, '2023-06-08 06:00:00', 'Upgraded to ocean suite for next stay', '2026-03-12 07:21:17');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `session_id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`session_data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`session_id`, `user_id`, `session_data`, `ip_address`, `user_agent`, `is_active`, `last_activity`, `expires_at`, `created_at`) VALUES
('026eb0f835621a500c04cc36a30cfe49058d83b299e7ee0ab88b1ec5cd478487', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 08:27:07', '2026-03-13 01:27:07', '2026-03-12 08:27:07'),
('03b074e46a0d7a59ec9fabdc7f3ff7daf6a2d7c65542a358d639b5c41c9f3dba', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-13 09:13:25', '2026-03-14 02:13:25', '2026-03-13 09:13:25'),
('07ea029d297af7ab14fffa61e870970a9eaed1ebc18102c2356a8fb3ef4583d5', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 13:33:32', '2026-03-13 06:33:32', '2026-03-12 13:33:32'),
('0919a6c2903db1ac5c207f5cdedafd86d8f0d4dbaaee879abcb6a430ff5fcbc0', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-03-12 07:50:14', '2026-03-13 00:50:14', '2026-03-12 07:50:14'),
('0c38cc47b2222f2b4d640b455658b4178f380fa352e93f690decf94fe4bf9d59', 104, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-03-13 04:11:24', '2026-03-13 21:11:24', '2026-03-13 04:11:24'),
('10cc99c4dedd9ebb3f77c27954e2e6d7c0fc32019b729d6dda0fb20383ff1c0f', 104, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-13 04:13:47', '2026-03-13 21:13:47', '2026-03-13 04:13:47'),
('14c99b239a768464a7a16f9fca1b9a13ad5745d78e5cdd8c7b4959b4803f4057', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 08:31:06', '2026-03-13 01:31:06', '2026-03-12 08:31:06'),
('1f8bd8e101ff11a6b3497f737c7f0e3c8c8b36e2720cdda074519d209ca6531a', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 08:45:49', '2026-03-13 01:45:49', '2026-03-12 08:45:49'),
('1fab64a0319d40b8cb2b2487d8abbd079cb56a4c9a41557a6fe9af911f38a689', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 08:47:06', '2026-03-13 01:47:06', '2026-03-12 08:47:06'),
('324a4bd37ed619277cbf43eca35feddfcde9a346e25e21cf483dd123b06e7ddd', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 13:33:41', '2026-03-13 06:33:41', '2026-03-12 13:33:41'),
('32712a148d986854bbec87932d7427b906320956011387f2ba232961f27ad9a6', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 14:05:12', '2026-03-13 07:05:12', '2026-03-12 14:05:12'),
('3b7a76247f3b9febe7fbaa25049309ec92efef7fef76715a4278159b0460b484', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 13:39:02', '2026-03-13 06:39:02', '2026-03-12 13:39:02'),
('4869e6392fdf8b9708d2cfc3080c587d944c9b24247ddd1139d315d9dd5da768', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 13:45:24', '2026-03-13 06:45:24', '2026-03-12 13:45:24'),
('56449065aab662f04404dfa855414d6ef20d8218ad5c319c58b89c93e10480a1', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 13:21:18', '2026-03-13 06:21:18', '2026-03-12 13:21:18'),
('5687f0f69a5e2d37ca4357636ec3ade2aca3a10be62c5fa45af8a4b36247d3e5', 1, NULL, '::1', '', 1, '2026-03-12 14:00:50', '2026-03-13 07:00:50', '2026-03-12 14:00:50'),
('5e212de9ca3edfbdf9e359afacdf6a3c3a957fa9719c4e60d9ab00c07b7e84b4', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 15:54:10', '2026-03-13 08:54:10', '2026-03-12 15:54:10'),
('5ed2438ba42ae81943bab0609efd01d77b669adbff3fdf3ced4241e9c25ad975', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-03-12 08:02:15', '2026-03-13 01:02:15', '2026-03-12 08:02:15'),
('5fcd5d1127441400a478736bc6ebaed44f5cb8c59776cacebc7ff4e3a5515837', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-13 03:03:10', '2026-03-13 20:03:10', '2026-03-13 03:03:10'),
('66a8273cad83713b6715974af23c10827ab21241202e26be538f745d2d2e4694', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-03-12 14:08:46', '2026-03-13 07:08:46', '2026-03-12 14:08:46'),
('6f4b1eb04c208aa09e10a87000ef1488bac8489992f37db33e1c9d11f03473e4', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-13 03:08:45', '2026-03-13 20:08:45', '2026-03-13 03:08:45'),
('925e78958fafe2a567deae7bae1170359a212f6dc23163c9585bfcf88f306e1e', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-03-12 08:54:23', '2026-03-13 01:54:23', '2026-03-12 08:54:23'),
('95340971e39791cf99ba5204642fd8176358023adf3df24ce20acf9b2790e204', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 13:45:24', '2026-03-13 06:45:24', '2026-03-12 13:45:24'),
('9765034ad412d13eee65e11fec67298c203da686b15d189e5b6c7e4e7f54f58b', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-03-12 08:49:53', '2026-03-13 01:49:53', '2026-03-12 08:49:53'),
('98e35a69d2979219887c4e8ab0eabc30bc55464465cb7c284b149d86f7005c31', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 13:34:07', '2026-03-13 06:34:07', '2026-03-12 13:34:07'),
('9a3de56cd111a50a06a7140cac341b8f807e0d85eac690bb9ab9d491562fc14f', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-03-12 08:50:30', '2026-03-13 01:50:30', '2026-03-12 08:50:30'),
('9a918f418525f37f16cd8abe8e7df0fe929608ba78de022c275bc57b23d7b19e', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 13:42:04', '2026-03-13 06:42:04', '2026-03-12 13:42:04'),
('aaa1787ab95ad27e9574414767b0d3974ab5c924302a65f1296ecb7d49fc8c0c', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 13:35:15', '2026-03-13 06:35:15', '2026-03-12 13:35:15'),
('ae17c5a987f2de82691467de1094790d6ad5e29e5c227dcca1c224f5caf07095', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-03-12 08:51:09', '2026-03-13 01:51:09', '2026-03-12 08:51:09'),
('b73e282239a229ee1f4c773320f630c761814120966676b97b30e0f7a4fca95f', 1, NULL, '::1', '', 1, '2026-03-12 13:37:23', '2026-03-13 06:37:23', '2026-03-12 13:37:23'),
('caff0e554497266cf7b2f077b084492e98598ddc8dadce721874830badcfa8b1', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 08:44:12', '2026-03-13 01:44:12', '2026-03-12 08:44:12'),
('cc6feecc0ffeeca7b44df832bdb2d0d8b9a4bc36119b6639c262597ec5266bc6', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 08:46:52', '2026-03-13 01:46:52', '2026-03-12 08:46:52'),
('cc74cc014fc1cf82586c207b5db8e8fc11bcbaf51279d1d86625c187c907616f', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 08:40:57', '2026-03-13 01:40:57', '2026-03-12 08:40:57'),
('dd2500da8dff0706caae91c143b9711effa81d5fc759096a9065611a4a22a410', 1, NULL, '::1', '', 1, '2026-03-12 13:59:48', '2026-03-13 06:59:48', '2026-03-12 13:59:48'),
('e2e9991ce79c8ad703278f62da159027b41e0086fa6ed21a621b5cb49ee78202', 104, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-13 07:50:09', '2026-03-14 00:50:09', '2026-03-13 07:50:09'),
('e2e9c832049c5feba0abf3fa425e6a59aa20fe5f062b935123ab36feaff1f216', 104, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-13 05:23:48', '2026-03-13 22:23:48', '2026-03-13 05:23:48'),
('e7d34847d19ebe330d6ddcc853d8add5fab4d00f2c5d0d265b4a9efd03780e3a', 104, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-13 03:16:08', '2026-03-13 20:16:08', '2026-03-13 03:16:08'),
('f1c02034c35d818a8db90c0d0ecc61546cf237731aacccad1bfd9892180e8ea7', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-03-12 08:15:12', '2026-03-13 01:15:12', '2026-03-12 08:15:12'),
('f3c997ac3eaa44e20e9d454b689523cd787cec2a26d1d6c22a42ea6a74b8a10f', 100, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-13 09:13:09', '2026-03-14 02:13:09', '2026-03-13 09:13:09'),
('fb94fcd22bdc37ad8208882a08dd115a00691cdb6cc451d541541c66209f60c1', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-03-12 14:06:22', '2026-03-13 07:06:22', '2026-03-12 14:06:22'),
('fbda549e7bd52b798d159d8eb8d16721f36290c0fe660d9f4716ebdf3a6c48aa', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-12 08:48:08', '2026-03-13 01:48:08', '2026-03-12 08:48:08');

-- --------------------------------------------------------

--
-- Table structure for table `waiting_list`
--

CREATE TABLE `waiting_list` (
  `waiting_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `waiting_type` enum('restaurant_table','hotel_room','service') NOT NULL,
  `party_size` int(11) DEFAULT NULL,
  `estimated_wait_time_minutes` int(11) DEFAULT NULL,
  `priority_level` enum('normal','vip','urgent') DEFAULT 'normal',
  `contact_method` enum('sms','email','phone_call') DEFAULT 'sms',
  `special_requests` text DEFAULT NULL,
  `waiting_status` enum('waiting','notified','seated','cancelled','no_show') DEFAULT 'waiting',
  `notified_at` timestamp NULL DEFAULT NULL,
  `seated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `waiting_list`
--

INSERT INTO `waiting_list` (`waiting_id`, `user_id`, `waiting_type`, `party_size`, `estimated_wait_time_minutes`, `priority_level`, `contact_method`, `special_requests`, `waiting_status`, `notified_at`, `seated_at`, `created_at`, `updated_at`) VALUES
(1, 3, 'restaurant_table', 4, 20, 'normal', 'sms', 'Prefer window seat if available', 'waiting', NULL, NULL, '2026-03-12 07:21:17', '2026-03-12 07:21:17'),
(2, 1, 'restaurant_table', 2, 15, 'vip', 'sms', 'Celebrating anniversary', 'notified', NULL, NULL, '2026-03-12 07:21:17', '2026-03-12 07:21:17');

-- --------------------------------------------------------

--
-- Structure for view `active_bookings_view`
--
DROP TABLE IF EXISTS `active_bookings_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_bookings_view`  AS SELECT `hb`.`booking_id` AS `booking_id`, `hb`.`booking_reference` AS `booking_reference`, `hb`.`user_id` AS `user_id`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `guest_name`, `hr`.`room_number` AS `room_number`, `hr`.`room_type` AS `room_type`, `hb`.`check_in_date` AS `check_in_date`, `hb`.`check_out_date` AS `check_out_date`, `hb`.`number_of_guests` AS `number_of_guests`, `hb`.`total_amount` AS `total_amount`, `hb`.`booking_status` AS `booking_status`, `hb`.`payment_status` AS `payment_status` FROM ((`hotel_bookings` `hb` join `users` `u` on(`hb`.`user_id` = `u`.`user_id`)) join `hotel_rooms` `hr` on(`hb`.`room_id` = `hr`.`room_id`)) WHERE `hb`.`booking_status` in ('pending','confirmed','checked_in') ;

-- --------------------------------------------------------

--
-- Structure for view `today_reservations_view`
--
DROP TABLE IF EXISTS `today_reservations_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `today_reservations_view`  AS SELECT `rr`.`reservation_id` AS `reservation_id`, `rr`.`reservation_reference` AS `reservation_reference`, `rr`.`user_id` AS `user_id`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `guest_name`, `rr`.`reservation_date` AS `reservation_date`, `rr`.`reservation_time` AS `reservation_time`, `rr`.`number_of_guests` AS `number_of_guests`, `rt`.`table_number` AS `table_number`, `rr`.`reservation_status` AS `reservation_status`, `rr`.`special_requests` AS `special_requests` FROM ((`restaurant_reservations` `rr` join `users` `u` on(`rr`.`user_id` = `u`.`user_id`)) left join `restaurant_tables` `rt` on(`rr`.`table_id` = `rt`.`table_id`)) WHERE `rr`.`reservation_date` = curdate() ORDER BY `rr`.`reservation_time` ASC, `rr`.`reservation_date` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `user_dashboard_view`
--
DROP TABLE IF EXISTS `user_dashboard_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_dashboard_view`  AS SELECT `u`.`user_id` AS `user_id`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `full_name`, `u`.`email` AS `email`, `u`.`membership_tier` AS `membership_tier`, `u`.`loyalty_points` AS `loyalty_points`, `u`.`profile_photo_url` AS `profile_photo_url`, count(distinct `hb`.`booking_id`) AS `total_hotel_bookings`, count(distinct `rr`.`reservation_id`) AS `total_restaurant_reservations`, count(distinct `fo`.`order_id`) AS `total_food_orders`, count(distinct `n`.`notification_id`) AS `unread_notifications` FROM ((((`users` `u` left join `hotel_bookings` `hb` on(`u`.`user_id` = `hb`.`user_id`)) left join `restaurant_reservations` `rr` on(`u`.`user_id` = `rr`.`user_id`)) left join `food_orders` `fo` on(`u`.`user_id` = `fo`.`user_id`)) left join `notifications` `n` on(`u`.`user_id` = `n`.`user_id` and `n`.`is_read` = 0)) WHERE `u`.`account_status` = 'active' GROUP BY `u`.`user_id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_table_name` (`table_name`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `food_orders`
--
ALTER TABLE `food_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_reference` (`order_reference`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_order_status` (`order_status`),
  ADD KEY `idx_order_type` (`order_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `food_order_items`
--
ALTER TABLE `food_order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_item_id` (`item_id`);

--
-- Indexes for table `hotel_bookings`
--
ALTER TABLE `hotel_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `booking_reference` (`booking_reference`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_booking_dates` (`check_in_date`,`check_out_date`),
  ADD KEY `idx_booking_status` (`booking_status`),
  ADD KEY `idx_payment_status` (`payment_status`);

--
-- Indexes for table `hotel_rooms`
--
ALTER TABLE `hotel_rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `room_number` (`room_number`),
  ADD KEY `idx_room_type` (`room_type`),
  ADD KEY `idx_room_status` (`room_status`),
  ADD KEY `idx_price` (`base_price_per_night`);

--
-- Indexes for table `loyalty_redemptions`
--
ALTER TABLE `loyalty_redemptions`
  ADD PRIMARY KEY (`redemption_id`),
  ADD UNIQUE KEY `redemption_reference` (`redemption_reference`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_reward_id` (`reward_id`),
  ADD KEY `idx_redemption_status` (`redemption_status`),
  ADD KEY `idx_redemption_date` (`redemption_date`);

--
-- Indexes for table `loyalty_rewards`
--
ALTER TABLE `loyalty_rewards`
  ADD PRIMARY KEY (`reward_id`),
  ADD KEY `idx_reward_type` (`reward_type`),
  ADD KEY `idx_points_cost` (`points_cost`),
  ADD KEY `idx_tier_requirement` (`tier_requirement`),
  ADD KEY `idx_reward_status` (`reward_status`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `menu_categories`
--
ALTER TABLE `menu_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `idx_category_order` (`display_order`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_item_status` (`item_status`),
  ADD KEY `idx_price` (`price`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_notification_type` (`notification_type`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_related_entity` (`related_entity_type`,`related_entity_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `payment_reference` (`payment_reference`),
  ADD KEY `payment_method_id` (`payment_method_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_payment_type` (`payment_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_related_entity` (`payment_type`,`related_entity_id`),
  ADD KEY `idx_payment_reference` (`payment_reference`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_payment_date` (`payment_date`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`payment_method_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_method_type` (`method_type`),
  ADD KEY `idx_is_default` (`is_default`);

--
-- Indexes for table `points_history`
--
ALTER TABLE `points_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_source_type` (`source_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `restaurant_reservations`
--
ALTER TABLE `restaurant_reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD UNIQUE KEY `reservation_reference` (`reservation_reference`),
  ADD KEY `table_id` (`table_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_reservation_datetime` (`reservation_date`,`reservation_time`),
  ADD KEY `idx_reservation_status` (`reservation_status`);

--
-- Indexes for table `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  ADD PRIMARY KEY (`table_id`),
  ADD UNIQUE KEY `table_number` (`table_number`),
  ADD KEY `idx_table_status` (`table_status`),
  ADD KEY `idx_table_type` (`table_type`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD UNIQUE KEY `transaction_reference` (`transaction_reference`),
  ADD KEY `payment_method_id` (`payment_method_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_transaction_status` (`transaction_status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_related_entity` (`related_entity_type`,`related_entity_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`user_role`),
  ADD KEY `idx_status` (`account_status`),
  ADD KEY `idx_membership` (`membership_tier`),
  ADD KEY `idx_loyalty_points` (`loyalty_points`);

--
-- Indexes for table `user_notification_preferences`
--
ALTER TABLE `user_notification_preferences`
  ADD PRIMARY KEY (`preference_id`),
  ADD UNIQUE KEY `unique_user_category` (`user_id`,`notification_category`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `user_reviews`
--
ALTER TABLE `user_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_review_type` (`review_type`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_review_status` (`review_status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `user_reward_redemptions`
--
ALTER TABLE `user_reward_redemptions`
  ADD PRIMARY KEY (`redemption_id`),
  ADD UNIQUE KEY `redemption_reference` (`redemption_reference`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_reward_id` (`reward_id`),
  ADD KEY `idx_redemption_status` (`redemption_status`),
  ADD KEY `idx_redemption_date` (`redemption_date`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_last_activity` (`last_activity`);

--
-- Indexes for table `waiting_list`
--
ALTER TABLE `waiting_list`
  ADD PRIMARY KEY (`waiting_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_waiting_type` (`waiting_type`),
  ADD KEY `idx_waiting_status` (`waiting_status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1000;

--
-- AUTO_INCREMENT for table `food_orders`
--
ALTER TABLE `food_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1005;

--
-- AUTO_INCREMENT for table `food_order_items`
--
ALTER TABLE `food_order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1008;

--
-- AUTO_INCREMENT for table `hotel_bookings`
--
ALTER TABLE `hotel_bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1003;

--
-- AUTO_INCREMENT for table `hotel_rooms`
--
ALTER TABLE `hotel_rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `loyalty_redemptions`
--
ALTER TABLE `loyalty_redemptions`
  MODIFY `redemption_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loyalty_rewards`
--
ALTER TABLE `loyalty_rewards`
  MODIFY `reward_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `menu_categories`
--
ALTER TABLE `menu_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1000;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `payment_method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `points_history`
--
ALTER TABLE `points_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1001;

--
-- AUTO_INCREMENT for table `restaurant_reservations`
--
ALTER TABLE `restaurant_reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1014;

--
-- AUTO_INCREMENT for table `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  MODIFY `table_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10000;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `user_notification_preferences`
--
ALTER TABLE `user_notification_preferences`
  MODIFY `preference_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `user_reviews`
--
ALTER TABLE `user_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `user_reward_redemptions`
--
ALTER TABLE `user_reward_redemptions`
  MODIFY `redemption_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1000;

--
-- AUTO_INCREMENT for table `waiting_list`
--
ALTER TABLE `waiting_list`
  MODIFY `waiting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `food_orders`
--
ALTER TABLE `food_orders`
  ADD CONSTRAINT `food_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `food_order_items`
--
ALTER TABLE `food_order_items`
  ADD CONSTRAINT `food_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `food_orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `food_order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`);

--
-- Constraints for table `hotel_bookings`
--
ALTER TABLE `hotel_bookings`
  ADD CONSTRAINT `hotel_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hotel_bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `hotel_rooms` (`room_id`);

--
-- Constraints for table `loyalty_redemptions`
--
ALTER TABLE `loyalty_redemptions`
  ADD CONSTRAINT `loyalty_redemptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loyalty_redemptions_ibfk_2` FOREIGN KEY (`reward_id`) REFERENCES `loyalty_rewards` (`reward_id`);

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`category_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`);

--
-- Constraints for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `points_history`
--
ALTER TABLE `points_history`
  ADD CONSTRAINT `points_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `restaurant_reservations`
--
ALTER TABLE `restaurant_reservations`
  ADD CONSTRAINT `restaurant_reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `restaurant_reservations_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables` (`table_id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`);

--
-- Constraints for table `user_notification_preferences`
--
ALTER TABLE `user_notification_preferences`
  ADD CONSTRAINT `user_notification_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_reviews`
--
ALTER TABLE `user_reviews`
  ADD CONSTRAINT `user_reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_reward_redemptions`
--
ALTER TABLE `user_reward_redemptions`
  ADD CONSTRAINT `user_reward_redemptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_reward_redemptions_ibfk_2` FOREIGN KEY (`reward_id`) REFERENCES `loyalty_rewards` (`reward_id`);

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `waiting_list`
--
ALTER TABLE `waiting_list`
  ADD CONSTRAINT `waiting_list_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
