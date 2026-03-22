-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 22, 2026 at 05:22 PM
-- Server version: 8.0.43
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dlsu_marketplace_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_accounts`
--

CREATE TABLE `admin_accounts` (
  `admin_id` int NOT NULL,
  `user_id` int NOT NULL,
  `admin_role_id` int NOT NULL,
  `assigned_by` int NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_accounts`
--

INSERT INTO `admin_accounts` (`admin_id`, `user_id`, `admin_role_id`, `assigned_by`, `assigned_at`) VALUES
(1, 5, 1, 5, '2026-03-10 16:39:28'),
(2, 1, 2, 5, '2026-03-10 16:39:35');

-- --------------------------------------------------------

--
-- Table structure for table `admin_roles`
--

CREATE TABLE `admin_roles` (
  `admin_role_id` int NOT NULL,
  `role_name` enum('Moderator','Superadmin') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_roles`
--

INSERT INTO `admin_roles` (`admin_role_id`, `role_name`) VALUES
(1, 'Superadmin'),
(2, 'Moderator');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int NOT NULL,
  `buyer_id` int NOT NULL,
  `listing_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `buyer_id`, `listing_id`, `quantity`, `added_at`) VALUES
(2, 2, 1, 1, '2026-03-18 08:05:44'),
(3, 3, 1, 1, '2026-03-18 08:14:59'),
(4, 4, 2, 3, '2026-03-18 08:32:32'),
(5, 4, 1, 1, '2026-03-18 08:50:16'),
(6, 5, 1, 1, '2026-03-22 15:45:22');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int NOT NULL,
  `category_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_by_admin_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `created_by_admin_id`, `created_at`) VALUES
(1, 'RVRCOB', 1, '2026-03-11 07:16:46'),
(2, 'GCOE', 1, '2026-03-11 07:17:25'),
(3, 'CCS', 1, '2026-03-11 07:17:49'),
(4, 'COS', 1, '2026-03-11 07:17:54'),
(5, 'CLA', 1, '2026-03-11 07:18:04'),
(6, 'BAGCED', 1, '2026-03-11 07:18:09'),
(7, 'SOE', 1, '2026-03-11 07:18:17'),
(8, 'COL', 1, '2026-03-11 07:18:33'),
(9, 'SHS', 1, '2026-03-16 15:19:47'),
(10, 'Clothes', 1, '2026-03-16 15:21:02'),
(11, 'Books', 1, '2026-03-16 15:21:07');

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `claim_id` int NOT NULL,
  `listing_id` int NOT NULL,
  `buyer_id` int NOT NULL,
  `seller_id` int NOT NULL,
  `status` enum('Pending','Completed','Cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `claimed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `listing_id` int NOT NULL,
  `seller_id` int NOT NULL,
  `product_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `category1_id` int NOT NULL,
  `category2_id` int DEFAULT NULL,
  `category3_id` int DEFAULT NULL,
  `status` enum('Available','Reserved','Sold') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Available',
  `is_removed` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`listing_id`, `seller_id`, `product_name`, `description`, `price`, `quantity`, `category1_id`, `category2_id`, `category3_id`, `status`, `is_removed`, `created_at`, `updated_at`) VALUES
(1, 1, 'New Trends in Computers', 'A book about new trends in computers', 180.00, 1, 3, 11, NULL, 'Available', 0, '2026-03-17 04:10:26', '2026-03-17 04:10:26'),
(2, 3, 'Men Regular Fit Solid Button Down Collar Casual Shirt', 'Comes in different colors.\r\n- beige\r\n- light blue\r\n- black\r\n', 180.00, 3, 10, NULL, NULL, 'Available', 0, '2026-03-18 08:28:28', '2026-03-18 08:28:28');

-- --------------------------------------------------------

--
-- Table structure for table `listing_images`
--

CREATE TABLE `listing_images` (
  `image_id` int NOT NULL,
  `listing_id` int NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `listing_images`
--

INSERT INTO `listing_images` (`image_id`, `listing_id`, `image_path`, `uploaded_at`) VALUES
(1, 1, 'uploads/listing_1_69b8d43238ca1.jpg', '2026-03-17 04:10:26'),
(2, 2, 'uploads/listing_2_69ba622c4c76d.jpg', '2026-03-18 08:28:28'),
(3, 2, 'uploads/listing_2_69ba622c4d55e.jpg', '2026-03-18 08:28:28'),
(4, 2, 'uploads/listing_2_69ba622c4e06d.jpg', '2026-03-18 08:28:28');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `rating_id` int NOT NULL,
  `claim_id` int NOT NULL,
  `rater_id` int NOT NULL,
  `rated_user_id` int NOT NULL,
  `rating_value` int DEFAULT NULL,
  `review` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_reviewed_by_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int NOT NULL,
  `reporter_id` int NOT NULL,
  `reported_listing_id` int DEFAULT NULL,
  `reported_user_id` int DEFAULT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Pending','Resolved','Rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `reviewed_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `log_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `action_type` enum('CREATE','UPDATE','DELETE') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_proofs`
--

CREATE TABLE `transaction_proofs` (
  `proof_id` int NOT NULL,
  `claim_id` int NOT NULL,
  `submitted_by` int NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `dlsu_id_number` int NOT NULL,
  `dlsu_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `course_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('Student','Faculty','Staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone_number` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `profile_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'default-pfp.jpg',
  `is_verified` tinyint(1) DEFAULT '0',
  `warning_count` int DEFAULT '0',
  `is_suspended` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `dlsu_id_number`, `dlsu_email`, `password_hash`, `first_name`, `last_name`, `course_code`, `role`, `phone_number`, `profile_picture`, `is_verified`, `warning_count`, `is_suspended`, `created_at`, `updated_at`) VALUES
(1, 12323780, 'camille_erika_sarabia@dlsu.edu.ph', '$2y$10$1evMpDD1mNlRpp3hLiUxne4atiVeQGYXEbUK4Ehf3k0lJcsM/yO1C', 'Camille Erika', 'Sarabia', 'BS-IT', 'Student', '09685706073', 'default-pfp.jpg', 0, 0, 0, '2026-03-11 00:39:10', '2026-03-11 00:39:10'),
(2, 12410012, 'gian_enriquez@dlsu.edu.ph', '$2y$10$tIqsLGExCeBZAGDMzKjZsuwpcrX4SO2ZoTCw1ZLWx.xu3SKLDfDhG', 'Gian Patrick', 'Enriquez', 'BS-IT', 'Student', '09458676744', 'default-pfp.jpg', 0, 0, 0, '2026-03-18 08:04:55', '2026-03-18 08:04:55'),
(3, 12413178, 'sky_parado@dlsu.edu.ph', '$2y$10$TDVmMCBJ3GMQlLmYIK9IYuI/Zh8e6kY9h1q4UedoqYR1.HayBGJS6', 'Sky Hannah', 'Parado', 'BSCS-NIS', 'Student', '09762447493', 'default-pfp.jpg', 0, 0, 0, '2026-03-18 08:10:14', '2026-03-18 08:10:14'),
(4, 12415421, 'andie_woo@dlsu.edu.ph', '$2y$10$7hyMz6zNUoZjGBC2Dx3ovOXYGk/DQ1Q0YoDlt.nzLVL6o5y08oV.i', 'Andie Kirsten', 'Woo', 'BSCS-ST', 'Student', '09171588460', 'default-pfp.jpg', 0, 0, 0, '2026-03-18 08:08:18', '2026-03-18 08:24:11'),
(5, 12415537, 'giancarlo_lawan@dlsu.edu.ph', '$2y$10$ieieEv0tDKCPsPswqNocnOxj6ig.fktSAJqx8ykGeJkpgEOTv1gy6', 'Giancarlo', 'Lawan', 'BS-IT', 'Student', '09285170610', 'default-pfp.jpg', 0, 0, 0, '2026-03-22 14:34:49', '2026-03-22 16:17:24');

-- --------------------------------------------------------

--
-- Table structure for table `warnings`
--

CREATE TABLE `warnings` (
  `warning_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating_id` int DEFAULT NULL,
  `issued_by` int NOT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  ADD PRIMARY KEY (`admin_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_role_id` (`admin_role_id`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `admin_roles`
--
ALTER TABLE `admin_roles`
  ADD PRIMARY KEY (`admin_role_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`buyer_id`),
  ADD KEY `listing_id` (`listing_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`),
  ADD KEY `created_by_admin_id` (`created_by_admin_id`);

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`claim_id`),
  ADD KEY `listing_id` (`listing_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`listing_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `category1_id` (`category1_id`) USING BTREE,
  ADD KEY `category2_id` (`category2_id`) USING BTREE,
  ADD KEY `category3_id` (`category3_id`) USING BTREE;

--
-- Indexes for table `listing_images`
--
ALTER TABLE `listing_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `listing_id` (`listing_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `claim_id` (`claim_id`),
  ADD KEY `rater_id` (`rater_id`),
  ADD KEY `rated_user_id` (`rated_user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `reported_listing_id` (`reported_listing_id`),
  ADD KEY `reported_user_id` (`reported_user_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transaction_proofs`
--
ALTER TABLE `transaction_proofs`
  ADD PRIMARY KEY (`proof_id`),
  ADD KEY `claim_id` (`claim_id`),
  ADD KEY `submitted_by` (`submitted_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `dlsu_email` (`dlsu_email`),
  ADD UNIQUE KEY `dlsu_id_number` (`dlsu_id_number`);

--
-- Indexes for table `warnings`
--
ALTER TABLE `warnings`
  ADD PRIMARY KEY (`warning_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `rating_id` (`rating_id`),
  ADD KEY `issued_by` (`issued_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  MODIFY `admin_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_roles`
--
ALTER TABLE `admin_roles`
  MODIFY `admin_role_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `claim_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `listing_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `listing_images`
--
ALTER TABLE `listing_images`
  MODIFY `image_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `rating_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `log_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaction_proofs`
--
ALTER TABLE `transaction_proofs`
  MODIFY `proof_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `warnings`
--
ALTER TABLE `warnings`
  MODIFY `warning_id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  ADD CONSTRAINT `admin_accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_accounts_ibfk_2` FOREIGN KEY (`admin_role_id`) REFERENCES `admin_roles` (`admin_role_id`),
  ADD CONSTRAINT `admin_accounts_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`) ON DELETE CASCADE;

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`),
  ADD CONSTRAINT `claims_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `claims_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `listings`
--
ALTER TABLE `listings`
  ADD CONSTRAINT `listings_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `listings_ibfk_2` FOREIGN KEY (`category1_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `listings_ibfk_3` FOREIGN KEY (`category2_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `listings_ibfk_4` FOREIGN KEY (`category3_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `listing_images`
--
ALTER TABLE `listing_images`
  ADD CONSTRAINT `listing_images_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
