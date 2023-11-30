-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versione server:              8.0.30 - MySQL Community Server - GPL
-- S.O. server:                  Win64
-- HeidiSQL Versione:            12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dump della struttura di tabella ecommerce.addresses
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `nation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `municipality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.addresses: ~0 rows (circa)

-- Dump della struttura di tabella ecommerce.address_morph
CREATE TABLE IF NOT EXISTS `address_morph` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `address_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `address_morph_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `address_morph_address_id_index` (`address_id`),
  KEY `address_morph_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.address_morph: ~0 rows (circa)

-- Dump della struttura di tabella ecommerce.agendas
CREATE TABLE IF NOT EXISTS `agendas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.agendas: ~0 rows (circa)

-- Dump della struttura di tabella ecommerce.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `featured_image_id` int DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_column` int DEFAULT NULL,
  `is_hidden` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.categories: ~5 rows (circa)
INSERT INTO `categories` (`id`, `created_at`, `updated_at`, `name`, `featured_image_id`, `slug`, `order_column`, `is_hidden`) VALUES
	(1, '2023-09-10 11:48:50', '2023-11-30 13:35:55', 'APPARTAMENTI', 6, 'appartamenti', NULL, 0),
	(2, '2023-11-27 09:04:44', '2023-11-30 13:58:01', 'VILLE/VILLETTE - BIFAMIGLIARI/TRIFAMIGLIARI', 16, 'ville', NULL, 0),
	(3, '2023-11-27 09:06:02', '2023-11-30 13:45:24', 'ATTICO/ MANSARDA', 11, 'attico', NULL, 0),
	(4, '2023-11-27 09:07:59', '2023-11-30 13:49:22', 'GARAGE/ BOX - POSTO AUTO', 13, 'garage', NULL, 0),
	(5, '2023-11-27 09:09:02', '2023-11-30 13:53:33', 'ATTIVITA\' COMMERCIALI', 15, 'attività commerciali', NULL, 0);

-- Dump della struttura di tabella ecommerce.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.failed_jobs: ~0 rows (circa)

-- Dump della struttura di tabella ecommerce.media
CREATE TABLE IF NOT EXISTS `media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `directory` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'media',
  `visibility` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `width` int unsigned DEFAULT NULL,
  `height` int unsigned DEFAULT NULL,
  `size` int unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'image',
  `ext` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `caption` text COLLATE utf8mb4_unicode_ci,
  `exif` text COLLATE utf8mb4_unicode_ci,
  `curations` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.media: ~16 rows (circa)
INSERT INTO `media` (`id`, `disk`, `directory`, `visibility`, `name`, `path`, `width`, `height`, `size`, `type`, `ext`, `alt`, `title`, `description`, `caption`, `exif`, `curations`, `created_at`, `updated_at`) VALUES
	(1, 'public', 'media', 'public', '04007a10-25cb-4ee1-8290-2bb7d2065532-1694353717', 'media/04007a10-25cb-4ee1-8290-2bb7d2065532-1694353717.jpg', 1200, 1800, 286992, 'image/jpeg', 'jpg', NULL, 'TMD-Antipasti-WEB-13', NULL, NULL, '{"FileName":"DxGPdiNJW2tA0T0K5S1qDMIg8Q47fr-metaVE1ELUFudGlwYXN0aS1XRUItMTMuanBn-.jpg","FileDateTime":1694353712,"FileSize":286992,"FileType":2,"MimeType":"image\\/jpeg","SectionsFound":"","COMPUTED":{"html":"width=\\"1200\\" height=\\"1800\\"","Height":1800,"Width":1200,"IsColor":1}}', NULL, '2023-09-10 11:48:37', '2023-09-10 11:48:37'),
	(2, 'public', 'media', 'public', '16448a3e-897b-4912-a539-30359c7668a6-1694535431', 'media/16448a3e-897b-4912-a539-30359c7668a6-1694535431.jpg', 1200, 1800, 580056, 'image/jpeg', 'jpg', NULL, 'Tagliere-22', NULL, NULL, '{"FileName":"xieC7tDtWxW70STq2HjZUulqiDwQ40-metaVGFnbGllcmUtMjIuanBn-.jpg","FileDateTime":1694535424,"FileSize":580056,"FileType":2,"MimeType":"image\\/jpeg","SectionsFound":"","COMPUTED":{"html":"width=\\"1200\\" height=\\"1800\\"","Height":1800,"Width":1200,"IsColor":1}}', NULL, '2023-09-12 14:17:11', '2023-09-12 14:17:11'),
	(3, 'public', 'media', 'public', 'd048e153-0f5f-427a-897f-8ccf60a2d894-1697555586', 'media/d048e153-0f5f-427a-897f-8ccf60a2d894-1697555586.jpg', 225, 225, 10413, 'image/jpeg', 'jpg', NULL, 'ristorantedodici', NULL, NULL, '{"FileName":"y7qDEUEimQqOD1nYHFx8LBoI2s0qkw-metacmlzdG9yYW50ZWRvZGljaS5qcGc=-.jpg","FileDateTime":1697555577,"FileSize":10413,"FileType":2,"MimeType":"image\\/jpeg","SectionsFound":"","COMPUTED":{"html":"width=\\"225\\" height=\\"225\\"","Height":225,"Width":225,"IsColor":1}}', NULL, '2023-10-17 13:13:06', '2023-10-17 13:13:06'),
	(4, 'public', 'media', 'public', 'a1e346c4-b7ab-49fa-8b5f-d148a1adce3b-1697556165', 'media/a1e346c4-b7ab-49fa-8b5f-d148a1adce3b-1697556165.jpg', 705, 419, 118044, 'image/jpeg', 'jpg', NULL, 'ristorantetredici', NULL, NULL, '{"FileName":"12FgUWM3OXk6xKJ42S5YaKtJQhwhzE-metacmlzdG9yYW50ZXRyZWRpY2kuanBn-.jpg","FileDateTime":1697556156,"FileSize":118044,"FileType":2,"MimeType":"image\\/jpeg","SectionsFound":"","COMPUTED":{"html":"width=\\"705\\" height=\\"419\\"","Height":419,"Width":705,"IsColor":1}}', NULL, '2023-10-17 13:22:45', '2023-10-17 13:22:45'),
	(5, 'public', 'media', 'public', '6e9a0e35-671a-499e-b99b-ff0d73020498', 'media/6e9a0e35-671a-499e-b99b-ff0d73020498.jpg', 1680, 945, 115888, 'image/jpeg', 'jpg', NULL, 'casa1', NULL, NULL, '{"FileName":"0FJkd1YrJKfHI3WRg0Hfac6pCUyUTh-metaY2FzYTEuanBn-.jpg","FileDateTime":1701081373,"FileSize":115888,"FileType":2,"MimeType":"image\\/jpeg","SectionsFound":"","COMPUTED":{"html":"width=\\"1680\\" height=\\"945\\"","Height":945,"Width":1680,"IsColor":1}}', NULL, '2023-11-27 09:36:18', '2023-11-27 09:36:18'),
	(6, 'public', 'media', 'public', '7cbd9754-f32b-463a-9f35-558e5ff9c562', 'media/7cbd9754-f32b-463a-9f35-558e5ff9c562.jpg', 612, 408, 42950, 'image/jpeg', 'jpg', NULL, 'appartamenti_1', NULL, NULL, '{"FileName":"9OKWBKljWHDVpVfymKVPrfonCAtXOi-metaYXBwYXJ0YW1lbnRpXzEuanBn-.jpg","FileDateTime":1701354888,"FileSize":42950,"FileType":2,"MimeType":"image\\/jpeg","SectionsFound":"","COMPUTED":{"html":"width=\\"612\\" height=\\"408\\"","Height":408,"Width":612,"IsColor":1}}', NULL, '2023-11-30 13:35:22', '2023-11-30 13:35:22'),
	(7, 'public', 'media', 'public', 'fa748da4-e2b4-4c6f-93c2-9c48517b799a', 'media/fa748da4-e2b4-4c6f-93c2-9c48517b799a.png', 974, 658, 1081911, 'image/png', 'png', NULL, 'villacongiardino_1', NULL, NULL, NULL, NULL, '2023-11-30 13:40:51', '2023-11-30 13:40:51'),
	(8, 'public', 'media', 'public', '224ea6e5-1e23-48cf-9ed8-bc0373487424', 'media/224ea6e5-1e23-48cf-9ed8-bc0373487424.png', 974, 658, 1081911, 'image/png', 'png', NULL, 'villacongiardino_1', NULL, NULL, NULL, NULL, '2023-11-30 13:42:24', '2023-11-30 13:42:24'),
	(9, 'public', 'media', 'public', '53757fc0-deb7-4c0b-aa40-58deb7a1e5be', 'media/53757fc0-deb7-4c0b-aa40-58deb7a1e5be.png', 974, 658, 1081911, 'image/png', 'png', NULL, 'villacongiardino_1', NULL, NULL, NULL, NULL, '2023-11-30 13:42:24', '2023-11-30 13:42:24'),
	(10, 'public', 'media', 'public', '45f53519-f09c-40b4-bb60-931db66f8f0b', 'media/45f53519-f09c-40b4-bb60-931db66f8f0b.jpg', 900, 675, 126283, 'image/jpeg', 'jpg', NULL, 'attico-casa_1', NULL, NULL, '{"FileName":"bRgXO9yBdJlcYeJbdXFBYPnn1FnMIB-metaYXR0aWNvLWNhc2FfMS5qcGc=-.jpg","FileDateTime":1701355496,"FileSize":126283,"FileType":2,"MimeType":"image\\/jpeg","SectionsFound":"","COMPUTED":{"html":"width=\\"900\\" height=\\"675\\"","Height":675,"Width":900,"IsColor":1}}', NULL, '2023-11-30 13:45:12', '2023-11-30 13:45:12'),
	(11, 'public', 'media', 'public', '653d1506-bbb4-476e-90ce-dc2618918ebc', 'media/653d1506-bbb4-476e-90ce-dc2618918ebc.jpg', 900, 675, 126283, 'image/jpeg', 'jpg', NULL, 'attico-casa_1', NULL, NULL, '{"FileName":"UDfOIhOMfmWm0AQz2GHCpdXyhuvyqX-metaYXR0aWNvLWNhc2FfMS5qcGc=-.jpg","FileDateTime":1701355506,"FileSize":126283,"FileType":2,"MimeType":"image\\/jpeg","SectionsFound":"","COMPUTED":{"html":"width=\\"900\\" height=\\"675\\"","Height":675,"Width":900,"IsColor":1}}', NULL, '2023-11-30 13:45:12', '2023-11-30 13:45:12'),
	(12, 'public', 'media', 'public', '40190635-1150-49e8-b6f1-0e5db063a6a8', 'media/40190635-1150-49e8-b6f1-0e5db063a6a8.jpg', 300, 300, 31336, 'image/jpeg', 'jpg', NULL, 'postoauto_1', NULL, NULL, '{"FileName":"UsiBQYh24x6VnfMaSnHUeqFEH4iXbs-metacG9zdG9hdXRvXzEuanBn-.jpg","FileDateTime":1701355732,"FileSize":31336,"FileType":2,"MimeType":"image\\/jpeg","SectionsFound":"","COMPUTED":{"html":"width=\\"300\\" height=\\"300\\"","Height":300,"Width":300,"IsColor":1}}', NULL, '2023-11-30 13:49:08', '2023-11-30 13:49:08'),
	(13, 'public', 'media', 'public', 'b6423d93-bcd6-4f0b-9c38-325ddafd156a', 'media/b6423d93-bcd6-4f0b-9c38-325ddafd156a.jpg', 300, 300, 31336, 'image/jpeg', 'jpg', NULL, 'postoauto_1', NULL, NULL, '{"FileName":"5RYLoyfDNYp4cOKmEQhyx0L3EiUUHg-metacG9zdG9hdXRvXzEuanBn-.jpg","FileDateTime":1701355743,"FileSize":31336,"FileType":2,"MimeType":"image\\/jpeg","SectionsFound":"","COMPUTED":{"html":"width=\\"300\\" height=\\"300\\"","Height":300,"Width":300,"IsColor":1}}', NULL, '2023-11-30 13:49:08', '2023-11-30 13:49:08'),
	(14, 'public', 'media', 'public', '2c099fcd-0bde-4d86-9234-29f4073f0dd2', 'media/2c099fcd-0bde-4d86-9234-29f4073f0dd2.jpg', 462, 306, 49496, 'image/jpeg', 'jpg', NULL, 'attivitacommerciali_1', NULL, NULL, '{"FileName":"VrzvLjCJEixCHwGPffABeM1wyA8DIK-metaYXR0aXZpdGFjb21tZXJjaWFsaV8xLmpwZw==-.jpg","FileDateTime":1701355987,"FileSize":49496,"FileType":2,"MimeType":"image\\/jpeg","SectionsFound":"","COMPUTED":{"html":"width=\\"462\\" height=\\"306\\"","Height":306,"Width":462,"IsColor":1}}', NULL, '2023-11-30 13:53:20', '2023-11-30 13:53:20'),
	(15, 'public', 'media', 'public', 'a8b52a12-daa7-4b9a-8c4b-f4c2efb2d972', 'media/a8b52a12-daa7-4b9a-8c4b-f4c2efb2d972.jpg', 462, 306, 49496, 'image/jpeg', 'jpg', NULL, 'attivitacommerciali_1', NULL, NULL, '{"FileName":"tDLnEHdvPwfQ7wnWCHWBIuG4O3iyLU-metaYXR0aXZpdGFjb21tZXJjaWFsaV8xLmpwZw==-.jpg","FileDateTime":1701355996,"FileSize":49496,"FileType":2,"MimeType":"image\\/jpeg","SectionsFound":"","COMPUTED":{"html":"width=\\"462\\" height=\\"306\\"","Height":306,"Width":462,"IsColor":1}}', NULL, '2023-11-30 13:53:20', '2023-11-30 13:53:20'),
	(16, 'public', 'media', 'public', '1d9f6b40-02a2-4c6c-8f3d-df88a4c6419f', 'media/1d9f6b40-02a2-4c6c-8f3d-df88a4c6419f.jpg', 2560, 1440, 746239, 'image/jpeg', 'jpg', NULL, 'villetta_2', NULL, NULL, '{"FileName":"TaSVTDe4orYSeAElgceMsDkRZLJRF1-metadmlsbGV0dGFfMi5qcGc=-.jpg","FileDateTime":1701356254,"FileSize":746239,"FileType":2,"MimeType":"image\\/jpeg","SectionsFound":"","COMPUTED":{"html":"width=\\"2560\\" height=\\"1440\\"","Height":1440,"Width":2560,"IsColor":1}}', NULL, '2023-11-30 13:57:49', '2023-11-30 13:57:49');

-- Dump della struttura di tabella ecommerce.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.migrations: ~26 rows (circa)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2014_10_12_000000_create_users_table', 1),
	(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
	(3, '2019_08_19_000000_create_failed_jobs_table', 1),
	(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
	(5, '2023_09_09_144038_create_pages_table', 1),
	(6, '2023_09_09_151544_create_categories_table', 1),
	(7, '2023_09_09_152658_create_media_table', 1),
	(8, '2023_09_09_160609_add_image_to_categories', 1),
	(10, '2023_09_10_072255_create_permission_tables', 2),
	(11, '2023_09_11_114527_create_products_table', 3),
	(12, '2023_09_11_115859_create_product_morph_table', 3),
	(13, '2023_09_11_172603_add_featured_image_id_to_products', 3),
	(14, '2023_09_13_162302_create_addresses_table', 4),
	(15, '2023_09_13_162521_create_address_morph_table', 4),
	(16, '2023_09_13_162521_create_order_morph_table', 4),
	(17, '2023_09_14_093927_create_orders_table', 4),
	(18, '2023_09_15_104037_create_teams_table', 4),
	(19, '2023_09_15_104239_create_team_morph_table', 4),
	(20, '2023_09_15_135624_add_slug_to_categories', 4),
	(21, '2023_09_15_135701_add_slug_to_products', 4),
	(22, '2023_09_19_092943_add_option_to_product_morph', 4),
	(23, '2023_09_14_093928_create_orders_table', 5),
	(24, '2023_09_22_083755_add_order_column_to_products', 6),
	(25, '2023_09_22_083759_add_order_column_to_categories', 6),
	(26, '2023_09_27_083726_add_hidden_column_to_categories', 6),
	(27, '2023_09_27_090347_add_weight_to_products', 7),
	(28, '2023_09_27_093331_add_type_to_product_morph', 7),
	(29, '2023_09_29_090952_create_tables_table', 7),
	(30, '2023_10_05_170736_create_reservations_table', 8),
	(31, '2023_10_05_174215_create_agendas_table', 9);

-- Dump della struttura di tabella ecommerce.model_has_permissions
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.model_has_permissions: ~0 rows (circa)

-- Dump della struttura di tabella ecommerce.model_has_roles
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.model_has_roles: ~2 rows (circa)
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
	(1, 'App\\Models\\User', 1),
	(2, 'App\\Models\\User', 1);

-- Dump della struttura di tabella ecommerce.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `delivery_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.orders: ~0 rows (circa)

-- Dump della struttura di tabella ecommerce.order_morph
CREATE TABLE IF NOT EXISTS `order_morph` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `order_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_morph_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `order_morph_order_id_index` (`order_id`),
  KEY `order_morph_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.order_morph: ~0 rows (circa)

-- Dump della struttura di tabella ecommerce.pages
CREATE TABLE IF NOT EXISTS `pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `layout` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `blocks` json NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`),
  KEY `pages_parent_id_foreign` (`parent_id`),
  KEY `pages_title_index` (`title`),
  KEY `pages_layout_index` (`layout`),
  CONSTRAINT `pages_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.pages: ~6 rows (circa)
INSERT INTO `pages` (`id`, `title`, `slug`, `layout`, `blocks`, `parent_id`, `created_at`, `updated_at`) VALUES
	(1, 'scegli categoria', 'categories', 'simple', '[{"data": {"link_one": "/", "link_two": null, "logo_url": "logo.png", "text_one": "HOME", "text_two": null, "link_four": null, "text_four": null, "link_three": null, "text_three": null, "cart_enabled": false}, "type": "header-one"}, {"data": [], "type": "breadcrumbs"}, {"data": {"second_button": false}, "type": "model-list"}]', NULL, '2023-09-10 11:33:21', '2023-11-30 14:41:00'),
	(2, 'PRODOTTI', 'products', 'simple', '[{"data": {"link_one": "/", "link_two": null, "logo_url": "#", "text_one": "HOME", "text_two": null, "link_four": null, "text_four": null, "link_three": null, "text_three": null, "cart_enabled": false}, "type": "header-one"}, {"data": [], "type": "breadcrumbs"}, {"data": [], "type": "real-estate-item"}]', NULL, '2023-09-12 14:24:35', '2023-11-30 14:45:02'),
	(3, 'CARRELLO', 'cart', 'simple', '[{"data": [], "type": "breadcrumbs"}, {"data": {"next_link": "/delivery-options", "back_to_shop_link": "/categories"}, "type": "cart"}]', NULL, '2023-09-12 14:50:55', '2023-09-19 15:06:39'),
	(4, 'ARRIVEDERCI', 'order-completed', 'simple', '[{"data": [], "type": "breadcrumbs"}, {"data": {"title": "Grazie per averci scelto", "subtitle": "Saremo felici di servirti nuovamente. A presto!", "button_link": "/categories", "button_text": "Ritorna al menù"}, "type": "thank-you"}]', NULL, '2023-09-13 15:43:09', '2023-09-19 14:48:13'),
	(5, 'OPZIONI CONSEGNA', 'delivery-options', 'simple', '[{"data": [], "type": "breadcrumbs"}, {"data": [], "type": "select-delivery-options"}]', NULL, '2023-09-19 14:52:07', '2023-09-19 14:52:07'),
	(6, 'AGGIUNTE', 'variations', 'default', '[{"data": [], "type": "breadcrumbs"}, {"data": {"second_button": false}, "type": "model-list"}]', NULL, '2023-09-19 14:54:24', '2023-09-19 14:54:24'),
	(7, 'HOME', 'home', 'simple', '[{"data": {"link_one": "#", "link_two": "#chi-siamo", "logo_url": "logo.png", "text_one": "HOME", "text_two": "CHI SIAMO", "link_four": "#contact-form", "text_four": "CONTATTI", "link_three": "#servizi", "text_three": "SERVIZI", "cart_enabled": false}, "type": "header-one"}, {"data": {"text_one": "IMMOBILIARE CA\' ROSSA", "text_two": "L\'Arte di Abitare", "image_url": "piazza-ferretto-a-mestre.jpg", "link_button": "/categories", "text_button": "RICERCA LA TUA CASA", "link_second_button": "#contact-form", "text_second_button": "VENDI LA TUA CASA"}, "type": "hero-background-image"}, {"data": {"anchor": "chi-siamo", "link_one": "http://127.0.0.1:8000/images/carossa_orz.jpg", "link_two": "http://127.0.0.1:8000/images/carossa_vert.jpg", "text_one": "CHI SIAMO", "text_six": null, "text_ten": null, "text_two": null, "text_five": "CA\' ROSSA IMMOBILIARE grazie alla pluriennale esperienza del suo team, attento e preparato, crea vero valore aggiunto per i propri clienti nel servizio di intermediazione immobiliare, sempre nel rispetto dei valori di onestà, correttezza, puntualità e professionalità.", "text_four": " E nei tuoi Sogni c’è la tua Casa.", "text_nine": "Il nostro mix di esperienza e innovazione è il nuovo modo di concepire l’intermediazione immobiliare: un servizio dinamico in funzione degli obiettivi del cliente, per dare soluzioni anche alle problematiche più complesse.", "anchor_two": "servizi", "text_eight": null, "text_seven": "La vostra trattativa, quindi, sarà condotta dall’Agenzia come se foste voi stessi a farlo, con la stessa cura, la stessa dedizione e lo stesso senso di responsabilità.", "text_three": "“La casa è il vostro corpo più grande. Vive nel Sole e si addormenta nella quiete della notte ed è piena di sogni” (Kahlil Gibran)  ", "text_eleven": "SERVIZI", "text_twelve": "CA\' ROSSA IMMOBILIARE mette a disposizione del cliente un servizio completo per garantire una vendita o un acquisto senza problemi, fornendo assistenza sia al venditore sia all’acquirente a partire dalla determinazione delle esigenze fino al rogito notarile, con beneficio e soddisfazione di entrambi. CA\' ROSSA IMMOBILIARE si occupa anche di servizi d’affitto/locazione.", "text_fifteen": "SCOPRI I SERVIZI PER VENDERE", "text_sixteen": "Stima del vostro immobile: viene redatta la stima gratuitamente e senza impegno, a valori di mercato. Fotografie Professionali dell’immobile Assistenza nelle visite all’immobile Assistenza catastale (Planimetrie, volture, visure) Acquisizione attestato di prestazione energetica (APE) Consulenza ed assistenza notarile fino al rogito Pubblicità: pubblicità dell’immobile con cartelli esposti nelle vetrine delle nostre sedi, cartelli portone su proprietà, con supporti cartacei (riviste specializzate, inserimento nei pieghevoli collocati negli espositori presso le nostre sedi o distribuiti in diverse zone della città). Ricerca dell’acquirente nella nostra ampia banca dati aggiornata in tempo reale, collaborazione con agenzie collegate al fine di ampliare la possibilità di vendita.", "text_eighteen": "CA\' ROSSA IMMOBILIARE è in grado di affittare al meglio il vostro immobile, garantendovi tranquillità sui futuri inquilini, ed indirizzandovi verso la tipologia di contratto più idonea alle vostre esigenze. Servizio di compilazione, registrazione, pagamento annualità successive, rinnovi e risoluzioni per via telematica, quindi direttamente nei nostri uffici senza bisogno di andare all’Ufficio delle Entrate o in banca per i versamenti.", "text_fourteen": "Acquisto sicuro: per chi vuole acquistare un immobile CA\' ROSSA IMMOBILIARE presenta tutta la documentazione che ne attesti la regolarità (atto di provenienza, documenti catastali, visure ipotecarie, eventuali concessioni edilizie, ecc.). Una volta comprovata la validità dei documenti, si procede alla sottoscrizione del contratto preliminare. Inserimento del nominativo di chi intende ricercare un immobile, nella nostra banca dati. Mutui: CA\' ROSSA IMMOBILIARE offre la propria esperienza per trovare, sulla base delle esigenze del cliente, il mutuo più conveniente in base alle convenzioni stipulate con i principali istituti bancari, ed anche avvalendosi della consulenza di professionisti del settore. Assistenza post-preliminare: consulenza, assistenza e presenza dei nostri funzionari sino a rogito.", "text_thirteen": "SCOPRI I SERVIZI PER ACQUISTARE", "text_seventeen": "SCOPRI I NOSTRI SERVIZI PER LOCARE"}, "type": "feature-one"}, {"data": [], "type": "contact-form"}]', NULL, '2023-09-26 07:35:31', '2023-11-30 14:56:57');

-- Dump della struttura di tabella ecommerce.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.password_reset_tokens: ~0 rows (circa)

-- Dump della struttura di tabella ecommerce.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.permissions: ~62 rows (circa)
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'view_category', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(2, 'view_any_category', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(3, 'create_category', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(4, 'update_category', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(5, 'restore_category', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(6, 'restore_any_category', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(7, 'replicate_category', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(8, 'reorder_category', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(9, 'delete_category', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(10, 'delete_any_category', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(11, 'force_delete_category', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(12, 'force_delete_any_category', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(13, 'view_media', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(14, 'view_any_media', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(15, 'create_media', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(16, 'update_media', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(17, 'restore_media', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(18, 'restore_any_media', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(19, 'replicate_media', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(20, 'reorder_media', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(21, 'delete_media', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(22, 'delete_any_media', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(23, 'force_delete_media', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(24, 'force_delete_any_media', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(25, 'view_page', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(26, 'view_any_page', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(27, 'create_page', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(28, 'update_page', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(29, 'restore_page', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(30, 'restore_any_page', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(31, 'replicate_page', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(32, 'reorder_page', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(33, 'delete_page', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(34, 'delete_any_page', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(35, 'force_delete_page', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(36, 'force_delete_any_page', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(37, 'view_role', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(38, 'view_any_role', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(39, 'create_role', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(40, 'update_role', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(41, 'delete_role', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(42, 'delete_any_role', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(43, 'view_user', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(44, 'view_any_user', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(45, 'create_user', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(46, 'update_user', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(47, 'restore_user', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(48, 'restore_any_user', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(49, 'replicate_user', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(50, 'reorder_user', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(51, 'delete_user', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(52, 'delete_any_user', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(53, 'force_delete_user', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(54, 'force_delete_any_user', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(55, 'view_product', 'web', '2023-09-12 14:08:52', '2023-09-12 14:08:52'),
	(56, 'view_any_product', 'web', '2023-09-12 14:08:52', '2023-09-12 14:08:52'),
	(57, 'create_product', 'web', '2023-09-12 14:08:52', '2023-09-12 14:08:52'),
	(58, 'update_product', 'web', '2023-09-12 14:08:52', '2023-09-12 14:08:52'),
	(59, 'restore_product', 'web', '2023-09-12 14:08:52', '2023-09-12 14:08:52'),
	(60, 'restore_any_product', 'web', '2023-09-12 14:08:52', '2023-09-12 14:08:52'),
	(61, 'replicate_product', 'web', '2023-09-12 14:08:52', '2023-09-12 14:08:52'),
	(62, 'reorder_product', 'web', '2023-09-12 14:08:52', '2023-09-12 14:08:52'),
	(63, 'delete_product', 'web', '2023-09-12 14:08:52', '2023-09-12 14:08:52'),
	(64, 'delete_any_product', 'web', '2023-09-12 14:08:52', '2023-09-12 14:08:52'),
	(65, 'force_delete_product', 'web', '2023-09-12 14:08:52', '2023-09-12 14:08:52'),
	(66, 'force_delete_any_product', 'web', '2023-09-12 14:08:52', '2023-09-12 14:08:52');

-- Dump della struttura di tabella ecommerce.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.personal_access_tokens: ~0 rows (circa)

-- Dump della struttura di tabella ecommerce.products
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(8,2) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `featured_image_id` int DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_column` int DEFAULT NULL,
  `weight` decimal(8,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `products_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.products: ~4 rows (circa)
INSERT INTO `products` (`id`, `created_at`, `updated_at`, `name`, `description`, `price`, `user_id`, `featured_image_id`, `slug`, `order_column`, `weight`) VALUES
	(2, '2023-11-27 09:31:41', '2023-11-30 08:27:35', 'Appartamento corso del Popolo', 'MESTRE VICINANZE PIAZZA BARCHE\n\nMestre, pezzo unico, stupendo, quinto e ultimo piano panoramico a 270°, vicinanze piazza barche, (7 minuti a piedi) signorile appartamento su recente costruzione, qualita\' di finiture interne da intenditori, composto da salone diviso dal salotto, (mq. 70) cucina abitabile, tre camere, due bagni padronali, tre terrazze capienti per poter ospitare tavoli per pranzi e cene in compagnia, garage e posto auto esclusivo. Da vedere! rif. E 19 per info giovanni.', 599.00, NULL, 5, 'appartamento-corso-popolo', NULL, 0.00),
	(3, '2023-11-30 14:04:41', '2023-11-30 14:08:46', 'Appartamento Piazza Ferreto', NULL, 200.00, NULL, NULL, '', NULL, 0.00),
	(4, '2023-11-30 14:05:37', '2023-11-30 14:08:26', 'Appartamento Via Cibrario', NULL, 120.00, NULL, NULL, '', NULL, 0.00),
	(5, '2023-11-30 14:06:18', '2023-11-30 14:08:03', 'Appartamento Via P.F. Calvi', NULL, 130.00, NULL, NULL, '', NULL, 0.00);

-- Dump della struttura di tabella ecommerce.product_morph
CREATE TABLE IF NOT EXISTS `product_morph` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `product_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `option` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_morph_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `product_morph_product_id_index` (`product_id`),
  KEY `product_morph_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.product_morph: ~5 rows (circa)
INSERT INTO `product_morph` (`id`, `created_at`, `updated_at`, `model_type`, `model_id`, `product_id`, `user_id`, `option`, `type`) VALUES
	(1, '2023-09-12 14:17:34', '2023-09-12 14:17:34', 'App\\Models\\Category', 1, 1, NULL, NULL, NULL),
	(2, '2023-11-27 09:31:41', '2023-11-27 09:31:41', 'App\\Models\\Category', 1, 2, NULL, NULL, NULL),
	(3, '2023-11-30 14:04:41', '2023-11-30 14:04:41', 'App\\Models\\Category', 1, 3, NULL, NULL, NULL),
	(4, '2023-11-30 14:05:38', '2023-11-30 14:05:38', 'App\\Models\\Category', 1, 4, NULL, NULL, NULL),
	(5, '2023-11-30 14:06:18', '2023-11-30 14:06:18', 'App\\Models\\Category', 1, 5, NULL, NULL, NULL);

-- Dump della struttura di tabella ecommerce.reservations
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `people_number` int NOT NULL DEFAULT '1',
  `date_time` datetime NOT NULL,
  `telephone_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `allergens` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.reservations: ~2 rows (circa)
INSERT INTO `reservations` (`id`, `created_at`, `updated_at`, `name`, `people_number`, `date_time`, `telephone_number`, `allergens`, `user_id`) VALUES
	(1, '2023-10-06 13:28:34', '2023-10-06 13:28:34', 'Mario Rossi', 3, '2023-10-20 20:30:00', '3519888896', 'kiwi, legumi, grano, pollo, suino', NULL),
	(2, '2023-10-06 14:31:26', '2023-10-06 14:31:26', 'Test', 1, '2023-10-08 20:30:18', '', NULL, NULL),
	(3, '2023-10-06 14:31:57', '2023-10-06 14:31:57', 'rrrrr', 1, '0000-00-00 00:00:00', '', NULL, NULL);

-- Dump della struttura di tabella ecommerce.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.roles: ~2 rows (circa)
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'super_admin', 'web', '2023-09-10 11:46:57', '2023-09-10 11:46:57'),
	(2, 'filament_user', 'web', '2023-09-10 11:46:58', '2023-09-10 11:46:58');

-- Dump della struttura di tabella ecommerce.role_has_permissions
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.role_has_permissions: ~66 rows (circa)
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
	(1, 1),
	(2, 1),
	(3, 1),
	(4, 1),
	(5, 1),
	(6, 1),
	(7, 1),
	(8, 1),
	(9, 1),
	(10, 1),
	(11, 1),
	(12, 1),
	(13, 1),
	(14, 1),
	(15, 1),
	(16, 1),
	(17, 1),
	(18, 1),
	(19, 1),
	(20, 1),
	(21, 1),
	(22, 1),
	(23, 1),
	(24, 1),
	(25, 1),
	(26, 1),
	(27, 1),
	(28, 1),
	(29, 1),
	(30, 1),
	(31, 1),
	(32, 1),
	(33, 1),
	(34, 1),
	(35, 1),
	(36, 1),
	(37, 1),
	(38, 1),
	(39, 1),
	(40, 1),
	(41, 1),
	(42, 1),
	(43, 1),
	(44, 1),
	(45, 1),
	(46, 1),
	(47, 1),
	(48, 1),
	(49, 1),
	(50, 1),
	(51, 1),
	(52, 1),
	(53, 1),
	(54, 1),
	(55, 1),
	(56, 1),
	(57, 1),
	(58, 1),
	(59, 1),
	(60, 1),
	(61, 1),
	(62, 1),
	(63, 1),
	(64, 1),
	(65, 1),
	(66, 1);

-- Dump della struttura di tabella ecommerce.tables
CREATE TABLE IF NOT EXISTS `tables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seats` int DEFAULT NULL,
  `zone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `x` double(8,2) DEFAULT NULL,
  `y` double(8,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.tables: ~0 rows (circa)

-- Dump della struttura di tabella ecommerce.teams
CREATE TABLE IF NOT EXISTS `teams` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.teams: ~0 rows (circa)

-- Dump della struttura di tabella ecommerce.team_morph
CREATE TABLE IF NOT EXISTS `team_morph` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `team_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_morph_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `team_morph_team_id_index` (`team_id`),
  KEY `team_morph_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.team_morph: ~0 rows (circa)

-- Dump della struttura di tabella ecommerce.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dump dei dati della tabella ecommerce.users: ~0 rows (circa)
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'Margherita', 'marghi8210@gmail.com', NULL, '$2y$10$fdCA54q5U02RzoEz.HDGcew7lF2Hp6Xbu1m8JlXHVcAhHuZ/2tYOK', 'xFyxYZ06gW4DIgFVPycYyhwGq9m4t7UT5DS5kkHZgdKRhcMOZfw1LxXN4Mxd', '2023-09-10 11:14:50', '2023-09-10 11:14:50');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
