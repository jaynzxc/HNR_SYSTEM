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
-- Database: `lucas_admin`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `SyncOrderStats` ()   BEGIN
    UPDATE daily_stats 
    SET 
        total_online_orders = (SELECT COUNT(*) FROM orders WHERE DATE(order_time) = CURDATE()),
        total_online_revenue = (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(order_time) = CURDATE()),
        total_commission = (SELECT COALESCE(SUM(commission), 0) FROM orders WHERE DATE(order_time) = CURDATE()),
        avg_order_value = (SELECT COALESCE(AVG(total_amount), 0) FROM orders WHERE DATE(order_time) = CURDATE())
    WHERE stat_date = CURDATE();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateCampaignRedemptions` (IN `campaign_id` INT, IN `redemption_amount` DECIMAL(10,2))   BEGIN
    UPDATE marketing_campaigns 
    SET 
        current_redemptions = current_redemptions + 1,
        revenue_generated = revenue_generated + redemption_amount,
        roi = ((revenue_generated + redemption_amount) - budget) / budget * 100
    WHERE id = campaign_id;
    
    -- Update daily stats
    UPDATE daily_stats 
    SET 
        total_redemptions = total_redemptions + 1,
        marketing_revenue = marketing_revenue + redemption_amount
    WHERE stat_date = CURDATE();
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `api_settings`
--

CREATE TABLE `api_settings` (
  `id` int(11) NOT NULL,
  `platform_id` int(11) DEFAULT NULL,
  `api_endpoint` varchar(255) DEFAULT NULL,
  `webhook_secret` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_sync` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_settings`
--

INSERT INTO `api_settings` (`id`, `platform_id`, `api_endpoint`, `webhook_secret`, `is_active`, `last_sync`) VALUES
(1, 1, 'https://api.lucas.stay/orders/webhook/foodpanda', NULL, 1, NULL),
(2, 2, 'https://api.lucas.stay/orders/webhook/grab', NULL, 1, NULL),
(3, 3, 'https://api.lucas.stay/orders/webhook/lalamove', NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `available_integrations`
--

CREATE TABLE `available_integrations` (
  `id` int(11) NOT NULL,
  `platform_name` varchar(50) NOT NULL,
  `platform_icon` varchar(50) DEFAULT NULL,
  `icon_class` varchar(50) DEFAULT NULL,
  `bg_color` varchar(50) DEFAULT 'slate-100',
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `available_integrations`
--

INSERT INTO `available_integrations` (`id`, `platform_name`, `platform_icon`, `icon_class`, `bg_color`, `is_active`) VALUES
(1, 'GoFood', 'utensils', 'fa-solid fa-utensils', 'slate-100', 1),
(2, 'Deliveroo', 'bicycle', 'fa-solid fa-bicycle', 'slate-100', 1),
(3, 'ShopeeFood', 'shop', 'fa-solid fa-shop', 'slate-100', 1),
(4, 'WhatsApp', 'square-whatsapp', 'fa-brands fa-square-whatsapp', 'slate-100', 1),
(5, 'Messenger', 'facebook-messenger', 'fa-brands fa-facebook-messenger', 'slate-100', 1);

-- --------------------------------------------------------

--
-- Table structure for table `commission_summary`
--

CREATE TABLE `commission_summary` (
  `id` int(11) NOT NULL,
  `platform_id` int(11) DEFAULT NULL,
  `period_date` date DEFAULT NULL,
  `total_orders` int(11) DEFAULT 0,
  `gross_revenue` decimal(10,2) DEFAULT 0.00,
  `total_commission` decimal(10,2) DEFAULT 0.00,
  `net_revenue` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `commission_summary`
--

INSERT INTO `commission_summary` (`id`, `platform_id`, `period_date`, `total_orders`, `gross_revenue`, `total_commission`, `net_revenue`) VALUES
(1, 1, '2026-03-13', 18, 15300.00, 3825.00, 11475.00),
(2, 2, '2026-03-13', 15, 8100.00, 1782.00, 6318.00),
(3, 3, '2026-03-13', 9, 3510.00, 526.50, 2983.50),
(4, 4, '2026-03-13', 8, 10000.00, 0.00, 10000.00);

-- --------------------------------------------------------

--
-- Table structure for table `connected_platforms`
--

CREATE TABLE `connected_platforms` (
  `id` int(11) NOT NULL,
  `platform_name` varchar(50) NOT NULL,
  `platform_type` enum('delivery','on-demand','direct') DEFAULT 'delivery',
  `status` enum('connected','disconnected','pending') DEFAULT 'pending',
  `commission_rate` decimal(5,2) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `webhook_url` varchar(255) DEFAULT NULL,
  `icon_class` varchar(50) DEFAULT 'bag-shopping',
  `bg_color` varchar(50) DEFAULT 'amber-100',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `connected_platforms`
--

INSERT INTO `connected_platforms` (`id`, `platform_name`, `platform_type`, `status`, `commission_rate`, `api_key`, `api_secret`, `webhook_url`, `icon_class`, `bg_color`, `created_at`, `updated_at`) VALUES
(1, 'Foodpanda', 'delivery', 'connected', 25.00, '', '', 'http://localhost/HNR_SYSTEM/src/admin_portal/marketing/platform_edit.php?id=1', 'globe', 'amber-100', '2026-03-13 02:24:47', '2026-03-13 03:20:07'),
(2, 'GrabFood', 'delivery', 'connected', 22.00, NULL, NULL, NULL, 'motorcycle', 'green-100', '2026-03-13 02:24:47', '2026-03-13 02:24:47'),
(3, 'Lalamove', 'on-demand', 'connected', 15.00, NULL, NULL, NULL, 'truck', 'yellow-100', '2026-03-13 02:24:47', '2026-03-13 02:24:47'),
(4, 'Lùcas Website', 'direct', 'connected', 0.00, NULL, NULL, NULL, 'globe', 'amber-100', '2026-03-13 02:24:47', '2026-03-13 02:24:47');

-- --------------------------------------------------------

--
-- Table structure for table `daily_stats`
--

CREATE TABLE `daily_stats` (
  `id` int(11) NOT NULL,
  `stat_date` date DEFAULT NULL,
  `total_online_orders` int(11) DEFAULT 0,
  `total_online_revenue` decimal(15,2) DEFAULT 0.00,
  `total_commission` decimal(15,2) DEFAULT 0.00,
  `avg_order_value` decimal(10,2) DEFAULT 0.00,
  `connected_platforms` int(11) DEFAULT 0,
  `active_campaigns` int(11) DEFAULT 0,
  `total_redemptions` int(11) DEFAULT 0,
  `marketing_revenue` decimal(15,2) DEFAULT 0.00,
  `conversion_rate` decimal(5,2) DEFAULT 0.00,
  `marketing_roi` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_stats`
--

INSERT INTO `daily_stats` (`id`, `stat_date`, `total_online_orders`, `total_online_revenue`, `total_commission`, `avg_order_value`, `connected_platforms`, `active_campaigns`, `total_redemptions`, `marketing_revenue`, `conversion_rate`, `marketing_roi`, `created_at`) VALUES
(1, '2026-03-13', 42, 38450.00, 3845.00, 915.00, 4, 3, 342, 1200000.00, 18.50, 320.00, '2026-03-13 02:24:49');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` enum('Food','Beverage','Housekeeping','Linens','Amenities','Maintenance') NOT NULL,
  `sku` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(50) NOT NULL,
  `reorder_level` int(11) NOT NULL DEFAULT 10,
  `status` enum('in stock','low stock','out of stock') GENERATED ALWAYS AS (case when `quantity` <= 0 then 'out of stock' when `quantity` <= `reorder_level` then 'low stock' else 'in stock' end) STORED,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`id`, `name`, `category`, `sku`, `quantity`, `unit`, `reorder_level`, `created_at`, `updated_at`) VALUES
(1, 'Rice (50kg)', 'Food', 'FOD-001', 24, 'bags', 10, '2026-03-13 14:45:32', '2026-03-13 14:45:32'),
(2, 'Chicken (kg)', 'Food', 'FOD-002', 8, 'kg', 15, '2026-03-13 14:45:32', '2026-03-13 14:45:32'),
(3, 'Bath Towels', 'Linens', 'LIN-001', 124, 'pcs', 50, '2026-03-13 14:45:32', '2026-03-13 14:45:32'),
(4, 'Toilet Paper (case)', 'Housekeeping', 'HKS-001', 6, 'cases', 10, '2026-03-13 14:45:32', '2026-03-13 14:45:32'),
(5, 'Shampoo (ml)', 'Amenities', 'AME-001', 0, 'bottles', 50, '2026-03-13 14:45:32', '2026-03-13 14:45:32'),
(6, 'Light Bulbs', 'Maintenance', 'MNT-001', 32, 'pcs', 20, '2026-03-13 14:45:32', '2026-03-13 14:45:32'),
(7, 'asds', 'Food', 'FOO-003', 12, 'liters', 10, '2026-03-13 14:46:00', '2026-03-13 14:46:00');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_po_items`
--

CREATE TABLE `inventory_po_items` (
  `id` int(11) NOT NULL,
  `po_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_purchase_orders`
--

CREATE TABLE `inventory_purchase_orders` (
  `id` int(11) NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `order_date` date NOT NULL,
  `expected_date` date DEFAULT NULL,
  `status` enum('pending','approved','shipped','received','cancelled') DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_stock_movements`
--

CREATE TABLE `inventory_stock_movements` (
  `id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `type` enum('in','out','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `previous_quantity` int(11) DEFAULT NULL,
  `new_quantity` int(11) DEFAULT NULL,
  `reference_type` enum('purchase','sale','adjustment','waste') DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_suppliers`
--

CREATE TABLE `inventory_suppliers` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_suppliers`
--

INSERT INTO `inventory_suppliers` (`id`, `company_name`, `contact_person`, `phone`, `email`, `address`, `category`, `created_at`) VALUES
(1, 'Fresh Foods Inc.', 'Juan Dela Cruz', '0917 555 1234', 'juan@freshfoods.com', NULL, 'Food', '2026-03-13 14:45:32'),
(2, 'Hotel Supplies Co.', 'Maria Santos', '0917 555 5678', 'maria@hotelsupplies.com', NULL, 'Housekeeping', '2026-03-13 14:45:32'),
(3, 'Linens & More', 'Jose Reyes', '0917 555 9012', 'jose@linensmore.com', NULL, 'Linens', '2026-03-13 14:45:32'),
(4, 'Amenities Plus', 'Ana Lopez', '0917 555 3456', 'ana@amenitiesplus.com', NULL, 'Amenities', '2026-03-13 14:45:32'),
(5, 'Maintenance Pro', 'Pedro Cruz', '0917 555 7890', 'pedro@maintenancepro.com', NULL, 'Maintenance', '2026-03-13 14:45:32');

-- --------------------------------------------------------

--
-- Table structure for table `marketing_campaigns`
--

CREATE TABLE `marketing_campaigns` (
  `id` int(11) NOT NULL,
  `campaign_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `campaign_type` enum('discount','package','gift','event') DEFAULT 'discount',
  `status` enum('active','scheduled','ended','draft') DEFAULT 'draft',
  `discount_type` enum('percentage','fixed','free_item') DEFAULT NULL,
  `discount_value` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `target_audience` varchar(255) DEFAULT NULL,
  `target_redemptions` int(11) DEFAULT 0,
  `current_redemptions` int(11) DEFAULT 0,
  `revenue_generated` decimal(15,2) DEFAULT 0.00,
  `budget` decimal(15,2) DEFAULT 0.00,
  `roi` decimal(10,2) DEFAULT 0.00,
  `bg_color` varchar(20) DEFAULT 'green-100',
  `text_color` varchar(20) DEFAULT 'green-700',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marketing_campaigns`
--

INSERT INTO `marketing_campaigns` (`id`, `campaign_name`, `description`, `campaign_type`, `status`, `discount_type`, `discount_value`, `start_date`, `end_date`, `target_audience`, `target_redemptions`, `current_redemptions`, `revenue_generated`, `budget`, `roi`, `bg_color`, `text_color`, `created_at`, `updated_at`) VALUES
(1, 'Summer Escape', '20% off on all deluxe rooms', 'discount', 'active', 'percentage', 20.00, '2025-05-01', '2025-05-31', '', 500, 124, 248000.00, 50000.00, 396.00, 'green-100', 'green-700', '2026-03-13 03:28:15', '2026-03-13 03:50:14'),
(2, 'Weekend Getaway', 'Free breakfast for 2 · Fri-Sun stays', 'gift', 'active', 'free_item', 0.00, '2025-05-01', '2025-06-15', NULL, 300, 87, 182700.00, 30000.00, 509.00, 'green-100', 'green-700', '2026-03-13 03:28:15', '2026-03-13 03:28:15'),
(3, 'Father\'s Day Special', '15% off + welcome drink for dad', 'discount', 'scheduled', 'percentage', 15.00, '2025-06-01', '2025-06-18', NULL, 200, 0, 0.00, 50000.00, 0.00, 'blue-100', 'blue-700', '2026-03-13 03:28:15', '2026-03-13 03:28:15'),
(4, 'Spa & Relax', '20% off on all spa treatments', 'discount', 'active', 'percentage', 20.00, '2025-05-01', '2025-05-30', NULL, 200, 56, 84000.00, 20000.00, 320.00, 'green-100', 'green-700', '2026-03-13 03:28:15', '2026-03-13 03:28:15'),
(5, 'Anniversary Package', 'Romantic setup + champagne', 'package', 'draft', 'fixed', 1500.00, '2025-06-10', '2025-07-10', '', 100, 0, 0.00, 30000.00, 0.00, 'slate-100', 'slate-600', '2026-03-13 03:28:15', '2026-03-13 04:13:54'),
(6, 'Spring Fling', '25% off on suites', 'discount', 'ended', 'percentage', 25.00, '2025-04-01', '2025-05-15', NULL, 300, 312, 624000.00, 80000.00, 680.00, 'slate-100', 'slate-600', '2026-03-13 03:28:15', '2026-03-13 03:28:15'),
(7, 'Summer Escape', 'wala', 'package', 'scheduled', 'fixed', 2000.00, '2026-03-15', '2026-03-16', '', 10, 0, 0.00, 10000.00, 0.00, 'green-100', 'green-700', '2026-03-13 03:51:21', '2026-03-13 03:51:21');

-- --------------------------------------------------------

--
-- Stand-in structure for view `marketing_dashboard`
-- (See below for the actual view)
--
CREATE TABLE `marketing_dashboard` (
`active_campaigns` bigint(21)
,`scheduled_campaigns` bigint(21)
,`ended_campaigns` bigint(21)
,`draft_campaigns` bigint(21)
,`total_redemptions` decimal(32,0)
,`active_campaign_revenue` decimal(37,2)
,`avg_roi` decimal(14,6)
);

-- --------------------------------------------------------

--
-- Table structure for table `marketing_stats`
--

CREATE TABLE `marketing_stats` (
  `id` int(11) NOT NULL,
  `stat_date` date DEFAULT NULL,
  `active_campaigns` int(11) DEFAULT 0,
  `scheduled_campaigns` int(11) DEFAULT 0,
  `ended_campaigns` int(11) DEFAULT 0,
  `draft_campaigns` int(11) DEFAULT 0,
  `total_redemptions` int(11) DEFAULT 0,
  `monthly_revenue` decimal(15,2) DEFAULT 0.00,
  `conversion_rate` decimal(5,2) DEFAULT 0.00,
  `avg_redemption_value` decimal(10,2) DEFAULT 0.00,
  `overall_roi` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marketing_stats`
--

INSERT INTO `marketing_stats` (`id`, `stat_date`, `active_campaigns`, `scheduled_campaigns`, `ended_campaigns`, `draft_campaigns`, `total_redemptions`, `monthly_revenue`, `conversion_rate`, `avg_redemption_value`, `overall_roi`, `created_at`) VALUES
(1, '2026-03-13', 3, 1, 1, 1, 342, 1200000.00, 18.50, 2450.00, 320.00, '2026-03-13 03:28:16');

-- --------------------------------------------------------

--
-- Stand-in structure for view `online_ordering_dashboard`
-- (See below for the actual view)
--
CREATE TABLE `online_ordering_dashboard` (
`connected_platforms` bigint(21)
,`today_orders` bigint(21)
,`today_revenue` decimal(32,2)
,`today_commission` decimal(32,2)
,`avg_order_value` decimal(14,6)
);

-- --------------------------------------------------------

--
-- Table structure for table `online_orders`
--

CREATE TABLE `online_orders` (
  `id` int(11) NOT NULL,
  `platform_id` int(11) NOT NULL,
  `platform_order_id` varchar(100) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`items`)),
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `commission_fee` decimal(10,2) DEFAULT 0.00,
  `net_revenue` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','confirmed','preparing','ready','out_for_delivery','delivered','cancelled','refunded') DEFAULT 'pending',
  `payment_method` enum('cash','card','online','wallet') DEFAULT 'cash',
  `payment_status` enum('paid','unpaid','refunded') DEFAULT 'unpaid',
  `notes` text DEFAULT NULL,
  `ordered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivered_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `online_orders`
--

INSERT INTO `online_orders` (`id`, `platform_id`, `platform_order_id`, `customer_name`, `customer_phone`, `customer_address`, `items`, `subtotal`, `delivery_fee`, `discount`, `total_amount`, `commission_fee`, `net_revenue`, `status`, `payment_method`, `payment_status`, `notes`, `ordered_at`, `delivered_at`) VALUES
(1, 1, 'FP-12345', 'John D.', NULL, NULL, NULL, 0.00, 0.00, 0.00, 850.00, 212.50, 637.50, 'preparing', 'cash', 'unpaid', NULL, '2026-03-13 02:42:26', NULL),
(2, 2, 'GRAB-7890', 'Maria S.', NULL, NULL, NULL, 0.00, 0.00, 0.00, 540.00, 118.80, 421.20, 'delivered', 'cash', 'unpaid', NULL, '2026-03-13 02:27:26', NULL),
(3, 4, 'WEB-001', 'Anna R.', NULL, NULL, NULL, 0.00, 0.00, 0.00, 1250.00, 0.00, 1250.00, 'preparing', 'cash', 'unpaid', NULL, '2026-03-13 02:17:26', NULL),
(4, 3, 'LALA-456', 'Robert T.', NULL, NULL, NULL, 0.00, 0.00, 0.00, 390.00, 58.50, 331.50, 'out_for_delivery', 'cash', 'unpaid', NULL, '2026-03-13 02:02:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ordering_platforms`
--

CREATE TABLE `ordering_platforms` (
  `id` int(11) NOT NULL,
  `platform_name` varchar(100) NOT NULL,
  `slug` varchar(50) DEFAULT NULL,
  `platform_type` enum('delivery','pickup','dine-in','direct') DEFAULT 'delivery',
  `status` enum('connected','disconnected','pending') DEFAULT 'pending',
  `commission_rate` decimal(5,2) DEFAULT 0.00,
  `api_key` varchar(255) DEFAULT NULL,
  `webhook_url` varchar(255) DEFAULT NULL,
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `icon_class` varchar(50) DEFAULT 'globe',
  `bg_color` varchar(50) DEFAULT 'amber-100',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ordering_platforms`
--

INSERT INTO `ordering_platforms` (`id`, `platform_name`, `slug`, `platform_type`, `status`, `commission_rate`, `api_key`, `webhook_url`, `last_synced_at`, `icon_class`, `bg_color`, `created_at`, `updated_at`) VALUES
(1, 'Foodpanda', 'foodpanda', 'delivery', 'connected', 25.00, NULL, NULL, NULL, 'bag-shopping', 'pink-100', '2026-03-13 02:52:26', '2026-03-13 02:52:26'),
(2, 'GrabFood', 'grabfood', 'delivery', 'connected', 22.00, NULL, NULL, NULL, 'motorcycle', 'green-100', '2026-03-13 02:52:26', '2026-03-13 02:52:26'),
(3, 'Lalamove', 'lalamove', 'delivery', 'connected', 15.00, NULL, NULL, NULL, 'truck', 'yellow-100', '2026-03-13 02:52:26', '2026-03-13 02:52:26'),
(4, 'Lùcas Website', 'lucas-website', 'direct', 'connected', 0.00, NULL, NULL, NULL, 'globe', 'amber-100', '2026-03-13 02:52:26', '2026-03-13 02:52:26');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `platform_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `total_items` int(11) DEFAULT 0,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `commission` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','preparing','picked_up','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('paid','unpaid','refunded') DEFAULT 'unpaid',
  `order_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `platform_id`, `customer_name`, `customer_phone`, `customer_email`, `total_items`, `subtotal`, `delivery_fee`, `commission`, `total_amount`, `status`, `payment_status`, `order_time`, `delivery_address`) VALUES
(1, 'FP-12345', 1, 'John D.', NULL, NULL, 3, NULL, 0.00, 212.50, 850.00, 'preparing', 'unpaid', '2026-03-13 02:14:48', NULL),
(2, 'GRAB-7890', 2, 'Maria S.', NULL, NULL, 2, NULL, 0.00, 118.80, 540.00, 'delivered', 'unpaid', '2026-03-13 01:59:48', NULL),
(3, 'WEB-001', 4, 'Anna R.', NULL, NULL, 4, NULL, 0.00, 0.00, 1250.00, 'preparing', 'unpaid', '2026-03-13 01:49:48', NULL),
(4, 'LALA-456', 3, 'Robert T.', NULL, NULL, 2, NULL, 0.00, 58.50, 390.00, 'picked_up', 'unpaid', '2026-03-13 01:34:48', NULL);

--
-- Triggers `orders`
--
DELIMITER $$
CREATE TRIGGER `after_order_insert` AFTER INSERT ON `orders` FOR EACH ROW BEGIN
    INSERT INTO daily_stats (stat_date, total_online_orders, total_online_revenue, total_commission, avg_order_value)
    VALUES (CURDATE(), 1, NEW.total_amount, NEW.commission, NEW.total_amount)
    ON DUPLICATE KEY UPDATE
        total_online_orders = total_online_orders + 1,
        total_online_revenue = total_online_revenue + NEW.total_amount,
        total_commission = total_commission + NEW.commission,
        avg_order_value = (total_online_revenue + NEW.total_amount) / (total_online_orders + 1);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` int(11) NOT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `max_uses` int(11) DEFAULT 1,
  `current_uses` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`id`, `campaign_id`, `code`, `description`, `max_uses`, `current_uses`, `is_active`) VALUES
(1, 1, 'SUMMER20', '20% off deluxe rooms', 500, 124, 1),
(2, 2, 'BREAKFASTFREE', 'Free breakfast for 2', 300, 87, 1),
(3, 4, 'SPA20', '20% off spa treatments', 200, 56, 1),
(4, 3, 'FATHERS15', '15% off for Father\'s Day', 200, 0, 1),
(5, 5, 'ANNIV1500', '₱1500 off anniversary package', 100, 0, 1),
(6, 6, 'SPRING25', '25% off suites', 300, 312, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('admin','manager','staff') DEFAULT 'staff',
  `avatar_color` varchar(20) DEFAULT 'amber-200',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `avatar_color`, `created_at`) VALUES
(1, 'admin', 'admin@lucas.stay', '$2y$10$YourHashHere', 'Administrator', 'admin', 'amber-200', '2026-03-13 02:24:47'),
(2, 'areyes', 'andreo@lucas.stay', '$2y$10$YourHashHere', 'Andreo Reyes', 'manager', 'amber-200', '2026-03-13 02:24:47');

-- --------------------------------------------------------

--
-- Structure for view `marketing_dashboard`
--
DROP TABLE IF EXISTS `marketing_dashboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `marketing_dashboard`  AS SELECT (select count(0) from `marketing_campaigns` where `marketing_campaigns`.`status` = 'active') AS `active_campaigns`, (select count(0) from `marketing_campaigns` where `marketing_campaigns`.`status` = 'scheduled') AS `scheduled_campaigns`, (select count(0) from `marketing_campaigns` where `marketing_campaigns`.`status` = 'ended') AS `ended_campaigns`, (select count(0) from `marketing_campaigns` where `marketing_campaigns`.`status` = 'draft') AS `draft_campaigns`, (select coalesce(sum(`marketing_campaigns`.`current_redemptions`),0) from `marketing_campaigns`) AS `total_redemptions`, (select coalesce(sum(`marketing_campaigns`.`revenue_generated`),0) from `marketing_campaigns` where `marketing_campaigns`.`status` = 'active') AS `active_campaign_revenue`, (select coalesce(avg(`marketing_campaigns`.`roi`),0) from `marketing_campaigns` where `marketing_campaigns`.`status` = 'active') AS `avg_roi` ;

-- --------------------------------------------------------

--
-- Structure for view `online_ordering_dashboard`
--
DROP TABLE IF EXISTS `online_ordering_dashboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `online_ordering_dashboard`  AS SELECT (select count(0) from `connected_platforms` where `connected_platforms`.`status` = 'connected') AS `connected_platforms`, (select count(0) from `orders` where cast(`orders`.`order_time` as date) = curdate()) AS `today_orders`, (select coalesce(sum(`orders`.`total_amount`),0) from `orders` where cast(`orders`.`order_time` as date) = curdate()) AS `today_revenue`, (select coalesce(sum(`orders`.`commission`),0) from `orders` where cast(`orders`.`order_time` as date) = curdate()) AS `today_commission`, (select coalesce(avg(`orders`.`total_amount`),0) from `orders` where cast(`orders`.`order_time` as date) = curdate()) AS `avg_order_value` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api_settings`
--
ALTER TABLE `api_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `platform_id` (`platform_id`);

--
-- Indexes for table `available_integrations`
--
ALTER TABLE `available_integrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `commission_summary`
--
ALTER TABLE `commission_summary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `platform_id` (`platform_id`);

--
-- Indexes for table `connected_platforms`
--
ALTER TABLE `connected_platforms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daily_stats`
--
ALTER TABLE `daily_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stat_date` (`stat_date`),
  ADD KEY `idx_daily_stats_date` (`stat_date`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `inventory_po_items`
--
ALTER TABLE `inventory_po_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `inventory_purchase_orders`
--
ALTER TABLE `inventory_purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `inventory_stock_movements`
--
ALTER TABLE `inventory_stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `inventory_suppliers`
--
ALTER TABLE `inventory_suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marketing_campaigns`
--
ALTER TABLE `marketing_campaigns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marketing_stats`
--
ALTER TABLE `marketing_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stat_date` (`stat_date`);

--
-- Indexes for table `online_orders`
--
ALTER TABLE `online_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `platform_id` (`platform_id`);

--
-- Indexes for table `ordering_platforms`
--
ALTER TABLE `ordering_platforms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_orders_platform` (`platform_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_date` (`order_time`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `campaign_id` (`campaign_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `api_settings`
--
ALTER TABLE `api_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `available_integrations`
--
ALTER TABLE `available_integrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `commission_summary`
--
ALTER TABLE `commission_summary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `connected_platforms`
--
ALTER TABLE `connected_platforms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `daily_stats`
--
ALTER TABLE `daily_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `inventory_po_items`
--
ALTER TABLE `inventory_po_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_purchase_orders`
--
ALTER TABLE `inventory_purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_stock_movements`
--
ALTER TABLE `inventory_stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_suppliers`
--
ALTER TABLE `inventory_suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `marketing_campaigns`
--
ALTER TABLE `marketing_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `marketing_stats`
--
ALTER TABLE `marketing_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `online_orders`
--
ALTER TABLE `online_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ordering_platforms`
--
ALTER TABLE `ordering_platforms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `api_settings`
--
ALTER TABLE `api_settings`
  ADD CONSTRAINT `api_settings_ibfk_1` FOREIGN KEY (`platform_id`) REFERENCES `connected_platforms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `commission_summary`
--
ALTER TABLE `commission_summary`
  ADD CONSTRAINT `commission_summary_ibfk_1` FOREIGN KEY (`platform_id`) REFERENCES `ordering_platforms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_po_items`
--
ALTER TABLE `inventory_po_items`
  ADD CONSTRAINT `inventory_po_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `inventory_purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_po_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`);

--
-- Constraints for table `inventory_purchase_orders`
--
ALTER TABLE `inventory_purchase_orders`
  ADD CONSTRAINT `inventory_purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `inventory_suppliers` (`id`);

--
-- Constraints for table `inventory_stock_movements`
--
ALTER TABLE `inventory_stock_movements`
  ADD CONSTRAINT `inventory_stock_movements_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`);

--
-- Constraints for table `online_orders`
--
ALTER TABLE `online_orders`
  ADD CONSTRAINT `online_orders_ibfk_1` FOREIGN KEY (`platform_id`) REFERENCES `ordering_platforms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`platform_id`) REFERENCES `connected_platforms` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD CONSTRAINT `promo_codes_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `marketing_campaigns` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
