-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:4308:4308
-- Generation Time: Apr 16, 2025 at 06:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pharmacare`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `doctor_id`, `patient_name`, `appointment_date`, `appointment_time`, `created_at`, `status`) VALUES
(1, 1, 'Sujan ghimirey', '2025-12-12', '12:12:00', '2025-04-09 03:11:51', 'approved'),
(2, 1, 'Sujan ghimirey', '2025-12-12', '12:12:00', '2025-04-09 03:15:03', 'cancelled'),
(3, 5, 'prakash saput', '2004-11-12', '23:02:00', '2025-04-09 03:17:29', 'pending'),
(4, 1, 'prakash saput', '2026-11-12', '23:11:00', '2025-04-09 03:18:18', 'approved'),
(5, 5, 'Bigyann tiwari', '2025-11-12', '23:11:00', '2025-04-10 03:58:51', 'pending'),
(6, 5, 'Bigyann tiwari', '2025-11-11', '11:12:00', '2025-04-10 04:09:54', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `art`
--

CREATE TABLE `art` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `excerpt` text NOT NULL,
  `content` longtext NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `catego_id` int(11) NOT NULL,
  `author` varchar(100) NOT NULL,
  `publish_date` datetime NOT NULL,
  `read_time` int(11) NOT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_new` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `art`
--

INSERT INTO `art` (`id`, `title`, `excerpt`, `content`, `image_url`, `catego_id`, `author`, `publish_date`, `read_time`, `views`, `is_featured`, `is_new`) VALUES
(1, 'Understanding Blood Pressure Medications', 'Learn about different types of blood pressure medications and how they work.', '<p>Blood pressure medications come in several classes, each working differently to lower your blood pressure...</p>', 'uploads/67f525eea1626_Medication Guides.jpg', 1, 'Dr. Dipesh Rijal', '2025-04-08 00:00:00', 12, 3, 0, 1),
(2, '10 Daily Habits for Heart Health', 'Simple lifestyle changes that can significantly improve your cardiovascular health.', '<p>Maintaining a healthy heart doesn\\\\\\\'t require drastic measures. Here are 10 simple daily habits...</p>', '../Dasboard/uploads/67f52b0b583cd_Health Tips.jpg', 2, 'Dr. Lekhraj Ghimirey', '2025-04-08 00:00:00', 20, 2, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `bmi_history`
--

CREATE TABLE `bmi_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `weight` decimal(5,1) NOT NULL,
  `height` decimal(5,1) NOT NULL,
  `bmi` decimal(5,1) NOT NULL,
  `category` varchar(50) NOT NULL,
  `body_fat` decimal(5,1) NOT NULL,
  `ideal_weight` decimal(5,1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bmi_history`
--

INSERT INTO `bmi_history` (`id`, `user_id`, `age`, `gender`, `weight`, `height`, `bmi`, `category`, `body_fat`, `ideal_weight`, `created_at`) VALUES
(1, 6, 20, 'male', 45.0, 167.0, 16.1, '0', 7.8, 64.5, '2025-03-29 08:52:34'),
(2, 6, 20, 'male', 43.0, 167.0, 15.4, '0', 6.9, 64.5, '2025-03-29 09:01:51'),
(3, 6, 20, 'female', 48.0, 145.0, 22.8, '0', 26.6, 39.1, '2025-03-29 09:02:43'),
(4, 6, 20, 'male', 178.0, 176.0, 57.5, '0', 57.4, 74.4, '2025-03-29 11:24:27'),
(5, 6, 25, 'male', 56.0, 167.0, 20.1, '0', 13.6, 64.5, '2025-03-29 11:46:52'),
(6, 6, 25, 'male', 100.0, 189.0, 28.0, '0', 23.1, 88.7, '2025-03-29 11:48:16'),
(7, 6, 20, 'male', 67.0, 178.0, 21.1, '0', 13.8, 76.6, '2025-03-29 17:21:50'),
(8, 6, 47, 'male', 89.0, 178.0, 28.1, '0', 28.3, 76.6, '2025-03-30 03:04:03'),
(9, 6, 21, 'male', 167.0, 178.0, 52.7, '0', 51.9, 76.6, '2025-04-01 23:46:00'),
(10, 14, 20, 'male', 46.0, 167.0, 16.5, '0', 8.2, 64.5, '2025-04-11 08:53:08'),
(11, 12, 19, 'male', 85.0, 183.0, 25.4, '0', 18.6, 82.1, '2025-04-11 08:59:40'),
(12, 12, 20, 'female', 52.0, 167.0, 18.6, '0', 21.6, 58.9, '2025-04-11 09:01:01'),
(13, 12, 20, 'female', 52.0, 165.0, 19.1, '0', 22.1, 57.1, '2025-04-11 09:01:48');

-- --------------------------------------------------------

--
-- Table structure for table `cat`
--

CREATE TABLE `cat` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(100) NOT NULL,
  `bg_color` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cat`
--

INSERT INTO `cat` (`id`, `name`, `icon`, `bg_color`) VALUES
(1, 'Medication Guides', 'fas fa-pills', '#4e73df'),
(2, 'Health Tips', 'fas fa-heartbeat', '#4e73df'),
(3, 'Disease Prevention', 'fas fa-shield-virus', '#f6c23e'),
(4, 'Nutrition Advice', 'fas fa-apple-alt', '#e74a3b');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `categories_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `categories_name`) VALUES
(4, 'Medicine	'),
(5, 'paracetamol	'),
(9, 'sunscream	'),
(10, 'Healthcare	'),
(11, 'Personal Care	'),
(12, 'Vitamins	'),
(13, 'Health Packages	'),
(14, 'Lab Tests	'),
(15, 'Medicines	'),
(16, 'Vitamins & Supplements	'),
(17, 'Featured Products	'),
(19, 'New products	');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `specialization` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `first_name`, `last_name`, `specialization`) VALUES
(1, 'Rahul', 'chaudhary', 'Neurology'),
(5, 'Dipesh ', 'Rijal', 'cardiology'),
(6, 'sujan', 'ghimirey', 'cardiology');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `payment_details` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `payment_status` varchar(20) DEFAULT 'pending',
  `pidx` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `shipping_fee`, `payment_method`, `payment_details`, `status`, `created_at`, `name`, `email`, `phone`, `address`, `payment_status`, `pidx`) VALUES
(10, 6, 'ORD-20250326-0010', 174.00, 50.00, 'cod', 'Cash on Delivery', 'processing', '2025-03-26 14:13:22', 'Josue Carter', 'your.email+fakedata39352@gmail.com', '915-719-1466', '6198 Cartwright Estate', 'pending', NULL),
(11, 6, 'ORD-20250326-0011', 546.00, 50.00, 'cod', 'Cash on Delivery', 'processing', '2025-03-26 14:15:02', 'Jett Dibbert', 'your.email+fakedata61986@gmail.com', '466-961-6827', '9170 Zemlak Lake', 'pending', NULL),
(12, 6, 'ORD-20250326-0012', 670.00, 50.00, 'cod', 'Cash on Delivery', 'processing', '2025-03-26 14:19:19', 'Florida Heller', 'your.email+fakedata57508@gmail.com', '214-028-7038', '754 Cummerata Branch', 'pending', NULL),
(13, 6, 'ORD-20250326-0013', 170.00, 50.00, 'cod', 'Cash on Delivery', 'processing', '2025-03-26 16:14:03', 'Angelica Frami', 'your.email+fakedata58172@gmail.com', '536-164-7866', '115 Luettgen Pass', 'pending', NULL),
(14, 6, 'ORD-20250326-0014', 170.00, 50.00, 'cod', 'Cash on Delivery', 'processing', '2025-03-26 16:32:52', 'Barsha Magar', 'barshamagar980892@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(15, 6, 'ORD-20250326-0015', 1638.00, 0.00, 'cod', 'Cash on Delivery', 'processing', '2025-03-26 18:05:55', 'Barsha Magar', 'barsha1234@gmial.com', '9703080249', 'Mdhumalla', 'pending', NULL),
(16, 6, 'ORD-20250327-0016', 302.00, 50.00, 'cod', 'Cash on Delivery', 'processing', '2025-03-27 05:18:25', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(17, 6, 'ORD-20250327-0017', 302.00, 50.00, 'cod', 'Cash on Delivery', 'processing', '2025-03-27 05:23:02', 'Rahul chaudhary', 'rahultharu46882@icloud.com', '9815760082', 'pato', 'pending', NULL),
(18, 6, 'ORD-20250327-0018', 10200.00, 0.00, 'khalti', 'Cash on Delivery', 'pending', '2025-03-27 12:17:40', 'Rijan Chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'paid', 'jpF6hkpVfDjbCKBcddNkn9'),
(19, 6, 'ORD-20250327-0019', 5950.00, 0.00, 'cod', 'Cash on Delivery', 'processing', '2025-03-27 12:19:11', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(20, 6, 'ORD-20250327-0020', 7650.00, 0.00, 'cod', 'Cash on Delivery', 'processing', '2025-03-27 12:28:35', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(21, 6, 'ORD-20250327-0021', 900.00, 50.00, 'cod', 'Cash on Delivery', 'processing', '2025-03-27 15:25:51', 'Rahul chaudhary', 'rahultharu980893@gmail.com', '9815760082', 'pato-6', 'pending', NULL),
(22, 6, 'ORD-20250328-0022', 900.00, 50.00, 'khalti', 'Khalti ID: KH1743121763', 'processing', '2025-03-28 00:29:23', 'Buddy Wolf', 'your.email+fakedata98535@gmail.com', '365-667-9916', '414 Coy Ramp', 'pending', NULL),
(23, 6, 'ORD-20250328-0023', 900.00, 50.00, 'cod', 'Cash on Delivery', 'processing', '2025-03-28 00:41:55', 'Aurelio Cole', 'your.email+fakedata52655@gmail.com', '398-027-2618', '314 Heller Grove', 'pending', NULL),
(24, 6, 'ORD-20250328-0024', 900.00, 50.00, 'khalti', 'Khalti ID: KH1743122768', 'pending', '2025-03-28 00:46:08', 'Jody Franey', 'your.email+fakedata39467@gmail.com', '699-251-5617', '949 Rempel Dale', 'paid', '6zJdwegp5sMfb7QWio2efH'),
(25, 6, 'ORD-20250328-0025', 5100.00, 0.00, 'cod', 'Cash on Delivery', 'processing', '2025-03-28 04:06:12', 'Noe Kuhn', 'your.email+fakedata91775@gmail.com', '910-895-4186', '7329 Jake Station', 'pending', NULL),
(26, NULL, 'ORD-20250329-67E74FC418991', 3400.00, 0.00, 'cod', '', 'pending', '2025-03-29 01:41:24', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(27, NULL, 'ORD-20250329-67E75004957D1', 176.00, 50.00, 'khalti', '', 'pending', '2025-03-29 01:42:28', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(28, NULL, 'ORD-20250329-67E7542CA67AD', 900.00, 50.00, 'khalti', '', 'pending', '2025-03-29 02:00:12', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(29, NULL, 'ORD-20250329-67E7549160D0D', 176.00, 50.00, 'khalti', '', 'pending', '2025-03-29 02:01:53', 'Rahul Chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(30, NULL, 'ORD-20250329-67E7588733043', 900.00, 50.00, 'khalti', '', 'pending', '2025-03-29 02:18:47', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(31, NULL, 'ORD-20250329-67E758873A7DA', 3400.00, 0.00, 'khalti', '{\"payment_method\":\"khalti\",\"transaction_id\":\"QAm72DpB3jfpnFb3apJi4Y\",\"pidx\":\"z2EFTDbzvbSbx6XPCbvUpg\",\"amount\":3400,\"verification_response\":{\"pidx\":\"z2EFTDbzvbSbx6XPCbvUpg\",\"total_amount\":340000,\"status\":\"Completed\",\"transaction_id\":\"QAm72DpB3jfpnFb3apJi4Y', 'completed', '2025-03-29 02:20:21', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(32, NULL, 'ORD-20250329-67E759646CB63', 176.00, 50.00, 'cod', '', 'pending', '2025-03-29 02:22:28', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(33, NULL, 'ORD-20250329-67E7599F9B041', 176.00, 50.00, 'khalti', '', 'pending', '2025-03-29 02:23:27', 'Rahul chaudhary', 'rahultharu46882@icloud.com', '9815760082', 'pato', 'pending', NULL),
(34, NULL, 'ORD-20250329-67E759BE643E4', 176.00, 50.00, 'khalti', '', 'pending', '2025-03-29 02:23:58', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(35, NULL, 'ORD-20250329-67E759D35E00F', 900.00, 50.00, 'khalti', '', 'pending', '2025-03-29 02:24:19', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(36, NULL, 'ORD-20250329-67E759F72FD26', 900.00, 50.00, 'khalti', '', 'pending', '2025-03-29 02:24:55', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(37, NULL, 'ORD-20250329-67E75A4D4A840', 176.00, 50.00, 'khalti', '', 'pending', '2025-03-29 02:26:21', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(38, NULL, 'ORD-20250329-67E75AA05A386', 900.00, 50.00, 'khalti', '', 'pending', '2025-03-29 02:27:44', 'Rahul chaudhary', 'rahultharu46882@icloud.com', '9815760082', 'pato', 'pending', NULL),
(43, NULL, 'ORD-20250329-67E7695B1FDA4', 1826.00, 0.00, 'cod', '', 'pending', '2025-03-29 03:30:35', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(44, NULL, 'ORD-20250329-67E769834794A', 900.00, 50.00, 'khalti', '', 'pending', '2025-03-29 03:31:15', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(45, NULL, 'ORD-20250329-67E769D8A844B', 176.00, 50.00, 'khalti', '', 'pending', '2025-03-29 03:32:40', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(46, NULL, 'ORD-20250329-67E7721B150C5', 176.00, 50.00, 'khalti', '', 'pending', '2025-03-29 04:07:55', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(47, NULL, 'ORD-20250329-67E77291D6445', 900.00, 50.00, 'cod', '', 'pending', '2025-03-29 04:09:53', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(48, NULL, 'ORD-20250329-67E774CB126FB', 900.00, 50.00, 'khalti', '', 'pending', '2025-03-29 04:19:23', 'Rahul chaudhary', 'rahultharu46882@icloud.com', '9815760082', 'pato', 'pending', NULL),
(49, NULL, 'ORD-20250329-67E777F9E005B', 900.00, 50.00, 'khalti', '', 'pending', '2025-03-29 04:32:57', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(50, NULL, 'ORD-20250329-67E779BB0FB50', 900.00, 50.00, 'khalti', '', 'pending', '2025-03-29 04:40:27', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(51, NULL, 'ORD-20250329-67E77A491962C', 176.00, 50.00, 'khalti', '', 'pending', '2025-03-29 04:42:49', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(52, NULL, 'ORD-20250329-67E77B997170A', 176.00, 50.00, 'khalti', '', 'pending', '2025-03-29 04:48:25', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(53, NULL, 'ORD-20250329-67E77D0A53EE5', 900.00, 50.00, 'khalti', '', 'pending', '2025-03-29 04:54:34', 'Rahul chaudhary', 'rahultharu46882@icloud.com', '9815760082', 'pato', 'pending', NULL),
(54, NULL, 'ORD-20250329-67E7890ADD221', 900.00, 50.00, 'khalti', '', 'pending', '2025-03-29 05:45:46', 'Rahul chaudhary', 'rahultharu46882@icloud.com', '9815760082', 'pato', 'pending', NULL),
(55, NULL, 'ORD-20250329-67E78B9B71A2D', 900.00, 50.00, 'khalti', '', 'pending', '2025-03-29 05:56:43', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(56, NULL, 'ORD-20250329-67E78C58A3754', 900.00, 50.00, 'khalti', '{\"status\":\"amount_mismatch\",\"response\":{\"pidx\":\"iUKJSqSEqHjYXDYDxYqqWJ\",\"total_amount\":90000,\"status\":\"Completed\",\"transaction_id\":\"mN2aGUDuA6QGoC9UruhREG\",\"fee\":2700,\"refunded\":false}}', 'failed', '2025-03-29 05:59:52', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(57, NULL, 'ORD-20250329-67E79123B6586', 900.00, 50.00, 'khalti', '', 'pending', '2025-03-29 06:20:19', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(58, NULL, 'ORD-20250329-67E791A42F2A8', 900.00, 50.00, 'khalti', '{\"status\":\"amount_mismatch\",\"expected\":900,\"paid\":0,\"response\":{\"pidx\":\"KveudFKxHYBYU32ms4s976\",\"total_amount\":90000,\"status\":\"Completed\",\"transaction_id\":\"opwz4nvTs2D3mCD7KndaBX\",\"fee\":2700,\"refunded\":false}}', 'failed', '2025-03-29 06:22:28', 'Rahul chaudhary', 'root@gmail.com', '9815000000', 'pato', 'pending', NULL),
(59, NULL, 'ORD-20250329-67E7937B3E734', 900.00, 50.00, 'khalti', '', 'pending', '2025-03-29 06:30:19', 'sujan ghimirey', 'sujanghimirey123@gmail.com', '9815760082', 'tarhara-6', 'pending', NULL),
(60, NULL, 'ORD-20250329-67E793B968B1B', 900.00, 50.00, 'khalti', '', 'pending', '2025-03-29 06:31:21', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(61, NULL, 'ORD-20250329-67E7948E5446F', 176.00, 50.00, 'khalti', '', 'pending', '2025-03-29 06:34:54', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(62, NULL, 'ORD-20250329-67E796530AC9B', 176.00, 50.00, 'khalti', '', 'pending', '2025-03-29 06:42:27', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(63, NULL, 'ORD-20250329-67E798AEAC082', 900.00, 50.00, 'khalti', '', 'processing', '2025-03-29 06:52:30', 'Rahul chaudhary', 'rahultharu980893@gmail.com', '9815760082', 'pato-6', 'paid', 'kVMzVU55R8z5WSSiBJX7nf'),
(64, NULL, 'ORD-20250329-67E799BFE05A9', 176.00, 50.00, 'khalti', '', 'processing', '2025-03-29 06:57:03', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'paid', 'H9T2X6wUagbokhDmYH8nVi'),
(65, NULL, 'ORD-20250329-67E79A393B9D1', 900.00, 50.00, 'cod', '', 'pending', '2025-03-29 06:59:05', 'sujan ghimirey', 'sujanghimirey123@gmail.com', '9815760082', 'tarhara-6', 'pending', NULL),
(66, NULL, 'ORD-20250329-67E79AAF4F19D', 900.00, 50.00, 'cod', '', 'pending', '2025-03-29 07:01:03', 'Rahul chaudhary', 'rahultharu980893@gmail.com', '9815760082', 'pato-6', 'pending', NULL),
(67, NULL, 'ORD-20250329-67E79FD48045B', 900.00, 50.00, 'khalti', '', 'processing', '2025-03-29 07:23:00', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'paid', 'PcUiBo9RuqnCphiu3EZrVN'),
(68, NULL, 'ORD-20250329-67E7C49C0C145', 6400.00, 0.00, 'khalti', '', 'processing', '2025-03-29 09:59:56', 'sujan ghimirey', 'sujanghimirey123@gmail.com', '9815760082', 'tarhara-6', 'paid', 'FMRLsavetGEoYVgNnMsSGJ'),
(69, NULL, 'ORD-20250329-67E7C60FACDD7', 176.00, 50.00, 'khalti', '', 'processing', '2025-03-29 10:06:07', 'Rahul chaudhary', 'rahultharu46882@icloud.com', '9815760082', 'pato', 'paid', 'KbLBCt4v4qDFW74KwM4N8d'),
(70, NULL, 'ORD-20250329-67E82C6626309', 6400.00, 0.00, 'khalti', '', 'processing', '2025-03-29 17:22:46', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'paid', 'NxgyuAnudT7Ht4Q8v6G3YA'),
(71, NULL, 'ORD-20250330-67E8BB1B01BC7', 6400.00, 0.00, 'khalti', '', 'processing', '2025-03-30 03:31:39', 'Rahul chaudhary', 'rahultharu980893@gmail.com', '9815760082', 'pato-6', 'paid', 'ZV8mqGPLEZpuV6aaiT4E72'),
(72, NULL, 'ORD-20250330-67E8D6B90EE1E', 12800.00, 0.00, 'khalti', '', 'cancelled', '2025-03-30 05:29:29', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(73, NULL, 'ORD-20250330-67E8D724B9DBD', 900.00, 50.00, 'khalti', '', 'processing', '2025-03-30 05:31:16', 'Rahul chaudhary', 'rahultharu46882@icloud.com', '9815760082', 'pato', 'paid', 'C9b9PPorQGbuXiyAbgyHC5'),
(74, NULL, 'ORD-20250330-67E8E02BE5763', 176.00, 50.00, 'khalti', '', 'processing', '2025-03-30 06:09:47', 'Rahul chaudhary', 'rahultharu46882@icloud.com', '9815760082', 'pato', 'paid', 'meXRmSDxP98Ud27XNS7du8'),
(75, NULL, 'ORD-20250330-67E8E3A5B5BEF', 900.00, 50.00, 'khalti', '', 'completed', '2025-03-30 06:24:37', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'paid', 'J3NEiHbzx9qcpcHmmJEMF2'),
(76, NULL, 'ORD-20250330-67E9332A14B6B', 1700.00, 0.00, 'khalti', '', 'completed', '2025-03-30 12:03:54', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'paid', 'HKtFYYyTS6zhp897HZkoqa'),
(77, 78, 'ORD-20250330-67E934811D42A', 6400.00, 0.00, 'cod', '', 'pending', '2025-03-30 12:09:37', 'sujan ghimirey', 'sujanghimirey123@gmail.com', '9815760082', 'tarhara-6', 'pending', NULL),
(78, NULL, 'ORD-20250330-67E9392648486', 6400.00, 0.00, 'cod', '', 'pending', '2025-03-30 12:29:26', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(79, NULL, 'ORD-20250330-67E93AAAF1D27', 176.00, 50.00, 'cod', '', 'pending', '2025-03-30 12:35:54', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(80, NULL, 'ORD-20250330-67E946258A27A', 176.00, 50.00, 'cod', '', 'pending', '2025-03-30 13:24:53', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(81, NULL, 'ORD-20250330-67E94DD62335F', 900.00, 50.00, 'cod', '', 'pending', '2025-03-30 13:57:42', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(82, NULL, 'ORD-20250330-67E9521AAAB7B', 900.00, 50.00, 'cod', '', 'pending', '2025-03-30 14:15:54', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(83, NULL, 'ORD-20250330-67E95511A50CD', 900.00, 50.00, 'cod', '', 'pending', '2025-03-30 14:28:33', 'Rahul chaudhary', 'rahultharu980893@gmail.com', '9815760082', 'pato-6', 'pending', NULL),
(84, NULL, 'ORD-20250330-67E957E661B14', 176.00, 50.00, 'cod', '', 'pending', '2025-03-30 14:40:38', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(85, NULL, 'ORD-20250330-67E95D6ADC95B', 6400.00, 0.00, 'cod', '', 'pending', '2025-03-30 15:04:10', 'Rahul chaudhary', 'root@gmail.com', '9815760082', 'pato', 'pending', NULL),
(86, NULL, 'ORD-20250330-67E95DFE5F8CB', 900.00, 50.00, 'cod', '', 'pending', '2025-03-30 15:06:38', 'Rahul chaudhary', 'rahultharu46882@icloud.com', '9815760082', 'pato', 'pending', NULL),
(87, NULL, 'ORD-20250330-67E95EA7E1141', 900.00, 50.00, 'cod', '', 'pending', '2025-03-30 15:09:27', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(88, NULL, 'ORD-20250330-67E96391CA604', 900.00, 50.00, 'cod', '', 'pending', '2025-03-30 15:30:25', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(89, NULL, 'ORD-20250330-67E96630AA1CA', 6400.00, 0.00, 'cod', '', 'pending', '2025-03-30 15:41:36', 'Barsha Magar', 'barsha980892@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(90, NULL, 'ORD-20250331-67E9EA46561FA', 1700.00, 0.00, 'khalti', '', 'completed', '2025-03-31 01:05:10', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'paid', 'bVQ6Wa82CD95399CFzBL4o'),
(91, NULL, 'ORD-20250331-67E9EE2051B64', 900.00, 50.00, 'khalti', '', 'completed', '2025-03-31 01:21:36', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'paid', 'kRvdCBWy55c4ZNfWpjVg5a'),
(92, NULL, 'ORD-20250331-67E9F15B33DE6', 6400.00, 0.00, 'khalti', '', 'completed', '2025-03-31 01:35:23', 'sujan ghimirey', 'sujanghimirey123@gmail.com', '9815760082', 'tarhara-6', 'paid', 'rwwLFEAsX5gqcmkKFUrZt5'),
(93, NULL, 'ORD-20250331-67E9F20C16B21', 900.00, 50.00, 'khalti', '', 'completed', '2025-03-31 01:38:20', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'paid', 'PttBkBL4giJVhawAUmdoCT'),
(94, NULL, 'ORD-20250331-67E9F46044790', 6400.00, 0.00, 'cod', '', 'pending', '2025-03-31 01:48:16', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(95, NULL, 'ORD-20250331-67E9F8FBEBFDC', 900.00, 50.00, 'cod', '', 'pending', '2025-03-31 02:07:55', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(96, NULL, 'ORD-20250331-67E9F9C2EE979', 6400.00, 0.00, 'khalti', '', 'completed', '2025-03-31 02:11:14', 'Rahul chaudhary', 'rahultharu46882@icloud.com', '9813450912', 'pato', 'paid', 'XxjDXEV9rQVrSat4D43GXf'),
(97, NULL, 'ORD-20250331-67EA02E7D007E', 6400.00, 0.00, 'cod', '', 'pending', '2025-03-31 02:50:15', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(98, NULL, 'ORD-20250331-67EA03B31CF56', 900.00, 50.00, 'cod', '', 'pending', '2025-03-31 02:53:39', 'Rahul chaudhary', 'rahultharu980893@gmail.com', '9815760082', 'pato-6', 'pending', NULL),
(99, NULL, 'ORD-20250331-67EA063589E50', 900.00, 50.00, 'cod', '', 'pending', '2025-03-31 03:04:21', 'Rahul chaudhary', 'rahultharu46882@icloud.com', '9813450912', 'pato', 'pending', NULL),
(100, NULL, 'ORD-20250331-67EA0F8CDA8C0', 6400.00, 0.00, 'cod', '', 'pending', '2025-03-31 03:44:12', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(101, NULL, 'ORD-20250331-67EA0FD6661C5', 6400.00, 0.00, 'khalti', '', 'completed', '2025-03-31 03:45:26', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'paid', 'bDEXyDbi8mgow8EgZoPqH6'),
(102, NULL, 'ORD-20250331-67EA18563BC6A', 6400.00, 0.00, 'khalti', '', 'completed', '2025-03-31 04:21:42', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'paid', 'wgCEFPME824abEGrks6UKP'),
(103, NULL, 'ORD-20250331-67EA1A4A2C72E', 900.00, 50.00, 'cod', '', 'pending', '2025-03-31 04:30:02', 'Rahul chaudhary', 'rahultharu46882@icloud.com', '9812345673', 'pato', 'pending', NULL),
(104, NULL, 'ORD-20250331-67EA1F4263204', 900.00, 50.00, 'cod', '', 'pending', '2025-03-31 04:51:14', 'Sujan Ghimirey', 'sujanghimirey123@gmail.com', '9813450912', 'pato-6', 'pending', NULL),
(105, NULL, 'ORD-20250331-67EA326435D31', 176.00, 50.00, 'cod', '', 'pending', '2025-03-31 06:12:52', 'Sujan Ghimirey', 'sujanghimirey123@gmail.com', '9813450912', 'pato-6', 'pending', NULL),
(106, NULL, 'ORD-20250331-67EA83285E755', 1700.00, 0.00, 'cod', '', 'pending', '2025-03-31 11:57:28', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(107, NULL, 'ORD-20250331-67EAA83C49F88', 900.00, 50.00, 'cod', '', 'pending', '2025-03-31 14:35:40', 'Sujan Ghimirey', 'sujanghimirey123@gmail.com', '9813450912', 'pato-6', 'pending', NULL),
(108, NULL, 'ORD-20250331-67EABEB06FAD5', 900.00, 50.00, 'khalti', '', 'completed', '2025-03-31 16:11:28', 'Rahul chaudhary', 'root@gmail.com', '9815760082', 'pato', 'paid', '3HR2NqfBvHmBmvCFzZaJZR'),
(109, NULL, 'ORD-20250331-67EAC724F220A', 6400.00, 0.00, 'cod', '', 'pending', '2025-03-31 16:47:32', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(110, NULL, 'ORD-20250331-67EAD103080B2', 6400.00, 0.00, 'cod', '', 'pending', '2025-03-31 17:29:39', 'Rahul chaudhary', 'rahultharu46882@icloud.com', '9815000000', 'pato', 'pending', NULL),
(111, NULL, 'ORD-20250331-67EAD67D74A67', 6400.00, 0.00, 'cod', '', 'pending', '2025-03-31 17:53:01', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(112, NULL, 'ORD-20250331-67EADE924A8E6', 900.00, 50.00, 'khalti', '', 'completed', '2025-03-31 18:27:30', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'paid', 'EaHkv83mfFnGoXCmQksBND'),
(113, NULL, 'ORD-20250331-67EAE15BA799B', 6400.00, 0.00, 'cod', '', 'pending', '2025-03-31 18:39:23', 'Barsha Magar', 'barsha980892@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(114, NULL, 'ORD-20250401-67EB344335912', 12800.00, 0.00, 'cod', '', 'pending', '2025-04-01 00:33:07', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(115, NULL, 'ORD-20250401-67EB36ABEA381', 900.00, 50.00, 'cod', '', 'pending', '2025-04-01 00:43:23', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(116, NULL, 'ORD-20250401-67EB39A3884DC', 900.00, 50.00, 'cod', '', 'pending', '2025-04-01 00:56:03', 'Rahul chaudhary', 'rahultharu980893@gmail.com', '9815760082', 'pato-6', 'pending', NULL),
(117, NULL, 'ORD-20250401-67EB3B2B4B529', 6400.00, 0.00, 'cod', '', 'pending', '2025-04-01 01:02:35', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(118, NULL, 'ORD-20250401-67EB3EE10282F', 6400.00, 0.00, 'cod', '', 'pending', '2025-04-01 01:18:25', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(119, NULL, 'ORD-20250401-67EB4790B4D65', 900.00, 50.00, 'cod', '', 'shipped', '2025-04-01 01:55:28', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(120, NULL, 'ORD-20250401-67EB500F6D293', 6400.00, 0.00, 'cod', '', 'shipped', '2025-04-01 02:31:43', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(121, NULL, 'ORD-20250401-67EB50971BDCE', 6400.00, 0.00, 'cod', '', 'completed', '2025-04-01 02:33:59', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(122, NULL, 'ORD-20250401-67EB77DB712FA', 1700.00, 0.00, 'khalti', '', 'completed', '2025-04-01 05:21:31', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'paid', 'mZeZBd3oYjujp88DXZAzqd'),
(123, NULL, 'ORD-20250401-67EB7C6B63AE5', 900.00, 50.00, 'khalti', '', 'completed', '2025-04-01 05:40:59', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(124, NULL, 'ORD-20250401-67EB7C8963E8A', 900.00, 50.00, 'khalti', '', 'completed', '2025-04-01 05:41:29', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(125, NULL, 'ORD-20250401-67EB7CA0E2985', 6400.00, 0.00, 'khalti', '', 'processing', '2025-04-01 05:41:52', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'paid', 'RR4mrL6nUcxKsB4eYpeca2'),
(126, NULL, 'ORD-20250402-67EC83C441E3A', 200.00, 50.00, 'cod', '', 'completed', '2025-04-02 00:24:36', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(127, NULL, 'ORD-20250402-67ED24F8ED556', 700.00, 50.00, 'khalti', '', 'pending', '2025-04-02 11:52:24', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'paid', 'nVhYYCoXYNYQZrQdNpkeKY'),
(128, NULL, 'ORD-20250403-67EE08AF78A00', 185.00, 50.00, 'khalti', '', 'pending', '2025-04-03 04:03:59', 'Rijan Chaudhary', 'rijan123@gmail.com', '9800000000', 'itahari-9', 'pending', NULL),
(129, NULL, 'ORD-20250403-67EE08D45C6E1', 65.00, 50.00, 'khalti', '', 'pending', '2025-04-03 04:04:36', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(130, NULL, 'ORD-20250403-67EE08FB9E48C', 200.00, 50.00, 'khalti', '', 'pending', '2025-04-03 04:05:15', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(131, NULL, 'ORD-20250403-67EE0924259AA', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-03 04:05:56', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(132, NULL, 'ORD-20250403-67EE09C4F1956', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-03 04:08:36', 'Rahul chaudhary', 'root@gmail.com', '9703080249', 'pato', 'pending', NULL),
(133, NULL, 'ORD-20250403-67EE09DC5FA82', 6400.00, 0.00, 'cod', '', 'pending', '2025-04-03 04:09:00', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(134, NULL, 'ORD-20250403-67EE0A4A0B2A4', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-03 04:10:50', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(135, NULL, 'ORD-20250403-67EE0A755DA2D', 200.00, 50.00, 'cod', '', 'pending', '2025-04-03 04:11:33', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(136, NULL, 'ORD-20250403-67EE0AE495D85', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-03 04:13:24', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(137, NULL, 'ORD-20250403-67EE0AFD4B484', 200.00, 50.00, 'khalti', '', 'pending', '2025-04-03 04:13:49', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(138, NULL, 'ORD-20250403-67EE0CEF673FF', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-03 04:22:07', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(139, NULL, 'ORD-20250403-67EE0DFFE5904', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-03 04:26:39', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(140, NULL, 'ORD-20250403-67EE0E4711C77', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-03 04:27:51', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(141, NULL, 'ORD-20250403-67EE0E8A87073', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-03 04:28:58', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(142, NULL, 'ORD-20250403-67EE0EE009130', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-03 04:30:24', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(143, NULL, 'ORD-20250403-67EE1043418E4', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-03 04:36:19', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(144, NULL, 'ORD-20250403-67EE1052727D7', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-03 04:36:34', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(145, NULL, 'ORD-20250403-67EE10B0DF0C1', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-03 04:38:08', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(146, NULL, 'ORD-20250403-67EE678E554D9', 12800.00, 0.00, 'khalti', '', 'pending', '2025-04-03 10:48:46', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(147, NULL, 'ORD-20250403-67EE689B0C9CE', 200.00, 50.00, 'khalti', '', 'pending', '2025-04-03 10:53:15', 'Rahul chaudhary', 'rahultharu980893@gmail.com', '9815760082', 'pato-6', 'pending', NULL),
(148, NULL, 'ORD-20250403-67EE68E80E0AD', 1500.00, 0.00, 'cod', '', 'pending', '2025-04-03 10:54:32', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(149, NULL, 'ORD-20250403-67EE6A0991926', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-03 10:59:21', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(150, NULL, 'ORD-20250403-67EE6AA8AFDEB', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-03 11:02:00', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(151, NULL, 'ORD-20250403-67EE6AF2D7D40', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-03 11:03:14', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(152, NULL, 'ORD-20250404-67EF3B30B0E89', 700.00, 50.00, 'khalti', '', 'pending', '2025-04-04 01:51:44', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(153, NULL, 'ORD-20250404-67EF5570D2A94', 12800.00, 0.00, 'khalti', '', 'pending', '2025-04-04 03:43:44', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(154, NULL, 'ORD-20250407-67F36B783718A', 80.00, 50.00, 'khalti', '', 'pending', '2025-04-07 06:06:48', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(155, NULL, 'ORD-20250407-67F36BA1D2470', 80.00, 50.00, 'khalti', '', 'processing', '2025-04-07 06:07:29', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(156, NULL, 'ORD-20250408-67F517360F7E5', 6550.00, 0.00, 'khalti', '', 'pending', '2025-04-08 12:31:50', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'paid', 'cosTq3VF6PLJWZ3ykiCztC'),
(157, NULL, 'ORD-20250409-67F5C8BF8659F', 4020.00, 0.00, 'khalti', '', 'pending', '2025-04-09 01:09:19', 'Rahul chaudhary', 'rahultharu980893@gmail.com', '9815760082', 'pato-6', 'paid', 'pgGKKnZiQgFJtmviJ4xy83'),
(158, NULL, 'ORD-20250409-67F5F605930A8', 4020.00, 0.00, 'khalti', '', 'processing', '2025-04-09 04:22:29', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'paid', 'UGsQZXxaBJSuB7qk4PTiqJ'),
(159, NULL, 'ORD-20250409-67F61B809D923', 6550.00, 0.00, 'khalti', '', 'pending', '2025-04-09 07:02:24', 'Paruhang Rai', 'paruhang@123gmail.com', '9815000000', 'Itahari', 'paid', 'VL5zGP5ybRDuW5hopuccLN'),
(160, NULL, 'ORD-20250409-67F659DDC4334', 105.00, 50.00, 'khalti', '', 'pending', '2025-04-09 11:28:29', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(161, NULL, 'ORD-20250409-67F659EB31A42', 105.00, 50.00, 'khalti', '', 'pending', '2025-04-09 11:28:43', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(162, NULL, 'ORD-20250409-67F659FC6F086', 105.00, 50.00, 'khalti', '', 'pending', '2025-04-09 11:29:00', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(163, NULL, 'ORD-20250409-67F65A06A8E73', 105.00, 50.00, 'cod', '', 'pending', '2025-04-09 11:29:10', 'Paruhang Rai', 'paruhang@123gmail.com', '9813450912', 'Itahari', 'pending', NULL),
(164, NULL, 'ORD-20250409-67F65A246EC3B', 85.00, 50.00, 'khalti', '', 'pending', '2025-04-09 11:29:40', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(165, NULL, 'ORD-20250409-67F65A3C029F9', 85.00, 50.00, 'khalti', '', 'pending', '2025-04-09 11:30:04', 'Rahul chaudhary', 'rahultharu980893@gmail.com', '9815760082', 'pato-6', 'pending', NULL),
(166, NULL, 'ORD-20250409-67F65A6B4E8FB', 85.00, 50.00, 'khalti', '', 'pending', '2025-04-09 11:30:51', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(167, NULL, 'ORD-20250409-67F65A7B5A7DF', 85.00, 50.00, 'khalti', '', 'pending', '2025-04-09 11:31:07', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(168, NULL, 'ORD-20250409-67F65AC3A4C4A', 85.00, 50.00, 'khalti', '', 'pending', '2025-04-09 11:32:19', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(169, NULL, 'ORD-20250409-67F65C6A2C0FB', 85.00, 50.00, 'khalti', '', 'pending', '2025-04-09 11:39:22', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(171, NULL, 'ORD-20250409-67F65CCC96DA5', 85.00, 50.00, 'khalti', '', 'pending', '2025-04-09 11:41:00', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(172, NULL, 'ORD-20250409-67F65DC0A0009', 140.00, 50.00, 'cod', '', 'pending', '2025-04-09 11:45:04', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(173, NULL, 'ORD-20250409-67F661FE62234', 225.00, 50.00, 'khalti', '', 'pending', '2025-04-09 12:03:10', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(174, NULL, 'ORD-20250409-67F662B90D2EA', 225.00, 50.00, 'khalti', '', 'pending', '2025-04-09 12:06:17', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(175, NULL, 'ORD-20250409-67F66419D8D4E', 225.00, 50.00, 'khalti', '', 'pending', '2025-04-09 12:12:09', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(176, NULL, 'ORD-20250409-67F66424A4193', 225.00, 50.00, 'khalti', '', 'pending', '2025-04-09 12:12:20', 'Sujan Ghimirey', 'sujanghimirey123@gmail.com', '9813450912', 'pato-6', 'pending', NULL),
(177, NULL, 'ORD-20250409-67F664DBA8370', 225.00, 50.00, 'khalti', '', 'pending', '2025-04-09 12:15:23', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(178, NULL, 'ORD-20250409-67F664F9D2B90', 200.00, 50.00, 'khalti', '', 'pending', '2025-04-09 12:15:53', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(179, NULL, 'ORD-20250409-67F67F690EE1A', 200.00, 50.00, 'esewa', '', 'pending', '2025-04-09 14:08:41', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(180, NULL, 'ORD-20250409-67F6800BCD26A', 200.00, 50.00, 'esewa', '', 'pending', '2025-04-09 14:11:23', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(181, NULL, 'ORD-20250409-67F680B5AED15', 200.00, 50.00, 'esewa', 'eSewa transaction_code: 000A84C', 'paid', '2025-04-09 14:14:13', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(182, NULL, 'ORD-20250409-67F68EDA8D77C', 200.00, 50.00, 'esewa', 'eSewa transaction_code: 000A84Q', 'paid', '2025-04-09 15:14:34', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(183, NULL, 'ORD-20250409-67F69403103C5', 200.00, 50.00, 'khalti', '', 'pending', '2025-04-09 15:36:35', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'pending', NULL),
(184, NULL, 'ORD-20250409-67F696D425F9D', 200.00, 50.00, 'esewa', '', 'completed', '2025-04-09 15:48:36', 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', 'madhumala', 'paid', NULL),
(185, NULL, 'ORD-20250410-67F7287068D1B', 80.00, 50.00, 'khalti', '', 'pending', '2025-04-10 02:09:52', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(186, NULL, 'ORD-20250410-67F7287904A4F', 80.00, 50.00, 'esewa', '', 'paid', '2025-04-10 02:10:01', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'paid', NULL),
(187, NULL, 'ORD-20250410-67F7739ECD80E', 80.00, 50.00, 'esewa', '', 'paid', '2025-04-10 07:30:38', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'paid', NULL),
(188, NULL, 'ORD-20250410-67F78EE2B0EC2', 80.00, 50.00, 'khalti', '', 'pending', '2025-04-10 09:26:58', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(189, NULL, 'ORD-20250411-67F8D27A36C25', 6415.00, 0.00, 'khalti', '', 'pending', '2025-04-11 08:27:38', 'Rahul chaudhary', 'root@gmail.com', '9812345673', 'pato', 'pending', NULL),
(190, NULL, 'ORD-20250411-67F8D36B397DF', 12815.00, 0.00, 'esewa', '', 'pending', '2025-04-11 08:31:39', 'Rahul chaudhary', 'rahultharu980893@gmail.com', '9815760082', 'pato-6', 'pending', NULL),
(191, NULL, 'ORD-20250411-67F8D39616D55', 12815.00, 0.00, 'khalti', '', 'processing', '2025-04-11 08:32:22', 'Rahul chaudhary', 'rahultharu980893@gmail.com', '9815760082', 'pato-6', 'pending', NULL),
(192, NULL, 'ORD-20250411-67F8D52CD2D64', 19215.00, 0.00, 'cod', '', 'processing', '2025-04-11 08:39:08', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(193, NULL, 'ORD-20250411-67F8D5ABDD984', 200.00, 50.00, 'khalti', '', 'pending', '2025-04-11 08:41:15', 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', 'Madhumalla', 'pending', NULL),
(194, NULL, 'ORD-20250411-67F8D5B3E6F32', 200.00, 50.00, 'esewa', '', 'pending', '2025-04-11 08:41:23', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(195, NULL, 'ORD-20250411-67F8DA9E90554', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-11 09:02:22', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(196, NULL, 'ORD-20250411-67F8DAA87A9E6', 6400.00, 0.00, 'esewa', '', 'pending', '2025-04-11 09:02:32', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(197, NULL, 'ORD-20250412-67F9F9C41C0F1', 350.00, 50.00, 'esewa', 'eSewa transaction_code: 000A8YU', 'paid', '2025-04-12 05:27:32', 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'itahari', 'pending', NULL),
(198, NULL, 'ORD-20250412-67F9FA062CC00', 6400.00, 0.00, 'khalti', '', 'pending', '2025-04-12 05:28:38', 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 'pending', NULL),
(199, NULL, 'ORD-20250415-67FE1E5D4E759', 12800.00, 0.00, 'khalti', '', 'pending', '2025-04-15 08:52:45', 'Rahul chaudhary', 'rahultharu980893@gmail.com', '9815760082', 'pato-6', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders1`
--

CREATE TABLE `orders1` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(20) NOT NULL,
  `payment_details` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders1`
--

INSERT INTO `orders1` (`id`, `order_number`, `user_id`, `name`, `email`, `phone`, `address`, `total_amount`, `shipping_fee`, `payment_method`, `payment_details`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ORD-20250330-67E9804D77980', 6, 'Rahul chaudhary', 'rahultharu980893@gmail.com', '9815760082', 'pato-6', 176.00, 50.00, 'cod', '', 'pending', '2025-03-30 17:33:01', '2025-03-30 17:33:01'),
(2, 'ORD-20250330-67E983A19E142', 6, 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 6400.00, 0.00, 'cod', '', 'pending', '2025-03-30 17:47:13', '2025-03-30 17:47:13'),
(3, 'ORD-20250330-67E98ECBEFC24', 6, 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'pato', 176.00, 50.00, 'cod', '', 'pending', '2025-03-30 18:34:51', '2025-03-30 18:34:51'),
(4, 'ORD-20250330-67E999BEACC53', 6, 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', '0', 6400.00, 0.00, 'khalti', NULL, 'pending', '2025-03-30 19:21:34', '2025-03-30 19:21:34'),
(5, 'ORD-20250330-67E999CB9F86F', 6, 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', '0', 6400.00, 0.00, 'khalti', NULL, 'pending', '2025-03-30 19:21:47', '2025-03-30 19:21:47'),
(6, 'ORD-20250331-67E9E8B552716', 6, 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', '0', 1700.00, 0.00, 'khalti', NULL, 'pending', '2025-03-31 00:58:29', '2025-03-31 00:58:29'),
(7, 'ORD-20250331-67E9E9A528A6B', 6, 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', '0', 1700.00, 0.00, 'khalti', NULL, 'pending', '2025-03-31 01:02:29', '2025-03-31 01:02:29'),
(8, 'ORD-20250403-67EE6AF2DB5E5', 11, 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', '0', 6400.00, 0.00, 'khalti', NULL, 'pending', '2025-04-03 11:03:14', '2025-04-03 11:03:14'),
(9, 'ORD-20250404-67EF3B30BD5E6', 12, 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', '0', 700.00, 0.00, 'khalti', NULL, 'pending', '2025-04-04 01:51:44', '2025-04-04 01:51:44'),
(10, 'ORD-20250404-67EF5570DA2D6', 12, 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', '0', 12800.00, 0.00, 'khalti', NULL, 'pending', '2025-04-04 03:43:44', '2025-04-04 03:43:44'),
(11, 'ORD-20250409-67F65A6B5677D', 12, 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', '0', 85.00, 50.00, 'khalti', NULL, 'pending', '2025-04-09 11:30:51', '2025-04-09 11:30:51'),
(12, 'ORD-20250409-67F65A7B5F8D0', 12, 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', '0', 85.00, 50.00, 'khalti', NULL, 'pending', '2025-04-09 11:31:07', '2025-04-09 11:31:07'),
(13, 'ORD-20250409-67F65AC3ABF82', 12, 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', '0', 85.00, 50.00, 'khalti', NULL, 'pending', '2025-04-09 11:32:19', '2025-04-09 11:32:19'),
(14, 'ORD-20250409-67F65CCC9CCC6', 12, 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', '0', 85.00, 50.00, 'khalti', NULL, 'pending', '2025-04-09 11:41:00', '2025-04-09 11:41:00'),
(15, 'ORD-20250409-67F661FE6ABE0', 12, 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', '0', 225.00, 50.00, 'khalti', NULL, 'pending', '2025-04-09 12:03:10', '2025-04-09 12:03:10'),
(16, 'ORD-20250409-67F6940317D33', 12, 'Barsha Magar', 'barsha1234@gmail.com', '9815000000', '0', 200.00, 50.00, 'khalti', NULL, 'pending', '2025-04-09 15:36:35', '2025-04-09 15:36:35'),
(17, 'ORD-20250410-67F7287072142', 12, 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', '0', 80.00, 50.00, 'khalti', NULL, 'pending', '2025-04-10 02:09:52', '2025-04-10 02:09:52'),
(18, 'ORD-20250410-67F78EE2B8EF1', 12, 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', '0', 80.00, 50.00, 'khalti', NULL, 'pending', '2025-04-10 09:26:58', '2025-04-10 09:26:58'),
(19, 'ORD-20250411-67F8D27A407F7', 12, 'Rahul chaudhary', 'root@gmail.com', '9812345673', '0', 6415.00, 0.00, 'khalti', NULL, 'pending', '2025-04-11 08:27:38', '2025-04-11 08:27:38'),
(20, 'ORD-20250411-67F8D39621FBE', 12, 'Rahul chaudhary', 'rahultharu980893@gmail.com', '9815760082', '0', 12815.00, 0.00, 'khalti', NULL, 'pending', '2025-04-11 08:32:22', '2025-04-11 08:32:22'),
(21, 'ORD-20250411-67F8D5ABE4D28', 14, 'Barsha Magar', 'barsha1234@gmail.com', '9703080249', '0', 200.00, 50.00, 'khalti', NULL, 'pending', '2025-04-11 08:41:15', '2025-04-11 08:41:15'),
(22, 'ORD-20250411-67F8DA9E96736', 12, 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', '0', 6400.00, 0.00, 'khalti', NULL, 'pending', '2025-04-11 09:02:22', '2025-04-11 09:02:22'),
(23, 'ORD-20250412-67F9FA06316CB', 12, 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', '0', 6400.00, 0.00, 'khalti', NULL, 'pending', '2025-04-12 05:28:38', '2025-04-12 05:28:38'),
(24, 'ORD-20250415-67FE1E5D594C0', 12, 'Rahul chaudhary', 'rahultharu980893@gmail.com', '9815760082', '0', 12800.00, 0.00, 'khalti', NULL, 'pending', '2025-04-15 08:52:45', '2025-04-15 08:52:45');

-- --------------------------------------------------------

--
-- Table structure for table `order_history`
--

CREATE TABLE `order_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` int(11) DEFAULT NULL COMMENT 'User ID who made the change'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_history`
--

INSERT INTO `order_history` (`id`, `order_id`, `status`, `notes`, `created_at`, `updated_by`) VALUES
(1, 1, 'pending', 'Order placed by customer', '2025-03-30 17:33:01', NULL),
(2, 2, 'pending', 'Order placed by customer', '2025-03-30 17:47:13', NULL),
(3, 3, 'pending', 'Order placed by customer', '2025-03-30 18:34:51', NULL),
(4, 4, 'pending', 'Order initialized via Khalti', '2025-03-30 19:21:34', NULL),
(5, 5, 'pending', 'Order initialized via Khalti', '2025-03-30 19:21:47', NULL),
(6, 6, 'pending', 'Order initialized via Khalti', '2025-03-31 00:58:29', NULL),
(7, 7, 'pending', 'Order initialized via Khalti', '2025-03-31 01:02:29', NULL),
(15, 8, 'pending', 'Order initialized via Khalti', '2025-04-03 11:03:14', NULL),
(16, 9, 'pending', 'Order initialized via Khalti', '2025-04-04 01:51:44', NULL),
(17, 10, 'pending', 'Order initialized via Khalti', '2025-04-04 03:43:44', NULL),
(22, 11, 'pending', 'Order initialized via Khalti', '2025-04-09 11:30:51', NULL),
(23, 12, 'pending', 'Order initialized via Khalti', '2025-04-09 11:31:07', NULL),
(24, 13, 'pending', 'Order initialized via Khalti', '2025-04-09 11:32:19', NULL),
(26, 14, 'pending', 'Order initialized via Khalti', '2025-04-09 11:41:00', NULL),
(27, 15, 'pending', 'Order initialized via Khalti', '2025-04-09 12:03:10', NULL),
(28, 16, 'pending', 'Order initialized via Khalti', '2025-04-09 15:36:35', NULL),
(29, 17, 'pending', 'Order initialized via Khalti', '2025-04-10 02:09:52', NULL),
(30, 18, 'pending', 'Order initialized via Khalti', '2025-04-10 09:26:58', NULL),
(31, 18, 'paid', 'Payment completed via Khalti (PIDX: jpF6hkpVfDjbCKBcddNkn9)', '2025-04-10 09:27:30', NULL),
(32, 19, 'pending', 'Order initialized via Khalti', '2025-04-11 08:27:38', NULL),
(33, 20, 'pending', 'Order initialized via Khalti', '2025-04-11 08:32:22', NULL),
(34, 21, 'pending', 'Order initialized via Khalti', '2025-04-11 08:41:15', NULL),
(35, 22, 'pending', 'Order initialized via Khalti', '2025-04-11 09:02:22', NULL),
(36, 23, 'pending', 'Order initialized via Khalti', '2025-04-12 05:28:38', NULL),
(37, 24, 'pending', 'Order initialized via Khalti', '2025-04-15 08:52:45', NULL),
(38, 24, 'paid', 'Payment completed via Khalti (PIDX: 6zJdwegp5sMfb7QWio2efH)', '2025-04-15 08:53:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `name`, `price`, `quantity`, `total`) VALUES
(9, 10, NULL, 'Dettol', 124.00, 1, 124.00),
(10, 11, NULL, 'Dettol', 124.00, 4, 496.00),
(11, 12, NULL, 'Dettol', 124.00, 5, 620.00),
(12, 13, NULL, 'sinex', 120.00, 1, 120.00),
(13, 14, NULL, 'sinex', 120.00, 1, 120.00),
(14, 15, NULL, 'lifeboy', 126.00, 13, 1638.00),
(15, 16, NULL, 'lifeboy', 126.00, 2, 252.00),
(16, 17, NULL, 'lifeboy', 126.00, 2, 252.00),
(17, 18, NULL, 'Shadow', 850.00, 12, 10200.00),
(18, 19, NULL, 'Shadow', 850.00, 7, 5950.00),
(19, 20, NULL, 'Shadow', 850.00, 9, 7650.00),
(20, 21, NULL, 'Shadow', 850.00, 1, 850.00),
(21, 22, NULL, 'Shadow', 850.00, 1, 850.00),
(22, 23, NULL, 'Shadow', 850.00, 1, 850.00),
(23, 24, NULL, 'Shadow', 850.00, 1, 850.00),
(24, 25, NULL, 'Shadow', 850.00, 6, 5100.00),
(25, 26, NULL, 'Shadow', 850.00, 4, 3400.00),
(26, 27, NULL, 'lifeboy', 126.00, 1, 126.00),
(27, 28, NULL, 'Shadow', 850.00, 1, 850.00),
(28, 29, NULL, 'lifeboy', 126.00, 1, 126.00),
(29, 30, NULL, 'Shadow', 850.00, 1, 850.00),
(30, 31, NULL, 'Shadow', 850.00, 4, 3400.00),
(31, 32, NULL, 'lifeboy', 126.00, 1, 126.00),
(32, 33, NULL, 'lifeboy', 126.00, 1, 126.00),
(33, 34, NULL, 'lifeboy', 126.00, 1, 126.00),
(34, 35, NULL, 'Shadow', 850.00, 1, 850.00),
(35, 36, NULL, 'Shadow', 850.00, 1, 850.00),
(36, 37, NULL, 'lifeboy', 126.00, 1, 126.00),
(37, 38, NULL, 'Shadow', 850.00, 1, 850.00),
(38, 43, NULL, 'Shadow', 850.00, 2, 1700.00),
(39, 43, NULL, 'lifeboy', 126.00, 1, 126.00),
(40, 44, NULL, 'Shadow', 850.00, 1, 850.00),
(41, 45, NULL, 'lifeboy', 126.00, 1, 126.00),
(42, 46, NULL, 'lifeboy', 126.00, 1, 126.00),
(43, 47, NULL, 'Shadow', 850.00, 1, 850.00),
(44, 48, NULL, 'Shadow', 850.00, 1, 850.00),
(45, 49, NULL, 'Shadow', 850.00, 1, 850.00),
(46, 50, NULL, 'Shadow', 850.00, 1, 850.00),
(47, 51, NULL, 'lifeboy', 126.00, 1, 126.00),
(48, 52, NULL, 'lifeboy', 126.00, 1, 126.00),
(49, 53, NULL, 'Shadow', 850.00, 1, 850.00),
(50, 54, NULL, 'Shadow', 850.00, 1, 850.00),
(51, 55, NULL, 'Shadow', 850.00, 1, 850.00),
(52, 56, NULL, 'Shadow', 850.00, 1, 850.00),
(53, 57, NULL, 'Shadow', 850.00, 1, 850.00),
(54, 58, NULL, 'Shadow', 850.00, 1, 850.00),
(55, 59, NULL, 'Shadow', 850.00, 1, 850.00),
(56, 60, NULL, 'Shadow', 850.00, 1, 850.00),
(57, 61, NULL, 'lifeboy', 126.00, 1, 126.00),
(58, 62, NULL, 'lifeboy', 126.00, 1, 126.00),
(59, 63, NULL, 'Shadow', 850.00, 1, 850.00),
(60, 64, NULL, 'lifeboy', 126.00, 1, 126.00),
(61, 65, NULL, 'Shadow', 850.00, 1, 850.00),
(62, 66, NULL, 'Shadow', 850.00, 1, 850.00),
(63, 67, NULL, 'Shadow', 850.00, 1, 850.00),
(64, 68, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(65, 69, NULL, 'lifeboy', 126.00, 1, 126.00),
(66, 70, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(67, 71, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(68, 72, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 2, 12800.00),
(69, 73, NULL, 'Shadow', 850.00, 1, 850.00),
(70, 74, NULL, 'lifeboy', 126.00, 1, 126.00),
(71, 75, NULL, 'Shadow', 850.00, 1, 850.00),
(72, 76, NULL, 'Shadow', 850.00, 2, 1700.00),
(73, 77, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(74, 78, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(75, 79, NULL, 'lifeboy', 126.00, 1, 126.00),
(76, 80, NULL, 'lifeboy', 126.00, 1, 126.00),
(77, 81, NULL, 'Shadow', 850.00, 1, 850.00),
(78, 82, NULL, 'Shadow', 850.00, 1, 850.00),
(79, 83, NULL, 'Shadow', 850.00, 1, 850.00),
(80, 84, NULL, 'lifeboy', 126.00, 1, 126.00),
(81, 85, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(82, 86, NULL, 'Shadow', 850.00, 1, 850.00),
(83, 87, NULL, 'Shadow', 850.00, 1, 850.00),
(84, 88, NULL, 'Shadow', 850.00, 1, 850.00),
(85, 89, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(86, 90, NULL, 'Shadow', 850.00, 2, 1700.00),
(87, 91, NULL, 'Shadow', 850.00, 1, 850.00),
(88, 92, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(89, 93, NULL, 'Shadow', 850.00, 1, 850.00),
(90, 94, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(91, 95, NULL, 'Shadow', 850.00, 1, 850.00),
(92, 96, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(93, 97, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(94, 98, NULL, 'Shadow', 850.00, 1, 850.00),
(95, 99, NULL, 'Shadow', 850.00, 1, 850.00),
(96, 100, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(97, 101, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(98, 102, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(99, 103, NULL, 'Shadow', 850.00, 1, 850.00),
(100, 104, NULL, 'Shadow', 850.00, 1, 850.00),
(101, 105, NULL, 'lifeboy', 126.00, 1, 126.00),
(102, 106, NULL, 'Shadow', 850.00, 2, 1700.00),
(103, 107, NULL, 'Shadow', 850.00, 1, 850.00),
(104, 108, NULL, 'Shadow', 850.00, 1, 850.00),
(105, 109, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(106, 110, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(107, 111, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(108, 112, NULL, 'Shadow', 850.00, 1, 850.00),
(109, 113, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(110, 114, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 2, 12800.00),
(111, 115, NULL, 'Shadow', 850.00, 1, 850.00),
(112, 116, NULL, 'Shadow', 850.00, 1, 850.00),
(113, 117, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(114, 118, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(115, 119, NULL, 'Shadow', 850.00, 1, 850.00),
(116, 120, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(117, 121, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(118, 122, NULL, 'Shadow', 850.00, 2, 1700.00),
(119, 123, NULL, 'Shadow', 850.00, 1, 850.00),
(120, 124, NULL, 'Shadow', 850.00, 1, 850.00),
(121, 125, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(122, 126, 9, 'Paracetamol', 150.00, 1, 150.00),
(123, 127, 29, 'Vitamin E Capsules', 550.00, 1, 550.00),
(124, 127, 19, 'Dettol Antiseptic Liquid', 85.00, 1, 85.00),
(125, 127, 10, 'Cetirizine 10mg', 15.00, 1, 15.00),
(126, 128, 11, 'Amoxicillin 500mg', 120.00, 1, 120.00),
(127, 128, 10, 'Cetirizine 10mg', 15.00, 1, 15.00),
(128, 129, 10, 'Cetirizine 10mg', 15.00, 1, 15.00),
(129, 130, 9, 'Paracetamol', 150.00, 1, 150.00),
(130, 131, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(131, 132, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(132, 133, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(133, 134, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(134, 135, 9, 'Paracetamol', 150.00, 1, 150.00),
(135, 136, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(136, 137, 9, 'Paracetamol', 150.00, 1, 150.00),
(137, 138, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(138, 139, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(139, 140, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(140, 141, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(141, 142, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(142, 143, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(143, 144, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(144, 145, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(145, 146, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 2, 12800.00),
(146, 147, 9, 'Paracetamol', 150.00, 1, 150.00),
(147, 148, 35, 'Liver Function Test (LFT)', 1500.00, 1, 1500.00),
(148, 149, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(149, 150, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(150, 151, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(151, 152, 25, 'Vitamin C 500mg', 250.00, 2, 500.00),
(152, 152, 9, 'Paracetamol', 150.00, 1, 150.00),
(153, 153, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 2, 12800.00),
(154, 154, 10, 'Cetirizine 10mg', 15.00, 2, 30.00),
(155, 155, 10, 'Cetirizine 10mg', 15.00, 2, 30.00),
(156, 156, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(157, 156, 9, 'Paracetamol', 150.00, 1, 150.00),
(158, 157, 53, 'cosrx-bha-blackhead-power-liquid-100ml', 4020.00, 1, 4020.00),
(159, 158, 53, 'cosrx-bha-blackhead-power-liquid-100ml', 4020.00, 1, 4020.00),
(160, 159, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(161, 159, 9, 'Paracetamol', 150.00, 1, 150.00),
(162, 160, 32, 'Omeprazole 20mg', 55.00, 1, 55.00),
(163, 161, 32, 'Omeprazole 20mg', 55.00, 1, 55.00),
(164, 162, 32, 'Omeprazole 20mg', 55.00, 1, 55.00),
(165, 163, 32, 'Omeprazole 20mg', 55.00, 1, 55.00),
(166, 164, 12, 'Nepalgin Syrup', 35.00, 1, 35.00),
(167, 165, 12, 'Nepalgin Syrup', 35.00, 1, 35.00),
(168, 166, 12, 'Nepalgin Syrup', 35.00, 1, 35.00),
(169, 167, 12, 'Nepalgin Syrup', 35.00, 1, 35.00),
(170, 168, 12, 'Nepalgin Syrup', 35.00, 1, 35.00),
(171, 169, 12, 'Nepalgin Syrup', 35.00, 1, 35.00),
(173, 171, 12, 'Nepalgin Syrup', 35.00, 1, 35.00),
(174, 172, 12, 'Nepalgin Syrup', 35.00, 1, 35.00),
(175, 172, 32, 'Omeprazole 20mg', 55.00, 1, 55.00),
(176, 173, 30, 'Ranitidine 150mg', 25.00, 1, 25.00),
(177, 173, 9, 'Paracetamol', 150.00, 1, 150.00),
(178, 174, 30, 'Ranitidine 150mg', 25.00, 1, 25.00),
(179, 174, 9, 'Paracetamol', 150.00, 1, 150.00),
(180, 175, 30, 'Ranitidine 150mg', 25.00, 1, 25.00),
(181, 175, 9, 'Paracetamol', 150.00, 1, 150.00),
(182, 176, 30, 'Ranitidine 150mg', 25.00, 1, 25.00),
(183, 176, 9, 'Paracetamol', 150.00, 1, 150.00),
(184, 177, 30, 'Ranitidine 150mg', 25.00, 1, 25.00),
(185, 177, 9, 'Paracetamol', 150.00, 1, 150.00),
(186, 178, 9, 'Paracetamol', 150.00, 1, 150.00),
(187, 179, 9, 'Paracetamol', 150.00, 1, 150.00),
(188, 180, 9, 'Paracetamol', 150.00, 1, 150.00),
(189, 181, 9, 'Paracetamol', 150.00, 1, 150.00),
(190, 182, 9, 'Paracetamol', 150.00, 1, 150.00),
(191, 183, 9, 'Paracetamol', 150.00, 1, 150.00),
(192, 184, 9, 'Paracetamol', 150.00, 1, 150.00),
(193, 185, 10, 'Cetirizine 10mg', 15.00, 2, 30.00),
(194, 186, 10, 'Cetirizine 10mg', 15.00, 2, 30.00),
(195, 187, 10, 'Cetirizine 10mg', 15.00, 2, 30.00),
(196, 188, 10, 'Cetirizine 10mg', 15.00, 2, 30.00),
(197, 189, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(198, 189, 10, 'Cetirizine 10mg', 15.00, 1, 15.00),
(199, 190, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 2, 12800.00),
(200, 190, 10, 'Cetirizine 10mg', 15.00, 1, 15.00),
(201, 191, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 2, 12800.00),
(202, 191, 10, 'Cetirizine 10mg', 15.00, 1, 15.00),
(203, 192, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 3, 19200.00),
(204, 192, 10, 'Cetirizine 10mg', 15.00, 1, 15.00),
(205, 193, 9, 'Paracetamol', 150.00, 1, 150.00),
(206, 194, 9, 'Paracetamol', 150.00, 1, 150.00),
(207, 195, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(208, 196, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(209, 197, 9, 'Paracetamol', 150.00, 2, 300.00),
(210, 198, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(211, 199, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 2, 12800.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_items1`
--

CREATE TABLE `order_items1` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items1`
--

INSERT INTO `order_items1` (`id`, `order_id`, `product_id`, `name`, `price`, `quantity`, `total`) VALUES
(1, 1, 6, 'lifeboy', 126.00, 1, 126.00),
(2, 2, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(3, 3, 6, 'lifeboy', 126.00, 1, 126.00),
(4, 4, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(5, 5, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(6, 6, 7, 'Shadow', 850.00, 2, 1700.00),
(7, 7, 7, 'Shadow', 850.00, 2, 1700.00),
(8, 11, 12, 'Nepalgin Syrup', 35.00, 1, 35.00),
(9, 12, 12, 'Nepalgin Syrup', 35.00, 1, 35.00),
(10, 13, 12, 'Nepalgin Syrup', 35.00, 1, 35.00),
(11, 14, 12, 'Nepalgin Syrup', 35.00, 1, 35.00),
(12, 15, 30, 'Ranitidine 150mg', 25.00, 1, 25.00),
(13, 15, 9, 'Paracetamol', 150.00, 1, 150.00),
(14, 16, 9, 'Paracetamol', 150.00, 1, 150.00),
(15, 17, 10, 'Cetirizine 10mg', 15.00, 2, 30.00),
(16, 18, 10, 'Cetirizine 10mg', 15.00, 2, 30.00),
(17, 19, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(18, 19, 10, 'Cetirizine 10mg', 15.00, 1, 15.00),
(19, 20, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 2, 12800.00),
(20, 20, 10, 'Cetirizine 10mg', 15.00, 1, 15.00),
(21, 21, 9, 'Paracetamol', 150.00, 1, 150.00),
(22, 22, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(23, 23, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 1, 6400.00),
(24, 24, 8, 'MuscleBlaze Weight Gainer High Protein', 6400.00, 2, 12800.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_shipping`
--

CREATE TABLE `order_shipping` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `carrier_id` int(11) NOT NULL,
  `tracking_number` varchar(100) NOT NULL,
  `shipping_method` varchar(100) DEFAULT NULL,
  `estimated_delivery` date DEFAULT NULL,
  `actual_delivery` date DEFAULT NULL,
  `status` enum('processing','shipped','out_for_delivery','delivered','returned') NOT NULL DEFAULT 'processing',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_updates`
--

CREATE TABLE `order_updates` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `message` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `tracking_code` varchar(50) DEFAULT NULL,
  `update_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_updates`
--

INSERT INTO `order_updates` (`id`, `order_id`, `status`, `message`, `location`, `tracking_code`, `update_time`) VALUES
(1, 117, 'pending', 'Your order has been received and is being processed', NULL, NULL, '2025-04-01 08:01:22'),
(2, 118, 'pending', 'Your order has been received and is being processed', NULL, NULL, '2025-04-01 08:03:56'),
(3, 115, 'pending', 'Your order has been received and is being processed', NULL, NULL, '2025-04-01 08:04:22'),
(4, 126, 'pending', 'Your order has been received and is being processed', NULL, NULL, '2025-04-02 06:12:11'),
(5, 125, 'pending', 'Your order has been received and is being processed', NULL, NULL, '2025-04-02 06:14:08'),
(6, 124, 'pending', 'Your order has been received and is being processed', NULL, NULL, '2025-04-02 06:14:43');

-- --------------------------------------------------------

--
-- Table structure for table `otps1`
--

CREATE TABLE `otps1` (
  `id` int(11) NOT NULL,
  `email1` varchar(255) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` enum('image','pdf') NOT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified_by` int(11) DEFAULT NULL,
  `verification_date` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `user_id`, `file_name`, `file_path`, `file_type`, `status`, `upload_date`, `verified_by`, `verification_date`, `notes`) VALUES
(1, 6, 'prescriptions.jpeg', 'C:\\xamppnew\\htdocs\\ecommerce\\ECOMMERCE\\frontend/../uploads/prescriptions/67e80b2c8e0f9.jpeg', 'image', 'rejected', '2025-03-29 15:01:00', 6, '2025-03-29 17:39:32', ''),
(2, 6, 'protein.jpeg', 'C:\\xamppnew\\htdocs\\ecommerce\\ECOMMERCE\\frontend/../uploads/prescriptions/67e80bbad80b9.jpeg', 'image', 'rejected', '2025-03-29 15:03:22', 6, '2025-03-29 17:55:25', ''),
(3, 6, 'prescriptions.jpeg', 'uploads/prescriptions/67e81febd9513.jpeg', 'image', 'pending', '2025-03-29 16:29:31', NULL, NULL, NULL),
(4, 6, 'prescriptions.jpeg', 'uploads/prescriptions/67e822b35d040.jpeg', 'image', 'rejected', '2025-03-29 16:41:23', 6, '2025-03-29 17:39:26', ''),
(5, 6, 'prescriptions.jpeg', 'uploads/prescriptions/67e822e253054.jpeg', 'image', 'rejected', '2025-03-29 16:42:10', 6, '2025-03-31 07:11:01', ''),
(6, 6, 'prescriptions.jpeg', 'uploads/prescriptions/67e823ba62ed6.jpeg', 'image', 'pending', '2025-03-29 16:45:46', NULL, NULL, NULL),
(7, 6, 'prescriptions.jpeg', 'uploads/prescriptions/67e828ebdae00.jpeg', 'image', 'pending', '2025-03-29 17:07:55', NULL, NULL, NULL),
(8, 6, 'prescriptions.jpeg', 'uploads/prescriptions/67e8292d91643.jpeg', 'image', 'verified', '2025-03-29 17:09:01', 6, '2025-03-29 17:39:15', 'iyhikhkhkjhnkjnjkh'),
(9, 6, 'Order Confirmation _ PharmaCare1.pdf', 'uploads/prescriptions/67e829bbddfe1.pdf', 'pdf', 'pending', '2025-03-29 17:11:23', NULL, NULL, NULL),
(10, 6, 'prescriptions.jpeg', 'uploads/prescriptions/67e82c089d493.jpeg', 'image', 'verified', '2025-03-29 17:21:12', 6, '2025-03-29 17:39:03', 'gujg,jughkjhk,jhhjhkkjjk'),
(11, 6, 'pres23.jpeg', 'uploads/prescriptions/67e82d2684135.jpeg', 'image', 'verified', '2025-03-29 17:25:58', 6, '2025-03-29 17:36:53', 'yes i liked very much'),
(12, 6, 'pres23.jpeg', 'uploads/prescriptions/67e82fd1e51f2.jpeg', 'image', 'verified', '2025-03-29 17:37:21', 6, '2025-03-29 17:38:53', 'jhjkh'),
(13, 6, 'eprescriptions.jpeg', 'uploads/prescriptions/67e8b7acb996c.jpeg', 'image', 'verified', '2025-03-30 03:17:00', 6, '2025-03-30 03:17:38', 'this priscriptions is certified from top doctiors'),
(14, 6, 'eprescriptions.jpeg', 'uploads/prescriptions/67ea3ff324aef.jpeg', 'image', 'pending', '2025-03-31 07:10:43', NULL, NULL, NULL),
(15, 6, 'dettol.jpg', 'uploads/prescriptions/67ea41869b5c9.jpg', 'image', 'pending', '2025-03-31 07:17:26', NULL, NULL, NULL),
(16, 6, 'prescriptions.jpeg', 'uploads/prescriptions/67eb7bba14763.jpeg', 'image', 'pending', '2025-04-01 05:38:02', NULL, NULL, NULL),
(17, 11, 'prescriptions.jpeg', 'uploads/prescriptions/67ec85c805490.jpeg', 'image', 'pending', '2025-04-02 00:33:12', NULL, NULL, NULL),
(18, 14, 'paracetamol111.jpeg', 'uploads/prescriptions/67f8d806e3345.jpeg', 'image', 'rejected', '2025-04-11 08:51:18', 14, '2025-04-11 08:52:10', '');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_comments`
--

CREATE TABLE `prescription_comments` (
  `id` int(11) NOT NULL,
  `prescription_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescription_comments`
--

INSERT INTO `prescription_comments` (`id`, `prescription_id`, `user_id`, `comment`, `created_at`) VALUES
(1, 11, 6, 'yes i liked very much', '2025-03-29 17:36:53'),
(2, 12, 6, 'jhjkh', '2025-03-29 17:38:53'),
(3, 10, 6, 'gujg,jughkjhk,jhhjhkkjjk', '2025-03-29 17:39:03'),
(4, 8, 6, 'iyhikhkhkjhnkjnjkh', '2025-03-29 17:39:15'),
(5, 13, 6, 'this priscriptions is certified from top doctiors', '2025-03-30 03:17:38');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `prescription_required` enum('Yes','No') DEFAULT 'No',
  `expiry_date` date NOT NULL,
  `manufacturer` varchar(255) NOT NULL,
  `active_ingredients` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bmi_category` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `description`, `category_id`, `price`, `quantity`, `prescription_required`, `expiry_date`, `manufacturer`, `active_ingredients`, `image_path`, `created_at`, `bmi_category`) VALUES
(8, 'MuscleBlaze Weight Gainer High Protein', 'Gain Mass Like Never Before - Packed with a whopping 1422 kcal in three servings, MuscleBlaze Weight Gainer ignites your weight gain journey so you achieve your bulking goals.', 4, 6400.00, 100, 'No', '2030-12-25', 'Dr. Barsha Magar', '30 Servings - 3kgs/6.6lbs\r\nMass Gainer\r\nWeight Gainer', '../uploads/products/67e7b6d22e54d_prteinm.jpg', '2025-03-29 09:01:06', 'Underweight'),
(9, 'Paracetamol', 'Paracetamol is a medicine used to treat mild to moderate pain.', 15, 150.00, 100, 'Yes', '2028-11-23', 'Farmson Basic Drugs Private Limited', 'acetaminophen', '../uploads/products/67ebfdb69354c_medicines1.jpeg', '2025-04-01 14:52:38', 'Obese Class I'),
(10, 'Cetirizine 10mg', 'Allergy relief tablets', 15, 15.00, 50, 'Yes', '2027-12-23', 'Deurali-Janta', 'Cetirizine HCl', '../uploads/products/67ec94301e14c_cetirizine_tablet.webp', '2025-04-02 01:34:40', 'Obese Class I'),
(11, 'Amoxicillin 500mg', 'Antibiotic for bacterial infections (Rx)', 15, 120.00, 60, 'Yes', '2026-02-16', 'Himalayan Remedies', 'Amoxicillin Trihydrate', '../uploads/products/67ec9583122f9_Amoxicillin.jpeg', '2025-04-02 01:40:19', 'Obese Class II'),
(12, 'Nepalgin Syrup', 'Ayurvedic cold/cough syrup', 15, 35.00, 70, 'Yes', '2027-10-23', '35.00', 'Tulsi, Adulsa, Sunthi', '../uploads/products/67ec967050cb3_Nepalgin_Syrup.jpg', '2025-04-02 01:44:16', 'Obese Class I'),
(14, 'Digital Thermometer', 'Fast-reading oral/axillary thermometer', 10, 250.00, 120, 'No', '2040-12-13', 'Omron Healthcare', 'Electronic sensor', '../uploads/products/67ec9a37c08a9_Digital_Thermometer.webp', '2025-04-02 02:00:23', NULL),
(15, 'Blood Pressure Monitor', 'Automatic arm cuff monitor', 10, 1500.00, 120, 'No', '2040-12-13', 'Dr. Morepen', 'Oscillometric measurement', '../uploads/products/67ec9b3e82978_Blood_Pressure_Monitor.jpg', '2025-04-02 02:04:46', NULL),
(16, 'Glucometer Kit', 'Complete diabetes testing kit', 10, 1200.00, 120, 'No', '2040-12-13', 'Accu-Chek', 'Electrochemical strip technology', '../uploads/products/67ec9be726a3d_Glucometer_Kit.webp', '2025-04-02 02:07:35', NULL),
(17, 'Hot Water Bag', 'Rubber pain relief hot pack', 10, 350.00, 120, 'No', '2040-12-13', 'Care Touch', 'Natural rubber', '../uploads/products/67ec9cb84d6cf_Hot_Water_Bag.jpg', '2025-04-02 02:11:04', NULL),
(18, 'First Aid Kit', '35-piece emergency medical kit', 10, 850.00, 120, 'No', '2040-12-13', 'Medicare Nepal', 'Sterile gauze, bandages, etc.', '../uploads/products/67ec9d5f35541_First_Aid_Kit.jpeg', '2025-04-02 02:13:51', NULL),
(19, 'Dettol Antiseptic Liquid', 'Germ-killing disinfectant (100ml)', 11, 85.00, 120, 'No', '2040-12-13', 'Reckitt Nepal', 'Chloroxylenol 4.8%', '../uploads/products/67ec9ec13e52c_Dettol_Antiseptic_Liquid.jpeg', '2025-04-02 02:19:45', NULL),
(20, 'Himalaya Neem Face Wash', 'Ayurvedic acne control face wash', 11, 120.00, 500, 'No', '2040-12-13', 'Himalaya Herbals', 'Neem + Turmeric extracts', '../uploads/products/67eca4278b51a_Himalaya_Neem_Face_Wash.jpeg', '2025-04-02 02:42:47', NULL),
(22, 'Sensodyne Toothpaste', 'Sensitivity relief toothpaste', 11, 220.00, 500, 'No', '2040-12-13', 'GSK Nepal', 'Potassium Nitrate 5%', '../uploads/products/67eca60d39a4e_Sensodyne_Toothpaste.webp', '2025-04-02 02:50:53', NULL),
(23, 'Nivea Body Lotion', 'Moisturizing lotion (200ml)', 11, 250.00, 500, 'No', '2040-12-13', 'Beiersdorf Nepal', 'Glycerin + Vitamin E', '../uploads/products/67eca69fcf860_Nivea_Body_Lotion.jpg', '2025-04-02 02:53:19', NULL),
(24, 'Electric Toothbrush', 'Rechargeable with 3 modes', 11, 250.00, 500, 'No', '2040-12-13', 'Oral-B Nepal', 'Oscillating brush head', '../uploads/products/67eca7921ea8d_Electric_Toothbrush.jpg', '2025-04-02 02:57:22', NULL),
(25, 'Vitamin C 500mg', 'Immune booster tablets (60pc)', 16, 250.00, 500, 'Yes', '2040-12-13', 'Himalayan Organics', 'Ascorbic Acid', '../uploads/products/67ecb290d5a54_Vitamin_C_500mg.jpg', '2025-04-02 03:44:16', NULL),
(26, 'B-Complex Capsules', 'Energy metabolism support', 16, 150.00, 500, 'Yes', '2040-12-13', 'National Health Care', 'B1+B2+B3+B5+B6+B9+B12', '../uploads/products/67ecb30e5b367_B_Complex_Capsules.jpeg', '2025-04-02 03:46:22', NULL),
(27, 'Calcium + Vitamin D', 'Bone strength tablets', 16, 150.00, 500, 'Yes', '2040-12-13', 'Biocare Nepal', 'Calcium Carbonate + D3', '../uploads/products/67ecb3794b7ce_Calcium___Vitamin_D.webp', '2025-04-02 03:48:09', NULL),
(28, 'Omega-3 Fish Oil', '1000mg heart health capsules', 16, 550.00, 500, 'Yes', '2040-12-13', 'Nordic Naturals Nepal', 'EPA 180mg + DHA 120mg', '../uploads/products/67ecb3ff21ba9_Omega_3_Fish_Oil.webp', '2025-04-02 03:50:23', NULL),
(29, 'Vitamin E Capsules', 'Antioxidant skin support', 16, 550.00, 500, 'Yes', '2040-12-13', 'Swanson Nepal', 'd-alpha Tocopherol 400IU', '../uploads/products/67ecb460e4b46_Vitamin_E_Capsules.webp', '2025-04-02 03:52:00', NULL),
(30, 'Ranitidine 150mg', 'Acidity and stomach pain relief', 15, 25.00, 150, 'No', '2027-12-30', '2025', '', '../uploads/products/67ecca783218b_Ranitidine_150mg.jpg', '2025-04-02 05:26:16', NULL),
(31, 'Ibuprofen 400mg', 'Anti-inflammatory painkiller', 15, 45.00, 150, 'No', '2027-12-30', '2025', '', '../uploads/products/67eccc6064073_Ibuprofen_400mg.png', '2025-04-02 05:34:24', NULL),
(32, 'Omeprazole 20mg', 'Gastric acid reducer', 15, 55.00, 150, 'No', '2027-12-30', '2025', '', '../uploads/products/67eccce467332_Omeprazole_20mg.webp', '2025-04-02 05:36:36', NULL),
(33, 'Azithromycin 500mg', 'Broad-spectrum antibiotic (Rx)', 15, 150.00, 150, 'No', '2030-12-30', '2025', '', '../uploads/products/67eccd8ed9f72_Azithromycin_500mg.jpeg', '2025-04-02 05:39:26', NULL),
(34, 'Pulse Oximeter', 'Finger-tip oxygen level monitor', 10, 1800.00, 150, 'No', '2032-12-30', '2025', '', '../uploads/products/67ecced3a4d12_Pulse_Oximeter.png', '2025-04-02 05:44:51', NULL),
(35, 'Liver Function Test (LFT)', 'Evaluates liver enzymes (SGOT, SGPT), proteins, bilirubin', 14, 1500.00, 50, 'Yes', '2030-12-08', 'Nepal Mediciti Lab', 'Spectrophotometry', '../uploads/products/67eceb9985fd4_abnormal_liver_blood_tests.jpg', '2025-04-02 07:47:37', NULL),
(36, 'Vitamin D (25-OH) Test', 'Measures vitamin D deficiency', 14, 2200.00, 50, 'Yes', '2030-12-08', 'Om Hospital Lab', 'CLIA (Chemiluminescence)', '../uploads/products/67eced97c244a_Vitamin_D__25_OH__Test.jpeg', '2025-04-02 07:56:07', NULL),
(37, 'Hepatitis B Surface Antigen', 'Detects HBV infection', 14, 800.00, 50, 'Yes', '2030-12-08', 'CIWEC Clinic Lab', 'ELISA', '../uploads/products/67ecee743aa36_Hepatitis_B_Surface_Antigen.png', '2025-04-02 07:59:48', NULL),
(38, 'Thyroid Profile (T3,T4,TSH)', 'Assesses thyroid hormone levels', 14, 1800.00, 50, 'Yes', '2030-12-08', 'HAMS Hospital Lab', 'Chemiluminescence immunoassay', '../uploads/products/67ecef372e452_Thyroid_Profile__T3_T4_TSH_.jpg', '2025-04-02 08:03:03', NULL),
(39, 'Blood Glucose (Fasting)', 'Measures sugar levels after 8-hour fasting', 14, 200.00, 50, 'Yes', '2030-12-08', 'Norvic Hospital Lab', 'Glucose oxidase method', '../uploads/products/67ecefe37cb43_Blood_Glucose__Fasting_.webp', '2025-04-02 08:05:55', NULL),
(40, 'Basic Health Checkup', 'CBC, sugar, cholesterol, uric acid', 13, 1500.00, 44, 'Yes', '2028-11-11', 'Norvic International', 'CBC, RBS, Cholesterol, Uric Acid', '../uploads/products/67ecf21a9e0b0_Basic_Health_Checkup.jpg', '2025-04-02 08:15:22', NULL),
(41, 'Full Body Checkup', '80+ tests with doctor consultation', 13, 8000.00, 40, 'Yes', '2028-11-11', 'Grande International', '80+ Tests with CT Scan', '../uploads/products/67ecf34c17b99_Full_Body_Checkup.webp', '2025-04-02 08:20:28', NULL),
(42, 'Complete Blood Count (CBC)', 'Measures red/white blood cells, hemoglobin, and platelets', 14, 600.00, 50, 'Yes', '2030-11-11', 'National Pathology Center', 'Automated hematology analyzer', '../uploads/products/67ed2ee122d67_complete_body_count.jpg', '2025-04-02 12:34:41', NULL),
(43, 'Dolo 650', 'Fever reducer & pain reliever', 17, 15.00, 100, 'Yes', '2028-12-13', 'Micro Labs Nepal', 'Paracetamol 650mg', '../uploads/products/67edd0d22bdd1_Dolo_650.webp', '2025-04-03 00:05:38', NULL),
(44, 'Omeprazole', 'Gastric acid reducer', 17, 55.00, 100, 'Yes', '2028-11-22', 'Aristo Pharma', 'Omeprazole 20mg', '../uploads/products/67eddab469e76_Omeprazole_20mg.webp', '2025-04-03 00:47:48', NULL),
(45, 'Liv-52 Syrup', 'Liver tonic & digestive aid', 17, 180.00, 100, 'Yes', '2028-12-13', 'Himalaya Herbals', 'Herbal extract (Caper Bush)', '../uploads/products/67ede5445c04d_Liv_52_Syrup.jpeg', '2025-04-03 01:32:52', NULL),
(46, 'N95 Mask', 'Respiratory protection', 17, 150.00, 100, 'No', '2028-12-13', '3M Nepal', 'Melt-blown polypropylene', '../uploads/products/67ede66789203_N95_Mask.jpg', '2025-04-03 01:37:43', NULL),
(47, 'Volini Gel', 'Pain relief gel for joints', 17, 250.00, 100, 'No', '2028-12-13', 'Sanofi India', 'Diclofenac 1% + Linseed Oil', '../uploads/products/67ede75dae1fb_Volini_Gel.jpg', '2025-04-03 01:41:49', NULL),
(48, 'Accu-Chek Active', 'Blood glucose monitoring system', 17, 1800.00, 150, 'No', '2028-11-11', 'Roche Nepal', 'Test strips + Glucometer', '../uploads/products/67edef4d58327_Accu_Chek_Active.webp', '2025-04-03 02:15:41', NULL),
(49, 'ORS Lemon', 'Electrolyte replacement solution', 17, 25.00, 200, 'No', '2026-12-13', 'Jeevan Jal', 'Sodium Chloride + Citrate', '../uploads/products/67edf4b6ebfa8_ORS_Lemon.jpeg', '2025-04-03 02:38:46', NULL),
(50, 'Cetrizine-DX', 'Allergy relief syrup', 17, 120.00, 100, 'Yes', '2029-08-12', 'Deurali-Janta', 'Cetirizine 5mg/5ml', '../uploads/products/67edf82c7cb1f_Cetrizine_DX.webp', '2025-04-03 02:53:32', NULL),
(53, 'cosrx-bha-blackhead-power-liquid-100ml', 'unclog pores, reduce blackheads', 19, 4020.00, 200, 'No', '2029-11-12', 'COSRX Inc., South Korea', 'Betaine Salicylate (4%), Niacinamide, Willow Bark', '../uploads/products/67eecd6d62bc5_cosrx_bha_blackhead_power_liquid_100ml.jpeg', '2025-04-03 18:03:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shipping_carriers`
--

CREATE TABLE `shipping_carriers` (
  `id` int(11) NOT NULL,
  `carrier_name` varchar(100) NOT NULL,
  `tracking_url` varchar(255) NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_carriers`
--

INSERT INTO `shipping_carriers` (`id`, `carrier_name`, `tracking_url`, `logo_url`, `created_at`) VALUES
(1, 'FedEx', 'https://www.fedex.com/fedextrack/?tracknumbers={tracking_number}', 'https://example.com/logos/fedex.png', '2025-03-31 15:24:39'),
(2, 'UPS', 'https://www.ups.com/track?tracknum={tracking_number}', 'https://example.com/logos/ups.png', '2025-03-31 15:51:10'),
(3, 'USPS', 'https://tools.usps.com/go/TrackConfirmAction?tLabels={tracking_number}', 'uploads/products/67eabcec97339_USPS.png', '2025-03-31 16:03:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `dob` date NOT NULL,
  `address` text NOT NULL,
  `province` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `role` enum('customer','pharmacist') NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `confirmPassword` varchar(123) NOT NULL,
  `status` enum('active','inactive','suspended','pending') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `gender`, `dob`, `address`, `province`, `city`, `role`, `password`, `created_at`, `updated_at`, `confirmPassword`, `status`) VALUES
(1, 'Rahul chaudhary', 'rahul12344@gmail.com', '9815760082', 'male', '2000-12-13', 'pato', 'koshi', 'dakneswori', 'pharmacist', '$2y$10$oBUAwLIfwKFtWrP17izyeeDnfB8zR8HtRp4710Is2GHZe1V25YBFq', '2025-03-24 02:10:04', '2025-03-24 02:10:04', '$2y$10$Rx0CF2PzjgA2WdTDkWO1EuFFs5bbeQVysIiztAytLT4tLCuZQ62Vq', 'active'),
(2, 'Rijan chaudhary', 'rijan1234@gmail.com', '9812345673', 'male', '2004-12-13', 'itahari', 'koshi', 'itahari', 'pharmacist', '$2y$10$TK8xl.lm7Unov/ko3ROc.OJXkAuDwtMCQiW3QM8y/eklzJe8ab.wi', '2025-03-24 02:40:01', '2025-03-24 02:40:01', '$2y$10$5etApFRe/I616wdrt7LYsOn8Cewcxy1t7hXBKULNnbedw6QoCQsgi', 'active'),
(3, 'Barsha Magar', 'barshamagar1234@gmail.com', '9703080249', 'female', '2005-03-12', 'Madhumalla', 'koshi', 'urlabari', 'customer', '$2y$10$Oo.dO5Fc8a76Nehj1q.gruNHD6HiTRerCgS5OjVBZph2YHGJAgKS6', '2025-03-25 16:24:05', '2025-03-25 16:24:05', '$2y$10$ZvubrjldgMFIVI3Jow8ts.Bg9ecyFbBXqyielGzMg/JhFhZsswF1S', 'active'),
(5, 'Barsha Magar ', 'barsha1234@gmail.com', '9703080249', 'female', '2005-03-12', 'Madhumalla', 'koshi', 'urlabari', 'customer', '$2y$10$agnVN093iTDUZCa/vmKjQ.ZZBOnowd1YQyuDoIQKZzr5J6HXyHP02', '2025-03-25 16:32:26', '2025-03-25 16:32:26', '$2y$10$OLIBN1IWaLhUi9KkadH5Me5QzYA7D5axznIDx.GKwDhtf65.PD0ti', 'active'),
(6, 'Barsha Magar', 'barsha980892@gmail.com', '9703080249', 'female', '2003-12-12', 'Madhumalla', 'koshi', 'Urlabari', 'customer', '$2y$10$P.iGnpu8bR/HNMP4/fu2.OWDsUz0qnsG8Ae5LoHsPbzJtDwjQAG66', '2025-03-26 11:51:33', '2025-03-26 11:51:33', '$2y$10$ODOIl1qPlBc1bhESgZuMZ.AKrEAU4SlP/VzYaaIiFyMCCa6.bSWEe', 'active'),
(7, 'Rahul Chaudhary', 'rahultharu980893@gmail.com', '9815760082', 'male', '2005-12-13', 'itahari', 'koshi', 'iahari', 'customer', '$2y$10$y.7qMXaOrL8Chje9GOnbpucmJKw3bDDmTX40VAMTrGGx6HosCHYHm', '2025-03-29 15:28:47', '2025-03-29 15:28:47', '$2y$10$/k/8nx6uHcMBehc6LygEg.8CxCHCiKYYA1toR3qlZOU5FOCWnc1Fe', 'active'),
(8, 'nishchay', 'nishchay123@gmail.com', '9800000001', 'male', '2006-12-18', 'itahari', 'koshi', 'itahari', 'pharmacist', '$2y$10$Y6rPM8iGZYBvEmLNwGlBMO734jj3QbVBo70EB0Voxr/dxRWj3NjUe', '2025-03-29 16:02:49', '2025-03-29 16:02:49', '$2y$10$x.piQ.byZ5XyE65JU24OUuifp4Ge7C1UA95b76siUKJJQkVQGqf5u', 'active'),
(11, 'Rahul chaudhary', 'rahultharu46882@icloud.com', '9815760082', 'male', '2004-08-07', 'pato-6', 'madhesh', 'Dakneshwori', 'pharmacist', '$2y$10$prH5x5be6GT7jdnV9M.jKuaibVd4vfEobPhuCAqLeYJoZc.WFVuX2', '2025-04-02 00:17:30', '2025-04-02 00:17:30', '', 'active'),
(12, 'Rijan Chaudhary', 'rijan123@gmail.com', '9800000000', 'male', '2010-12-13', 'itahari', 'koshi', 'itahari', 'customer', '$2y$10$kyctugfWEq8LEOe9ghSaIePnoVrwoCJu0wb0vFE7Dcc7nThjpCdLi', '2025-04-02 11:50:15', '2025-04-02 11:50:15', '', 'active'),
(13, 'Rahul Chaudhary', 'rahutharul9803@gmail.com', '9815760082', 'male', '2004-07-08', 'pato-6', 'madhesh', 'Dakneshwori-6', 'pharmacist', '$2y$10$bo21dsKDuB2bq24qMPA2M.1cqY0AUqN3LtoNzc2duluyGy68x7q5y', '2025-04-07 07:02:44', '2025-04-07 07:02:44', '', 'active'),
(14, 'Rahul chaudhary', 'rahultharu9804@icloud.com', '9800000000', 'male', '2004-07-08', 'Pato-7', 'madhesh', 'Dakneshwori', 'pharmacist', '$2y$10$ySYSwqZn8zyIpSanVaDI3uORcm.hQgMhJf6HmIuQMQQbY/uMKOWUu', '2025-04-08 12:28:28', '2025-04-08 12:28:28', '', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `art`
--
ALTER TABLE `art`
  ADD PRIMARY KEY (`id`),
  ADD KEY `catego_id` (`catego_id`);

--
-- Indexes for table `bmi_history`
--
ALTER TABLE `bmi_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cat`
--
ALTER TABLE `cat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`);

--
-- Indexes for table `orders1`
--
ALTER TABLE `orders1`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_history`
--
ALTER TABLE `order_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_items1`
--
ALTER TABLE `order_items1`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_shipping`
--
ALTER TABLE `order_shipping`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `carrier_id` (`carrier_id`),
  ADD KEY `tracking_number` (`tracking_number`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `order_updates`
--
ALTER TABLE `order_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `otps1`
--
ALTER TABLE `otps1`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `prescription_comments`
--
ALTER TABLE `prescription_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prescription_id` (`prescription_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `shipping_carriers`
--
ALTER TABLE `shipping_carriers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `art`
--
ALTER TABLE `art`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bmi_history`
--
ALTER TABLE `bmi_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `cat`
--
ALTER TABLE `cat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=200;

--
-- AUTO_INCREMENT for table `orders1`
--
ALTER TABLE `orders1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `order_history`
--
ALTER TABLE `order_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=212;

--
-- AUTO_INCREMENT for table `order_items1`
--
ALTER TABLE `order_items1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `order_shipping`
--
ALTER TABLE `order_shipping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_updates`
--
ALTER TABLE `order_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `otps1`
--
ALTER TABLE `otps1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `prescription_comments`
--
ALTER TABLE `prescription_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `shipping_carriers`
--
ALTER TABLE `shipping_carriers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`);

--
-- Constraints for table `art`
--
ALTER TABLE `art`
  ADD CONSTRAINT `art_ibfk_1` FOREIGN KEY (`catego_id`) REFERENCES `cat` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bmi_history`
--
ALTER TABLE `bmi_history`
  ADD CONSTRAINT `bmi_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_history`
--
ALTER TABLE `order_history`
  ADD CONSTRAINT `order_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders1` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items1`
--
ALTER TABLE `order_items1`
  ADD CONSTRAINT `order_items1_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders1` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_updates`
--
ALTER TABLE `order_updates`
  ADD CONSTRAINT `order_updates_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `prescription_comments`
--
ALTER TABLE `prescription_comments`
  ADD CONSTRAINT `prescription_comments_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescription_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
