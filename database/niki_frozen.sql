-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2026 at 09:54 AM
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
-- Database: `niki_frozen`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `address`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'Cabang Utama', 'Jl. Ring Road Utara, Ngringin, Condongcatur, Kec. Depok, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55281', '081234567890', '2026-06-10 22:53:26', '2026-06-12 21:38:00'),
(2, 'Cabang Kedua', 'Jl. Wonosari KM 17, Patuk, Kec. Patuk, Kabupaten Gunungkidul, Daerah Istimewa Yogyakarta 55862', '081234567891', '2026-06-10 22:53:26', '2026-06-12 21:36:36');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financial_reports`
--

CREATE TABLE `financial_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `total_revenue` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_expense` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net_profit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_transactions` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `financial_reports`
--

INSERT INTO `financial_reports` (`id`, `branch_id`, `date`, `total_revenue`, `total_expense`, `net_profit`, `total_transactions`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-07-01', 142000.00, 0.00, 142000.00, 1, '2026-07-01 07:25:04', '2026-07-01 07:25:04');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_counters`
--

CREATE TABLE `invoice_counters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `counter_date` date NOT NULL,
  `last_number` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_counters`
--

INSERT INTO `invoice_counters` (`id`, `counter_date`, `last_number`, `created_at`, `updated_at`) VALUES
(1, '2026-07-01', 2, '2026-07-01 06:49:22', '2026-07-01 07:25:04');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_06_11_045507_create_branches_table', 2),
(5, '2026_06_11_045615_create_users_table', 3),
(6, '2026_06_11_045833_create_products_table', 4),
(7, '2026_06_11_045941_create_stocks_table', 5),
(8, '2026_06_11_050111_create_shifts_table', 6),
(9, '2026_06_11_050156_create_transactions_table', 7),
(10, '2026_06_11_050251_create_transaction_details_table', 8),
(11, '2026_06_11_050339_create_financial_reports_table', 9),
(12, '2026_06_11_060618_create_personal_access_tokens_table', 10),
(13, '2026_06_13_051857_create_settings_table', 11),
(14, '2026_06_13_051956_create_stock_mutations_table', 11),
(15, '2026_06_30_080547_add_image_to_products_table', 12),
(16, '2026_06_30_090000_change_category_to_string_on_products_table', 13),
(17, '2026_07_01_000000_add_client_txn_id_to_transactions_table', 14),
(18, '2026_07_01_000001_create_invoice_counters_table', 15),
(19, '2026_07_01_000002_create_notifications_table', 16);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('da91533d-b18f-4fa2-ab5e-2fa0ffc3d6da', 'App\\Notifications\\CashDifferenceNotification', 'App\\Models\\User', 1, '{\"title\":\"Selisih Kas Shift\",\"message\":\"Shift Marjukii di cabang Cabang Utama ditutup dengan selisih kas lebih Rp8.000.\",\"shift_id\":10,\"branch_id\":1,\"difference\":8000}', '2026-07-01 07:26:07', '2026-07-01 07:25:41', '2026-07-01 07:26:07');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(4, 'App\\Models\\User', 3, 'auth_token', 'ecbd860d17538d75be3d14b96f33922ec324075b5bd31f826214175ecc8fb615', '[\"*\"]', NULL, NULL, '2026-06-11 00:11:38', '2026-06-11 00:11:38'),
(11, 'App\\Models\\User', 5, 'auth_token', 'edfd9f3e56a7b635f4c659bbd2bbe07d3041e38e39f6e0eaa79d7bccac6d7fbd', '[\"*\"]', NULL, NULL, '2026-06-11 01:28:59', '2026-06-11 01:28:59'),
(41, 'App\\Models\\User', 6, 'auth_token', '962803694ce3c3aff02b95713eaac9682a67d2f837e44e3bf960146da325c814', '[\"*\"]', '2026-06-22 02:12:46', NULL, '2026-06-18 00:19:58', '2026-06-22 02:12:46'),
(60, 'App\\Models\\User', 2, 'auth_token', 'a5b520706b155de400dfba6ffef8183e08290cc1de1c14b2392bf31001de5d2b', '[\"*\"]', '2026-06-23 01:56:58', NULL, '2026-06-23 01:56:51', '2026-06-23 01:56:58'),
(63, 'App\\Models\\User', 1, 'auth_token', '88a9f9cadb57a2bf70910f2c68a4fd45f86e8ac8eba43e3efb5a54b8a981a192', '[\"*\"]', '2026-06-25 00:55:51', NULL, '2026-06-25 00:11:56', '2026-06-25 00:55:51');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'Frozen',
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `expired_date` date NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `image`, `price`, `expired_date`, `branch_id`, `created_at`, `updated_at`) VALUES
(6, 'Fiesta Chicken Katsu 500g', 'Frozen', 'products/dP49Ke6WenklaLU3WoI9aPBqt3yo60SjBYTc3FZ6.jpg', 46000.00, '2027-11-16', 1, '2026-06-10 22:53:29', '2026-07-01 05:44:33'),
(7, 'Fiesta Chicken Ring 500g', 'Frozen', 'products/bC9liFF7pKERxw8DMHNZCzZwKazog4LRJa1MMb6J.jpg', 48000.00, '2027-06-15', 1, '2026-06-10 22:53:29', '2026-07-01 05:46:10'),
(11, 'Fiesta Crispy Wings 500g', 'Frozen', 'products/l9m20BTA7Owa58Dsksi9m8hCSrDzK8gvGDMGxExZ.jpg', 75000.00, '2027-07-07', 2, '2026-06-11 04:28:42', '2026-07-01 05:43:21'),
(12, 'Fiesta Crispy Wings 500g', 'Frozen', 'products/KGK8CF0yrvUDlMzWMailS6F2wNenR8XB7bzQEBfR.jpg', 75000.00, '2027-07-07', 1, '2026-06-30 02:21:25', '2026-07-01 05:43:48'),
(13, 'Fiesta Chicken Ring 500g', 'Frozen', 'products/0zkIqk5mspQ9VipTS9UXNoqidWBpdqxMf2KyeOlt.jpg', 48000.00, '2027-06-15', 2, '2026-07-01 05:39:28', '2026-07-01 05:39:49'),
(14, 'Fiesta Chicken Katsu 500g', 'Frozen', 'products/GMDzrZwkstEUkkmZ8SVbHkUBxQfI0L5mLnSTFy2Q.jpg', 46000.00, '2027-11-16', 2, '2026-07-01 05:45:15', '2026-07-01 05:45:37'),
(15, 'Fiesta Cordon Bleu 500g', 'Frozen', 'products/5jain89C35X5tdIpM3vYU6Q1vQ5OUnaCcQUpGBK7.jpg', 59500.00, '2026-08-02', 1, '2026-07-01 05:47:22', '2026-07-01 05:47:22'),
(16, 'Fiesta Cordon Bleu 500g', 'Frozen', 'products/dNUO4ns2SG8cKEm8yBcgUb1oFtcyCiq7myZKc3Ny.jpg', 59500.00, '2026-08-02', 2, '2026-07-01 05:48:01', '2026-07-01 05:48:01'),
(17, 'Fiesta Pop Bites 500g', 'Frozen', 'products/dTFGT7QzpApqtFgmsXdztBOP8tgA4STQHACEtK3Q.jpg', 52500.00, '2026-07-10', 1, '2026-07-01 05:49:18', '2026-07-01 05:49:18');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('77l47EAKRdsc8P0dicdCk2eHVgyIGSeqkKTnmM4s', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.126.0 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNzkzeUFnQkxUUmZqQ2ZsaFBpa3FNaHFCZmN3YnlHcExWVHVKdjluVSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1782890549),
('am7pryMDmJVq7Ldy1cWt2skaioVSnxz7motyUoBn', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoicWNjMDdlZnc5WVJMRjQ1S2FaQXVQTEdYRkEzODRWRmppdEtTRGNNSiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9vd25lci9icmFuY2hlcyI7czo1OiJyb3V0ZSI7czoxNDoib3duZXIuYnJhbmNoZXMiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NDoibmFtZSI7czoxODoiT3duZXIgTmlja3kgRnJvemVuIjtzOjQ6InJvbGUiO3M6NToib3duZXIiO3M6OToiYnJhbmNoX2lkIjtOO30=', 1782892059),
('tMAEjBsZPasct97IEqro3CwOAdxoSKN6Z8JvE0Iw', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVlNBNnhDZVpkV3NieEs4cnVaTzdaT1lVN0RKNUw0bThYc2tFWWt1dyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1782890566);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'text',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `label`, `type`, `created_at`, `updated_at`) VALUES
(1, 'profit_margin', '25', 'Estimasi Margin Laba (%)', 'number', '2026-06-12 22:26:50', '2026-06-12 22:26:50'),
(2, 'store_name', 'Niki Frozen', 'Nama Toko', 'text', '2026-06-12 22:26:50', '2026-06-12 23:02:56'),
(3, 'store_address', 'Jl. Ring Road Utara, Ngringin, Condongcatur, Kec. Depok, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55281', 'Alamat Toko', 'text', '2026-06-12 22:26:50', '2026-06-12 23:04:13'),
(4, 'store_phone', '081234567890', 'No. Telepon Toko', 'text', '2026-06-12 22:26:50', '2026-06-12 23:03:52'),
(5, 'low_stock_threshold', '10', 'Batas Stok Menipis', 'number', '2026-06-12 22:26:50', '2026-06-12 22:26:50'),
(6, 'expiry_warning_days', '7', 'Peringatan Expired (hari)', 'number', '2026-06-12 22:26:50', '2026-06-12 22:26:50'),
(7, 'tax_percent', '0', NULL, 'text', '2026-06-30 10:04:08', '2026-06-30 10:04:08'),
(8, 'receipt_note', 'Terima kasih telah berbelanja!', NULL, 'text', '2026-06-30 10:04:08', '2026-06-30 10:04:08');

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `opening_cash` decimal(12,2) NOT NULL DEFAULT 0.00,
  `closing_cash` decimal(12,2) DEFAULT NULL,
  `expected_cash` decimal(12,2) DEFAULT NULL,
  `difference` decimal(12,2) DEFAULT NULL,
  `total_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_transactions` int(11) NOT NULL DEFAULT 0,
  `status` enum('aktif','tutup') NOT NULL DEFAULT 'aktif',
  `opened_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `user_id`, `branch_id`, `opening_cash`, `closing_cash`, `expected_cash`, `difference`, `total_sales`, `total_transactions`, `status`, `opened_at`, `closed_at`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 10000.00, 10000.00, 10000.00, 0.00, 0.00, 0, 'tutup', '2026-06-18 01:55:10', '2026-06-18 01:57:12', '2026-06-18 01:55:10', '2026-06-18 01:57:12'),
(2, 2, 1, 10000.00, 30000.00, 30000.00, 0.00, 20000.00, 1, 'tutup', '2026-06-18 02:00:00', '2026-06-18 03:00:12', '2026-06-18 02:00:00', '2026-06-18 03:00:12'),
(3, 2, 1, 10000.00, 35000.00, 35000.00, 0.00, 25000.00, 1, 'tutup', '2026-06-18 03:01:34', '2026-06-18 03:02:32', '2026-06-18 03:01:34', '2026-06-18 03:02:32'),
(4, 5, 1, 10000.00, 118000.00, 118000.00, 0.00, 108000.00, 1, 'tutup', '2026-06-30 01:02:09', '2026-06-30 01:03:16', '2026-06-30 01:02:09', '2026-06-30 01:03:16'),
(5, 5, 1, 0.00, 63000.00, 63000.00, 0.00, 63000.00, 2, 'tutup', '2026-06-30 01:18:21', '2026-06-30 02:17:09', '2026-06-30 01:18:21', '2026-06-30 02:17:09'),
(6, 5, 1, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 'tutup', '2026-06-30 02:19:08', '2026-06-30 09:51:56', '2026-06-30 02:19:08', '2026-06-30 09:51:56'),
(7, 5, 1, 0.00, 65000.00, 65000.00, 0.00, 65000.00, 1, 'tutup', '2026-06-30 09:52:06', '2026-06-30 09:52:48', '2026-06-30 09:52:06', '2026-06-30 09:52:48'),
(8, 5, 1, 0.00, 46000.00, 46000.00, 0.00, 46000.00, 1, 'tutup', '2026-07-01 06:48:24', '2026-07-01 06:49:39', '2026-07-01 06:48:24', '2026-07-01 06:49:39'),
(9, 5, 1, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 'tutup', '2026-07-01 06:51:57', '2026-07-01 06:52:20', '2026-07-01 06:51:57', '2026-07-01 06:52:20'),
(10, 5, 1, 0.00, 150000.00, 142000.00, 8000.00, 142000.00, 1, 'tutup', '2026-07-01 07:24:53', '2026-07-01 07:25:40', '2026-07-01 07:24:53', '2026-07-01 07:25:40');

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `min_stock` int(11) NOT NULL DEFAULT 10,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`id`, `product_id`, `branch_id`, `quantity`, `min_stock`, `updated_at`) VALUES
(6, 6, 1, 98, 10, '2026-06-10 22:53:29'),
(7, 7, 1, 98, 10, '2026-06-10 22:53:29'),
(11, 11, 2, 100, 10, '2026-06-30 02:20:06'),
(12, 12, 1, 100, 10, NULL),
(13, 13, 2, 100, 10, NULL),
(14, 14, 2, 100, 10, NULL),
(15, 15, 1, 100, 10, NULL),
(16, 16, 2, 100, 10, NULL),
(17, 17, 1, 100, 10, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_mutations`
--

CREATE TABLE `stock_mutations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('in','out') NOT NULL,
  `quantity` int(11) NOT NULL,
  `before_stock` int(11) NOT NULL,
  `after_stock` int(11) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_mutations`
--

INSERT INTO `stock_mutations` (`id`, `product_id`, `branch_id`, `user_id`, `type`, `quantity`, `before_stock`, `after_stock`, `note`, `created_at`, `updated_at`) VALUES
(1, 11, 2, 1, 'in', 100, 2, 102, NULL, '2026-06-30 02:20:06', '2026-06-30 02:20:06');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(20) NOT NULL,
  `client_txn_id` varchar(64) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `shift_id` bigint(20) UNSIGNED NOT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment` decimal(12,2) NOT NULL DEFAULT 0.00,
  `change_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('sukses','batal','pending') NOT NULL DEFAULT 'sukses',
  `sync_status` enum('tersinkronisasi','pending','gagal') NOT NULL DEFAULT 'tersinkronisasi',
  `synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `invoice_number`, `client_txn_id`, `user_id`, `branch_id`, `shift_id`, `total`, `payment`, `change_amount`, `status`, `sync_status`, `synced_at`, `created_at`, `updated_at`) VALUES
(1, 'INV-20260618-0001', NULL, 2, 1, 2, 20000.00, 50000.00, 30000.00, 'sukses', 'tersinkronisasi', '2026-06-18 02:00:26', '2026-06-18 02:00:26', '2026-06-18 02:00:26'),
(2, 'INV-20260618-0002', NULL, 2, 1, 3, 25000.00, 50000.00, 25000.00, 'sukses', 'tersinkronisasi', '2026-06-18 03:01:59', '2026-06-18 03:01:59', '2026-06-18 03:01:59'),
(3, 'INV-20260630-0003', NULL, 5, 1, 4, 108000.00, 150000.00, 42000.00, 'sukses', 'tersinkronisasi', '2026-06-30 01:02:55', '2026-06-30 01:02:55', '2026-06-30 01:02:55'),
(4, 'INV-20260630-0004', NULL, 5, 1, 5, 28000.00, 50000.00, 22000.00, 'sukses', 'tersinkronisasi', '2026-06-30 02:12:33', '2026-06-30 02:12:33', '2026-06-30 02:12:33'),
(5, 'INV-20260630-0005', NULL, 5, 1, 5, 35000.00, 70000.00, 35000.00, 'sukses', 'tersinkronisasi', '2026-06-30 02:13:01', '2026-06-30 02:13:01', '2026-06-30 02:13:01'),
(6, 'INV-20260630-0006', NULL, 5, 1, 7, 65000.00, 100000.00, 35000.00, 'sukses', 'tersinkronisasi', '2026-06-30 09:52:24', '2026-06-30 09:52:24', '2026-06-30 09:52:24'),
(7, 'INV-20260701-0001', 'txn-1782888561925-3iw1t8tw', 5, 1, 8, 46000.00, 100000.00, 54000.00, 'sukses', 'tersinkronisasi', '2026-07-01 06:49:22', '2026-07-01 06:49:22', '2026-07-01 06:49:22'),
(8, 'INV-20260701-0002', 'txn-1782890704251-mb7d1gqx', 5, 1, 10, 142000.00, 200000.00, 58000.00, 'sukses', 'tersinkronisasi', '2026-07-01 07:25:04', '2026-07-01 07:25:04', '2026-07-01 07:25:04');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_details`
--

CREATE TABLE `transaction_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transaction_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `price_at_sale` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaction_details`
--

INSERT INTO `transaction_details` (`id`, `transaction_id`, `product_id`, `qty`, `price_at_sale`, `subtotal`, `created_at`, `updated_at`) VALUES
(2, 2, 7, 1, 25000.00, 25000.00, '2026-06-18 03:01:59', '2026-06-18 03:01:59'),
(6, 3, 7, 1, 25000.00, 25000.00, '2026-06-30 01:02:55', '2026-06-30 01:02:55'),
(9, 6, 6, 1, 55000.00, 55000.00, '2026-06-30 09:52:24', '2026-06-30 09:52:24'),
(10, 6, 12, 1, 10000.00, 10000.00, '2026-06-30 09:52:24', '2026-06-30 09:52:24'),
(11, 7, 6, 1, 46000.00, 46000.00, '2026-07-01 06:49:22', '2026-07-01 06:49:22'),
(12, 8, 6, 1, 46000.00, 46000.00, '2026-07-01 07:25:04', '2026-07-01 07:25:04'),
(13, 8, 7, 2, 48000.00, 96000.00, '2026-07-01 07:25:04', '2026-07-01 07:25:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('owner','kasir') NOT NULL DEFAULT 'kasir',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`, `branch_id`, `status`) VALUES
(1, 'Owner Nicky Frozen', 'owner@nicksfrozen.com', NULL, '$2y$12$elwss0hHgNi7iLhPpVvslO9v7NIdoIs5eoCqyuBTsIjxsD.ZwZqge', 'owner', 'Q2Lx5hJwZuO3CU3eZGkZnyAIE8ZsLJyl9RTkYfM1jhKZoT5YRgN7jhnq8MID', '2026-06-10 22:53:27', '2026-06-10 22:53:27', NULL, 'aktif'),
(2, 'Siti Aisyah', 'siti@nicksfrozen.com', NULL, '$2y$12$rk7Myg1amAJ4trp2WmymEetzcKm7fk6aI1z.eKTn2wlqrRBxPb2CS', 'kasir', NULL, '2026-06-10 22:53:27', '2026-06-23 01:56:45', 2, 'aktif'),
(3, 'Budi Santoso', 'budi@nicksfrozen.com', NULL, '$2y$12$.93gEZjRWkhSM5rlI3woouakW0w8VA0BkrXXESrYC1BkTtF/lA9d6', 'kasir', NULL, '2026-06-10 22:53:28', '2026-06-10 22:53:28', 2, 'aktif'),
(5, 'Marjukii', 'ramadhanzaki@students.amikom.ac.id', NULL, '$2y$12$g1e7ZTJlFHWcpxvTswzwVeGWQJevrrq7hXkn3Xhc6FZnB654IIY7m', 'kasir', 'N2XGFzxDk4W4WW2UvLDKhYzqcAepL8ObXLSStDH4GxOB7NW9oGN2eGN4cPaT', '2026-06-11 01:28:47', '2026-06-30 00:55:31', 1, 'aktif'),
(6, 'ronaldo', 'ronaldo@gmail.com', NULL, '$2y$12$zsoyEEKkYPmGahbPgB6CWeztogDnw0rdPuJ04dKCRp83xGRCIqqIm', 'kasir', NULL, '2026-06-18 00:18:34', '2026-06-18 00:18:34', 2, 'aktif');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `financial_reports`
--
ALTER TABLE `financial_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `financial_reports_branch_id_date_unique` (`branch_id`,`date`);

--
-- Indexes for table `invoice_counters`
--
ALTER TABLE `invoice_counters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_counters_counter_date_unique` (`counter_date`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shifts_user_id_foreign` (`user_id`),
  ADD KEY `shifts_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stocks_product_id_branch_id_unique` (`product_id`,`branch_id`),
  ADD KEY `stocks_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `stock_mutations`
--
ALTER TABLE `stock_mutations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_mutations_product_id_foreign` (`product_id`),
  ADD KEY `stock_mutations_branch_id_foreign` (`branch_id`),
  ADD KEY `stock_mutations_user_id_foreign` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transactions_invoice_number_unique` (`invoice_number`),
  ADD UNIQUE KEY `transactions_client_txn_id_unique` (`client_txn_id`),
  ADD KEY `transactions_user_id_foreign` (`user_id`),
  ADD KEY `transactions_branch_id_foreign` (`branch_id`),
  ADD KEY `transactions_shift_id_foreign` (`shift_id`);

--
-- Indexes for table `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_details_transaction_id_foreign` (`transaction_id`),
  ADD KEY `transaction_details_product_id_foreign` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_branch_id_foreign` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_reports`
--
ALTER TABLE `financial_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invoice_counters`
--
ALTER TABLE `invoice_counters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `stock_mutations`
--
ALTER TABLE `stock_mutations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transaction_details`
--
ALTER TABLE `transaction_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `financial_reports`
--
ALTER TABLE `financial_reports`
  ADD CONSTRAINT `financial_reports_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shifts`
--
ALTER TABLE `shifts`
  ADD CONSTRAINT `shifts_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shifts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `stocks_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stocks_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_mutations`
--
ALTER TABLE `stock_mutations`
  ADD CONSTRAINT `stock_mutations_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_mutations_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_mutations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD CONSTRAINT `transaction_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_details_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
