-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 22, 2021 at 06:58 PM
-- Server version: 5.7.33-0ubuntu0.16.04.1
-- PHP Version: 7.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `signable`
--

-- --------------------------------------------------------

--
-- Table structure for table `actions`
--

CREATE TABLE `actions` (
  `id` int(11) NOT NULL,
  `user_profile_id` int(11) NOT NULL,
  `location_role_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `purpose` int(11) NOT NULL,
  `action` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `audits`
--

CREATE TABLE `audits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_id` bigint(20) UNSIGNED NOT NULL,
  `old_values` text COLLATE utf8mb4_unicode_ci,
  `new_values` text COLLATE utf8mb4_unicode_ci,
  `url` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(1023) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audits`
--

INSERT INTO `audits` (`id`, `user_type`, `user_id`, `event`, `auditable_type`, `auditable_id`, `old_values`, `new_values`, `url`, `ip_address`, `user_agent`, `tags`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 1, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":1}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 02:25:14', '2021-04-21 02:25:14'),
(2, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 2, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":2}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 02:26:27', '2021-04-21 02:26:27'),
(3, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 3, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":3}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 03:00:06', '2021-04-21 03:00:06'),
(4, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 4, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":4}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 03:07:41', '2021-04-21 03:07:41'),
(5, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 5, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":5}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 03:08:11', '2021-04-21 03:08:11'),
(6, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 6, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":6}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 03:08:38', '2021-04-21 03:08:38'),
(7, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 7, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":7}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 03:09:04', '2021-04-21 03:09:04'),
(8, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 8, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":8}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 03:09:29', '2021-04-21 03:09:29'),
(9, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 9, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":9}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 03:09:46', '2021-04-21 03:09:46'),
(10, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 10, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":10}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 03:10:18', '2021-04-21 03:10:18'),
(11, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 11, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":11}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 03:13:27', '2021-04-21 03:13:27'),
(12, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 12, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":12}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 03:15:46', '2021-04-21 03:15:46'),
(13, NULL, NULL, 'created', 'App\\Models\\Token', 1, '[]', '{"email":"vikas.verve@gmail.com","token":"kxxoiinedfgtpwboimrx","expired_time":1619082197,"id":1}', 'http://localhost/SignableService/public/api/forget_password', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 03:33:17', '2021-04-21 03:33:17'),
(14, NULL, NULL, 'created', 'App\\Models\\Token', 2, '[]', '{"email":"vikas.verve@gmail.com","token":"tyrpyzyrztfzaqaufdnv","expired_time":1619082239,"id":2}', 'http://localhost/SignableService/public/api/forget_password', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 03:33:59', '2021-04-21 03:33:59'),
(15, NULL, NULL, 'created', 'App\\Models\\Token', 3, '[]', '{"email":"rudresh.verve@gmail.com","token":"blbvwxxfhxtncojdqjjd","expired_time":1619082347,"id":3}', 'http://localhost/SignableService/public/api/forget_password', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 03:35:47', '2021-04-21 03:35:47'),
(16, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 13, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":13}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 05:01:34', '2021-04-21 05:01:34'),
(17, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 14, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":14}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 05:15:26', '2021-04-21 05:15:26'),
(18, NULL, NULL, 'created', 'App\\Models\\Token', 4, '[]', '{"email":"vikas.verve@gmail.com","token":"dbtngmhqrmttkpyquifu","expired_time":1619096339,"id":4}', 'http://localhost/SignableService/public/api/forget_password', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 07:28:59', '2021-04-21 07:28:59'),
(19, NULL, NULL, 'created', 'App\\Models\\Token', 5, '[]', '{"email":"vikas.verve@gmail.com","token":"wixivmitymorerprxlae","expired_time":1619096394,"id":5}', 'http://localhost/SignableService/public/api/forget_password', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 07:29:55', '2021-04-21 07:29:55'),
(20, NULL, NULL, 'created', 'App\\Models\\Token', 6, '[]', '{"email":"vikas.verve@gmail.com","token":"boupoflbuslzniewkyxb","user_profile_id":1,"expired_time":1619096410,"id":6}', 'http://localhost/SignableService/public/api/forget_password', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 07:30:10', '2021-04-21 07:30:10'),
(21, NULL, NULL, 'created', 'App\\Models\\Token', 7, '[]', '{"email":"vikas.verve@gmail.com","token":"bsfxpralqxszgkmljkii","user_profile_id":1,"expired_time":1619096501,"id":7}', 'http://localhost/SignableService/public/api/forget_password', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 07:31:41', '2021-04-21 07:31:41'),
(22, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 15, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":15}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 07:39:04', '2021-04-21 07:39:04'),
(23, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 16, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":16}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 07:40:11', '2021-04-21 07:40:11'),
(24, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 17, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":17}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 23:34:10', '2021-04-21 23:34:10'),
(25, 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{"password":"$2y$12$lOhWQ..AePuMmflenI1YZuuH8A1OE2cOkTQC1xZHEuKl6\\/Me\\/PZFa"}', '{"password":"$2y$10$PfaMMoRebT1yTss.SKXnG.F0Isi31oDR9VK9SQqeIZr95aPpK2N3S"}', 'http://localhost/SignableService/public/api/change_password', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 23:37:27', '2021-04-21 23:37:27'),
(26, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 18, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":18}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-21 23:38:05', '2021-04-21 23:38:05'),
(27, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 19, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":19}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 00:58:44', '2021-04-22 00:58:44'),
(28, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 20, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":20}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 00:59:29', '2021-04-22 00:59:29'),
(29, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 21, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":21}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 01:00:32', '2021-04-22 01:00:32'),
(30, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 22, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":22}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 01:00:57', '2021-04-22 01:00:57'),
(31, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 23, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":23}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 01:03:54', '2021-04-22 01:03:54'),
(32, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 24, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":24}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 01:05:17', '2021-04-22 01:05:17'),
(33, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 25, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":25}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 01:07:15', '2021-04-22 01:07:15'),
(34, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 26, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":26}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 01:22:15', '2021-04-22 01:22:15'),
(35, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 27, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":27}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 01:58:35', '2021-04-22 01:58:35'),
(36, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 28, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":28}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 02:13:34', '2021-04-22 02:13:34'),
(37, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 29, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":29}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 03:01:04', '2021-04-22 03:01:04'),
(38, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 30, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":30}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 03:05:16', '2021-04-22 03:05:16'),
(39, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 31, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":31}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 03:05:51', '2021-04-22 03:05:51'),
(40, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 32, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":32}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 03:06:40', '2021-04-22 03:06:40'),
(41, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 33, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":33}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 03:07:42', '2021-04-22 03:07:42'),
(42, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 34, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":34}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 03:07:59', '2021-04-22 03:07:59'),
(43, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 35, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":35}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 03:08:45', '2021-04-22 03:08:45'),
(44, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 36, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":36}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 03:14:59', '2021-04-22 03:14:59'),
(45, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 37, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":37}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 03:16:40', '2021-04-22 03:16:40'),
(46, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 38, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":38}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 03:17:24', '2021-04-22 03:17:24'),
(47, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 39, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":39}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 03:18:46', '2021-04-22 03:18:46'),
(48, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 40, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":40}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 03:19:13', '2021-04-22 03:19:13'),
(49, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 41, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":41}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 03:19:51', '2021-04-22 03:19:51'),
(50, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 42, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":42}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 03:20:17', '2021-04-22 03:20:17'),
(51, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 43, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":43}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 03:20:36', '2021-04-22 03:20:36'),
(52, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 44, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":44}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 03:23:10', '2021-04-22 03:23:10'),
(53, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 45, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":45}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 04:05:52', '2021-04-22 04:05:52'),
(54, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 46, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":46}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 04:52:33', '2021-04-22 04:52:33'),
(55, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 47, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":47}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 05:05:17', '2021-04-22 05:05:17'),
(56, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 48, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":48}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 05:06:09', '2021-04-22 05:06:09'),
(57, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 49, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":49}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 05:07:36', '2021-04-22 05:07:36'),
(58, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 50, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":50}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 05:08:18', '2021-04-22 05:08:18'),
(59, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 51, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":51}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 05:11:03', '2021-04-22 05:11:03'),
(60, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 52, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":52}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 05:12:58', '2021-04-22 05:12:58'),
(61, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 53, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":53}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 05:23:17', '2021-04-22 05:23:17'),
(62, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 54, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":54}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 05:31:08', '2021-04-22 05:31:08'),
(63, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 55, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":55}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 05:32:02', '2021-04-22 05:32:02'),
(64, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 56, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":56}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 05:55:10', '2021-04-22 05:55:10'),
(65, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 57, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":57}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 05:55:48', '2021-04-22 05:55:48'),
(66, 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{"email":"vikas.verve@gmail.com","phone":"8320308312"}', '{"email":"rudraa8600@gmail.com","phone":"8780165446"}', 'http://localhost/SignableService/public/api/update_profile', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 06:43:58', '2021-04-22 06:43:58'),
(67, 'App\\Models\\User', 1, 'updated', 'App\\Models\\UserProfile', 1, '{"first_name":"Vikas","last_name":"Jain","profile_photo":null,"date_of_join":"2000-04-12"}', '{"first_name":"Rudresh","last_name":"Dave","profile_photo":"uploads\\/users\\/zsds_1619094298.jpg","date_of_join":"2020-06-15"}', 'http://localhost/SignableService/public/api/update_profile', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 06:54:58', '2021-04-22 06:54:58'),
(68, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 58, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":58}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 06:58:37', '2021-04-22 06:58:37'),
(69, 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{"email":"vikas.verve@gmail.com"}', '{"email":"rudraa8600@gmail.com"}', 'http://localhost/SignableService/public/api/update_profile', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 06:59:17', '2021-04-22 06:59:17'),
(70, 'App\\Models\\User', 1, 'updated', 'App\\Models\\UserProfile', 1, '{"profile_photo":"uploads\\/users\\/zsds_1619094298.jpg"}', '{"profile_photo":"uploads\\/users\\/mmqe_1619094557.jpg"}', 'http://localhost/SignableService/public/api/update_profile', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 06:59:17', '2021-04-22 06:59:17'),
(71, 'App\\Models\\User', 1, 'updated', 'App\\Models\\UserProfile', 1, '{"profile_photo":"uploads\\/users\\/mmqe_1619094557.jpg"}', '{"profile_photo":"uploads\\/users\\/xdox_1619094602.jpg"}', 'http://localhost/SignableService/public/api/update_profile', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 07:00:02', '2021-04-22 07:00:02'),
(72, 'App\\Models\\User', 1, 'updated', 'App\\Models\\UserProfile', 1, '{"profile_photo":"uploads\\/users\\/xdox_1619094602.jpg"}', '{"profile_photo":"uploads\\/users\\/kian_1619095399.jpg"}', 'http://localhost/SignableService/public/api/update_profile', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 07:13:19', '2021-04-22 07:13:19'),
(73, 'App\\Models\\User', 1, 'updated', 'App\\Models\\UserProfile', 1, '{"profile_photo":"uploads\\/users\\/kian_1619095399.jpg"}', '{"profile_photo":"uploads\\/users\\/lvkb_1619095784.jpg"}', 'http://localhost/SignableService/public/api/update_profile', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 07:19:44', '2021-04-22 07:19:44'),
(74, 'App\\Models\\User', 1, 'updated', 'App\\Models\\UserProfile', 1, '{"profile_photo":"uploads\\/users\\/lvkb_1619095784.jpg"}', '{"profile_photo":"uploads\\/users\\/mdtl_1619095818.jpg"}', 'http://localhost/SignableService/public/api/update_profile', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 07:20:18', '2021-04-22 07:20:18'),
(75, 'App\\Models\\User', 1, 'updated', 'App\\Models\\UserProfile', 1, '{"profile_photo":"uploads\\/users\\/mdtl_1619095818.jpg"}', '{"profile_photo":"uploads\\/users\\/smne_1619095838.jpg"}', 'http://localhost/SignableService/public/api/update_profile', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 07:20:38', '2021-04-22 07:20:38'),
(76, 'App\\Models\\User', 1, 'updated', 'App\\Models\\Location', 1, '{"city":8,"miles":"6","region":"5","site":"zansi"}', '{"city":"1","miles":"","region":"regs","site":"database"}', 'http://localhost/SignableService/public/api/update_profile', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 07:20:38', '2021-04-22 07:20:38'),
(77, 'App\\Models\\User', 2, 'created', 'App\\Models\\LoginHistory', 59, '[]', '{"user_profile_id":2,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":59}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 07:27:06', '2021-04-22 07:27:06'),
(78, 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{"password":"$2y$12$XcVbzOEJZDUwIZPJF31r7OebAPi0Ym58tvXTfhGnjyFLr.4gU2txq"}', '{"password":"$2y$10$DttqRO5ZlshXLvfzveB4Yu0YOcQiz8XXVFv8tthernBwBPT.uZw9m"}', 'http://localhost/SignableService/public/api/change_password', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 07:28:16', '2021-04-22 07:28:16'),
(79, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 60, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":60}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 07:29:00', '2021-04-22 07:29:00'),
(80, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 61, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":61}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 07:30:08', '2021-04-22 07:30:08'),
(81, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 62, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":62}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 07:30:41', '2021-04-22 07:30:41'),
(82, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 63, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":63}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 07:31:01', '2021-04-22 07:31:01'),
(83, 'App\\Models\\User', 1, 'created', 'App\\Models\\LoginHistory', 64, '[]', '{"user_profile_id":1,"activity":"login","ip_address":"::1","browser":"PostmanRuntime\\/7.26.8","id":64}', 'http://localhost/SignableService/public/api/login', '::1', 'PostmanRuntime/7.26.8', NULL, '2021-04-22 07:31:09', '2021-04-22 07:31:09');

-- --------------------------------------------------------

--
-- Table structure for table `calls`
--

CREATE TABLE `calls` (
  `id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `qb_call_id` varchar(255) NOT NULL,
  `from_user_profile_id` int(11) NOT NULL,
  `to_user_profile_id` int(11) NOT NULL,
  `ip_found_date_time` varchar(255) NOT NULL,
  `call_start_time` varchar(255) NOT NULL,
  `call_end_time` varchar(255) NOT NULL,
  `call_picked_start_time` varchar(255) NOT NULL,
  `is_call_failed` int(11) NOT NULL DEFAULT '0',
  `qb_data` text NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `call_initiator`
--

CREATE TABLE `call_initiator` (
  `id` int(11) NOT NULL,
  `call_id` int(11) NOT NULL,
  `user_profile_id` int(11) NOT NULL,
  `bandwidth` varchar(255) NOT NULL,
  `resolution` varchar(255) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `call_receiver`
--

CREATE TABLE `call_receiver` (
  `id` int(11) NOT NULL,
  `call_id` int(11) NOT NULL,
  `user_profile_id` int(11) NOT NULL,
  `bandwidth` varchar(255) NOT NULL,
  `resolution` varchar(255) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `country_code` varchar(255) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `name`, `country`, `country_code`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Delhi', 'India', 'IN', NULL, '2021-04-20 11:57:37', '2021-04-20 11:57:37'),
(2, 'Mumbai', 'India', 'IN', NULL, '2021-04-20 11:58:32', '2021-04-20 11:58:32'),
(3, 'Kolkata', 'India', 'IN', NULL, '2021-04-20 11:58:32', '2021-04-20 11:58:32'),
(4, 'Bangalore', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-20 11:58:33'),
(5, 'Chennai', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-20 11:58:33'),
(6, 'Hyderabad', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-21 05:34:53'),
(7, 'Pune', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-20 11:58:33'),
(8, 'Ahmadabad', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-21 05:35:04'),
(9, 'Surat', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-21 05:35:09'),
(10, 'Lucknow', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-20 11:58:33'),
(11, 'Jaipur', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-20 11:58:33'),
(12, 'Cawnpore', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-20 11:58:33'),
(13, 'Mirzapur', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-21 05:35:16'),
(14, 'Nagpur', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-21 05:35:22'),
(15, 'Ghaziabad', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-21 05:35:34'),
(16, 'Indore', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-20 11:58:33'),
(17, 'Vadodara', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-20 11:58:33'),
(18, 'Vishakhapatnam', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-21 05:35:38'),
(19, 'Bhopal', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-21 05:35:43'),
(20, 'Chinchvad', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-20 11:58:33'),
(21, 'Patna', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-20 11:58:33'),
(22, 'Ludhiana', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-21 05:35:49'),
(23, 'Agra', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-21 05:35:55'),
(24, 'Kalyan', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-21 05:36:01'),
(25, 'Madurai', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-20 11:58:33'),
(26, 'Jamshedpur', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-20 11:58:33'),
(27, 'Nasik', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-21 05:36:06'),
(28, 'Faridabad', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-21 05:36:18'),
(29, 'Aurangabad', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-21 05:36:26'),
(30, 'Rajkot', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-21 05:36:30'),
(31, 'Meerut', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-20 11:58:33'),
(32, 'Jabalpur', 'India', 'IN', NULL, '2021-04-20 11:58:33', '2021-04-20 11:58:33'),
(33, 'Thane', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-21 05:36:38'),
(34, 'Dhanbad', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-21 05:36:41'),
(35, 'Allahabad', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-21 05:37:37'),
(36, 'Varanasi', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-21 05:37:45'),
(37, 'Srinagar', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-21 05:37:52'),
(38, 'Amritsar', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-20 11:58:34'),
(39, 'Aligarh', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-21 05:38:54'),
(40, 'Bhiwandi', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-20 11:58:34'),
(41, 'Gwalior', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-20 11:58:34'),
(42, 'Bhilai', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-20 11:58:34'),
(43, 'Howrah', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-21 05:40:38'),
(44, 'Ranchi', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-21 05:39:07'),
(45, 'Bezwada', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-21 05:40:59'),
(46, 'Chandigarh', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-21 05:39:11'),
(47, 'Mysore', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-20 11:58:34'),
(48, 'Raipur', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-20 11:58:34'),
(49, 'Kota', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-20 11:58:34'),
(50, 'Bareilly', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-20 11:58:34'),
(51, 'Jodhpur', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-20 11:58:34'),
(52, 'Coimbatore', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-20 11:58:34'),
(53, 'Dispur', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-20 11:58:34'),
(54, 'Guw??h??ti', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-20 11:58:34'),
(55, 'Sol??pur', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-20 11:58:34'),
(56, 'Trichinopoly', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-20 11:58:34'),
(57, 'Hubli', 'India', 'IN', NULL, '2021-04-20 11:58:34', '2021-04-20 11:58:34'),
(58, 'Jalandhar', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(59, 'Bhubaneshwar', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(60, 'Bhayandar', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(61, 'Mor??d??b??d', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(62, 'Kolh??pur', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(63, 'Thiruvananthapuram', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(64, 'Sah??ranpur', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(65, 'Warangal', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(66, 'Salem', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(67, 'M??legaon', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(68, 'Kochi', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(69, 'Gorakhpur', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(70, 'Shimoga', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(71, 'Tirupp??r', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(72, 'Gunt??r', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(73, 'Raurkela', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(74, 'Mangalore', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(75, 'N??nded', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(76, 'Cuttack', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(77, 'Ch??nda', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(78, 'Dehra D??n', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(79, 'Durg??pur', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(80, '??sansol', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(81, 'Bh??vnagar', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(82, 'Amr??vati', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(83, 'Nellore', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(84, 'Ajmer', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(85, 'Tinnevelly', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(86, 'B??kaner', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(87, 'Agartala', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(88, 'Ujjain', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(89, 'Jh??nsi', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(90, 'Ulh??snagar', 'India', 'IN', NULL, '2021-04-20 11:58:35', '2021-04-20 11:58:35'),
(91, 'Davangere', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(92, 'Jammu', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(93, 'Belgaum', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(94, 'Gulbarga', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(95, 'J??mnagar', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(96, 'Dh??lia', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(97, 'Gaya', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(98, 'Jalgaon', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(99, 'Kurnool', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(100, 'Udaipur', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(101, 'Bellary', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(102, 'S??ngli', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(103, 'Tuticorin', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(104, 'Calicut', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(105, 'Akola', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(106, 'Bh??galpur', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(107, 'S??kar', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(108, 'Tumk??r', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(109, 'Quilon', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(110, 'Muzaffarnagar', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(111, 'Bh??lw??ra', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(112, 'Nizamabad', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-21 05:41:23'),
(113, 'Bhatapara', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-21 05:54:07'),
(114, 'Kakinada', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-21 05:56:04'),
(115, 'Parbhani', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(116, 'P??nih??ti', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(117, 'Latur', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-21 05:58:40'),
(118, 'Rohtak', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(119, 'R??jap??laiyam', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(120, 'Ahmadnagar', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(121, 'Cuddapah', 'India', 'IN', NULL, '2021-04-20 11:58:36', '2021-04-20 11:58:36'),
(122, 'R??jahmundry', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(123, 'Alwar', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(124, 'Muzaffarpur', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(125, 'Bilaspur', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-21 06:14:28'),
(126, 'Mathura', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(127, 'Kamarhati', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-21 06:14:41'),
(128, 'Patiala', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-21 06:14:46'),
(129, 'Saugor', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(130, 'Bijapur', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-21 06:03:59'),
(131, 'Brahmapur', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(132, 'Shahjanpur', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-21 06:15:34'),
(133, 'Trichar', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-21 06:15:37'),
(134, 'Barddhaman', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-21 06:15:43'),
(135, 'Kulti', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(136, 'Sambalpur', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(137, 'Purnea', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(138, 'Hisar', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(139, 'Firozabad', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-21 06:14:10'),
(140, 'B??dar', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(141, 'Rampur', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-21 06:13:52'),
(142, 'Shiliguri', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(143, 'B??li', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(144, 'Panipat', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-21 06:13:33'),
(145, 'Kar??mnagar', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(146, 'Bhuj', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(147, 'Ichalkaranji', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(148, 'Tirupati', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(149, 'Hospet', 'India', 'IN', NULL, '2021-04-20 11:58:37', '2021-04-20 11:58:37'),
(150, '???zawl', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(151, 'Sannai', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(152, 'B??r??sat', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(153, 'Ratlam', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-21 06:11:37'),
(154, 'Handwara', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-21 06:11:32'),
(155, 'Drug', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(156, 'Imphal', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-21 06:11:24'),
(157, 'Anantapur', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(158, 'Etawah', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-21 06:11:19'),
(159, 'Raichur', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-21 06:11:15'),
(160, 'Ongole', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(161, 'Bharatpur', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(162, 'Begusarai', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(163, 'Sonipat', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-21 06:10:45'),
(164, 'Ramgundam', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-21 06:10:24'),
(165, 'Hapur', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-21 06:10:17'),
(166, 'Uluberiya', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(167, 'Porbandar', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(168, 'Pali', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-21 06:06:03'),
(169, 'Vizianagaram', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(170, 'Puducherry', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(171, 'Karnal', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-21 06:09:53'),
(172, 'Nagercoil', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-21 06:09:35'),
(173, 'Tanjore', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(174, 'Sambhal', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(175, 'Shimla', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(176, 'Ghandinagar', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-21 06:04:29'),
(177, 'Shillong', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(178, 'New Delhi', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(179, 'Port Blair', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(180, 'Gangtok', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-20 11:58:38'),
(181, 'Kohima', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-21 06:08:35'),
(182, 'Itanagar', 'India', 'IN', NULL, '2021-04-20 11:58:38', '2021-04-21 06:05:13'),
(183, 'Panaji', 'India', 'IN', NULL, '2021-04-20 11:58:39', '2021-04-20 11:58:39'),
(184, 'Daman', 'India', 'IN', NULL, '2021-04-20 11:58:39', '2021-04-21 06:04:42'),
(185, 'Kavaratti', 'India', 'IN', NULL, '2021-04-20 11:58:39', '2021-04-20 11:58:39'),
(186, 'Panchkula', 'India', 'IN', NULL, '2021-04-20 11:58:39', '2021-04-20 11:58:39'),
(187, 'Kagaznagar', 'India', 'IN', NULL, '2021-04-20 11:58:39', '2021-04-21 06:09:02');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_type` tinyint(1) NOT NULL COMMENT '1-Suppliers,2-Consumers',
  `con_name` varchar(255) NOT NULL,
  `con_email` varchar(255) NOT NULL,
  `company_address1` varchar(255) NOT NULL,
  `company_address2` varchar(225) NOT NULL,
  `company_city` varchar(225) NOT NULL,
  `company_state` varchar(225) NOT NULL,
  `company_country` varchar(225) NOT NULL,
  `company_zipcode` varchar(225) NOT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `company_name`, `company_type`, `con_name`, `con_email`, `company_address1`, `company_address2`, `company_city`, `company_state`, `company_country`, `company_zipcode`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'SIGNABLE PVT LTD', 1, 'VIKAS JAIN', 'vikas.verve@gmail.com', 'IKP Knowledge Park Ground Floor', 'Jalahalli Metro Station,', 'Bengaluru', 'Karnataka', 'INDIA', '560057', 1, 1, NULL, '2021-04-21 05:11:41', '2021-04-21 05:11:41'),
(2, 'AMAZON INDIA', 2, 'ANKIT BOKARE', 'ankitb.verve@gmail.com', 'Brigade Gateway, 8th floor, 26/1', 'Dr. Rajkumar Road, Malleshwaram(W)', 'Bangalore', 'Karnataka', 'INDIA', '560055', 1, 1, NULL, '2021-04-21 05:16:49', '2021-04-21 05:16:49');

-- --------------------------------------------------------

--
-- Table structure for table `dispositions`
--

CREATE TABLE `dispositions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1-Suppliers,2-Consumers',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `dispositions`
--

INSERT INTO `dispositions` (`id`, `name`, `description`, `type`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Business Process', 'Business Process', 2, NULL, '2021-04-21 07:22:18', '2021-04-21 07:22:18'),
(2, 'Waiting Time', 'Waiting Time', 2, NULL, '2021-04-21 07:22:18', '2021-04-21 07:22:18'),
(3, 'Professionalism', 'Professionalism', 2, NULL, '2021-04-21 07:22:18', '2021-04-21 07:22:18'),
(4, 'Language Improvement', 'Language Improvement', 2, NULL, '2021-04-21 07:22:18', '2021-04-21 07:22:18'),
(5, 'Availability', 'Availability', 2, NULL, '2021-04-21 07:22:18', '2021-04-21 07:22:18'),
(6, 'Courteous', 'Courteous', 2, NULL, '2021-04-21 07:22:18', '2021-04-21 07:22:18');

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL,
  `template_title` varchar(255) NOT NULL,
  `template_key` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(11) UNSIGNED NOT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `template_title`, `template_key`, `is_active`, `is_deleted`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'Forgot Password', 'forget_password', 1, 0, 1, 1, '2020-02-13 18:57:41', '2020-02-13 13:27:41');

-- --------------------------------------------------------

--
-- Table structure for table `email_template_contents`
--

CREATE TABLE `email_template_contents` (
  `id` int(11) NOT NULL COMMENT 'primary key auto increment',
  `email_template_id` int(11) NOT NULL COMMENT 'belongs to email template id primary key',
  `language_id` int(11) DEFAULT NULL,
  `email_subject` text NOT NULL,
  `email_body` text NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `email_template_contents`
--

INSERT INTO `email_template_contents` (`id`, `email_template_id`, `language_id`, `email_subject`, `email_body`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Reset Password', '&#10;    &#10;    &#10;    &#10;    &#10;    Check Rent | Property Management Software&#10;  &#10;&#10;&#10;    <table width="100%" cellspacing="0" cellpadding="0" border="0" align="center">&#10;      <tbody>&#10;        <tr>&#10;          <td>&#10;            <table cellspacing="0" cellpadding="0" border="0" align="center">&#10;              &#10;              <tbody>&#10;                <tr>&#10;                  <td>&#10;                    <table cellspacing="0" cellpadding="0" border="0">&#10;                      <tbody>&#10;                        <tr>&#10;                          <td width="20"></td>&#10;                          <td>&#10;                            <table class="main" width="600" cellspacing="0" cellpadding="0" border="0">&#10;                              <tbody>&#10;                                <tr>&#10;                                  <td>&#10;                                    <table cellspacing="0" cellpadding="0" border="0">&#10;                                      <tbody>&#10;                                        <tr>&#10;                                          <td>&#10;                                            <table class="main" cellspacing="0" cellpadding="0" border="0">&#10;                                              <tbody>&#10;                                                &#10;                                                <tr>&#10;                                                  <td>&#10;                                                    <table cellspacing="0" cellpadding="0" border="0">&#10;                                                      <tbody>&#10;                                                        <tr><td colspan="2">&#160;</td></tr>&#10;                                                        <tr>&#10;                                                          <td width="20"></td>&#10;                                                          <td><a href="{{SITE_URL}}" target="_blank"><img alt="Check Rent" src="{{LOGO}}" width="177"> </a></td>&#10;                                                          <td width="20"></td>&#10;                                                        </tr>&#10;                                                        <tr><td colspan="2">&#160;</td></tr>&#10;                                                      </tbody>&#10;                                                    </table>&#10;                                                  </td>&#10;                                                </tr>&#10;                                                <tr>&#10;                                                  <td height="13"></td>&#10;                                                </tr>&#10;                                                <tr>&#10;                                                  <td>&#10;                                                    <table cellspacing="0" cellpadding="0" border="0">&#10;                                                      <tbody>&#10;                                                        <tr>&#10;                                                          <td>&#10;                                                            <table cellspacing="0" cellpadding="0" border="0">&#10;                                                              <tbody>&#10;                                <tr>&#10;                                                                  <td colspan="3" height="20"></td>&#10;                                                                </tr>&#10;                                <tr align="center">&#10;                                                                  <td colspan="3"><strong align="center">Reset Password</strong></td>&#10;                                                                </tr>&#10;                                                                <tr>&#10;                                                                  <td colspan="3" height="50"></td>&#10;                                                                </tr>&#10;                                                                <tr>&#10;                                                                  <td width="20"></td>&#10;                                                                  <td><strong>Dear {{fullname}},</strong></td>&#10;                                                                  <td width="0"></td>&#10;                                                                </tr>&#10;                                                                <tr>&#10;                                                                  <td colspan="3" height="15"></td>&#10;                                                                </tr>&#10;                                                                <tr>&#10;                                                                  <td width="20"></td>&#10;                                                                  <td>Thanks for contacting Check-Rent for your new password request. In an order to generate a new password, please click the URL below.</td>&#10;                                                                  <td width="20"></td>&#10;                                                                </tr>&#10;                                                                <tr>&#10;                                                                  <td colspan="3" height="60"></td>&#10;                                                                </tr>&#10;                                <tr>&#10;                                                                  <td colspan="3">&#10;                                                                    <table width="144" cellspacing="0" cellpadding="0" border="0" align="center">&#10;                                                                      <tbody><tr>&#10;                                                                        <td width="144" height="36" bgcolor="#173a67">{{forget_password_link}}</td>&#10;                                                                      </tr>&#10;                                                                    </tbody></table>&#10;                                                                  </td>&#10;                                                                </tr>&#10;                                <tr>&#10;                                                                  <td colspan="3" height="20"></td>&#10;                                                                </tr>&#10;                                                                <tr>&#10;                                                                  <td colspan="3">&#10;                                                                    <table>&#10;                                                                      <tbody>&#10;                                                                        <tr>&#10;                                                                          <td width="12"></td>&#10;                                                                          <td>Thanks &amp; kind regards</td>&#10;                                                                          <td width="12"></td>&#10;                                                                        </tr>&#10;                                                                      </tbody>&#10;                                                                    </table>&#10;                                                                  </td>&#10;                                                                </tr>&#10;                                                                <tr>&#10;                                                                  <td colspan="3">&#10;                                                                    <table>&#10;                                                                      <tbody>&#10;                                                                        <tr>&#10;                                                                          <td width="12"></td>&#10;                                                                          <td valign="top">Your Check-Rent Team</td>&#10;                                                                          <td width="12"></td>&#10;                                                                        </tr>&#10;                                                                      </tbody>&#10;                                                                    </table>&#10;                                                                  </td>&#10;                                                                </tr>&#10;                                                                &#10;                                                                &#10;                                                               &#10;                                                              </tbody>&#10;                                                            </table>&#10;                                                          </td>&#10;                                                          <td></td>&#10;                                                        </tr>&#10;                                                      </tbody>&#10;                                                    </table>&#10;                                                  </td>&#10;                                                </tr>&#10;                                                <tr>&#10;                                                  <td height="20"></td>&#10;                                                </tr>&#10;                                              </tbody>&#10;                                            </table>&#10;                                          </td>&#10;                                        </tr>&#10;                                      </tbody>&#10;                                    </table>&#10;                                  </td>&#10;                                </tr>&#10;                              </tbody>&#10;                            </table>&#10;                          </td>&#10;                          <td width="20"></td>&#10;                        </tr>&#10;                      </tbody>&#10;                    </table>&#10;                  </td>&#10;                </tr>&#10;              </tbody>&#10;            </table>&#10;            <table height="16" cellspacing="0" cellpadding="0" border="0" align="center">&#10;              &#10;              <tbody>&#10;                <tr>&#10;                  <td height="16"></td>&#10;                </tr>&#10;              </tbody>&#10;            </table>&#10;            <table cellspacing="0" cellpadding="0" border="0" align="center">&#10;              &#10;              <tbody>&#10;                <tr>&#10;                  <td>&#10;                    <table cellspacing="0" cellpadding="0" border="0">&#10;                      <tbody>&#10;                        <tr>&#10;                          <td width="20"></td>&#10;                          <td>&#10;                            <table class="main" width="600" cellspacing="0" cellpadding="0" border="0">&#10;                              <tbody>&#10;                                <tr>&#10;                                  <td>&#10;                                    <table width="100%;" cellspacing="0" cellpadding="0" border="0">&#10;                                      <tbody>&#10;                                        <tr>&#10;                                          <td>&#10;                                            <table class="main" width="100%" cellspacing="0" cellpadding="0" border="0">&#10;                                              <tbody>&#10;                                                <tr>&#10;                                                  <td>&#10;                                                    <table cellspacing="0" cellpadding="0" border="0">&#10;                                                      <tbody>&#10;                                                        <tr>&#10;                                                          <td height="20"></td>&#10;                                                        </tr>&#10;                                                        <tr>&#10;                                                          <td colspan="3" valign="top">Download the Check-Rent app!</td>&#10;                                                        </tr>&#10;                                                        <tr>&#10;                                                          <td height="20"></td>&#10;                                                        </tr>&#10;                                                        <tr>&#10;                                                          <td colspan="3" valign="top">Make sure you always have the latest version installed.</td>&#10;                                                        </tr>&#10;                                                        <tr>&#10;                                                          <td height="20"></td>&#10;                                                        </tr>&#10;                                                        <tr>&#10;                                                          <td width="20"></td>&#10;                                                          <td>&#10;                                                            <table cellspacing="0" cellpadding="0" border="0">&#10;                                                              <tbody>&#10;                                                                <tr>&#10;                                                                  <td align="right"><a href="{{app_store_link}}" target="_blank"><img alt="App Store" src="{{app_store_logo}}"> </a></td>&#10;                                                                  <td width="20"></td>&#10;                                                                  <td align="left"><a href="{{play_store_link}}" target="_blank"><img alt="Google Play" src="{{play_store_logo}}"> </a></td>&#10;                                                              </tr></tbody>&#10;                                                            </table>&#10;                                                          </td>&#10;                                                          <td width="20"></td>&#10;                                                        </tr>&#10;                                                        <tr>&#10;                                                          <td colspan="3">&#160;</td>&#10;                                                        </tr>&#10;                                                      </tbody>&#10;                                                    </table>&#10;                                                  </td>&#10;                                                </tr>&#10;                                              </tbody>&#10;                                            </table>&#10;                                          </td>&#10;                                        </tr>&#10;                                      </tbody>&#10;                                    </table>&#10;                                  </td>&#10;                                </tr>&#10;                              </tbody>&#10;                            </table>&#10;                          </td>&#10;                          <td width="20"></td>&#10;                        </tr>&#10;                      </tbody>&#10;                    </table>&#10;                  </td>&#10;                </tr>&#10;              </tbody>&#10;            </table>&#10;            <table cellspacing="0" cellpadding="0" border="0" align="center">&#10;              &#10;              <tbody>&#10;                <tr>&#10;                  <td height="10"></td>&#10;                </tr>&#10;                <tr>&#10;                  <td valign="top">&#169; 2020 Check Rent - Property Management Software</td>&#10;                </tr>&#10;                <tr>&#10;                  <td height="10"></td>&#10;                </tr>&#10;              </tbody>&#10;            </table>&#10;          </td>&#10;        </tr>&#10;      </tbody>&#10;    </table>&#10;&#10;', 0, '2020-02-13 18:59:30', '2020-09-10 06:40:14');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'English', 1, '2021-04-22 08:51:52', '2021-04-22 08:51:52', NULL),
(2, 'Hindi', 1, '2021-04-22 08:51:52', '2021-04-22 08:51:52', NULL),
(3, 'Kannada', 1, '2021-04-22 08:51:52', '2021-04-22 08:51:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `user_profile_id` int(11) NOT NULL,
  `city` int(11) NOT NULL,
  `miles` varchar(255) NOT NULL,
  `region` varchar(255) NOT NULL,
  `site` varchar(255) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `user_profile_id`, `city`, `miles`, `region`, `site`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '', 'regs', 'database', NULL, '2021-04-21 06:27:56', '2021-04-22 07:20:38'),
(2, 2, 4, '6', '5', 'Gujarat', NULL, '2021-04-21 06:29:12', '2021-04-21 06:29:12');

-- --------------------------------------------------------

--
-- Table structure for table `login_histories`
--

CREATE TABLE `login_histories` (
  `id` int(11) NOT NULL,
  `user_profile_id` int(11) NOT NULL,
  `activity` enum('login','logout') NOT NULL,
  `origin` varchar(10) NOT NULL DEFAULT 'web' COMMENT 'Mobile/Web/marketing',
  `operating_system` varchar(255) DEFAULT NULL,
  `browser` varchar(255) DEFAULT NULL,
  `device_type` varchar(255) DEFAULT NULL COMMENT 'Android/IPHONE/Tablet',
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `login_histories`
--

INSERT INTO `login_histories` (`id`, `user_profile_id`, `activity`, `origin`, `operating_system`, `browser`, `device_type`, `ip_address`, `created_at`, `updated_at`) VALUES
(1, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-21 07:55:13', '2021-04-21 02:25:13'),
(2, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-21 07:56:27', '2021-04-21 02:26:27'),
(3, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-21 08:30:06', '2021-04-21 03:00:06'),
(4, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-21 08:37:41', '2021-04-21 03:07:41'),
(5, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-21 08:38:11', '2021-04-21 03:08:11'),
(6, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-21 08:38:38', '2021-04-21 03:08:38'),
(7, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-21 08:39:04', '2021-04-21 03:09:04'),
(8, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-21 08:39:29', '2021-04-21 03:09:29'),
(9, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-21 08:39:46', '2021-04-21 03:09:46'),
(10, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-21 08:40:18', '2021-04-21 03:10:18'),
(11, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-21 08:43:27', '2021-04-21 03:13:27'),
(12, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-21 08:45:46', '2021-04-21 03:15:46'),
(13, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-21 10:31:34', '2021-04-21 05:01:34'),
(14, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-21 10:45:26', '2021-04-21 05:15:26'),
(15, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-21 13:09:04', '2021-04-21 07:39:04'),
(16, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-21 13:10:11', '2021-04-21 07:40:11'),
(17, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 05:04:10', '2021-04-21 23:34:10'),
(18, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 05:08:04', '2021-04-21 23:38:04'),
(19, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 06:28:44', '2021-04-22 00:58:44'),
(20, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 06:29:29', '2021-04-22 00:59:29'),
(21, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 06:30:32', '2021-04-22 01:00:32'),
(22, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 06:30:57', '2021-04-22 01:00:57'),
(23, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 06:33:54', '2021-04-22 01:03:54'),
(24, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 06:35:17', '2021-04-22 01:05:17'),
(25, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 06:37:15', '2021-04-22 01:07:15'),
(26, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 06:52:15', '2021-04-22 01:22:15'),
(27, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 07:28:35', '2021-04-22 01:58:35'),
(28, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 07:43:34', '2021-04-22 02:13:34'),
(29, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 08:31:04', '2021-04-22 03:01:04'),
(30, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 08:35:16', '2021-04-22 03:05:16'),
(31, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 08:35:51', '2021-04-22 03:05:51'),
(32, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 08:36:40', '2021-04-22 03:06:40'),
(33, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 08:37:42', '2021-04-22 03:07:42'),
(34, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 08:37:59', '2021-04-22 03:07:59'),
(35, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 08:38:45', '2021-04-22 03:08:45'),
(36, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 08:44:58', '2021-04-22 03:14:58'),
(37, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 08:46:40', '2021-04-22 03:16:40'),
(38, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 08:47:24', '2021-04-22 03:17:24'),
(39, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 08:48:46', '2021-04-22 03:18:46'),
(40, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 08:49:13', '2021-04-22 03:19:13'),
(41, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 08:49:51', '2021-04-22 03:19:51'),
(42, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 08:50:17', '2021-04-22 03:20:17'),
(43, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 08:50:36', '2021-04-22 03:20:36'),
(44, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 08:53:10', '2021-04-22 03:23:10'),
(45, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 09:35:52', '2021-04-22 04:05:52'),
(46, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 10:22:33', '2021-04-22 04:52:33'),
(47, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 10:35:17', '2021-04-22 05:05:17'),
(48, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 10:36:09', '2021-04-22 05:06:09'),
(49, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 10:37:36', '2021-04-22 05:07:36'),
(50, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 10:38:17', '2021-04-22 05:08:17'),
(51, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 10:41:03', '2021-04-22 05:11:03'),
(52, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 10:42:58', '2021-04-22 05:12:58'),
(53, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 10:53:17', '2021-04-22 05:23:17'),
(54, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 11:01:08', '2021-04-22 05:31:08'),
(55, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 11:02:01', '2021-04-22 05:32:01'),
(56, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 11:25:09', '2021-04-22 05:55:09'),
(57, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 11:25:48', '2021-04-22 05:55:48'),
(58, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 12:28:37', '2021-04-22 06:58:37'),
(59, 2, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 12:57:06', '2021-04-22 07:27:06'),
(60, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 12:59:00', '2021-04-22 07:29:00'),
(61, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 13:00:08', '2021-04-22 07:30:08'),
(62, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 13:00:40', '2021-04-22 07:30:40'),
(63, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 13:01:01', '2021-04-22 07:31:01'),
(64, 1, 'login', 'web', NULL, 'PostmanRuntime/7.26.8', NULL, '::1', '2021-04-22 13:01:09', '2021-04-22 07:31:09');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `miles`
--

CREATE TABLE `miles` (
  `id` int(11) NOT NULL,
  `name` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permission_role`
--

CREATE TABLE `permission_role` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purposes`
--

CREATE TABLE `purposes` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `purposes`
--

INSERT INTO `purposes` (`id`, `name`, `description`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Human Resources (HR)', 'Human Resources (HR)', NULL, '2021-04-21 06:56:51', '2021-04-21 06:56:51'),
(2, 'Behaviour', 'Behaviour', NULL, '2021-04-21 06:56:51', '2021-04-21 06:56:51'),
(3, 'Attendance', 'Attendance', NULL, '2021-04-21 06:56:51', '2021-04-21 06:56:51'),
(4, 'Attitude', 'Attitude', NULL, '2021-04-21 06:56:51', '2021-04-21 06:56:51'),
(5, 'Carelessness', 'Carelessness', NULL, '2021-04-21 06:56:51', '2021-04-21 06:56:51'),
(6, 'Process Related', 'Process Related', NULL, '2021-04-21 06:56:51', '2021-04-21 06:56:51'),
(7, 'others', 'others', NULL, '2021-04-21 06:56:51', '2021-04-21 06:56:51');

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `id` int(11) NOT NULL,
  `name` int(255) NOT NULL,
  `value` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'Super Admin', 'Super Admin handle the system.', NULL, '2021-04-12 11:53:46', '2021-04-12 11:53:46'),
(2, 'supplier_admin', 'Supplier Admin', 'Supplier Admin', NULL, '2021-04-12 11:53:46', '2021-04-12 11:53:46'),
(3, 'supplier_supervisor', 'Supplier Supervisor', 'Supplier Supervisor', NULL, '2021-04-12 11:53:46', '2021-04-12 11:53:46'),
(4, 'supplier_employee', 'Supplier Employee', 'Supplier Employee', NULL, '2021-04-12 11:53:46', '2021-04-12 11:53:46'),
(5, 'company_admin', 'Company Admin', 'company_admin', NULL, '2021-04-12 11:53:46', '2021-04-13 10:09:47'),
(6, 'company_supervisor', 'Company Supervisor', 'Company Supervisor', NULL, '2021-04-12 11:53:46', '2021-04-13 10:09:53'),
(7, 'company_employee', 'Company Employee', 'Company Employee', NULL, '2021-04-12 11:53:46', '2021-04-13 10:09:59');

-- --------------------------------------------------------

--
-- Table structure for table `role_locations`
--

CREATE TABLE `role_locations` (
  `id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_locations`
--

INSERT INTO `role_locations` (`id`, `location_id`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2021-04-21 06:30:39', '2021-04-21 06:30:39'),
(2, 2, 5, '2021-04-21 06:30:39', '2021-04-21 06:30:39');

-- --------------------------------------------------------

--
-- Table structure for table `role_users`
--

CREATE TABLE `role_users` (
  `id` int(11) NOT NULL,
  `user_profile_id` int(11) NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_users`
--

INSERT INTO `role_users` (`id`, `user_profile_id`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2021-04-21 06:30:39', '2021-04-21 06:30:39'),
(2, 2, 5, '2021-04-21 06:30:39', '2021-04-21 06:30:39');

-- --------------------------------------------------------

--
-- Table structure for table `tokens`
--

CREATE TABLE `tokens` (
  `id` int(11) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `user_profile_id` int(11) DEFAULT NULL,
  `email` text NOT NULL,
  `token` text NOT NULL,
  `expired_time` varchar(100) DEFAULT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Used to store all token records';

--
-- Dumping data for table `tokens`
--

INSERT INTO `tokens` (`id`, `type`, `user_profile_id`, `email`, `token`, `expired_time`, `is_used`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'vikas.verve@gmail.com', 'kxxoiinedfgtpwboimrx', '1619082197', 0, '2021-04-21 03:33:17', '2021-04-21 12:57:43'),
(2, NULL, NULL, 'vikas.verve@gmail.com', 'tyrpyzyrztfzaqaufdnv', '1619082239', 0, '2021-04-21 03:33:59', '2021-04-21 03:33:59'),
(3, NULL, NULL, 'rudresh.verve@gmail.com', 'blbvwxxfhxtncojdqjjd', '1619082347', 0, '2021-04-21 03:35:47', '2021-04-21 03:35:47'),
(4, NULL, NULL, 'vikas.verve@gmail.com', 'dbtngmhqrmttkpyquifu', '1619096339', 0, '2021-04-21 07:28:59', '2021-04-21 07:28:59'),
(5, NULL, NULL, 'vikas.verve@gmail.com', 'wixivmitymorerprxlae', '1619096394', 0, '2021-04-21 07:29:54', '2021-04-21 07:29:54'),
(6, NULL, 1, 'vikas.verve@gmail.com', 'boupoflbuslzniewkyxb', '1619096410', 0, '2021-04-21 07:30:10', '2021-04-21 07:30:10'),
(7, NULL, 1, 'vikas.verve@gmail.com', 'bsfxpralqxszgkmljkii', '1619096501', 0, '2021-04-21 07:31:41', '2021-04-21 07:31:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` text COLLATE utf8mb4_unicode_ci,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `login_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-simple,2-SSO,3-Social',
  `authorization_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qb_authorization` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qb_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qb_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL,
  `is_verified` tinyint(4) NOT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `is_deleted` tinyint(4) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `phone`, `login_type`, `authorization_key`, `social_type`, `qb_authorization`, `qb_id`, `qb_password`, `is_active`, `is_verified`, `created_by`, `updated_by`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'vikas.verve@gmail.com', '$2y$10$DttqRO5ZlshXLvfzveB4Yu0YOcQiz8XXVFv8tthernBwBPT.uZw9m', '8780165446', 1, NULL, NULL, NULL, 'nikulverve', 'nikulverve', 1, 1, 1, 1, 0, '2021-04-21 05:27:54', '2021-04-22 12:58:43'),
(2, 'ankitb.verve@gmail.com', '$2y$12$XcVbzOEJZDUwIZPJF31r7OebAPi0Ym58tvXTfhGnjyFLr.4gU2txq', '9512902033', 1, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, '2021-04-21 06:24:38', '2021-04-21 06:24:38');

-- --------------------------------------------------------

--
-- Table structure for table `user_languages`
--

CREATE TABLE `user_languages` (
  `id` int(11) NOT NULL,
  `user_profile_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `ranking` int(11) NOT NULL COMMENT '1-fully,2-mediator,3-low',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_languages`
--

INSERT INTO `user_languages` (`id`, `user_profile_id`, `language_id`, `ranking`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2021-04-22 07:00:02', '2021-04-22 08:52:40', '2021-04-22 07:00:02'),
(2, 1, 2, 1, '2021-04-22 07:00:02', '2021-04-22 08:52:40', '2021-04-22 07:00:02'),
(3, 2, 1, 1, NULL, '2021-04-22 08:53:02', '2021-04-22 08:53:02'),
(4, 2, 2, 1, NULL, '2021-04-22 08:53:02', '2021-04-22 08:53:02');

-- --------------------------------------------------------

--
-- Table structure for table `user_profies`
--

CREATE TABLE `user_profies` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `company_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `gender` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1-male,2-female',
  `date_of_join` varchar(255) NOT NULL,
  `date_of_birth` varchar(255) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_profies`
--

INSERT INTO `user_profies` (`id`, `user_id`, `company_id`, `first_name`, `last_name`, `profile_photo`, `gender`, `date_of_join`, `date_of_birth`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Rudresh', 'Dave', 'uploads/users/smne_1619095838.jpg', 1, '2020-06-15', '1957-12-12', NULL, '2021-04-21 06:21:47', '2021-04-22 07:20:38'),
(2, 2, 2, 'Ankit', 'Bokare', NULL, 1, '2015-05-21', '1991-04-02', NULL, '2021-04-21 06:26:57', '2021-04-21 06:26:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `actions`
--
ALTER TABLE `actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_profile_id` (`user_profile_id`),
  ADD KEY `location_role_id` (`location_role_id`),
  ADD KEY `language_id` (`language_id`);

--
-- Indexes for table `audits`
--
ALTER TABLE `audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audits_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  ADD KEY `audits_user_id_user_type_index` (`user_id`,`user_type`);

--
-- Indexes for table `calls`
--
ALTER TABLE `calls`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `call_initiator`
--
ALTER TABLE `call_initiator`
  ADD PRIMARY KEY (`id`),
  ADD KEY `call_id` (`call_id`),
  ADD KEY `user_profile_id` (`user_profile_id`);

--
-- Indexes for table `call_receiver`
--
ALTER TABLE `call_receiver`
  ADD PRIMARY KEY (`id`),
  ADD KEY `call_id` (`call_id`),
  ADD KEY `user_profile_id` (`user_profile_id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dispositions`
--
ALTER TABLE `dispositions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `email_template_contents`
--
ALTER TABLE `email_template_contents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_template_id` (`email_template_id`),
  ADD KEY `language_id` (`language_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_profile_id` (`user_profile_id`);

--
-- Indexes for table `login_histories`
--
ALTER TABLE `login_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_profile_id`),
  ADD KEY `user_profile_id` (`user_profile_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`);

--
-- Indexes for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `permission_role_role_id_foreign` (`role_id`);

--
-- Indexes for table `purposes`
--
ALTER TABLE `purposes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indexes for table `role_locations`
--
ALTER TABLE `role_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `user_id` (`location_id`);

--
-- Indexes for table `role_users`
--
ALTER TABLE `role_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `user_id` (`user_profile_id`);

--
-- Indexes for table `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_profile_id` (`user_profile_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_languages`
--
ALTER TABLE `user_languages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `language_id` (`language_id`),
  ADD KEY `user_profile_id` (`user_profile_id`);

--
-- Indexes for table `user_profies`
--
ALTER TABLE `user_profies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `company_id` (`company_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audits`
--
ALTER TABLE `audits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;
--
-- AUTO_INCREMENT for table `calls`
--
ALTER TABLE `calls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `call_initiator`
--
ALTER TABLE `call_initiator`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `call_receiver`
--
ALTER TABLE `call_receiver`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;
--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `dispositions`
--
ALTER TABLE `dispositions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `email_template_contents`
--
ALTER TABLE `email_template_contents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key auto increment', AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `login_histories`
--
ALTER TABLE `login_histories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;
--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `purposes`
--
ALTER TABLE `purposes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `role_locations`
--
ALTER TABLE `role_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `role_users`
--
ALTER TABLE `role_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `tokens`
--
ALTER TABLE `tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `user_languages`
--
ALTER TABLE `user_languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `user_profies`
--
ALTER TABLE `user_profies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `actions`
--
ALTER TABLE `actions`
  ADD CONSTRAINT `actions_ibfk_1` FOREIGN KEY (`user_profile_id`) REFERENCES `user_profies` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `actions_ibfk_2` FOREIGN KEY (`location_role_id`) REFERENCES `role_locations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `actions_ibfk_3` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`);

--
-- Constraints for table `call_initiator`
--
ALTER TABLE `call_initiator`
  ADD CONSTRAINT `call_initiator_ibfk_1` FOREIGN KEY (`call_id`) REFERENCES `calls` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `call_initiator_ibfk_2` FOREIGN KEY (`user_profile_id`) REFERENCES `user_profies` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `call_receiver`
--
ALTER TABLE `call_receiver`
  ADD CONSTRAINT `call_receiver_ibfk_1` FOREIGN KEY (`call_id`) REFERENCES `calls` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `call_receiver_ibfk_2` FOREIGN KEY (`user_profile_id`) REFERENCES `user_profies` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`user_profile_id`) REFERENCES `user_profies` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `login_histories`
--
ALTER TABLE `login_histories`
  ADD CONSTRAINT `login_histories_ibfk_1` FOREIGN KEY (`user_profile_id`) REFERENCES `user_profies` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `role_locations`
--
ALTER TABLE `role_locations`
  ADD CONSTRAINT `role_locations_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `role_locations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`);

--
-- Constraints for table `role_users`
--
ALTER TABLE `role_users`
  ADD CONSTRAINT `role_users_ibfk_1` FOREIGN KEY (`user_profile_id`) REFERENCES `user_profies` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `role_users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `tokens`
--
ALTER TABLE `tokens`
  ADD CONSTRAINT `tokens_ibfk_1` FOREIGN KEY (`user_profile_id`) REFERENCES `user_profies` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `user_languages`
--
ALTER TABLE `user_languages`
  ADD CONSTRAINT `user_languages_ibfk_1` FOREIGN KEY (`user_profile_id`) REFERENCES `user_profies` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `user_languages_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `user_profies`
--
ALTER TABLE `user_profies`
  ADD CONSTRAINT `user_profies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `user_profies_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
