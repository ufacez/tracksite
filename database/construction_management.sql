-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 09, 2025 at 01:17 AM
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
-- Database: `construction_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `table_name`, `record_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:08:28'),
(2, 1, 'logout', 'users', 1, 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:10:07'),
(3, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:10:11'),
(4, 1, 'add_worker', 'workers', 1, 'Added new worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:14:49'),
(5, 1, 'logout', 'users', 1, 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:14:52'),
(6, 2, 'login', 'users', 2, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:14:57'),
(7, 2, 'logout', 'users', 2, 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:15:13'),
(8, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:15:17'),
(9, 1, 'archive_worker', 'workers', 1, 'Archived worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:15:41'),
(10, 1, 'logout', 'users', 1, 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:16:01'),
(11, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:16:21'),
(12, 1, 'restore_worker', 'workers', 1, 'Restored worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:18:21'),
(13, 1, 'edit_worker', 'workers', 1, 'Updated worker: Ean Paolo Espiritu (WKR-0001) - Updated: status', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:18:34'),
(14, 1, 'logout', 'users', 1, 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:18:39'),
(15, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:19:32'),
(16, 1, 'edit_worker', 'workers', 1, 'Updated worker: Ean Paolo Espiritu (WKR-0001) - Updated: status', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:19:51'),
(17, 1, 'logout', 'users', 1, 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:20:45'),
(18, 2, 'login', 'users', 2, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:20:54'),
(19, 2, 'logout', 'users', 2, 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:21:28'),
(20, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:21:33'),
(21, 1, 'archive_worker', 'workers', 1, 'Archived worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:21:43'),
(22, 1, 'restore_worker', 'workers', 1, 'Restored worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:21:55'),
(23, 1, 'logout', 'users', 1, 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:32:44'),
(24, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-06 12:32:50'),
(25, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-07 10:40:46'),
(26, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-07 10:41:00'),
(27, 1, 'logout', 'users', 1, 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-07 10:41:30'),
(28, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 00:05:01'),
(29, 1, 'edit_worker', 'workers', 1, 'Updated worker: Ean Paolo Espiritu (WKR-0001) - Updated worker details', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 00:05:18'),
(30, 1, 'logout', 'users', 1, 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 00:05:24'),
(31, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 00:05:48'),
(32, 1, 'edit_worker', 'workers', 1, 'Updated worker: Ean Paolo Espiritu (WKR-0001) - Updated: status', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 00:07:06'),
(33, 1, 'edit_worker', 'workers', 1, 'Updated worker: Ean Paolo Espiritu (WKR-0001) - Updated: status', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 00:07:36'),
(34, 1, 'logout', 'users', 1, 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 00:07:37'),
(35, 2, 'login', 'users', 2, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 00:07:42'),
(36, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 01:19:55'),
(37, 1, 'mark_attendance', 'attendance', 1, 'Marked attendance for worker ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 01:23:05'),
(38, 1, 'mark_attendance', 'attendance', 2, 'Marked attendance for worker ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 01:35:24'),
(39, 1, 'archive_worker', 'workers', 1, 'Archived worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 01:48:28'),
(40, 1, 'restore_worker', 'workers', 1, 'Restored worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 01:48:41'),
(41, 1, 'mark_attendance', 'attendance', 3, 'Marked attendance for worker ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 01:49:07'),
(42, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 09:15:32'),
(43, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 10:21:47'),
(44, 1, 'archive_worker', 'workers', 1, 'Archived worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 10:47:38'),
(45, 1, 'restore_worker', 'workers', 1, 'Restored worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 10:47:51'),
(46, 1, 'logout', 'users', 1, 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 11:03:30'),
(47, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 11:03:49'),
(48, 1, 'archive_worker', 'workers', 1, 'Archived worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 11:10:49'),
(49, 1, 'restore_worker', 'workers', 1, 'Restored worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 11:11:09'),
(50, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-08 12:58:49'),
(51, 1, 'archive_worker', 'workers', 1, 'Archived worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-08 12:58:54'),
(52, 1, 'restore_worker', 'workers', 1, 'Restored worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-08 12:59:32'),
(53, 1, 'archive_attendance', 'attendance', 3, 'Archived attendance record for Ean Paolo Espiritu on November 08, 2025', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-08 13:06:53'),
(54, 1, 'restore_attendance', 'attendance', 3, 'Restored archived attendance record', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-08 13:07:06'),
(55, 1, 'archive_worker', 'workers', 1, 'Archived worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-08 13:07:13'),
(56, 1, 'restore_worker', 'workers', 1, 'Restored worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-08 13:07:21'),
(57, 1, 'edit_worker', 'workers', 1, 'Updated worker: Ean Paolo Espiritu (WKR-0001) - Updated worker details', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-08 13:14:51'),
(58, 1, 'logout', 'users', 1, 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-08 13:14:53'),
(59, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-08 13:15:10'),
(60, 1, 'archive_worker', 'workers', 1, 'Archived worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-08 13:15:22'),
(61, 1, 'restore_worker', 'workers', 1, 'Restored worker: Ean Paolo Espiritu (WKR-0001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-08 13:15:42'),
(62, 1, 'archive_attendance', 'attendance', 3, 'Archived attendance record for Ean Paolo Espiritu on November 08, 2025', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-08 13:17:00'),
(63, 1, 'restore_attendance', 'attendance', 3, 'Restored archived attendance record', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-08 13:17:18'),
(64, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-08 23:41:46'),
(65, 1, 'login', 'users', 1, 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-11-08 23:43:15');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('present','late','absent','overtime','half_day') NOT NULL DEFAULT 'present',
  `hours_worked` decimal(5,2) DEFAULT 0.00,
  `overtime_hours` decimal(5,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0,
  `archived_at` timestamp NULL DEFAULT NULL,
  `archived_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `worker_id`, `attendance_date`, `time_in`, `time_out`, `status`, `hours_worked`, `overtime_hours`, `notes`, `verified_by`, `created_at`, `updated_at`, `is_archived`, `archived_at`, `archived_by`) VALUES
(3, 1, '2025-11-08', '09:48:00', '20:48:00', 'present', 11.00, 0.00, NULL, NULL, '2025-11-08 01:49:07', '2025-11-08 13:17:18', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cash_advances`
--

CREATE TABLE `cash_advances` (
  `advance_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `request_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','repaying','completed') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `repayment_amount` decimal(10,2) DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deductions`
--

CREATE TABLE `deductions` (
  `deduction_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `payroll_id` int(11) DEFAULT NULL,
  `deduction_type` enum('sss','philhealth','pagibig','tax','loan','other') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `deduction_date` date NOT NULL,
  `status` enum('pending','applied','cancelled') NOT NULL DEFAULT 'applied',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `face_encodings`
--

CREATE TABLE `face_encodings` (
  `encoding_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `encoding_data` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `payroll_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `pay_period_start` date NOT NULL,
  `pay_period_end` date NOT NULL,
  `days_worked` int(11) NOT NULL DEFAULT 0,
  `total_hours` decimal(10,2) NOT NULL DEFAULT 0.00,
  `overtime_hours` decimal(10,2) DEFAULT 0.00,
  `gross_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(10,2) DEFAULT 0.00,
  `net_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('pending','processing','paid','cancelled') NOT NULL DEFAULT 'pending',
  `payment_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `super_admin_profile`
--

CREATE TABLE `super_admin_profile` (
  `admin_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `super_admin_profile`
--

INSERT INTO `super_admin_profile` (`admin_id`, `user_id`, `first_name`, `last_name`, `phone`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 1, 'System', 'Administrator', '+63 900 000 0000', NULL, '2025-11-06 11:54:39', '2025-11-06 11:54:39');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'company_name', 'JHLibiran Construction Corp.', 'text', 'Company name', NULL, '2025-11-06 11:54:39', '2025-11-06 11:54:39'),
(2, 'system_name', 'TrackSite', 'text', 'System name', NULL, '2025-11-06 11:54:39', '2025-11-06 11:54:39'),
(3, 'timezone', 'Asia/Manila', 'text', 'System timezone', NULL, '2025-11-06 11:54:39', '2025-11-06 11:54:39'),
(4, 'currency', 'PHP', 'text', 'Currency code', NULL, '2025-11-06 11:54:39', '2025-11-06 11:54:39'),
(5, 'work_hours_per_day', '8', 'number', 'Standard work hours per day', NULL, '2025-11-06 11:54:39', '2025-11-06 11:54:39'),
(6, 'overtime_rate_multiplier', '1.25', 'number', 'Overtime rate multiplier', NULL, '2025-11-06 11:54:39', '2025-11-06 11:54:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `user_level` enum('super_admin','worker') NOT NULL DEFAULT 'worker',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `user_level`, `status`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'admin', '$2y$10$dVglklGhDwvNB963sbkKeeq8dcmHvLewuEbrW4qPa2x3M1eC06B72', 'admin@tracksite.com', 'super_admin', 'active', '2025-11-06 11:54:39', '2025-11-08 23:43:15', '2025-11-08 23:43:15'),
(2, 'ean', '$2y$10$YCUpLouzhpSOm54qGjB1WuWo80NaInNgWBgATGjMO/ISWtSdbhGPm', 'ean@gmail.com', 'worker', 'inactive', '2025-11-06 12:14:49', '2025-11-08 13:14:51', '2025-11-08 00:07:41'),
(4, 'admin2', 'admin1234', 'admin2@gmail.com', 'super_admin', 'active', '2025-11-07 10:15:54', '2025-11-07 10:15:54', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_payroll_summary`
-- (See below for the actual view)
--
CREATE TABLE `vw_payroll_summary` (
`worker_id` int(11)
,`worker_code` varchar(20)
,`worker_name` varchar(101)
,`position` varchar(50)
,`total_payrolls` bigint(21)
,`total_gross_pay` decimal(32,2)
,`total_deductions` decimal(32,2)
,`total_net_pay` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_worker_attendance_summary`
-- (See below for the actual view)
--
CREATE TABLE `vw_worker_attendance_summary` (
`worker_id` int(11)
,`worker_code` varchar(20)
,`worker_name` varchar(101)
,`position` varchar(50)
,`present_count` bigint(21)
,`late_count` bigint(21)
,`absent_count` bigint(21)
,`total_hours_worked` decimal(27,2)
,`total_overtime_hours` decimal(27,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `workers`
--

CREATE TABLE `workers` (
  `worker_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `worker_code` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `date_hired` date NOT NULL,
  `employment_status` enum('active','on_leave','terminated','blocklisted') NOT NULL DEFAULT 'active',
  `daily_rate` decimal(10,2) NOT NULL,
  `experience_years` int(11) DEFAULT 0,
  `profile_image` varchar(255) DEFAULT NULL,
  `sss_number` varchar(50) DEFAULT NULL,
  `philhealth_number` varchar(50) DEFAULT NULL,
  `pagibig_number` varchar(50) DEFAULT NULL,
  `tin_number` varchar(50) DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT 0,
  `archived_at` timestamp NULL DEFAULT NULL,
  `archived_by` int(11) DEFAULT NULL,
  `archive_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workers`
--

INSERT INTO `workers` (`worker_id`, `user_id`, `worker_code`, `first_name`, `last_name`, `position`, `phone`, `address`, `date_of_birth`, `gender`, `emergency_contact_name`, `emergency_contact_phone`, `date_hired`, `employment_status`, `daily_rate`, `experience_years`, `profile_image`, `sss_number`, `philhealth_number`, `pagibig_number`, `tin_number`, `is_archived`, `archived_at`, `archived_by`, `archive_reason`, `created_at`, `updated_at`) VALUES
(1, 2, 'WKR-0001', 'Ean Paolo', 'Espiritu', 'Carpenter', '+63 1234 567 789', '223 Street 5, Brgy. Dela Paz Norte, San Fernando City, Pampanga, 2000.', '2004-11-06', 'male', 'Marycris Espiritu', '091234567', '2022-02-10', 'active', 1000.00, 0, NULL, '12321321', '12321321', '12321321', '12321321', 0, NULL, NULL, NULL, '2025-11-06 12:14:49', '2025-11-08 13:15:42');

-- --------------------------------------------------------

--
-- Structure for view `vw_payroll_summary`
--
DROP TABLE IF EXISTS `vw_payroll_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_payroll_summary`  AS SELECT `w`.`worker_id` AS `worker_id`, `w`.`worker_code` AS `worker_code`, concat(`w`.`first_name`,' ',`w`.`last_name`) AS `worker_name`, `w`.`position` AS `position`, count(`p`.`payroll_id`) AS `total_payrolls`, sum(`p`.`gross_pay`) AS `total_gross_pay`, sum(`p`.`total_deductions`) AS `total_deductions`, sum(`p`.`net_pay`) AS `total_net_pay` FROM (`workers` `w` left join `payroll` `p` on(`w`.`worker_id` = `p`.`worker_id`)) GROUP BY `w`.`worker_id`, `w`.`worker_code`, `w`.`first_name`, `w`.`last_name`, `w`.`position` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_worker_attendance_summary`
--
DROP TABLE IF EXISTS `vw_worker_attendance_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_worker_attendance_summary`  AS SELECT `w`.`worker_id` AS `worker_id`, `w`.`worker_code` AS `worker_code`, concat(`w`.`first_name`,' ',`w`.`last_name`) AS `worker_name`, `w`.`position` AS `position`, count(case when `a`.`status` = 'present' then 1 end) AS `present_count`, count(case when `a`.`status` = 'late' then 1 end) AS `late_count`, count(case when `a`.`status` = 'absent' then 1 end) AS `absent_count`, sum(`a`.`hours_worked`) AS `total_hours_worked`, sum(`a`.`overtime_hours`) AS `total_overtime_hours` FROM (`workers` `w` left join `attendance` `a` on(`w`.`worker_id` = `a`.`worker_id`)) GROUP BY `w`.`worker_id`, `w`.`worker_code`, `w`.`first_name`, `w`.`last_name`, `w`.`position` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `unique_worker_date` (`worker_id`,`attendance_date`),
  ADD KEY `verified_by` (`verified_by`),
  ADD KEY `idx_attendance_date` (`attendance_date`),
  ADD KEY `idx_worker_date` (`worker_id`,`attendance_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_attendance_archived_by` (`archived_by`),
  ADD KEY `idx_is_archived` (`is_archived`);

--
-- Indexes for table `cash_advances`
--
ALTER TABLE `cash_advances`
  ADD PRIMARY KEY (`advance_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_worker_id` (`worker_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_request_date` (`request_date`);

--
-- Indexes for table `deductions`
--
ALTER TABLE `deductions`
  ADD PRIMARY KEY (`deduction_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_worker_id` (`worker_id`),
  ADD KEY `idx_payroll_id` (`payroll_id`),
  ADD KEY `idx_deduction_type` (`deduction_type`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `face_encodings`
--
ALTER TABLE `face_encodings`
  ADD PRIMARY KEY (`encoding_id`),
  ADD KEY `idx_worker_id` (`worker_id`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`payroll_id`),
  ADD KEY `processed_by` (`processed_by`),
  ADD KEY `idx_worker_id` (`worker_id`),
  ADD KEY `idx_pay_period` (`pay_period_start`,`pay_period_end`),
  ADD KEY `idx_payment_status` (`payment_status`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD UNIQUE KEY `unique_worker_day` (`worker_id`,`day_of_week`),
  ADD KEY `idx_worker_id` (`worker_id`),
  ADD KEY `idx_day_of_week` (`day_of_week`);

--
-- Indexes for table `super_admin_profile`
--
ALTER TABLE `super_admin_profile`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_user_level` (`user_level`);

--
-- Indexes for table `workers`
--
ALTER TABLE `workers`
  ADD PRIMARY KEY (`worker_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `worker_code` (`worker_code`),
  ADD KEY `archived_by` (`archived_by`),
  ADD KEY `idx_worker_code` (`worker_code`),
  ADD KEY `idx_employment_status` (`employment_status`),
  ADD KEY `idx_position` (`position`),
  ADD KEY `idx_is_archived` (`is_archived`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cash_advances`
--
ALTER TABLE `cash_advances`
  MODIFY `advance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deductions`
--
ALTER TABLE `deductions`
  MODIFY `deduction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `face_encodings`
--
ALTER TABLE `face_encodings`
  MODIFY `encoding_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `payroll_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `super_admin_profile`
--
ALTER TABLE `super_admin_profile`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `workers`
--
ALTER TABLE `workers`
  MODIFY `worker_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_attendance_archived_by` FOREIGN KEY (`archived_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `cash_advances`
--
ALTER TABLE `cash_advances`
  ADD CONSTRAINT `cash_advances_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cash_advances_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `deductions`
--
ALTER TABLE `deductions`
  ADD CONSTRAINT `deductions_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deductions_ibfk_2` FOREIGN KEY (`payroll_id`) REFERENCES `payroll` (`payroll_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deductions_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `face_encodings`
--
ALTER TABLE `face_encodings`
  ADD CONSTRAINT `face_encodings_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE;

--
-- Constraints for table `super_admin_profile`
--
ALTER TABLE `super_admin_profile`
  ADD CONSTRAINT `super_admin_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `workers`
--
ALTER TABLE `workers`
  ADD CONSTRAINT `workers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `workers_ibfk_2` FOREIGN KEY (`archived_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
