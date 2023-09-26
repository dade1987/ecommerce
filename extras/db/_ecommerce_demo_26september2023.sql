-- phpMyAdmin SQL Dump
-- version 5.2.1deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 26, 2023 at 12:38 PM
-- Server version: 10.11.4-MariaDB-1
-- PHP Version: 8.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fast_order`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `nation` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `municipality` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `created_at`, `updated_at`, `nation`, `region`, `province`, `municipality`, `street`, `postal_code`) VALUES
(1, '2023-09-13 14:54:38', '2023-09-13 14:54:38', 'Italia', 'Veneto', 'VE', 'Noale', 'Via del Musonetto, 4', '30033'),
(2, '2023-09-15 05:25:11', '2023-09-15 05:25:11', 'Italia', 'Veneto', 'VE', 'Marghera', 'Via Cibrario, 4', '30175'),
(5, '2023-09-20 13:30:05', '2023-09-20 13:30:05', 'Italia', 'Veneto', 'VE', 'Chirignago', 'Via Rossi, 5', '30175');

-- --------------------------------------------------------

--
-- Table structure for table `address_morph`
--

CREATE TABLE `address_morph` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `address_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `address_morph`
--

INSERT INTO `address_morph` (`id`, `created_at`, `updated_at`, `model_type`, `model_id`, `address_id`, `user_id`) VALUES
(2, '2023-09-14 07:53:27', '2023-09-14 07:53:27', 'App\\Models\\Order', 1, 1, NULL),
(3, '2023-09-14 10:23:58', '2023-09-14 10:23:58', 'App\\Models\\Order', 8, 1, NULL),
(4, '2023-09-14 10:24:31', '2023-09-14 10:24:31', 'App\\Models\\Order', 9, 1, NULL),
(5, '2023-09-14 10:26:31', '2023-09-14 10:26:31', 'App\\Models\\Order', 10, 1, NULL),
(8, '2023-09-15 07:20:58', '2023-09-15 07:20:58', 'App\\Models\\Order', 13, 1, NULL),
(9, '2023-09-15 07:21:29', '2023-09-15 07:21:29', 'App\\Models\\Order', 14, 1, NULL),
(11, '2023-09-15 07:23:25', '2023-09-15 07:23:25', 'App\\Models\\Order', 15, 2, NULL),
(12, '2023-09-15 07:28:43', '2023-09-15 07:28:43', 'App\\Models\\Order', 16, 2, NULL),
(14, '2023-09-15 07:28:53', '2023-09-15 07:28:53', 'App\\Models\\Order', 17, 1, NULL),
(15, '2023-09-15 07:29:04', '2023-09-15 07:29:04', 'App\\Models\\Order', 18, 1, NULL),
(16, '2023-09-15 07:29:34', '2023-09-15 07:29:34', 'App\\Models\\Order', 19, 1, NULL),
(17, '2023-09-15 07:29:48', '2023-09-15 07:29:48', 'App\\Models\\Order', 20, 1, NULL),
(18, '2023-09-15 07:30:00', '2023-09-15 07:30:00', 'App\\Models\\Order', 21, 1, NULL),
(19, '2023-09-15 07:30:11', '2023-09-15 07:30:11', 'App\\Models\\Order', 22, 1, NULL),
(20, '2023-09-15 07:30:37', '2023-09-15 07:30:37', 'App\\Models\\Order', 23, 1, NULL),
(21, '2023-09-15 07:34:05', '2023-09-15 07:34:05', 'App\\Models\\Order', 24, 1, NULL),
(23, '2023-09-15 07:34:23', '2023-09-15 07:34:23', 'App\\Models\\Order', 25, 2, NULL),
(25, '2023-09-15 07:36:05', '2023-09-15 07:36:05', 'App\\Models\\Order', 26, 1, NULL),
(27, '2023-09-15 07:36:13', '2023-09-15 07:36:13', 'App\\Models\\Order', 27, 2, NULL),
(28, '2023-09-15 07:36:48', '2023-09-15 07:36:48', 'App\\Models\\Order', 28, 2, NULL),
(29, '2023-09-15 07:37:11', '2023-09-15 07:37:11', 'App\\Models\\User', 3, 1, NULL),
(30, '2023-09-15 07:37:11', '2023-09-15 07:37:11', 'App\\Models\\Order', 29, 1, NULL),
(31, '2023-09-15 07:38:27', '2023-09-15 07:38:27', 'App\\Models\\Order', 30, 1, NULL),
(32, '2023-09-15 07:40:35', '2023-09-15 07:40:35', 'App\\Models\\Order', 31, 1, NULL),
(34, '2023-09-15 14:17:30', '2023-09-15 14:17:30', 'App\\Models\\Order', 32, 2, NULL),
(36, '2023-09-17 16:38:14', '2023-09-17 16:38:14', 'App\\Models\\Order', 33, 1, NULL),
(40, '2023-09-20 13:30:22', '2023-09-20 13:30:22', 'App\\Models\\Order', 34, 5, NULL),
(42, '2023-09-21 15:20:25', '2023-09-21 15:20:25', 'App\\Models\\Order', 1, 1, NULL),
(43, '2023-09-22 05:43:33', '2023-09-22 05:43:33', 'App\\Models\\User', 2, 2, NULL),
(44, '2023-09-22 05:43:33', '2023-09-22 05:43:33', 'App\\Models\\Order', 3, 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `featured_image_id` int(11) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `order_column` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `created_at`, `updated_at`, `name`, `featured_image_id`, `slug`, `order_column`) VALUES
(1, '2023-09-09 13:23:53', '2023-09-22 06:43:51', 'Primi Piatti', 12, '', 2),
(2, '2023-09-09 13:24:02', '2023-09-22 06:43:51', 'Secondi Piatti', 11, '', 3),
(3, '2023-09-09 13:24:08', '2023-09-22 06:43:51', 'Contorni', 13, '', 4),
(4, '2023-09-09 15:24:58', '2023-09-22 06:43:51', 'Antipasti', 10, '', 1),
(5, '2023-09-14 10:02:02', '2023-09-22 06:43:51', 'Varianti', NULL, '', 5),
(6, '2023-09-15 09:53:37', '2023-09-22 06:43:51', 'Test Admin', NULL, '', 6);

-- --------------------------------------------------------

--
-- Table structure for table `discount_coupons`
--

CREATE TABLE `discount_coupons` (
  `id` int(10) UNSIGNED NOT NULL,
  `valid_from` timestamp NULL DEFAULT NULL,
  `valid_until` timestamp NULL DEFAULT NULL,
  `uses` int(11) NOT NULL DEFAULT 0,
  `code` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `is_fixed` tinyint(1) NOT NULL DEFAULT 1,
  `value` decimal(9,2) NOT NULL DEFAULT 0.00,
  `lower_cart_limit` decimal(9,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
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
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `disk` varchar(255) NOT NULL DEFAULT 'public',
  `directory` varchar(255) NOT NULL DEFAULT 'media',
  `visibility` varchar(255) NOT NULL DEFAULT 'public',
  `name` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `width` int(10) UNSIGNED DEFAULT NULL,
  `height` int(10) UNSIGNED DEFAULT NULL,
  `size` int(10) UNSIGNED DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'image',
  `ext` varchar(255) NOT NULL,
  `alt` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `exif` text DEFAULT NULL,
  `curations` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `disk`, `directory`, `visibility`, `name`, `path`, `width`, `height`, `size`, `type`, `ext`, `alt`, `title`, `description`, `caption`, `exif`, `curations`, `created_at`, `updated_at`) VALUES
(2, 'public', 'media', 'public', 'c18a0cc8-3f34-4c95-a177-72f55495b84e-1694275142', 'media/c18a0cc8-3f34-4c95-a177-72f55495b84e-1694275142.png', 360, 227, 33744, 'image/png', 'png', NULL, 'primi', NULL, NULL, NULL, NULL, '2023-09-09 13:59:02', '2023-09-09 13:59:02'),
(3, 'public', 'media', 'public', '92d3fe13-1d8a-482b-a746-277b15d7aad9-1694275209', 'media/92d3fe13-1d8a-482b-a746-277b15d7aad9-1694275209.jpg', 289, 175, 9298, 'image/jpeg', 'jpg', NULL, 'secondi', NULL, NULL, '{\"FileName\":\"mxCBUCKWX47G0R25lC1M1mCJuudbY8-metac2Vjb25kaS5qcGVn-.jpg\",\"FileDateTime\":1694275198,\"FileSize\":9298,\"FileType\":2,\"MimeType\":\"image\\/jpeg\",\"SectionsFound\":\"\",\"COMPUTED\":{\"html\":\"width=\\\"289\\\" height=\\\"175\\\"\",\"Height\":175,\"Width\":289,\"IsColor\":1}}', NULL, '2023-09-09 14:00:09', '2023-09-09 14:00:09'),
(4, 'public', 'media', 'public', '19aab2a2-58c2-473d-8b0b-d490dcf2fb66-1694275228', 'media/19aab2a2-58c2-473d-8b0b-d490dcf2fb66-1694275228.jpg', 275, 183, 7659, 'image/jpeg', 'jpg', NULL, 'download', NULL, NULL, '{\"FileName\":\"C62RGXo6CuSvKij4sbjkEiDs251GWu-metaZG93bmxvYWQuanBlZw==-.jpg\",\"FileDateTime\":1694275226,\"FileSize\":7659,\"FileType\":2,\"MimeType\":\"image\\/jpeg\",\"SectionsFound\":\"\",\"COMPUTED\":{\"html\":\"width=\\\"275\\\" height=\\\"183\\\"\",\"Height\":183,\"Width\":275,\"IsColor\":1}}', NULL, '2023-09-09 14:00:28', '2023-09-09 14:00:28'),
(5, 'public', 'media', 'public', 'b1e0c989-7322-43bd-b70f-e463174d20c7-1694280282', 'media/b1e0c989-7322-43bd-b70f-e463174d20c7-1694280282.png', 512, 512, 592528, 'image/png', 'png', 'informatico', 'job-seeking-alert-as-laravel-and-vuejs-programmer-799338607', NULL, NULL, NULL, '[]', '2023-09-09 15:24:42', '2023-09-11 15:39:30'),
(6, 'public', 'media', 'public', 'dec5d7e2-d7a7-48cc-b6c7-96cb5e508127-1694453114', 'media/dec5d7e2-d7a7-48cc-b6c7-96cb5e508127-1694453114.jpg', 259, 194, 26093, 'image/jpeg', 'jpg', NULL, 'carbonara', NULL, NULL, '{\"FileName\":\"pIeCgCgtBN2xo42gTE8UjNozx3pgeO-metaY2FyYm9uYXJhLmpwZw==-.jpg\",\"FileDateTime\":1694453113,\"FileSize\":26093,\"FileType\":2,\"MimeType\":\"image\\/jpeg\",\"SectionsFound\":\"\",\"COMPUTED\":{\"html\":\"width=\\\"259\\\" height=\\\"194\\\"\",\"Height\":194,\"Width\":259,\"IsColor\":1}}', NULL, '2023-09-11 15:25:14', '2023-09-11 15:25:14'),
(7, 'public', 'media', 'public', '02179d65-9fb5-4c5b-83d1-9e49bf6b697f-1694453207', 'media/02179d65-9fb5-4c5b-83d1-9e49bf6b697f-1694453207.jpg', 275, 183, 29886, 'image/jpeg', 'jpg', NULL, 'tagliata', NULL, NULL, '{\"FileName\":\"7MQROc1oeqLd9y8KbzRuXXuTRbx4nj-metadGFnbGlhdGEuanBn-.jpg\",\"FileDateTime\":1694453206,\"FileSize\":29886,\"FileType\":2,\"MimeType\":\"image\\/jpeg\",\"SectionsFound\":\"\",\"COMPUTED\":{\"html\":\"width=\\\"275\\\" height=\\\"183\\\"\",\"Height\":183,\"Width\":275,\"IsColor\":1}}', NULL, '2023-09-11 15:26:47', '2023-09-11 15:26:47'),
(8, 'public', 'media', 'public', '3ea0f295-278c-40c9-95fa-250d998a4480-1694453249', 'media/3ea0f295-278c-40c9-95fa-250d998a4480-1694453249.jpg', 296, 170, 21512, 'image/jpeg', 'jpg', NULL, 'tagliatelle', NULL, NULL, '{\"FileName\":\"n2tZDyox1t8cCqf5zeJrGIWSICqebq-metadGFnbGlhdGVsbGUuanBn-.jpg\",\"FileDateTime\":1694453248,\"FileSize\":21512,\"FileType\":2,\"MimeType\":\"image\\/jpeg\",\"SectionsFound\":\"\",\"COMPUTED\":{\"html\":\"width=\\\"296\\\" height=\\\"170\\\"\",\"Height\":170,\"Width\":296,\"IsColor\":1}}', NULL, '2023-09-11 15:27:29', '2023-09-11 15:27:29'),
(9, 'public', 'media', 'public', '14f79cfa-9063-4110-be2e-afb927acf29e-1694453272', 'media/14f79cfa-9063-4110-be2e-afb927acf29e-1694453272.jpg', 275, 183, 29886, 'image/jpeg', 'jpg', NULL, 'tagliata', NULL, NULL, '{\"FileName\":\"BMXSVNMHHsZxEai68ufIfN9roF3eWi-metadGFnbGlhdGEuanBn-.jpg\",\"FileDateTime\":1694453269,\"FileSize\":29886,\"FileType\":2,\"MimeType\":\"image\\/jpeg\",\"SectionsFound\":\"\",\"COMPUTED\":{\"html\":\"width=\\\"275\\\" height=\\\"183\\\"\",\"Height\":183,\"Width\":275,\"IsColor\":1}}', NULL, '2023-09-11 15:27:52', '2023-09-11 15:27:52'),
(10, 'public', 'media', 'public', '689ad984-5d14-4c12-af58-e89108f1650d-1694454011', 'media/689ad984-5d14-4c12-af58-e89108f1650d-1694454011.png', 512, 512, 608424, 'image/png', 'png', NULL, 'appetizers-125440521', NULL, NULL, NULL, NULL, '2023-09-11 15:40:11', '2023-09-11 15:40:11'),
(11, 'public', 'media', 'public', '1bc94555-e968-430c-ad84-78973ce2631d-1694454293', 'media/1bc94555-e968-430c-ad84-78973ce2631d-1694454293.png', 512, 512, 438789, 'image/png', 'png', NULL, 'first-courses-14118609', NULL, NULL, NULL, NULL, '2023-09-11 15:44:53', '2023-09-11 15:44:53'),
(12, 'public', 'media', 'public', 'e42af5fa-8f15-4351-aec4-0bf62ff4ea96-1694454393', 'media/e42af5fa-8f15-4351-aec4-0bf62ff4ea96-1694454393.png', 512, 512, 542842, 'image/png', 'png', NULL, 'fresh-pasta-261986795', NULL, NULL, NULL, NULL, '2023-09-11 15:46:33', '2023-09-11 15:46:33'),
(13, 'public', 'media', 'public', '50b3879c-dfae-44db-a552-c0fd7b5eefb9-1694454604', 'media/50b3879c-dfae-44db-a552-c0fd7b5eefb9-1694454604.png', 512, 512, 538831, 'image/png', 'png', NULL, 'bowl-full-of-french-fries-640555441', NULL, NULL, NULL, NULL, '2023-09-11 15:50:04', '2023-09-11 15:50:04');

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
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2023_09_09_144038_create_pages_table', 2),
(6, '2023_09_09_151544_create_categories_table', 3),
(7, '2023_09_09_152658_create_media_table', 4),
(8, '2023_09_09_160609_add_image_to_categories', 5),
(9, '2023_09_09_163856_upgrade_media_table', 6),
(10, '2023_09_10_072255_create_permission_tables', 7),
(11, '2023_09_11_114526_create_products_table', 8),
(12, '2023_09_11_115858_create_product_morph_table', 9),
(13, '2023_09_11_120725_add_fields_to_products', 10),
(14, '2023_09_11_115859_create_product_morph_table', 11),
(15, '2023_09_11_114527_create_products_table', 12),
(16, '2023_09_11_172603_add_featured_image_id_to_products', 13),
(17, '2023_09_13_162302_create_addresses_table', 14),
(18, '2023_09_13_162521_create_address_morph_table', 14),
(20, '2023_09_14_093927_create_orders_table', 15),
(21, '2023_09_13_162521_create_order_morph_table', 16),
(22, '2023_09_15_104037_create_teams_table', 17),
(23, '2023_09_15_104239_create_team_user_table', 17),
(24, '2023_09_15_104239_create_team_morph_table', 18),
(25, '2023_09_15_135624_add_slug_to_categories', 19),
(26, '2023_09_15_135701_add_slug_to_products', 19),
(27, '2023_09_19_092943_add_option_to_product_morph', 20),
(28, '2023_09_20_153820_create_discount_coupons_table', 21),
(29, '2023_09_20_153820_create_order_tables', 22),
(30, '2023_09_14_093928_create_orders_table', 23),
(31, '2023_09_22_083755_add_order_column_to_products', 24),
(32, '2023_09_22_083759_add_order_column_to_categories', 24);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 2),
(2, 'App\\Models\\User', 3);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `delivery_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `created_at`, `updated_at`, `delivery_date`) VALUES
(1, '2023-09-21 15:20:25', '2023-09-21 15:20:25', '2023-09-21 20:45:00'),
(3, '2023-09-22 05:43:33', '2023-09-22 05:43:33', '2023-09-22 07:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `order_morph`
--

CREATE TABLE `order_morph` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_morph`
--

INSERT INTO `order_morph` (`id`, `created_at`, `updated_at`, `model_type`, `model_id`, `order_id`, `user_id`) VALUES
(1, '2023-09-15 07:19:43', '2023-09-15 07:19:43', 'App\\Models\\User', 3, 12, NULL),
(2, '2023-09-15 07:20:58', '2023-09-15 07:20:58', 'App\\Models\\User', 3, 13, NULL),
(3, '2023-09-15 07:21:29', '2023-09-15 07:21:29', 'App\\Models\\User', 3, 14, NULL),
(4, '2023-09-15 07:23:25', '2023-09-15 07:23:25', 'App\\Models\\User', 3, 15, NULL),
(5, '2023-09-15 07:28:43', '2023-09-15 07:28:43', 'App\\Models\\User', 3, 16, NULL),
(6, '2023-09-15 07:28:53', '2023-09-15 07:28:53', 'App\\Models\\User', 3, 17, NULL),
(7, '2023-09-15 07:29:04', '2023-09-15 07:29:04', 'App\\Models\\User', 3, 18, NULL),
(8, '2023-09-15 07:29:34', '2023-09-15 07:29:34', 'App\\Models\\User', 3, 19, NULL),
(9, '2023-09-15 07:29:48', '2023-09-15 07:29:48', 'App\\Models\\User', 3, 20, NULL),
(10, '2023-09-15 07:30:00', '2023-09-15 07:30:00', 'App\\Models\\User', 3, 21, NULL),
(11, '2023-09-15 07:30:11', '2023-09-15 07:30:11', 'App\\Models\\User', 3, 22, NULL),
(12, '2023-09-15 07:30:37', '2023-09-15 07:30:37', 'App\\Models\\User', 3, 23, NULL),
(13, '2023-09-15 07:34:05', '2023-09-15 07:34:05', 'App\\Models\\User', 3, 24, NULL),
(14, '2023-09-15 07:34:23', '2023-09-15 07:34:23', 'App\\Models\\User', 3, 25, NULL),
(15, '2023-09-15 07:36:05', '2023-09-15 07:36:05', 'App\\Models\\User', 3, 26, NULL),
(16, '2023-09-15 07:36:13', '2023-09-15 07:36:13', 'App\\Models\\User', 3, 27, NULL),
(17, '2023-09-15 07:36:48', '2023-09-15 07:36:48', 'App\\Models\\User', 3, 28, NULL),
(18, '2023-09-15 07:37:11', '2023-09-15 07:37:11', 'App\\Models\\User', 3, 29, NULL),
(19, '2023-09-15 07:38:27', '2023-09-15 07:38:27', 'App\\Models\\User', 3, 30, NULL),
(20, '2023-09-15 07:40:35', '2023-09-15 07:40:35', 'App\\Models\\User', 3, 31, NULL),
(21, '2023-09-15 14:17:30', '2023-09-15 14:17:30', 'App\\Models\\User', 2, 32, NULL),
(22, '2023-09-17 16:38:14', '2023-09-17 16:38:14', 'App\\Models\\User', 2, 33, NULL),
(23, '2023-09-20 13:30:22', '2023-09-20 13:30:22', 'App\\Models\\User', 2, 34, NULL),
(24, '2023-09-21 15:20:25', '2023-09-21 15:20:25', 'App\\Models\\User', 2, 1, NULL),
(25, '2023-09-22 05:43:19', '2023-09-22 05:43:19', 'App\\Models\\User', 2, 2, NULL),
(26, '2023-09-22 05:43:33', '2023-09-22 05:43:33', 'App\\Models\\User', 2, 3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `layout` varchar(255) NOT NULL DEFAULT 'default',
  `blocks` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`blocks`)),
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `title`, `slug`, `layout`, `blocks`, `parent_id`, `created_at`, `updated_at`) VALUES
(2, 'Scegli Categoria', 'categories', 'simple', '[{\"type\":\"header-one\",\"data\":{\"text_one\":\"HOME\",\"link_one\":\"#\",\"text_two\":null,\"link_two\":null,\"text_three\":null,\"link_three\":null,\"text_four\":null,\"link_four\":null}},{\"type\":\"breadcrumbs\",\"data\":[]},{\"type\":\"model-list\",\"data\":{\"second_button\":false}}]', NULL, '2023-09-09 12:48:27', '2023-09-26 10:09:37'),
(3, 'Scegli Prodotto', 'products', 'simple', '[{\"type\":\"header-one\",\"data\":{\"text_one\":\"HOME\",\"link_one\":\"\\/home\",\"text_two\":null,\"link_two\":null,\"text_three\":null,\"link_three\":null,\"text_four\":null,\"link_four\":null}},{\"type\":\"breadcrumbs\",\"data\":[]},{\"type\":\"model-list\",\"data\":{\"enable_variants\":true,\"second_button\":false}}]', NULL, '2023-09-11 10:55:57', '2023-09-26 10:14:33'),
(4, 'Carrello', 'cart', 'simple', '[{\"type\":\"header-one\",\"data\":{\"text_one\":\"HOME\",\"link_one\":\"\\/home\",\"text_two\":null,\"link_two\":null,\"text_three\":null,\"link_three\":null,\"text_four\":null,\"link_four\":null}},{\"type\":\"breadcrumbs\",\"data\":[]},{\"type\":\"cart\",\"data\":{\"back_to_shop_link\":\"\\/categories\",\"next_link\":\"\\/delivery-options\"}}]', NULL, '2023-09-12 15:04:24', '2023-09-26 10:16:51'),
(5, 'Ordine Completato', 'order-completed', 'simple', '[{\"type\":\"header-one\",\"data\":{\"text_one\":\"HOME\",\"link_one\":\"\\/\",\"text_two\":null,\"link_two\":null,\"text_three\":null,\"link_three\":null,\"text_four\":null,\"link_four\":null}},{\"type\":\"breadcrumbs\",\"data\":[]},{\"type\":\"thank-you\",\"data\":{\"title\":\"Grazie Per Aver Ordinato\",\"subtitle\":\"Saremo felice di servirti nuovamente non appena possibile\",\"button_text\":\"Ordina Ancora\",\"button_link\":\"\\/categories\"}}]', NULL, '2023-09-13 07:23:26', '2023-09-26 10:15:12'),
(6, 'Opzioni di Consegna', 'delivery-options', 'simple', '[{\"type\":\"header-one\",\"data\":{\"text_one\":\"HOME\",\"link_one\":\"\\/home\",\"text_two\":null,\"link_two\":null,\"text_three\":null,\"link_three\":null,\"text_four\":null,\"link_four\":null}},{\"type\":\"breadcrumbs\",\"data\":[]},{\"type\":\"select-delivery-options\",\"data\":[]}]', NULL, '2023-09-15 05:31:48', '2023-09-26 10:15:36'),
(7, 'Scegli Aggiunte', 'variations', 'simple', '[{\"type\":\"header-one\",\"data\":{\"text_one\":\"HOME\",\"link_one\":\"\\/home\",\"text_two\":null,\"link_two\":null,\"text_three\":null,\"link_three\":null,\"text_four\":null,\"link_four\":null}},{\"type\":\"breadcrumbs\",\"data\":[]},{\"type\":\"model-list\",\"data\":{\"second_button\":false}}]', NULL, '2023-09-15 13:32:23', '2023-09-26 10:15:58'),
(8, 'Home Page', 'home', 'simple', '[{\"type\":\"header-one\",\"data\":{\"text_one\":\"HOME\",\"link_one\":\"#\",\"text_two\":\"IL RISTORANTE\",\"link_two\":\"#\",\"text_three\":\"MENU\",\"link_three\":\"#\",\"text_four\":\"CONTATTI\",\"link_four\":\"#\"}},{\"type\":\"hero-background-image\",\"data\":{\"image_url\":\"http:\\/\\/127.0.0.1:8000\\/images\\/ristoranteuno.webp\",\"text_one\":\"Ristorante Paradiso\",\"text_two\":\"sapore, qualit\\u00e0, gusto supremo\",\"text_button\":\"VAI AL MENU\",\"link_button\":\"\\/categories\"}}]', NULL, '2023-09-26 10:08:31', '2023-09-26 10:08:31');

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
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view_category', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(2, 'view_any_category', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(3, 'create_category', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(4, 'update_category', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(5, 'restore_category', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(6, 'restore_any_category', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(7, 'replicate_category', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(8, 'reorder_category', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(9, 'delete_category', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(10, 'delete_any_category', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(11, 'force_delete_category', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(12, 'force_delete_any_category', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(13, 'view_media', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(14, 'view_any_media', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(15, 'create_media', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(16, 'update_media', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(17, 'restore_media', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(18, 'restore_any_media', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(19, 'replicate_media', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(20, 'reorder_media', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(21, 'delete_media', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(22, 'delete_any_media', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(23, 'force_delete_media', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(24, 'force_delete_any_media', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(25, 'view_page', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(26, 'view_any_page', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(27, 'create_page', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(28, 'update_page', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(29, 'restore_page', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(30, 'restore_any_page', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(31, 'replicate_page', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(32, 'reorder_page', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(33, 'delete_page', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(34, 'delete_any_page', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(35, 'force_delete_page', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(36, 'force_delete_any_page', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(37, 'view_role', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(38, 'view_any_role', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(39, 'create_role', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(40, 'update_role', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(41, 'delete_role', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(42, 'delete_any_role', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(43, 'view_user', 'web', '2023-09-10 06:56:45', '2023-09-10 06:56:45'),
(44, 'view_any_user', 'web', '2023-09-10 06:56:45', '2023-09-10 06:56:45'),
(45, 'create_user', 'web', '2023-09-10 06:56:45', '2023-09-10 06:56:45'),
(46, 'update_user', 'web', '2023-09-10 06:56:45', '2023-09-10 06:56:45'),
(47, 'restore_user', 'web', '2023-09-10 06:56:45', '2023-09-10 06:56:45'),
(48, 'restore_any_user', 'web', '2023-09-10 06:56:45', '2023-09-10 06:56:45'),
(49, 'replicate_user', 'web', '2023-09-10 06:56:45', '2023-09-10 06:56:45'),
(50, 'reorder_user', 'web', '2023-09-10 06:56:45', '2023-09-10 06:56:45'),
(51, 'delete_user', 'web', '2023-09-10 06:56:45', '2023-09-10 06:56:45'),
(52, 'delete_any_user', 'web', '2023-09-10 06:56:45', '2023-09-10 06:56:45'),
(53, 'force_delete_user', 'web', '2023-09-10 06:56:45', '2023-09-10 06:56:45'),
(54, 'force_delete_any_user', 'web', '2023-09-10 06:56:45', '2023-09-10 06:56:45'),
(55, 'view_product', 'web', '2023-09-12 10:33:41', '2023-09-12 10:33:41'),
(56, 'view_any_product', 'web', '2023-09-12 10:33:41', '2023-09-12 10:33:41'),
(57, 'create_product', 'web', '2023-09-12 10:33:41', '2023-09-12 10:33:41'),
(58, 'update_product', 'web', '2023-09-12 10:33:41', '2023-09-12 10:33:41'),
(59, 'restore_product', 'web', '2023-09-12 10:33:41', '2023-09-12 10:33:41'),
(60, 'restore_any_product', 'web', '2023-09-12 10:33:41', '2023-09-12 10:33:41'),
(61, 'replicate_product', 'web', '2023-09-12 10:33:41', '2023-09-12 10:33:41'),
(62, 'reorder_product', 'web', '2023-09-12 10:33:41', '2023-09-12 10:33:41'),
(63, 'delete_product', 'web', '2023-09-12 10:33:41', '2023-09-12 10:33:41'),
(64, 'delete_any_product', 'web', '2023-09-12 10:33:41', '2023-09-12 10:33:41'),
(65, 'force_delete_product', 'web', '2023-09-12 10:33:41', '2023-09-12 10:33:41'),
(66, 'force_delete_any_product', 'web', '2023-09-12 10:33:41', '2023-09-12 10:33:41');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `featured_image_id` int(11) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `order_column` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `created_at`, `updated_at`, `name`, `description`, `price`, `user_id`, `featured_image_id`, `slug`, `order_column`) VALUES
(1, '2023-09-11 10:15:40', '2023-09-14 09:58:37', 'Spaghetti alla Carbonara', 'Spaghetti, Guanciale, Uovo, Pecorino, Olio Extra Vergine d\'Oliva', 7.00, NULL, 6, '', NULL),
(3, '2023-09-11 11:20:06', '2023-09-11 15:27:55', 'Tagliata di Roastbeef', NULL, 18.00, NULL, 9, '', NULL),
(4, '2023-09-11 13:14:33', '2023-09-11 15:27:32', 'Tagliatelle al ragù', NULL, 5.00, NULL, 8, '', NULL),
(5, '2023-09-14 10:02:15', '2023-09-21 07:14:15', 'Origano', NULL, 1.00, NULL, NULL, '', NULL),
(6, '2023-09-14 10:02:21', '2023-09-22 08:12:12', 'Prezzemolo', NULL, 0.50, NULL, NULL, '', NULL),
(7, '2023-09-14 10:26:12', '2023-09-14 10:26:12', 'Spaghetti alla Carbonara', 'Uova, Guanciale, Pecorino', 6.00, NULL, 6, '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_morph`
--

CREATE TABLE `product_morph` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `option` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_morph`
--

INSERT INTO `product_morph` (`id`, `created_at`, `updated_at`, `model_type`, `model_id`, `product_id`, `user_id`, `option`) VALUES
(1, '2023-09-11 10:16:22', '2023-09-11 10:16:22', 'App\\Models\\Category', 1, 2, NULL, NULL),
(2, '2023-09-11 11:20:06', '2023-09-11 11:20:06', 'App\\Models\\Category', 2, 3, NULL, NULL),
(3, '2023-09-11 13:14:33', '2023-09-11 13:14:33', 'App\\Models\\Category', 1, 4, NULL, NULL),
(4, '2023-09-14 08:03:52', '2023-09-14 08:03:52', 'App\\Models\\Order', 1, 1, NULL, NULL),
(5, '2023-09-14 08:03:58', '2023-09-14 08:03:58', 'App\\Models\\Order', 1, 3, NULL, NULL),
(6, '2023-09-14 10:02:15', '2023-09-14 10:02:15', 'App\\Models\\Category', 5, 5, NULL, NULL),
(7, '2023-09-14 10:02:21', '2023-09-14 10:02:21', 'App\\Models\\Category', 5, 6, NULL, NULL),
(8, '2023-09-14 10:02:33', '2023-09-14 10:02:33', 'App\\Models\\Product', 1, 5, NULL, NULL),
(9, '2023-09-14 10:17:25', '2023-09-14 10:17:25', 'App\\Models\\Order', 3, 0, NULL, NULL),
(10, '2023-09-14 10:17:25', '2023-09-14 10:17:25', 'App\\Models\\Order', 3, 0, NULL, NULL),
(11, '2023-09-14 10:17:49', '2023-09-14 10:17:49', 'App\\Models\\Order', 4, 0, NULL, NULL),
(12, '2023-09-14 10:17:49', '2023-09-14 10:17:49', 'App\\Models\\Order', 4, 0, NULL, NULL),
(13, '2023-09-14 10:18:03', '2023-09-14 10:18:03', 'App\\Models\\Order', 5, 0, NULL, NULL),
(14, '2023-09-14 10:18:03', '2023-09-14 10:18:03', 'App\\Models\\Order', 5, 0, NULL, NULL),
(15, '2023-09-14 10:18:42', '2023-09-14 10:18:42', 'App\\Models\\Order', 6, 0, NULL, NULL),
(16, '2023-09-14 10:18:42', '2023-09-14 10:18:42', 'App\\Models\\Order', 6, 0, NULL, NULL),
(17, '2023-09-14 10:20:17', '2023-09-14 10:20:17', 'App\\Models\\Order', 7, 0, NULL, NULL),
(18, '2023-09-14 10:20:17', '2023-09-14 10:20:17', 'App\\Models\\Order', 7, 0, NULL, NULL),
(19, '2023-09-14 10:23:58', '2023-09-14 10:23:58', 'App\\Models\\Order', 8, 0, NULL, NULL),
(20, '2023-09-14 10:24:31', '2023-09-14 10:24:31', 'App\\Models\\Order', 9, 0, NULL, NULL),
(21, '2023-09-14 10:26:12', '2023-09-14 10:26:12', 'App\\Models\\Category', 1, 7, NULL, NULL),
(22, '2023-09-14 10:26:31', '2023-09-14 10:26:31', 'App\\Models\\Order', 10, 4, NULL, NULL),
(23, '2023-09-14 10:26:31', '2023-09-14 10:26:31', 'App\\Models\\Order', 10, 7, NULL, NULL),
(24, '2023-09-15 07:20:58', '2023-09-15 07:20:58', 'App\\Models\\Order', 13, 4, NULL, NULL),
(25, '2023-09-15 07:20:58', '2023-09-15 07:20:58', 'App\\Models\\Order', 13, 4, NULL, NULL),
(26, '2023-09-15 07:20:58', '2023-09-15 07:20:58', 'App\\Models\\Order', 13, 7, NULL, NULL),
(27, '2023-09-15 07:23:25', '2023-09-15 07:23:25', 'App\\Models\\Order', 15, 4, NULL, NULL),
(28, '2023-09-15 07:28:43', '2023-09-15 07:28:43', 'App\\Models\\Order', 16, 3, NULL, NULL),
(29, '2023-09-15 07:28:53', '2023-09-15 07:28:53', 'App\\Models\\Order', 17, 3, NULL, NULL),
(30, '2023-09-15 07:29:04', '2023-09-15 07:29:04', 'App\\Models\\Order', 18, 3, NULL, NULL),
(31, '2023-09-15 07:29:34', '2023-09-15 07:29:34', 'App\\Models\\Order', 19, 3, NULL, NULL),
(32, '2023-09-15 07:29:48', '2023-09-15 07:29:48', 'App\\Models\\Order', 20, 3, NULL, NULL),
(33, '2023-09-15 07:30:00', '2023-09-15 07:30:00', 'App\\Models\\Order', 21, 3, NULL, NULL),
(34, '2023-09-15 07:34:05', '2023-09-15 07:34:05', 'App\\Models\\Order', 24, 4, NULL, NULL),
(35, '2023-09-15 07:34:05', '2023-09-15 07:34:05', 'App\\Models\\Order', 24, 7, NULL, NULL),
(36, '2023-09-15 07:36:05', '2023-09-15 07:36:05', 'App\\Models\\Order', 26, 4, NULL, NULL),
(37, '2023-09-15 07:36:05', '2023-09-15 07:36:05', 'App\\Models\\Order', 26, 4, NULL, NULL),
(38, '2023-09-15 07:36:05', '2023-09-15 07:36:05', 'App\\Models\\Order', 26, 4, NULL, NULL),
(39, '2023-09-15 07:36:05', '2023-09-15 07:36:05', 'App\\Models\\Order', 26, 7, NULL, NULL),
(40, '2023-09-15 07:37:11', '2023-09-15 07:37:11', 'App\\Models\\Order', 29, 4, NULL, NULL),
(41, '2023-09-15 07:37:11', '2023-09-15 07:37:11', 'App\\Models\\Order', 29, 7, NULL, NULL),
(42, '2023-09-15 07:38:27', '2023-09-15 07:38:27', 'App\\Models\\Order', 30, 4, NULL, NULL),
(43, '2023-09-15 07:38:27', '2023-09-15 07:38:27', 'App\\Models\\Order', 30, 7, NULL, NULL),
(44, '2023-09-15 07:38:27', '2023-09-15 07:38:27', 'App\\Models\\Order', 30, 3, NULL, NULL),
(45, '2023-09-15 07:40:35', '2023-09-15 07:40:35', 'App\\Models\\Order', 31, 4, NULL, NULL),
(46, '2023-09-15 07:40:35', '2023-09-15 07:40:35', 'App\\Models\\Order', 31, 7, NULL, NULL),
(47, '2023-09-15 07:40:35', '2023-09-15 07:40:35', 'App\\Models\\Order', 31, 3, NULL, NULL),
(48, '2023-09-15 13:53:47', '2023-09-15 13:53:47', 'App\\Models\\Product', 7, 5, NULL, NULL),
(50, '2023-09-15 14:17:30', '2023-09-15 14:17:30', 'App\\Models\\Order', 32, 7, NULL, NULL),
(51, '2023-09-15 14:17:30', '2023-09-15 14:17:30', 'App\\Models\\Order', 32, 5, NULL, NULL),
(52, '2023-09-17 16:38:14', '2023-09-17 16:38:14', 'App\\Models\\Order', 33, 4, NULL, NULL),
(53, '2023-09-19 06:48:20', '2023-09-19 06:48:20', 'App\\Models\\Product', 7, 6, NULL, NULL),
(62, '2023-09-19 07:53:31', '2023-09-19 07:53:31', 'App\\Models\\Product', 4, 5, NULL, NULL),
(63, '2023-09-20 13:30:22', '2023-09-20 13:30:22', 'App\\Models\\Order', 34, 4, NULL, NULL),
(64, '2023-09-20 13:30:22', '2023-09-20 13:30:22', 'App\\Models\\Order', 34, 7, NULL, NULL),
(65, '2023-09-20 13:30:22', '2023-09-20 13:30:22', 'App\\Models\\Order', 34, 5, NULL, NULL),
(66, '2023-09-20 13:30:22', '2023-09-20 13:30:22', 'App\\Models\\Order', 34, 6, NULL, NULL),
(67, '2023-09-22 05:43:33', '2023-09-22 05:43:33', 'App\\Models\\Order', 3, 4, NULL, NULL),
(68, '2023-09-22 05:43:33', '2023-09-22 05:43:33', 'App\\Models\\Order', 3, 5, NULL, NULL),
(69, '2023-09-22 05:43:33', '2023-09-22 05:43:33', 'App\\Models\\Order', 3, 3, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06'),
(2, 'filament_user', 'web', '2023-09-10 05:23:06', '2023-09-10 05:23:06');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1),
(3, 2),
(4, 1),
(4, 2),
(5, 1),
(5, 2),
(6, 1),
(6, 2),
(7, 1),
(7, 2),
(8, 1),
(8, 2),
(9, 1),
(9, 2),
(10, 1),
(10, 2),
(11, 1),
(11, 2),
(12, 1),
(12, 2),
(13, 1),
(13, 2),
(14, 1),
(14, 2),
(15, 1),
(15, 2),
(16, 1),
(16, 2),
(17, 1),
(17, 2),
(18, 1),
(18, 2),
(19, 1),
(19, 2),
(20, 1),
(20, 2),
(21, 1),
(21, 2),
(22, 1),
(22, 2),
(23, 1),
(23, 2),
(24, 1),
(24, 2),
(25, 1),
(25, 2),
(26, 1),
(26, 2),
(27, 1),
(27, 2),
(28, 1),
(28, 2),
(29, 1),
(29, 2),
(30, 1),
(30, 2),
(31, 1),
(31, 2),
(32, 1),
(32, 2),
(33, 1),
(33, 2),
(34, 1),
(34, 2),
(35, 1),
(35, 2),
(36, 1),
(36, 2),
(37, 1),
(37, 2),
(38, 1),
(38, 2),
(39, 1),
(39, 2),
(40, 1),
(40, 2),
(41, 1),
(41, 2),
(42, 1),
(42, 2),
(43, 1),
(43, 2),
(44, 1),
(44, 2),
(45, 1),
(45, 2),
(46, 1),
(46, 2),
(47, 1),
(47, 2),
(48, 1),
(48, 2),
(49, 1),
(49, 2),
(50, 1),
(50, 2),
(51, 1),
(51, 2),
(52, 1),
(52, 2),
(53, 1),
(53, 2),
(54, 1),
(54, 2),
(55, 1),
(55, 2),
(56, 1),
(56, 2),
(57, 1),
(57, 2),
(58, 1),
(58, 2),
(59, 1),
(59, 2),
(60, 1),
(60, 2),
(61, 1),
(61, 2),
(62, 1),
(62, 2),
(63, 1),
(63, 2),
(64, 1),
(64, 2),
(65, 1),
(65, 2),
(66, 1),
(66, 2);

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `created_at`, `updated_at`, `name`, `slug`) VALUES
(1, '2023-09-15 09:00:12', '2023-09-15 09:00:12', 'Team Amministrazione', 'team-amministrazione'),
(2, '2023-09-15 09:54:58', '2023-09-15 09:54:58', 'Ristorante Test', 'ristorante-test');

-- --------------------------------------------------------

--
-- Table structure for table `team_morph`
--

CREATE TABLE `team_morph` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `team_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team_morph`
--

INSERT INTO `team_morph` (`id`, `created_at`, `updated_at`, `model_type`, `model_id`, `team_id`, `user_id`) VALUES
(2, '2023-09-15 09:53:37', '2023-09-15 09:53:37', 'App\\Models\\Category', 6, 1, NULL),
(3, '2023-09-15 09:54:36', '2023-09-15 09:54:36', 'App\\Models\\User', 3, 1, NULL),
(5, '2023-09-15 09:55:11', '2023-09-15 09:55:11', 'App\\Models\\User', 2, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `team_user`
--

CREATE TABLE `team_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `team_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team_user`
--

INSERT INTO `team_user` (`id`, `created_at`, `updated_at`, `team_id`, `user_id`) VALUES
(1, NULL, NULL, 1, 3);

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
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(2, 'Davide', 'davidecavallini1987@gmail.com', NULL, '$2y$10$vuJQuA4zTddxAnzSlh0QLeaw9RR15O/B4hrp70z/./U4NF/0bEu36', NULL, '2023-09-09 12:36:31', '2023-09-09 12:36:31'),
(3, 'Test', 'test@test.it', NULL, '$2y$10$eiEQhvVmAQVR5rvkXnzg2eQjUewFdjoff2NhlfQ.5xg1/r0RlbvuC', NULL, '2023-09-12 10:39:07', '2023-09-12 10:39:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `address_morph`
--
ALTER TABLE `address_morph`
  ADD PRIMARY KEY (`id`),
  ADD KEY `address_morph_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `address_morph_address_id_index` (`address_id`),
  ADD KEY `address_morph_user_id_index` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `discount_coupons`
--
ALTER TABLE `discount_coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `discount_coupons_code_unique` (`code`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_morph`
--
ALTER TABLE `order_morph`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_morph_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `order_morph_order_id_index` (`order_id`),
  ADD KEY `order_morph_user_id_index` (`user_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pages_slug_unique` (`slug`),
  ADD KEY `pages_parent_id_foreign` (`parent_id`),
  ADD KEY `pages_title_index` (`title`),
  ADD KEY `pages_layout_index` (`layout`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_user_id_index` (`user_id`);

--
-- Indexes for table `product_morph`
--
ALTER TABLE `product_morph`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_morph_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `product_morph_product_id_index` (`product_id`),
  ADD KEY `product_morph_user_id_index` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team_morph`
--
ALTER TABLE `team_morph`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_morph_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `team_morph_team_id_index` (`team_id`),
  ADD KEY `team_morph_user_id_index` (`user_id`);

--
-- Indexes for table `team_user`
--
ALTER TABLE `team_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `address_morph`
--
ALTER TABLE `address_morph`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `discount_coupons`
--
ALTER TABLE `discount_coupons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_morph`
--
ALTER TABLE `order_morph`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `product_morph`
--
ALTER TABLE `product_morph`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `team_morph`
--
ALTER TABLE `team_morph`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `team_user`
--
ALTER TABLE `team_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
