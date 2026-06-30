-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 30, 2026 at 11:54 AM
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
(16, '2026_06_30_090000_change_category_to_string_on_products_table', 13);

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
(1, 'Nugget Ayam', 'Frozen', NULL, 35000.00, '2025-12-31', 1, '2026-06-10 22:53:29', '2026-06-10 22:53:29'),
(2, 'Sosis Solo', 'Frozen', NULL, 28000.00, '2025-11-30', 1, '2026-06-10 22:53:29', '2026-06-10 22:53:29'),
(4, 'Kentang Goreng', 'Frozen', NULL, 20000.00, '2025-12-20', 1, '2026-06-10 22:53:29', '2026-06-10 22:53:29'),
(6, 'Fiesta Chicken Nugget', 'Frozen', 'products/fJLCRM34fNRj9kt6WzLrVsi3a2XbN0iLtpMxqWFh.png', 55000.00, '2027-11-17', 1, '2026-06-10 22:53:29', '2026-06-30 02:00:05'),
(7, 'Dimsum', 'Frozen', NULL, 25000.00, '2025-12-10', 1, '2026-06-10 22:53:29', '2026-06-10 22:53:29'),
(8, 'Cireng', 'Tepung', 'products/WBW4yVhhscYT1OeB4X0UZ6ugqihD99X5uJIs8ftx.jpg', 10000.00, '2026-07-16', 2, '2026-06-10 22:53:29', '2026-06-30 02:46:44'),
(9, 'Sosis Pip', 'Frozen', NULL, 8500.00, '2026-07-01', 1, '2026-06-11 03:58:58', '2026-06-23 01:55:39'),
(10, 'todd', 'Frozen', NULL, 5000.00, '2026-06-11', 1, '2026-06-11 04:22:58', '2026-06-11 04:22:58'),
(11, 'Fiesta Crispy Wings', 'Frozen', 'products/l9m20BTA7Owa58Dsksi9m8hCSrDzK8gvGDMGxExZ.jpg', 4555.00, '2026-06-17', 2, '2026-06-11 04:28:42', '2026-06-30 02:18:50'),
(12, 'Fiesta Crispy Wings', 'Frozen', 'products/KGK8CF0yrvUDlMzWMailS6F2wNenR8XB7bzQEBfR.jpg', 10000.00, '2027-07-07', 1, '2026-06-30 02:21:25', '2026-06-30 02:21:25');

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
('4FjmTlLKjkYKE2GeQKAvNoOv3Lbl8mdnJFbdsEMS', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.126.0 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVzBmYXZObDRSNmJoenBxN2RtRDJiZzVTTVFqNThIejFFd2d5ejR3eCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1782807397),
('ayPDDe4jqUhT80tFESwcSRde2678GW1f2EnsDk21', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiSmM3NklaUEFDUjE3c1UyTmFDbWpyc1lOTGh6aG9YU0lGeDJqbE1nWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9vd25lci9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6MTU6Im93bmVyLmRhc2hib2FyZCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czo0OiJuYW1lIjtzOjE4OiJPd25lciBOaWNreSBGcm96ZW4iO3M6NDoicm9sZSI7czo1OiJvd25lciI7czo5OiJicmFuY2hfaWQiO047fQ==', 1782811949),
('H8HExdSF3s3AAQ8SasaTO4irExNO4SiYFLufEmhW', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiY0lNR3RYNlY1MnpsZXo5dm5rR0RmOHlPUllxTDJUT1d1b1BlUElhayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9vd25lci91c2VycyI7czo1OiJyb3V0ZSI7czoxMToib3duZXIudXNlcnMiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NDoibmFtZSI7czoxODoiT3duZXIgTmlja3kgRnJvemVuIjtzOjQ6InJvbGUiO3M6NToib3duZXIiO3M6OToiYnJhbmNoX2lkIjtOO30=', 1782813235);

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
(6, 'expiry_warning_days', '7', 'Peringatan Expired (hari)', 'number', '2026-06-12 22:26:50', '2026-06-12 22:26:50');

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
(7, 5, 1, 0.00, 65000.00, 65000.00, 0.00, 65000.00, 1, 'tutup', '2026-06-30 09:52:06', '2026-06-30 09:52:48', '2026-06-30 09:52:06', '2026-06-30 09:52:48');

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
(1, 1, 1, 43, 10, '2026-06-10 22:53:29'),
(2, 2, 1, 6, 10, '2026-06-10 22:53:29'),
(4, 4, 1, 27, 10, '2026-06-10 22:53:29'),
(6, 6, 1, 99, 10, '2026-06-10 22:53:29'),
(7, 7, 1, 13, 10, '2026-06-10 22:53:29'),
(8, 8, 2, 13, 10, '2026-06-11 04:27:16'),
(9, 9, 1, 1000, 10, NULL),
(10, 10, 1, 5, 10, NULL),
(11, 11, 2, 102, 10, '2026-06-30 02:20:06'),
(12, 12, 1, 17, 10, NULL);

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

INSERT INTO `transactions` (`id`, `invoice_number`, `user_id`, `branch_id`, `shift_id`, `total`, `payment`, `change_amount`, `status`, `sync_status`, `synced_at`, `created_at`, `updated_at`) VALUES
(1, 'INV-20260618-0001', 2, 1, 2, 20000.00, 50000.00, 30000.00, 'sukses', 'tersinkronisasi', '2026-06-18 02:00:26', '2026-06-18 02:00:26', '2026-06-18 02:00:26'),
(2, 'INV-20260618-0002', 2, 1, 3, 25000.00, 50000.00, 25000.00, 'sukses', 'tersinkronisasi', '2026-06-18 03:01:59', '2026-06-18 03:01:59', '2026-06-18 03:01:59'),
(3, 'INV-20260630-0003', 5, 1, 4, 108000.00, 150000.00, 42000.00, 'sukses', 'tersinkronisasi', '2026-06-30 01:02:55', '2026-06-30 01:02:55', '2026-06-30 01:02:55'),
(4, 'INV-20260630-0004', 5, 1, 5, 28000.00, 50000.00, 22000.00, 'sukses', 'tersinkronisasi', '2026-06-30 02:12:33', '2026-06-30 02:12:33', '2026-06-30 02:12:33'),
(5, 'INV-20260630-0005', 5, 1, 5, 35000.00, 70000.00, 35000.00, 'sukses', 'tersinkronisasi', '2026-06-30 02:13:01', '2026-06-30 02:13:01', '2026-06-30 02:13:01'),
(6, 'INV-20260630-0006', 5, 1, 7, 65000.00, 100000.00, 35000.00, 'sukses', 'tersinkronisasi', '2026-06-30 09:52:24', '2026-06-30 09:52:24', '2026-06-30 09:52:24');

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
(1, 1, 8, 2, 10000.00, 20000.00, '2026-06-18 02:00:26', '2026-06-18 02:00:26'),
(2, 2, 7, 1, 25000.00, 25000.00, '2026-06-18 03:01:59', '2026-06-18 03:01:59'),
(3, 3, 1, 1, 35000.00, 35000.00, '2026-06-30 01:02:55', '2026-06-30 01:02:55'),
(4, 3, 4, 1, 20000.00, 20000.00, '2026-06-30 01:02:55', '2026-06-30 01:02:55'),
(5, 3, 2, 1, 28000.00, 28000.00, '2026-06-30 01:02:55', '2026-06-30 01:02:55'),
(6, 3, 7, 1, 25000.00, 25000.00, '2026-06-30 01:02:55', '2026-06-30 01:02:55'),
(7, 4, 2, 1, 28000.00, 28000.00, '2026-06-30 02:12:33', '2026-06-30 02:12:33'),
(8, 5, 1, 1, 35000.00, 35000.00, '2026-06-30 02:13:01', '2026-06-30 02:13:01'),
(9, 6, 6, 1, 55000.00, 55000.00, '2026-06-30 09:52:24', '2026-06-30 09:52:24'),
(10, 6, 12, 1, 10000.00, 10000.00, '2026-06-30 09:52:24', '2026-06-30 09:52:24');

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
(1, 'Owner Nicky Frozen', 'owner@nicksfrozen.com', NULL, '$2y$12$elwss0hHgNi7iLhPpVvslO9v7NIdoIs5eoCqyuBTsIjxsD.ZwZqge', 'owner', 'lFrDypl3xYn7jIUbqJbS4J0Xlg70n2uAhH8XrUM7eASbmIaFw2d530MrXOhM', '2026-06-10 22:53:27', '2026-06-10 22:53:27', NULL, 'aktif'),
(2, 'Siti Aisyah', 'siti@nicksfrozen.com', NULL, '$2y$12$rk7Myg1amAJ4trp2WmymEetzcKm7fk6aI1z.eKTn2wlqrRBxPb2CS', 'kasir', NULL, '2026-06-10 22:53:27', '2026-06-23 01:56:45', 2, 'aktif'),
(3, 'Budi Santoso', 'budi@nicksfrozen.com', NULL, '$2y$12$.93gEZjRWkhSM5rlI3woouakW0w8VA0BkrXXESrYC1BkTtF/lA9d6', 'kasir', NULL, '2026-06-10 22:53:28', '2026-06-10 22:53:28', 2, 'aktif'),
(5, 'Marjukii', 'ramadhanzaki@students.amikom.ac.id', NULL, '$2y$12$g1e7ZTJlFHWcpxvTswzwVeGWQJevrrq7hXkn3Xhc6FZnB654IIY7m', 'kasir', 'UmzywtcY4fSdNvMLrcflMoACcpAkDkKlaIdK8PDIbgL2g9rprOxnc1BBJn4e', '2026-06-11 01:28:47', '2026-06-30 00:55:31', 1, 'aktif'),
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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `stock_mutations`
--
ALTER TABLE `stock_mutations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `transaction_details`
--
ALTER TABLE `transaction_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
