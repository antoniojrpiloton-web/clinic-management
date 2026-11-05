-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               9.1.0 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for clinic_management
DROP DATABASE IF EXISTS `clinic_management`;
CREATE DATABASE IF NOT EXISTS `clinic_management` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `clinic_management`;

-- Dumping structure for table clinic_management.academic_years
DROP TABLE IF EXISTS `academic_years`;
CREATE TABLE IF NOT EXISTS `academic_years` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1st Semester',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `academic_years_code_unique` (`code`),
  KEY `academic_years_is_current_index` (`is_current`),
  KEY `academic_years_status_index` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.academic_years: 2 rows
DELETE FROM `academic_years`;
/*!40000 ALTER TABLE `academic_years` DISABLE KEYS */;
INSERT INTO `academic_years` (`id`, `code`, `name`, `semester`, `start_date`, `end_date`, `is_current`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'AY-2026-2027-1', 'Academic Year 2026-2027', '1st Semester', '2026-08-01', '2026-12-31', 1, 'active', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(2, 'AY-2025-2026-2', 'Academic Year 2025-2026', '2nd Semester', '2026-01-15', '2026-05-30', 0, 'closed', '2026-11-04 09:37:50', '2026-11-04 09:37:50');
/*!40000 ALTER TABLE `academic_years` ENABLE KEYS */;

-- Dumping structure for table clinic_management.audit_logs
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `event` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `audit_logs_event_created_at_index` (`event`,`created_at`),
  KEY `audit_logs_created_at_index` (`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.audit_logs: 3 rows
DELETE FROM `audit_logs`;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` (`id`, `user_id`, `event`, `description`, `subject_type`, `subject_id`, `properties`, `ip_address`, `user_agent`, `created_at`) VALUES
	(1, 2, 'login', 'Super Admin (SNSU) logged in', 'App\\Models\\User', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 09:38:14'),
	(2, 2, 'login', 'Super Admin (SNSU) signed in via Google', 'App\\Models\\User', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 09:38:14'),
	(3, 2, 'updated', 'Updated healthcare provider DR. CARMINA V. DELOS REYES, MD', 'App\\Models\\Clinician', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 09:41:15'),
	(4, 2, 'stock_out', 'Deducted 50 tablets from Cetirizine Dihydrochloride (Alnix) 10mg Tablet (gbbfg)', 'App\\Models\\MedicineBatch', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 09:50:05'),
	(5, 2, 'login', 'Super Admin (SNSU) logged in', 'App\\Models\\User', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 16:34:55'),
	(6, 1, 'login', 'System Administrator logged in', 'App\\Models\\User', 1, NULL, '127.0.0.1', 'Symfony', '2026-11-04 17:09:54'),
	(7, 2, 'updated', 'Updated Dental Consultation VIS-20261105-0002', 'App\\Models\\ClinicVisit', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 17:14:43'),
	(8, 2, 'updated', 'Updated application settings', NULL, NULL, '{"new": {"table_page_length": "10"}, "old": {"table_page_length": "25"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 17:47:33'),
	(9, 2, 'updated', 'Updated application settings and generated logo variants', NULL, NULL, '{"new": {"logo_seal": "logos/seal_VqVtysYh2ELq.png", "logo_large": "logos/large_VqVtysYh2ELq.png", "logo_small": "logos/small_VqVtysYh2ELq.png", "logo_medium": "logos/medium_VqVtysYh2ELq.png", "logo_compact": "logos/small_VqVtysYh2ELq.png", "logo_favicon": "logos/favicon_VqVtysYh2ELq.png", "logo_primary": "logos/large_VqVtysYh2ELq.png", "logo_original": "logos/original_VqVtysYh2ELq.png", "logo_grayscale": "logos/grayscale_VqVtysYh2ELq.png", "logo_watermark": "logos/watermark_VqVtysYh2ELq.png", "logo_transparent": "logos/transparent_VqVtysYh2ELq.png"}, "old": {"logo_seal": null, "logo_large": null, "logo_small": null, "logo_medium": null, "logo_compact": null, "logo_favicon": null, "logo_primary": null, "logo_original": null, "logo_grayscale": null, "logo_watermark": null, "logo_transparent": null}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 18:08:00'),
	(10, 2, 'updated', 'Updated application settings and generated logo variants', NULL, NULL, '{"new": {"university_name": "Agusan del Sur State University", "university_campus": "Main Campus, San Teodoro, Bunawan, Agusan del Sur", "university_address": "San Teodoro, Bunawan, Agusan del Sur", "university_website": "https://adssu.edu.ph", "university_president": "DR. JO E. CAPISTRANO", "university_short_name": "ADSSU"}, "old": {"university_name": "Surigao del Norte State University", "university_campus": "Main Campus, Surigao City", "university_address": "Narciso Street, Surigao City, Surigao del Norte, Philippines", "university_website": "https://snsu.edu.ph", "university_president": "Dr. Gregorio Z. Gamboa Jr.", "university_short_name": "SNSU"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 18:09:31'),
	(11, 2, 'updated', 'Updated application settings and generated logo variants', NULL, NULL, '{"new": {"clinic_name": "ADSSU University Clinic"}, "old": {"clinic_name": "SNSU University Clinic"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 18:09:53'),
	(12, 2, 'updated', 'Updated application settings and generated logo variants', NULL, NULL, '{"new": {"logo_seal": "logos/seal_bBdiv3jNqXvK.png", "logo_large": "logos/large_bBdiv3jNqXvK.png", "logo_small": "logos/small_bBdiv3jNqXvK.png", "logo_medium": "logos/medium_bBdiv3jNqXvK.png", "logo_compact": "logos/small_bBdiv3jNqXvK.png", "logo_favicon": "logos/favicon_bBdiv3jNqXvK.png", "logo_primary": "logos/large_bBdiv3jNqXvK.png", "logo_original": "logos/original_bBdiv3jNqXvK.png", "logo_grayscale": "logos/grayscale_bBdiv3jNqXvK.png", "logo_watermark": "logos/watermark_bBdiv3jNqXvK.png", "logo_transparent": "logos/transparent_bBdiv3jNqXvK.png"}, "old": {"logo_seal": "logos/seal_VqVtysYh2ELq.png", "logo_large": "logos/large_VqVtysYh2ELq.png", "logo_small": "logos/small_VqVtysYh2ELq.png", "logo_medium": "logos/medium_VqVtysYh2ELq.png", "logo_compact": "logos/small_VqVtysYh2ELq.png", "logo_favicon": "logos/favicon_VqVtysYh2ELq.png", "logo_primary": "logos/large_VqVtysYh2ELq.png", "logo_original": "logos/original_VqVtysYh2ELq.png", "logo_grayscale": "logos/grayscale_VqVtysYh2ELq.png", "logo_watermark": "logos/watermark_VqVtysYh2ELq.png", "logo_transparent": "logos/transparent_VqVtysYh2ELq.png"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 18:12:38'),
	(13, 2, 'updated', 'Updated application settings and generated logo variants', NULL, NULL, '{"new": {"logo_seal": "logos/seal_TnAmkr657YLw.png", "logo_large": "logos/large_TnAmkr657YLw.png", "logo_small": "logos/small_TnAmkr657YLw.png", "logo_medium": "logos/medium_TnAmkr657YLw.png", "logo_compact": "logos/small_TnAmkr657YLw.png", "logo_favicon": "logos/favicon_TnAmkr657YLw.png", "logo_primary": "logos/large_TnAmkr657YLw.png", "logo_original": "logos/original_TnAmkr657YLw.png", "logo_grayscale": "logos/grayscale_TnAmkr657YLw.png", "logo_watermark": "logos/watermark_TnAmkr657YLw.png", "logo_transparent": "logos/transparent_TnAmkr657YLw.png"}, "old": {"logo_seal": "logos/seal_bBdiv3jNqXvK.png", "logo_large": "logos/large_bBdiv3jNqXvK.png", "logo_small": "logos/small_bBdiv3jNqXvK.png", "logo_medium": "logos/medium_bBdiv3jNqXvK.png", "logo_compact": "logos/small_bBdiv3jNqXvK.png", "logo_favicon": "logos/favicon_bBdiv3jNqXvK.png", "logo_primary": "logos/large_bBdiv3jNqXvK.png", "logo_original": "logos/original_bBdiv3jNqXvK.png", "logo_grayscale": "logos/grayscale_bBdiv3jNqXvK.png", "logo_watermark": "logos/watermark_bBdiv3jNqXvK.png", "logo_transparent": "logos/transparent_bBdiv3jNqXvK.png"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 18:14:14'),
	(14, 2, 'login', 'Super Admin (SNSU) logged in', 'App\\Models\\User', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 23:48:33'),
	(15, 2, 'updated', 'Updated application settings and generated logo variants', NULL, NULL, '{"new": {"logo_seal": "logos/seal_doXxyw8XMW9a.png", "logo_large": "logos/large_doXxyw8XMW9a.png", "logo_small": "logos/small_doXxyw8XMW9a.png", "logo_medium": "logos/medium_doXxyw8XMW9a.png", "logo_compact": "logos/small_doXxyw8XMW9a.png", "logo_favicon": "logos/favicon_doXxyw8XMW9a.png", "logo_primary": "logos/large_doXxyw8XMW9a.png", "logo_original": "logos/original_doXxyw8XMW9a.png", "logo_grayscale": "logos/grayscale_doXxyw8XMW9a.png", "logo_watermark": "logos/watermark_doXxyw8XMW9a.png", "logo_transparent": "logos/transparent_doXxyw8XMW9a.png"}, "old": {"logo_seal": "logos/seal_TnAmkr657YLw.png", "logo_large": "logos/large_TnAmkr657YLw.png", "logo_small": "logos/small_TnAmkr657YLw.png", "logo_medium": "logos/medium_TnAmkr657YLw.png", "logo_compact": "logos/small_TnAmkr657YLw.png", "logo_favicon": "logos/favicon_TnAmkr657YLw.png", "logo_primary": "logos/large_TnAmkr657YLw.png", "logo_original": "logos/original_TnAmkr657YLw.png", "logo_grayscale": "logos/grayscale_TnAmkr657YLw.png", "logo_watermark": "logos/watermark_TnAmkr657YLw.png", "logo_transparent": "logos/transparent_TnAmkr657YLw.png"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36 Edg/156.0.0.0', '2026-11-05 01:37:57'),
	(16, 2, 'updated', 'Updated application settings and generated logo variants', NULL, NULL, '{"new": {"clinic_name": "NVC HEALTH DEPARTMENT", "university_name": "NORTHEASTERN VISAYAN COLLEGES INC.", "university_campus": "Kalibo Aklan", "university_short_name": "NVC"}, "old": {"clinic_name": "ADSSU University Clinic", "university_name": "Agusan del Sur State University", "university_campus": "Main Campus, San Teodoro, Bunawan, Agusan del Sur", "university_short_name": "ADSSU"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36 Edg/156.0.0.0', '2026-11-05 01:39:01'),
	(17, 2, 'updated', 'Updated application settings and generated logo variants', NULL, NULL, '{"new": {"university_address": "Kalibo Aklan"}, "old": {"university_address": "San Teodoro, Bunawan, Agusan del Sur"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36 Edg/156.0.0.0', '2026-11-05 01:39:20'),
	(18, 2, 'updated', 'Updated application settings and generated logo variants', NULL, NULL, '{"new": {"clinic_name": "ClinicAssist"}, "old": {"clinic_name": "NVC HEALTH DEPARTMENT"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36 Edg/156.0.0.0', '2026-11-05 01:39:52'),
	(19, 2, 'updated', 'Updated application settings and generated logo variants', NULL, NULL, '{"new": {"university_name": "NORTHWESTERN VISAYAN COLLEGES INC."}, "old": {"university_name": "NORTHEASTERN VISAYAN COLLEGES INC."}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36 Edg/156.0.0.0', '2026-11-05 02:00:45');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;

-- Dumping structure for table clinic_management.backups
DROP TABLE IF EXISTS `backups`;
CREATE TABLE IF NOT EXISTS `backups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_by` bigint unsigned DEFAULT NULL,
  `filename` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint unsigned NOT NULL DEFAULT '0',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `error` text COLLATE utf8mb4_unicode_ci,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backups_created_by_foreign` (`created_by`),
  KEY `backups_status_created_at_index` (`status`,`created_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.backups: 0 rows
DELETE FROM `backups`;
/*!40000 ALTER TABLE `backups` DISABLE KEYS */;
/*!40000 ALTER TABLE `backups` ENABLE KEYS */;

-- Dumping structure for table clinic_management.cache
DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.cache: 4 rows
DELETE FROM `cache`;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('nclinicassist-cache-spatie.permission.cache', 'a:3:{s:5:"alias";a:4:{s:1:"a";s:2:"id";s:1:"b";s:4:"name";s:1:"c";s:10:"guard_name";s:1:"r";s:5:"roles";}s:11:"permissions";a:97:{i:0;a:4:{s:1:"a";i:1;s:1:"b";s:14:"dashboard.view";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:1;a:4:{s:1:"a";i:2;s:1:"b";s:10:"users.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:2;a:4:{s:1:"a";i:3;s:1:"b";s:12:"users.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:3;a:4:{s:1:"a";i:4;s:1:"b";s:10:"users.edit";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:4;a:4:{s:1:"a";i:5;s:1:"b";s:12:"users.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:5;a:4:{s:1:"a";i:6;s:1:"b";s:10:"roles.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:6;a:3:{s:1:"a";i:7;s:1:"b";s:12:"roles.create";s:1:"c";s:3:"web";}i:7;a:3:{s:1:"a";i:8;s:1:"b";s:10:"roles.edit";s:1:"c";s:3:"web";}i:8;a:3:{s:1:"a";i:9;s:1:"b";s:12:"roles.delete";s:1:"c";s:3:"web";}i:9;a:3:{s:1:"a";i:10;s:1:"b";s:16:"permissions.view";s:1:"c";s:3:"web";}i:10;a:3:{s:1:"a";i:11;s:1:"b";s:18:"permissions.create";s:1:"c";s:3:"web";}i:11;a:3:{s:1:"a";i:12;s:1:"b";s:16:"permissions.edit";s:1:"c";s:3:"web";}i:12;a:3:{s:1:"a";i:13;s:1:"b";s:18:"permissions.delete";s:1:"c";s:3:"web";}i:13;a:4:{s:1:"a";i:14;s:1:"b";s:15:"audit-logs.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:14;a:4:{s:1:"a";i:15;s:1:"b";s:15:"settings.manage";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:15;a:4:{s:1:"a";i:16;s:1:"b";s:12:"backups.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:16;a:4:{s:1:"a";i:17;s:1:"b";s:14:"backups.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:17;a:4:{s:1:"a";i:18;s:1:"b";s:16:"backups.download";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:18;a:3:{s:1:"a";i:19;s:1:"b";s:14:"backups.delete";s:1:"c";s:3:"web";}i:19;a:4:{s:1:"a";i:20;s:1:"b";s:19:"academic-years.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:20;a:4:{s:1:"a";i:21;s:1:"b";s:21:"academic-years.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:21;a:4:{s:1:"a";i:22;s:1:"b";s:19:"academic-years.edit";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:22;a:4:{s:1:"a";i:23;s:1:"b";s:21:"academic-years.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:23;a:4:{s:1:"a";i:24;s:1:"b";s:13:"colleges.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:24;a:4:{s:1:"a";i:25;s:1:"b";s:15:"colleges.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:25;a:4:{s:1:"a";i:26;s:1:"b";s:13:"colleges.edit";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:26;a:4:{s:1:"a";i:27;s:1:"b";s:15:"colleges.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:27;a:4:{s:1:"a";i:28;s:1:"b";s:13:"programs.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:28;a:4:{s:1:"a";i:29;s:1:"b";s:15:"programs.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:29;a:4:{s:1:"a";i:30;s:1:"b";s:13:"programs.edit";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:30;a:4:{s:1:"a";i:31;s:1:"b";s:15:"programs.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:31;a:4:{s:1:"a";i:32;s:1:"b";s:15:"clinicians.view";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:32;a:4:{s:1:"a";i:33;s:1:"b";s:17:"clinicians.create";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:33;a:4:{s:1:"a";i:34;s:1:"b";s:15:"clinicians.edit";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:34;a:4:{s:1:"a";i:35;s:1:"b";s:17:"clinicians.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:35;a:4:{s:1:"a";i:36;s:1:"b";s:13:"patients.view";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:36;a:4:{s:1:"a";i:37;s:1:"b";s:15:"patients.create";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:37;a:4:{s:1:"a";i:38;s:1:"b";s:13:"patients.edit";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:38;a:4:{s:1:"a";i:39;s:1:"b";s:15:"patients.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:39;a:4:{s:1:"a";i:40;s:1:"b";s:15:"patients.import";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:40;a:4:{s:1:"a";i:41;s:1:"b";s:13:"students.view";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:41;a:4:{s:1:"a";i:42;s:1:"b";s:15:"students.create";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:42;a:4:{s:1:"a";i:43;s:1:"b";s:13:"students.edit";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:43;a:4:{s:1:"a";i:44;s:1:"b";s:15:"students.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:44;a:4:{s:1:"a";i:45;s:1:"b";s:15:"students.import";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:45;a:4:{s:1:"a";i:46;s:1:"b";s:14:"employees.view";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:46;a:4:{s:1:"a";i:47;s:1:"b";s:16:"employees.create";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:47;a:4:{s:1:"a";i:48;s:1:"b";s:14:"employees.edit";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:48;a:4:{s:1:"a";i:49;s:1:"b";s:16:"employees.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:49;a:4:{s:1:"a";i:50;s:1:"b";s:16:"employees.import";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:50;a:4:{s:1:"a";i:51;s:1:"b";s:26:"physical-examinations.view";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:51;a:4:{s:1:"a";i:52;s:1:"b";s:28:"physical-examinations.create";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:52;a:4:{s:1:"a";i:53;s:1:"b";s:26:"physical-examinations.edit";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:53;a:4:{s:1:"a";i:54;s:1:"b";s:28:"physical-examinations.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:54;a:4:{s:1:"a";i:55;s:1:"b";s:27:"physical-examinations.print";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:55;a:4:{s:1:"a";i:56;s:1:"b";s:21:"medical-profiles.view";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:56;a:4:{s:1:"a";i:57;s:1:"b";s:21:"medical-profiles.edit";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:57;a:4:{s:1:"a";i:58;s:1:"b";s:25:"medical-certificates.view";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:58;a:4:{s:1:"a";i:59;s:1:"b";s:27:"medical-certificates.create";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:59;a:4:{s:1:"a";i:60;s:1:"b";s:26:"medical-certificates.print";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:60;a:4:{s:1:"a";i:61;s:1:"b";s:14:"medicines.view";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:61;a:4:{s:1:"a";i:62;s:1:"b";s:16:"medicines.create";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:62;a:4:{s:1:"a";i:63;s:1:"b";s:14:"medicines.edit";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:63;a:4:{s:1:"a";i:64;s:1:"b";s:16:"medicines.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:64;a:4:{s:1:"a";i:65;s:1:"b";s:24:"medicine-categories.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:65;a:4:{s:1:"a";i:66;s:1:"b";s:26:"medicine-categories.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:66;a:4:{s:1:"a";i:67;s:1:"b";s:24:"medicine-categories.edit";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:67;a:4:{s:1:"a";i:68;s:1:"b";s:26:"medicine-categories.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:68;a:4:{s:1:"a";i:69;s:1:"b";s:14:"inventory.view";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:69;a:4:{s:1:"a";i:70;s:1:"b";s:18:"inventory.stock-in";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:70;a:4:{s:1:"a";i:71;s:1:"b";s:19:"inventory.stock-out";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:71;a:4:{s:1:"a";i:72;s:1:"b";s:18:"inventory.dispense";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:72;a:4:{s:1:"a";i:73;s:1:"b";s:17:"inventory.reports";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:73;a:4:{s:1:"a";i:74;s:1:"b";s:18:"clinic-visits.view";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:74;a:4:{s:1:"a";i:75;s:1:"b";s:20:"clinic-visits.create";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:75;a:4:{s:1:"a";i:76;s:1:"b";s:18:"clinic-visits.edit";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:76;a:4:{s:1:"a";i:77;s:1:"b";s:20:"clinic-visits.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:77;a:4:{s:1:"a";i:78;s:1:"b";s:26:"medical-consultations.view";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:78;a:4:{s:1:"a";i:79;s:1:"b";s:28:"medical-consultations.create";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:79;a:4:{s:1:"a";i:80;s:1:"b";s:26:"medical-consultations.edit";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:80;a:4:{s:1:"a";i:81;s:1:"b";s:28:"medical-consultations.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:81;a:4:{s:1:"a";i:82;s:1:"b";s:25:"dental-consultations.view";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:82;a:4:{s:1:"a";i:83;s:1:"b";s:27:"dental-consultations.create";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:83;a:4:{s:1:"a";i:84;s:1:"b";s:25:"dental-consultations.edit";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:84;a:4:{s:1:"a";i:85;s:1:"b";s:27:"dental-consultations.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:85;a:4:{s:1:"a";i:86;s:1:"b";s:22:"emergency-records.view";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:86;a:4:{s:1:"a";i:87;s:1:"b";s:24:"emergency-records.create";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:87;a:4:{s:1:"a";i:88;s:1:"b";s:22:"emergency-records.edit";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:88;a:4:{s:1:"a";i:89;s:1:"b";s:24:"emergency-records.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:89;a:4:{s:1:"a";i:90;s:1:"b";s:22:"first-aid-records.view";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:90;a:4:{s:1:"a";i:91;s:1:"b";s:24:"first-aid-records.create";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:91;a:4:{s:1:"a";i:92;s:1:"b";s:22:"first-aid-records.edit";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:92;a:4:{s:1:"a";i:93;s:1:"b";s:24:"first-aid-records.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:93;a:4:{s:1:"a";i:94;s:1:"b";s:28:"physical-exam-schedules.view";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:94;a:4:{s:1:"a";i:95;s:1:"b";s:30:"physical-exam-schedules.create";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:95;a:4:{s:1:"a";i:96;s:1:"b";s:28:"physical-exam-schedules.edit";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:96;a:4:{s:1:"a";i:97;s:1:"b";s:30:"physical-exam-schedules.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}}s:5:"roles";a:4:{i:0;a:3:{s:1:"a";i:2;s:1:"b";s:13:"Administrator";s:1:"c";s:3:"web";}i:1;a:3:{s:1:"a";i:3;s:1:"b";s:6:"Doctor";s:1:"c";s:3:"web";}i:2;a:3:{s:1:"a";i:4;s:1:"b";s:5:"Nurse";s:1:"c";s:3:"web";}i:3;a:3:{s:1:"a";i:5;s:1:"b";s:12:"Receptionist";s:1:"c";s:3:"web";}}}', 1793956258),
	('nclinicassist-cache-app.settings', 'a:32:{s:15:"university_name";s:34:"NORTHWESTERN VISAYAN COLLEGES INC.";s:21:"university_short_name";s:3:"NVC";s:17:"university_campus";s:12:"Kalibo Aklan";s:18:"university_address";s:12:"Kalibo Aklan";s:20:"university_president";s:20:"DR. JO E. CAPISTRANO";s:18:"university_website";s:20:"https://adssu.edu.ph";s:11:"clinic_name";s:12:"ClinicAssist";s:11:"clinic_head";s:26:"Dr. Maria Elena Santos, MD";s:17:"clinic_license_no";s:20:"DOH-R13-CLN-2026-088";s:12:"clinic_email";s:18:"clinic@snsu.edu.ph";s:12:"clinic_phone";s:17:"+63 (86) 826-0135";s:14:"clinic_address";s:55:"Ground Floor, Student Services Center, SNSU Main Campus";s:11:"date_format";s:6:"M d, Y";s:17:"table_page_length";s:2:"10";s:14:"backup_enabled";s:1:"1";s:21:"backup_retention_days";s:2:"30";s:24:"audit_log_retention_days";s:3:"365";s:18:"logo_print_enabled";s:1:"1";s:18:"logo_print_variant";s:4:"seal";s:15:"logo_print_size";s:6:"medium";s:19:"logo_print_position";s:4:"left";s:13:"logo_original";s:31:"logos/original_doXxyw8XMW9a.png";s:10:"logo_large";s:28:"logos/large_doXxyw8XMW9a.png";s:12:"logo_primary";s:28:"logos/large_doXxyw8XMW9a.png";s:11:"logo_medium";s:29:"logos/medium_doXxyw8XMW9a.png";s:10:"logo_small";s:28:"logos/small_doXxyw8XMW9a.png";s:12:"logo_compact";s:28:"logos/small_doXxyw8XMW9a.png";s:12:"logo_favicon";s:30:"logos/favicon_doXxyw8XMW9a.png";s:16:"logo_transparent";s:34:"logos/transparent_doXxyw8XMW9a.png";s:9:"logo_seal";s:27:"logos/seal_doXxyw8XMW9a.png";s:14:"logo_grayscale";s:32:"logos/grayscale_doXxyw8XMW9a.png";s:14:"logo_watermark";s:32:"logos/watermark_doXxyw8XMW9a.png";}', 2109232845),
	('nclinicassist-cache-dashboard.stats', 'a:24:{s:12:"total_visits";i:4;s:12:"today_visits";i:0;s:17:"this_month_visits";i:4;s:17:"last_month_visits";i:0;s:13:"visits_growth";s:5:"+100%";s:14:"student_visits";i:2;s:15:"employee_visits";i:2;s:14:"medical_visits";i:1;s:13:"dental_visits";i:1;s:16:"emergency_visits";i:1;s:16:"first_aid_visits";i:1;s:14:"total_patients";i:4;s:15:"active_patients";i:4;s:14:"students_count";i:2;s:15:"employees_count";i:2;s:8:"total_pe";i:4;s:12:"completed_pe";i:4;s:15:"total_medicines";i:10;s:19:"medicines_dispensed";i:0;s:17:"dispensing_orders";i:0;s:15:"low_stock_count";i:2;s:14:"expiring_count";i:0;s:13:"expired_count";i:1;s:16:"total_clinicians";i:5;}', 1793872798),
	('nclinicassist-cache-dashboard.charts', 'a:9:{s:19:"monthly_utilization";a:5:{s:6:"months";a:6:{i:0;s:8:"Jun 2026";i:1;s:8:"Jul 2026";i:2;s:8:"Aug 2026";i:3;s:8:"Sep 2026";i:4;s:8:"Oct 2026";i:5;s:8:"Nov 2026";}s:5:"total";a:6:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:4;}s:7:"medical";a:6:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:1;}s:6:"dental";a:6:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:1;}s:9:"emergency";a:6:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:2;}}s:24:"classification_breakdown";a:3:{s:6:"labels";a:4:{i:0;s:8:"Students";i:1;s:7:"Faculty";i:2;s:5:"Staff";i:3;s:6:"Others";}s:4:"data";a:4:{i:0;i:2;i:1;i:0;i:2;i:2;i:3;i:0;}s:6:"colors";a:4:{i:0;s:7:"#2563eb";i:1;s:7:"#10b981";i:2;s:7:"#f59e0b";i:3;s:7:"#64748b";}}s:18:"services_breakdown";a:3:{s:6:"labels";a:5:{i:0;s:7:"Medical";i:1;s:6:"Dental";i:2;s:9:"Emergency";i:3;s:9:"First Aid";i:4;s:15:"General Checkup";}s:4:"data";a:5:{i:0;i:1;i:1;i:1;i:2;i:1;i:3;i:1;i:4;i:0;}s:6:"colors";a:5:{i:0;s:7:"#3b82f6";i:1;s:7:"#06b6d4";i:2;s:7:"#ef4444";i:3;s:7:"#f59e0b";i:4;s:7:"#8b5cf6";}}s:14:"top_complaints";a:2:{s:6:"labels";a:4:{i:0;s:15:"Fever / Pyrexia";i:1;s:21:"Dental Pain / Checkup";i:2;s:19:"Dizziness / Syncope";i:3;s:21:"Trauma / Wound / Fall";}s:4:"data";a:4:{i:0;i:1;i:1;i:1;i:2;i:1;i:3;i:1;}}s:22:"disease_classification";a:5:{s:18:"communicable_count";i:1;s:22:"non_communicable_count";i:0;s:11:"other_count";i:0;s:16:"top_communicable";a:1:{i:0;a:2:{s:4:"name";s:4:"Urti";s:5:"count";i:1;}}s:20:"top_non_communicable";a:0:{}}s:13:"top_medicines";a:3:{s:6:"labels";a:7:{i:0;s:22:"Paracetamol (Biogesic)";i:1;s:31:"Ascorbic Acid + Zinc (ImmunPro)";i:2;s:27:"Ibuprofen (Medicol Advance)";i:3;s:46:"Antacid (AlOH + MgOH + Simethicone) (Kremil-S)";i:4;s:25:"Mefenamic Acid (Dolfenal)";i:5;s:34:"Cetirizine Dihydrochloride (Alnix)";i:6;s:32:"Oral Rehydration Salts (Hydrite)";}s:10:"quantities";a:7:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:0;}s:15:"total_dispensed";i:0;}s:19:"recent_visits_trend";a:2:{s:6:"labels";a:14:{i:0;s:6:"Oct 23";i:1;s:6:"Oct 24";i:2;s:6:"Oct 25";i:3;s:6:"Oct 26";i:4;s:6:"Oct 27";i:5;s:6:"Oct 28";i:6;s:6:"Oct 29";i:7;s:6:"Oct 30";i:8;s:6:"Oct 31";i:9;s:6:"Nov 01";i:10;s:6:"Nov 02";i:11;s:6:"Nov 03";i:12;s:6:"Nov 04";i:13;s:6:"Nov 05";}s:6:"values";a:14:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:0;i:7;i:0;i:8;i:0;i:9;i:0;i:10;i:0;i:11;i:0;i:12;i:4;i:13;i:0;}}s:20:"pe_fitness_breakdown";a:3:{s:6:"labels";a:5:{i:0;s:17:"Class A (Fit all)";i:1;s:28:"Class B (Correctable defect)";i:2;s:25:"Class C (Restricted work)";i:3;s:25:"Class D (Defective/Unfit)";i:4;s:24:"Class E (Pending/Exempt)";}s:4:"data";a:5:{i:0;i:4;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;}s:6:"colors";a:5:{i:0;s:7:"#10b981";i:1;s:7:"#06b6d4";i:2;s:7:"#f59e0b";i:3;s:7:"#f97316";i:4;s:7:"#ef4444";}}s:19:"college_utilization";a:2:{s:6:"labels";a:3:{i:0;s:4:"CCIS";i:1;s:3:"CHS";i:2;s:4:"CEAT";}s:4:"data";a:3:{i:0;i:2;i:1;i:1;i:2;i:0;}}}', 1793872798);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;

-- Dumping structure for table clinic_management.cache_locks
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.cache_locks: 0 rows
DELETE FROM `cache_locks`;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;

-- Dumping structure for table clinic_management.clinicians
DROP TABLE IF EXISTS `clinicians`;
CREATE TABLE IF NOT EXISTS `clinicians` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Doctor',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'School Physician',
  `license_number` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ptr_number` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specialization` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(35) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clinicians_user_id_foreign` (`user_id`),
  KEY `clinicians_type_index` (`type`),
  KEY `clinicians_license_number_index` (`license_number`),
  KEY `clinicians_is_active_index` (`is_active`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.clinicians: 5 rows
DELETE FROM `clinicians`;
/*!40000 ALTER TABLE `clinicians` DISABLE KEYS */;
INSERT INTO `clinicians` (`id`, `user_id`, `name`, `type`, `title`, `license_number`, `ptr_number`, `specialization`, `phone`, `email`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, NULL, 'DR. VICENTE S. QUIMPO, MD', 'Doctor', 'School Physician', '0052551', 'PTR-AKL-2026-8910', 'General Medicine & School Health', '+63 917 111 2233', 'vicente.quimpo@nvc.edu.ph', 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(2, NULL, 'DR. CARMINA V. DELOS REYES, MD', 'Doctor', 'Consulting Physician', '0078124', 'PTR-AKL-2026-8911', 'Pediatrics & Adolescent Health', '+63 918 222 3344', 'carmina.delosreyes@nvc.edu.ph', 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(3, NULL, 'NURSE MARIA THERESA SANTOS, RN', 'Nurse', 'Head Clinic Nurse', '0088921', NULL, 'School Health Nursing', '+63 920 333 4455', 'maria.santos@nvc.edu.ph', 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(4, NULL, 'NURSE JUAN CARLO BAUTISTA, RN', 'Nurse', 'Staff Nurse', '0094512', NULL, 'Emergency Care & First Aid', '+63 921 444 5566', 'jc.bautista@nvc.edu.ph', 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(5, NULL, 'DR. ROBERTO TAN, DMD', 'Dentist', 'University Dentist', '0034120', 'PTR-AKL-2026-8915', 'Dental Medicine & Oral Surgery', '+63 922 555 6677', 'roberto.tan@nvc.edu.ph', 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50');
/*!40000 ALTER TABLE `clinicians` ENABLE KEYS */;

-- Dumping structure for table clinic_management.clinic_visits
DROP TABLE IF EXISTS `clinic_visits`;
CREATE TABLE IF NOT EXISTS `clinic_visits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `visit_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patient_id` bigint unsigned NOT NULL,
  `visit_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `visit_category` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'walk_in',
  `patient_classification` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `classification_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `college_id` bigint unsigned DEFAULT NULL,
  `program_id` bigint unsigned DEFAULT NULL,
  `academic_year_id` bigint unsigned DEFAULT NULL,
  `year_level` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `office_department` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visit_date` datetime NOT NULL,
  `time_out` datetime DEFAULT NULL,
  `chief_complaint` text COLLATE utf8mb4_unicode_ci,
  `systolic` smallint unsigned DEFAULT NULL,
  `diastolic` smallint unsigned DEFAULT NULL,
  `pulse_rate` smallint unsigned DEFAULT NULL,
  `respiratory_rate` smallint unsigned DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `oxygen_saturation` smallint unsigned DEFAULT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `height_cm` decimal(5,2) DEFAULT NULL,
  `pain_scale` tinyint unsigned DEFAULT NULL,
  `clinician_id` bigint unsigned DEFAULT NULL,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `disposition` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `physical_examination_id` bigint unsigned DEFAULT NULL,
  `medicine_dispensing_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clinic_visits_visit_number_unique` (`visit_number`),
  KEY `clinic_visits_patient_id_foreign` (`patient_id`),
  KEY `clinic_visits_college_id_foreign` (`college_id`),
  KEY `clinic_visits_program_id_foreign` (`program_id`),
  KEY `clinic_visits_academic_year_id_foreign` (`academic_year_id`),
  KEY `clinic_visits_clinician_id_foreign` (`clinician_id`),
  KEY `clinic_visits_recorded_by_foreign` (`recorded_by`),
  KEY `clinic_visits_physical_examination_id_foreign` (`physical_examination_id`),
  KEY `clinic_visits_medicine_dispensing_id_foreign` (`medicine_dispensing_id`),
  KEY `clinic_visits_visit_type_index` (`visit_type`),
  KEY `clinic_visits_patient_classification_index` (`patient_classification`),
  KEY `clinic_visits_visit_date_index` (`visit_date`),
  KEY `clinic_visits_status_index` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.clinic_visits: 4 rows
DELETE FROM `clinic_visits`;
/*!40000 ALTER TABLE `clinic_visits` DISABLE KEYS */;
INSERT INTO `clinic_visits` (`id`, `visit_number`, `patient_id`, `visit_type`, `visit_category`, `patient_classification`, `classification_label`, `college_id`, `program_id`, `academic_year_id`, `year_level`, `office_department`, `visit_date`, `time_out`, `chief_complaint`, `systolic`, `diastolic`, `pulse_rate`, `respiratory_rate`, `temperature`, `oxygen_saturation`, `weight_kg`, `height_cm`, `pain_scale`, `clinician_id`, `recorded_by`, `disposition`, `status`, `remarks`, `physical_examination_id`, `medicine_dispensing_id`, `created_at`, `updated_at`) VALUES
	(1, 'VIS-20261105-0001', 1, 'medical', 'walk_in', 'student', 'Student • BSIT • 2nd Year • CCIS', 1, 1, 1, '2nd Year', NULL, '2026-11-04 20:50:29', '2026-11-04 22:20:29', 'Moderate fever (38.4°C), non-productive cough, and body malaise for 2 days.', 110, 70, 88, 19, 38.4, 98, NULL, NULL, 3, 1, 1, 'sent_home', 'completed', 'Advised oral hydration and bed rest for 2 days.', NULL, NULL, '2026-11-04 16:50:29', '2026-11-04 16:50:29'),
	(2, 'VIS-20261105-0002', 2, 'dental', 'scheduled', 'student', 'Student • BSN • 3rd Year • CHS', 3, 5, 1, '3rd Year', NULL, '2026-11-04 21:50:00', '2026-11-04 22:50:00', 'Occasional sensitivity to cold drinks on lower right molar.', 120, 80, 76, 16, 36.6, 99, NULL, NULL, 2, 2, 1, 'back_to_class', 'completed', 'Composite restoration completed uneventfully.', NULL, NULL, '2026-11-04 16:50:29', '2026-11-04 17:14:43'),
	(3, 'VIS-20261105-0003', 3, 'emergency', 'emergency_call', 'employee', 'Employee • Faculty • Assistant Professor IV • Department of Computer Science', 1, NULL, NULL, NULL, 'Department of Computer Science', '2026-11-04 22:50:29', '2026-11-04 23:50:29', 'Sudden episode of syncope and loss of consciousness during university assembly.', 90, 60, 96, 20, 36.4, 96, NULL, NULL, 0, 1, 1, 'observation', 'completed', 'Regained full consciousness within 2 minutes. Vital signs stabilized after oral rehydration.', NULL, NULL, '2026-11-04 16:50:29', '2026-11-04 16:50:29'),
	(4, 'VIS-20261105-0004', 4, 'first_aid', 'walk_in', 'employee', 'Employee • Non-Teaching / Staff • Administrative Officer II • Office of the University Registrar', NULL, NULL, NULL, NULL, 'Office of the University Registrar', '2026-11-04 23:50:29', '2026-11-05 00:20:29', 'Abrasion wound on right knee and forearm following a slip and fall along the corridor walkway.', 115, 75, 80, 18, 36.7, 99, NULL, NULL, 4, 3, 1, 'back_to_class', 'completed', 'Dressing clean and dry. Advised to keep wound dry for 24 hours.', NULL, NULL, '2026-11-04 16:50:29', '2026-11-04 16:50:29');
/*!40000 ALTER TABLE `clinic_visits` ENABLE KEYS */;

-- Dumping structure for table clinic_management.colleges
DROP TABLE IF EXISTS `colleges`;
CREATE TABLE IF NOT EXISTS `colleges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dean` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `colleges_code_unique` (`code`),
  KEY `colleges_is_active_index` (`is_active`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.colleges: 3 rows
DELETE FROM `colleges`;
/*!40000 ALTER TABLE `colleges` DISABLE KEYS */;
INSERT INTO `colleges` (`id`, `code`, `name`, `dean`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'CCIS', 'College of Computer and Information Sciences', 'Dr. Eleanor Vance', 'Department of Computing, Information Systems, and Cyber Security', 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(2, 'CEAT', 'College of Engineering and Architecture Technology', 'Engr. Manuel Santos', 'Engineering, Architecture, and Applied Industrial Sciences', 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(3, 'CHS', 'College of Health Sciences and Nursing', 'Dr. Patricia Ramos, RN, MD', 'Healthcare programs, Nursing, and Allied Clinical Services', 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50');
/*!40000 ALTER TABLE `colleges` ENABLE KEYS */;

-- Dumping structure for table clinic_management.dental_consultations
DROP TABLE IF EXISTS `dental_consultations`;
CREATE TABLE IF NOT EXISTS `dental_consultations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clinic_visit_id` bigint unsigned NOT NULL,
  `oral_examination_findings` text COLLATE utf8mb4_unicode_ci,
  `tooth_chart` json DEFAULT NULL,
  `procedures` json DEFAULT NULL,
  `teeth_involved` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anesthesia_used` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diagnosis` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `treatment_rendered` text COLLATE utf8mb4_unicode_ci,
  `recommendations` text COLLATE utf8mb4_unicode_ci,
  `oral_hygiene_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_dental_treatment` text COLLATE utf8mb4_unicode_ci,
  `dental_treatment_frequency` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_dental_visit` date DEFAULT NULL,
  `dental_fears` tinyint(1) NOT NULL DEFAULT '0',
  `dental_concerns` text COLLATE utf8mb4_unicode_ci,
  `has_toothache` tinyint(1) NOT NULL DEFAULT '0',
  `has_bleeding_gums` tinyint(1) NOT NULL DEFAULT '0',
  `has_bad_breath` tinyint(1) NOT NULL DEFAULT '0',
  `has_tooth_sensitivity` tinyint(1) NOT NULL DEFAULT '0',
  `has_jaw_pain` tinyint(1) NOT NULL DEFAULT '0',
  `has_loose_teeth` tinyint(1) NOT NULL DEFAULT '0',
  `has_swelling` tinyint(1) NOT NULL DEFAULT '0',
  `systemic_diabetes` tinyint(1) NOT NULL DEFAULT '0',
  `systemic_hypertension` tinyint(1) NOT NULL DEFAULT '0',
  `systemic_heart_disease` tinyint(1) NOT NULL DEFAULT '0',
  `systemic_bleeding_disorder` tinyint(1) NOT NULL DEFAULT '0',
  `systemic_allergies` tinyint(1) NOT NULL DEFAULT '0',
  `systemic_allergy_details` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `soft_tissue_lips` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `soft_tissue_cheeks` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `soft_tissue_tongue` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `soft_tissue_palate` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `soft_tissue_gingiva` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `soft_tissue_floor_of_mouth` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `periodontal_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `calculus_index` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gingival_index` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plaque_index` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dmft_decayed` tinyint unsigned DEFAULT NULL,
  `dmft_missing` tinyint unsigned DEFAULT NULL,
  `dmft_filled` tinyint unsigned DEFAULT NULL,
  `dmfs_decayed` tinyint unsigned DEFAULT NULL,
  `dmfs_missing` tinyint unsigned DEFAULT NULL,
  `dmfs_filled` tinyint unsigned DEFAULT NULL,
  `treatment_plan` text COLLATE utf8mb4_unicode_ci,
  `priority_level` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_tooth_chart` json DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dental_consultations_clinic_visit_id_unique` (`clinic_visit_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.dental_consultations: 1 rows
DELETE FROM `dental_consultations`;
/*!40000 ALTER TABLE `dental_consultations` DISABLE KEYS */;
INSERT INTO `dental_consultations` (`id`, `clinic_visit_id`, `oral_examination_findings`, `tooth_chart`, `procedures`, `teeth_involved`, `anesthesia_used`, `diagnosis`, `treatment_rendered`, `recommendations`, `oral_hygiene_status`, `previous_dental_treatment`, `dental_treatment_frequency`, `last_dental_visit`, `dental_fears`, `dental_concerns`, `has_toothache`, `has_bleeding_gums`, `has_bad_breath`, `has_tooth_sensitivity`, `has_jaw_pain`, `has_loose_teeth`, `has_swelling`, `systemic_diabetes`, `systemic_hypertension`, `systemic_heart_disease`, `systemic_bleeding_disorder`, `systemic_allergies`, `systemic_allergy_details`, `soft_tissue_lips`, `soft_tissue_cheeks`, `soft_tissue_tongue`, `soft_tissue_palate`, `soft_tissue_gingiva`, `soft_tissue_floor_of_mouth`, `periodontal_status`, `calculus_index`, `gingival_index`, `plaque_index`, `dmft_decayed`, `dmft_missing`, `dmft_filled`, `dmfs_decayed`, `dmfs_missing`, `dmfs_filled`, `treatment_plan`, `priority_level`, `primary_tooth_chart`, `follow_up_date`, `created_at`, `updated_at`) VALUES
	(1, 2, NULL, '{"46": "F"}', '["Oral Examination / Consultation", "Permanent Filling (Restoration)"]', '#46 (Lower Right 1st Molar)', 'Lidocaine 2% with 1:100,000 Epinephrine 1.8mL', 'Dental Caries, Class I (Tooth 46)', 'Caries excavation under local anesthesia, cavity conditioning, and light-cured composite resin restoration. Occlusion checked and adjusted.', 'Avoid chewing hard foods for 2 hours. Maintain twice daily brushing and dental flossing.', 'Good', NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2027-05-05', '2026-11-04 16:50:29', '2026-11-04 17:14:43');
/*!40000 ALTER TABLE `dental_consultations` ENABLE KEYS */;

-- Dumping structure for table clinic_management.emergency_records
DROP TABLE IF EXISTS `emergency_records`;
CREATE TABLE IF NOT EXISTS `emergency_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clinic_visit_id` bigint unsigned NOT NULL,
  `incident_datetime` datetime DEFAULT NULL,
  `incident_location` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_nature` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `triage_level` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level_of_consciousness` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gcs_score` tinyint unsigned DEFAULT NULL,
  `incident_description` text COLLATE utf8mb4_unicode_ci,
  `interventions` json DEFAULT NULL,
  `intervention_notes` text COLLATE utf8mb4_unicode_ci,
  `ambulance_called` tinyint(1) NOT NULL DEFAULT '0',
  `transferred_to_hospital` tinyint(1) NOT NULL DEFAULT '0',
  `hospital_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transport_mode` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transferred_at` datetime DEFAULT NULL,
  `guardian_notified` tinyint(1) NOT NULL DEFAULT '0',
  `notified_person` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notified_at` datetime DEFAULT NULL,
  `incident_report_number` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outcome` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emergency_records_clinic_visit_id_unique` (`clinic_visit_id`),
  KEY `emergency_records_triage_level_index` (`triage_level`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.emergency_records: 1 rows
DELETE FROM `emergency_records`;
/*!40000 ALTER TABLE `emergency_records` DISABLE KEYS */;
INSERT INTO `emergency_records` (`id`, `clinic_visit_id`, `incident_datetime`, `incident_location`, `emergency_nature`, `triage_level`, `level_of_consciousness`, `gcs_score`, `incident_description`, `interventions`, `intervention_notes`, `ambulance_called`, `transferred_to_hospital`, `hospital_name`, `transport_mode`, `transferred_at`, `guardian_notified`, `notified_person`, `notified_at`, `incident_report_number`, `outcome`, `created_at`, `updated_at`) VALUES
	(1, 3, '2026-11-04 22:35:29', 'University Gymnasium Main Stage', 'Syncope / Loss of Consciousness', 'yellow', 'alert', 15, 'Student collapsed while standing in line during the morning assembly under warm ambient temperature. Denies head trauma or convulsions.', '["Oxygen Therapy", "Recovery Position", "Monitoring of Vital Signs"]', 'Placed in Trendelenburg position. Humidified O2 at 3 LPM via nasal cannula given. Blood glucose checked (92 mg/dL - normal). Patient regained alertness.', 0, 0, NULL, NULL, NULL, 1, 'Carla Villanueva', '2026-11-04 22:50:29', 'IR-2026-001', 'Stabilized and Discharged', '2026-11-04 16:50:29', '2026-11-04 16:50:29');
/*!40000 ALTER TABLE `emergency_records` ENABLE KEYS */;

-- Dumping structure for table clinic_management.failed_jobs
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.failed_jobs: 0 rows
DELETE FROM `failed_jobs`;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;

-- Dumping structure for table clinic_management.first_aid_records
DROP TABLE IF EXISTS `first_aid_records`;
CREATE TABLE IF NOT EXISTS `first_aid_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clinic_visit_id` bigint unsigned NOT NULL,
  `injury_type` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body_part` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `incident_location` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `incident_cause` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_aid_given` json DEFAULT NULL,
  `treatment_notes` text COLLATE utf8mb4_unicode_ci,
  `supplies_used` text COLLATE utf8mb4_unicode_ci,
  `was_referred` tinyint(1) NOT NULL DEFAULT '0',
  `referred_to` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `first_aid_records_clinic_visit_id_unique` (`clinic_visit_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.first_aid_records: 1 rows
DELETE FROM `first_aid_records`;
/*!40000 ALTER TABLE `first_aid_records` DISABLE KEYS */;
INSERT INTO `first_aid_records` (`id`, `clinic_visit_id`, `injury_type`, `body_part`, `incident_location`, `incident_cause`, `first_aid_given`, `treatment_notes`, `supplies_used`, `was_referred`, `referred_to`, `created_at`, `updated_at`) VALUES
	(1, 4, 'Abrasion / Scratch', 'Right knee and right forearm', 'Academic Building 2, 2nd Floor Corridor', 'Slipped on wet floor tile after rain shower', '["Wound Cleaning", "Antiseptic Applied", "Dressing / Bandage Applied"]', 'Wound irrigated with sterile saline solution. Povidone-iodine antiseptic applied. Covered with sterile non-adherent gauze pad and hypoallergenic tape.', 'Normal Saline Solution 50mL, Betadine 10% solution, Gauze pads 4x4 (3 pcs), Micropore tape', 0, NULL, '2026-11-04 16:50:29', '2026-11-04 16:50:29');
/*!40000 ALTER TABLE `first_aid_records` ENABLE KEYS */;

-- Dumping structure for table clinic_management.inventory_transactions
DROP TABLE IF EXISTS `inventory_transactions`;
CREATE TABLE IF NOT EXISTS `inventory_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_number` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `medicine_id` bigint unsigned NOT NULL,
  `batch_id` bigint unsigned DEFAULT NULL,
  `type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `quantity_before` int NOT NULL,
  `quantity_after` int NOT NULL,
  `reference_number` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patient_id` bigint unsigned DEFAULT NULL,
  `clinician_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `transaction_date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_transactions_transaction_number_unique` (`transaction_number`),
  KEY `inventory_transactions_medicine_id_foreign` (`medicine_id`),
  KEY `inventory_transactions_batch_id_foreign` (`batch_id`),
  KEY `inventory_transactions_patient_id_foreign` (`patient_id`),
  KEY `inventory_transactions_clinician_id_foreign` (`clinician_id`),
  KEY `inventory_transactions_user_id_foreign` (`user_id`),
  KEY `inventory_transactions_type_index` (`type`),
  KEY `inventory_transactions_transaction_date_index` (`transaction_date`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.inventory_transactions: 10 rows
DELETE FROM `inventory_transactions`;
/*!40000 ALTER TABLE `inventory_transactions` DISABLE KEYS */;
INSERT INTO `inventory_transactions` (`id`, `transaction_number`, `medicine_id`, `batch_id`, `type`, `quantity`, `quantity_before`, `quantity_after`, `reference_number`, `reason`, `patient_id`, `clinician_id`, `user_id`, `transaction_date`, `created_at`, `updated_at`) VALUES
	(1, 'TXN-2026-00001', 1, 1, 'stock_in', 500, 0, 500, 'PO-INIT-MED-0001', 'Initial inventory delivery / stock receipt', NULL, NULL, NULL, '2026-10-20 17:37:50', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(2, 'TXN-2026-00002', 2, 2, 'stock_in', 200, 0, 200, 'PO-INIT-MED-0002', 'Initial inventory delivery / stock receipt', NULL, NULL, NULL, '2026-10-20 17:37:50', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(3, 'TXN-2026-00003', 3, 3, 'stock_in', 100, 0, 100, 'PO-INIT-MED-0003', 'Initial inventory delivery / stock receipt', NULL, NULL, NULL, '2026-10-20 17:37:50', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(4, 'TXN-2026-00004', 4, 4, 'stock_in', 300, 0, 300, 'PO-INIT-MED-0004', 'Initial inventory delivery / stock receipt', NULL, NULL, NULL, '2026-10-20 17:37:50', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(5, 'TXN-2026-00005', 5, 5, 'stock_in', 80, 0, 80, 'PO-INIT-MED-0005', 'Initial inventory delivery / stock receipt', NULL, NULL, NULL, '2026-10-20 17:37:50', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(6, 'TXN-2026-00006', 6, 6, 'stock_in', 250, 0, 250, 'PO-INIT-MED-0006', 'Initial inventory delivery / stock receipt', NULL, NULL, NULL, '2026-10-20 17:37:50', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(7, 'TXN-2026-00007', 7, 7, 'stock_in', 120, 0, 120, 'PO-INIT-MED-0007', 'Initial inventory delivery / stock receipt', NULL, NULL, NULL, '2026-10-20 17:37:50', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(8, 'TXN-2026-00008', 8, 8, 'stock_in', 400, 0, 400, 'PO-INIT-MED-0008', 'Initial inventory delivery / stock receipt', NULL, NULL, NULL, '2026-10-20 17:37:50', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(9, 'TXN-2026-00009', 9, 9, 'stock_in', 30, 0, 30, 'PO-INIT-MED-0009', 'Initial inventory delivery / stock receipt', NULL, NULL, NULL, '2026-10-20 17:37:50', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(10, 'TXN-2026-00010', 10, 10, 'stock_in', 50, 0, 50, 'PO-INIT-MED-0010', 'Initial inventory delivery / stock receipt', NULL, NULL, NULL, '2026-10-20 17:37:50', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(11, 'TXN-2026-00011', 4, 4, 'disposal', -50, 280, 230, NULL, 'gbbfg', NULL, NULL, 2, '2026-11-04 17:50:05', '2026-11-04 09:50:05', '2026-11-04 09:50:05');
/*!40000 ALTER TABLE `inventory_transactions` ENABLE KEYS */;

-- Dumping structure for table clinic_management.jobs
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.jobs: 0 rows
DELETE FROM `jobs`;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;

-- Dumping structure for table clinic_management.job_batches
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.job_batches: 0 rows
DELETE FROM `job_batches`;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;

-- Dumping structure for table clinic_management.medical_consultations
DROP TABLE IF EXISTS `medical_consultations`;
CREATE TABLE IF NOT EXISTS `medical_consultations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clinic_visit_id` bigint unsigned NOT NULL,
  `history_of_present_illness` text COLLATE utf8mb4_unicode_ci,
  `physical_findings` text COLLATE utf8mb4_unicode_ci,
  `diagnosis` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `icd_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `treatment_plan` text COLLATE utf8mb4_unicode_ci,
  `medications_prescribed` text COLLATE utf8mb4_unicode_ci,
  `advice` text COLLATE utf8mb4_unicode_ci,
  `medical_certificate_issued` tinyint(1) NOT NULL DEFAULT '0',
  `rest_days` smallint unsigned DEFAULT NULL,
  `referred_to` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referral_reason` text COLLATE utf8mb4_unicode_ci,
  `follow_up_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medical_consultations_clinic_visit_id_unique` (`clinic_visit_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.medical_consultations: 1 rows
DELETE FROM `medical_consultations`;
/*!40000 ALTER TABLE `medical_consultations` DISABLE KEYS */;
INSERT INTO `medical_consultations` (`id`, `clinic_visit_id`, `history_of_present_illness`, `physical_findings`, `diagnosis`, `icd_code`, `treatment_plan`, `medications_prescribed`, `advice`, `medical_certificate_issued`, `rest_days`, `referred_to`, `referral_reason`, `follow_up_date`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Patient reports gradual onset of body malaise and dry cough 48 hours prior to consult. No shortness of breath.', 'Pharyngeal erythema (+), clear breath sounds, supple neck, soft non-tender abdomen.', 'Acute Upper Respiratory Tract Infection (URTI)', 'J06.9', 'Paracetamol 500mg tab q4h PRN for fever. Cetirizine 10mg tab OD at bedtime. Increase fluid intake.', 'Paracetamol 500mg #10, Cetirizine 10mg #5', 'Adequate sleep, wear mask, return to clinic if fever persists beyond 3 days.', 1, 2, NULL, NULL, '2026-11-08', '2026-11-04 16:50:29', '2026-11-04 16:50:29');
/*!40000 ALTER TABLE `medical_consultations` ENABLE KEYS */;

-- Dumping structure for table clinic_management.medical_profiles
DROP TABLE IF EXISTS `medical_profiles`;
CREATE TABLE IF NOT EXISTS `medical_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint unsigned NOT NULL,
  `past_medical_history` json DEFAULT NULL,
  `past_medical_history_notes` text COLLATE utf8mb4_unicode_ci,
  `family_medical_history` json DEFAULT NULL,
  `family_medical_history_notes` text COLLATE utf8mb4_unicode_ci,
  `allergies_food` text COLLATE utf8mb4_unicode_ci,
  `allergies_drugs` text COLLATE utf8mb4_unicode_ci,
  `allergies_others` text COLLATE utf8mb4_unicode_ci,
  `existing_conditions` text COLLATE utf8mb4_unicode_ci,
  `current_medications` text COLLATE utf8mb4_unicode_ci,
  `hospitalization_history` json DEFAULT NULL,
  `surgical_history` json DEFAULT NULL,
  `smoking_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alcohol_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `immunizations` json DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medical_profiles_patient_id_unique` (`patient_id`),
  KEY `medical_profiles_updated_by_foreign` (`updated_by`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.medical_profiles: 4 rows
DELETE FROM `medical_profiles`;
/*!40000 ALTER TABLE `medical_profiles` DISABLE KEYS */;
INSERT INTO `medical_profiles` (`id`, `patient_id`, `past_medical_history`, `past_medical_history_notes`, `family_medical_history`, `family_medical_history_notes`, `allergies_food`, `allergies_drugs`, `allergies_others`, `existing_conditions`, `current_medications`, `hospitalization_history`, `surgical_history`, `smoking_status`, `alcohol_status`, `immunizations`, `notes`, `updated_by`, `created_at`, `updated_at`) VALUES
	(1, 1, '["Asthma / Wheezing", "Allergy / Hay Fever"]', NULL, '["Hypertension"]', NULL, 'Shellfish', 'Penicillin', NULL, 'Mild Asthma (controlled)', 'Salbutamol MDI PRN', NULL, NULL, 'non-smoker', 'non-drinker', NULL, NULL, NULL, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(2, 2, '["Asthma / Wheezing", "Allergy / Hay Fever"]', NULL, '["Hypertension"]', NULL, NULL, NULL, NULL, 'No prior chronic conditions', NULL, NULL, NULL, 'non-smoker', 'non-drinker', NULL, NULL, NULL, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(3, 3, '["Asthma / Wheezing", "Allergy / Hay Fever"]', NULL, '["Hypertension"]', NULL, NULL, NULL, NULL, 'Hypertension Stage 1 (on Maintenance)', NULL, NULL, NULL, 'non-smoker', 'non-drinker', NULL, NULL, NULL, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(4, 4, '["Asthma / Wheezing", "Allergy / Hay Fever"]', NULL, '["Hypertension"]', NULL, NULL, NULL, 'Dust mites', 'Allergic rhinitis', NULL, NULL, NULL, 'non-smoker', 'non-drinker', NULL, NULL, NULL, '2026-11-04 09:37:50', '2026-11-04 09:37:50');
/*!40000 ALTER TABLE `medical_profiles` ENABLE KEYS */;

-- Dumping structure for table clinic_management.medicines
DROP TABLE IF EXISTS `medicines`;
CREATE TABLE IF NOT EXISTS `medicines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `generic_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dosage_form` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `strength` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tablet',
  `reorder_level` int unsigned NOT NULL DEFAULT '50',
  `description` text COLLATE utf8mb4_unicode_ci,
  `side_effects` text COLLATE utf8mb4_unicode_ci,
  `storage_conditions` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_prescription_required` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medicines_code_unique` (`code`),
  KEY `medicines_category_id_foreign` (`category_id`),
  KEY `medicines_generic_name_index` (`generic_name`),
  KEY `medicines_brand_name_index` (`brand_name`),
  KEY `medicines_is_active_index` (`is_active`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.medicines: 10 rows
DELETE FROM `medicines`;
/*!40000 ALTER TABLE `medicines` DISABLE KEYS */;
INSERT INTO `medicines` (`id`, `code`, `category_id`, `generic_name`, `brand_name`, `dosage_form`, `strength`, `unit`, `reorder_level`, `description`, `side_effects`, `storage_conditions`, `is_prescription_required`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'MED-0001', 1, 'Paracetamol', 'Biogesic', 'Tablet', '500mg', 'tablet', 100, 'First-line relief for mild to moderate pain and headache/fever.', 'Rare in normal dosage; potential hepatotoxicity in extreme overdose.', 'Store at temperatures not exceeding 30°C.', 0, 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(2, 'MED-0002', 1, 'Ibuprofen', 'Medicol Advance', 'Capsule', '200mg', 'capsule', 60, 'NSAID for inflammatory headache, toothache, and dysmenorrhea.', 'Gastric irritation, take with meals.', 'Store below 25°C in a dry place.', 0, 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(3, 'MED-0003', 1, 'Mefenamic Acid', 'Dolfenal', 'Capsule', '500mg', 'capsule', 50, 'For acute post-traumatic pain and severe dysmenorrhea.', 'GI discomfort, nausea.', 'Store at room temperature.', 1, 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(4, 'MED-0004', 3, 'Cetirizine Dihydrochloride', 'Alnix', 'Tablet', '10mg', 'tablet', 50, 'Second generation antihistamine for allergic rhinitis and skin rashes.', 'Mild drowsiness in sensitive patients.', 'Protect from moisture and heat.', 0, 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(5, 'MED-0005', 4, 'Salbutamol Sulfate', 'Ventolin Nebules', 'Nebule', '2.5mg/2.5ml', 'nebule', 30, 'Short-acting beta2-agonist for acute bronchospasm and asthma relief.', 'Fine tremor, palpitations, headache.', 'Store below 30°C. Protect from light.', 1, 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(6, 'MED-0006', 5, 'Antacid (AlOH + MgOH + Simethicone)', 'Kremil-S', 'Chewable Tablet', 'Standard', 'tablet', 60, 'Fast acting antacid for hyperacidity, gas, and heartburn.', 'Chalky taste.', 'Keep container tightly closed.', 0, 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(7, 'MED-0007', 5, 'Oral Rehydration Salts', 'Hydrite', 'Sachet', 'Formula 75', 'sachet', 40, 'Restores fluid and electrolytes lost during diarrhea or dehydration.', 'None when properly reconstituted with drinking water.', 'Store in a cool dry area.', 0, 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(8, 'MED-0008', 6, 'Ascorbic Acid + Zinc', 'ImmunPro', 'Tablet', '500mg/10mg', 'tablet', 100, 'Immune defense supplement for staff and student athletes.', 'Minimal.', 'Store in cool dry conditions.', 0, 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(9, 'MED-0009', 7, 'Povidone Iodine Solution', 'Betadine 10%', 'Bottle', '10% w/v 60ml', 'bottle', 15, 'Broad spectrum antiseptic for wounds, cuts, and scrapes.', 'Local skin irritation in iodine-sensitive patients.', 'Store below 30°C.', 0, 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(10, 'MED-0010', 8, 'Amlodipine Besilate', 'Norvasc', 'Tablet', '5mg', 'tablet', 40, 'Calcium channel blocker for faculty and staff hypertension maintenance.', 'Peripheral edema, flushing, dizziness.', 'Store at 15-30°C.', 1, 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50');
/*!40000 ALTER TABLE `medicines` ENABLE KEYS */;

-- Dumping structure for table clinic_management.medicine_batches
DROP TABLE IF EXISTS `medicine_batches`;
CREATE TABLE IF NOT EXISTS `medicine_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `medicine_id` bigint unsigned NOT NULL,
  `batch_number` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiration_date` date NOT NULL,
  `quantity_received` int unsigned NOT NULL,
  `quantity_remaining` int unsigned NOT NULL DEFAULT '0',
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `received_date` date NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medicine_batches_medicine_id_foreign` (`medicine_id`),
  KEY `medicine_batches_batch_number_index` (`batch_number`),
  KEY `medicine_batches_expiration_date_index` (`expiration_date`),
  KEY `medicine_batches_quantity_remaining_index` (`quantity_remaining`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.medicine_batches: 10 rows
DELETE FROM `medicine_batches`;
/*!40000 ALTER TABLE `medicine_batches` DISABLE KEYS */;
INSERT INTO `medicine_batches` (`id`, `medicine_id`, `batch_number`, `supplier`, `expiration_date`, `quantity_received`, `quantity_remaining`, `unit_cost`, `received_date`, `remarks`, `created_at`, `updated_at`) VALUES
	(1, 1, 'LOT-PCM-2026-A1', 'Unilab Pharmaceuticals', '2028-07-04', 500, 450, 3.50, '2026-10-15', NULL, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(2, 2, 'LOT-IBU-2026-03', 'Zuellig Pharma', '2028-03-04', 200, 175, 6.25, '2026-10-20', NULL, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(3, 3, 'LOT-MEF-2025-08', 'Metro Drug Distribution', '2026-12-14', 100, 25, 12.00, '2026-05-04', NULL, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(4, 4, 'LOT-CET-2026-X1', 'Zuellig Pharma', '2028-11-04', 300, 230, 8.50, '2026-10-25', NULL, '2026-11-04 09:37:50', '2026-11-04 09:50:05'),
	(5, 5, 'LOT-SAL-2026-09', 'GlaxoSmithKline Philippines', '2028-05-04', 80, 65, 28.00, '2026-10-10', NULL, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(6, 6, 'LOT-KRM-2026-11', 'Unilab Pharmaceuticals', '2028-01-04', 250, 210, 7.00, '2026-10-17', NULL, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(7, 7, 'LOT-ORS-2026-01', 'DOH Provincial Supply Depot', '2028-09-04', 120, 95, 5.00, '2026-10-05', NULL, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(8, 8, 'LOT-VIT-2026-55', 'Unilab Pharmaceuticals', '2028-06-04', 400, 360, 9.20, '2026-10-21', NULL, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(9, 9, 'LOT-BET-2026-02', 'Mundipharma Philippines', '2029-01-04', 30, 24, 125.00, '2026-10-13', NULL, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(10, 10, 'LOT-AML-2024-99', 'Pfizer Philippines', '2026-10-25', 50, 10, 18.00, '2025-05-04', NULL, '2026-11-04 09:37:50', '2026-11-04 09:37:50');
/*!40000 ALTER TABLE `medicine_batches` ENABLE KEYS */;

-- Dumping structure for table clinic_management.medicine_categories
DROP TABLE IF EXISTS `medicine_categories`;
CREATE TABLE IF NOT EXISTS `medicine_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medicine_categories_code_unique` (`code`),
  KEY `medicine_categories_is_active_index` (`is_active`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.medicine_categories: 8 rows
DELETE FROM `medicine_categories`;
/*!40000 ALTER TABLE `medicine_categories` DISABLE KEYS */;
INSERT INTO `medicine_categories` (`id`, `name`, `code`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Analgesics & Antipyretics', 'ANAL', 'Pain relievers and fever reducers', 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(2, 'Antibiotics & Antimicrobials', 'ANTI', 'Prescription antibacterial agents', 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(3, 'Antihistamines & Anti-Allergy', 'HIST', 'Allergy, rhinitis, and urticaria relief', 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(4, 'Respiratory & Cough / Cold', 'RESP', 'Bronchodilators, mucolytics, and decongestants', 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(5, 'Gastrointestinal Preparations', 'GI', 'Antacids, anti-diarrheal, and digestive aids', 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(6, 'Vitamins & Nutritional Supplements', 'VIT', 'Immune boosters and daily multivitamins', 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(7, 'First Aid & Antiseptics', 'AID', 'Wound care solutions and emergency clinic supplies', 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(8, 'Cardiovascular & Emergency', 'CARD', 'Blood pressure and vital emergency medications', 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50');
/*!40000 ALTER TABLE `medicine_categories` ENABLE KEYS */;

-- Dumping structure for table clinic_management.medicine_dispensings
DROP TABLE IF EXISTS `medicine_dispensings`;
CREATE TABLE IF NOT EXISTS `medicine_dispensings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dispensing_number` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patient_id` bigint unsigned NOT NULL,
  `clinician_id` bigint unsigned DEFAULT NULL,
  `dispensed_by` bigint unsigned DEFAULT NULL,
  `dispensed_date` datetime NOT NULL,
  `dispensed_at` datetime NOT NULL,
  `diagnosis` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `total_items` smallint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medicine_dispensings_dispensing_number_unique` (`dispensing_number`),
  KEY `medicine_dispensings_patient_id_foreign` (`patient_id`),
  KEY `medicine_dispensings_clinician_id_foreign` (`clinician_id`),
  KEY `medicine_dispensings_dispensed_by_foreign` (`dispensed_by`),
  KEY `medicine_dispensings_dispensed_date_index` (`dispensed_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.medicine_dispensings: 0 rows
DELETE FROM `medicine_dispensings`;
/*!40000 ALTER TABLE `medicine_dispensings` DISABLE KEYS */;
/*!40000 ALTER TABLE `medicine_dispensings` ENABLE KEYS */;

-- Dumping structure for table clinic_management.medicine_dispensing_items
DROP TABLE IF EXISTS `medicine_dispensing_items`;
CREATE TABLE IF NOT EXISTS `medicine_dispensing_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dispensing_id` bigint unsigned NOT NULL,
  `medicine_id` bigint unsigned NOT NULL,
  `batch_id` bigint unsigned DEFAULT NULL,
  `quantity` int unsigned NOT NULL,
  `dosage_instruction` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medicine_dispensing_items_dispensing_id_foreign` (`dispensing_id`),
  KEY `medicine_dispensing_items_medicine_id_foreign` (`medicine_id`),
  KEY `medicine_dispensing_items_batch_id_foreign` (`batch_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.medicine_dispensing_items: 0 rows
DELETE FROM `medicine_dispensing_items`;
/*!40000 ALTER TABLE `medicine_dispensing_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `medicine_dispensing_items` ENABLE KEYS */;

-- Dumping structure for table clinic_management.migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.migrations: 16 rows
DELETE FROM `migrations`;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2026_11_04_135218_create_permission_tables', 1),
	(5, '2026_11_04_135243_add_status_columns_to_users_table', 1),
	(6, '2026_11_04_135244_create_audit_logs_table', 1),
	(7, '2026_11_04_135245_create_settings_table', 1),
	(8, '2026_11_04_135246_create_backups_table', 1),
	(9, '2026_11_04_135247_create_notifications_table', 1),
	(10, '2026_11_04_143038_add_google_id_to_users_table', 1),
	(11, '2026_11_04_152344_create_academic_years_table', 1),
	(12, '2026_11_04_152359_create_colleges_and_programs_tables', 1),
	(13, '2026_11_04_153500_create_clinicians_table', 1),
	(14, '2026_11_04_154301_create_patients_and_profiles_tables', 1),
	(15, '2026_11_04_161355_create_medical_profiles_and_physical_examinations_tables', 1),
	(16, '2026_11_04_170000_create_medicines_and_inventory_tables', 1),
	(17, '2026_11_05_004154_create_clinic_operations_tables', 2),
	(18, '2026_11_05_021925_add_oral_health_fields_to_dental_consultations', 3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;

-- Dumping structure for table clinic_management.model_has_permissions
DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.model_has_permissions: 0 rows
DELETE FROM `model_has_permissions`;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;

-- Dumping structure for table clinic_management.model_has_roles
DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.model_has_roles: 3 rows
DELETE FROM `model_has_roles`;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
	(1, 'App\\Models\\User', 1),
	(1, 'App\\Models\\User', 2),
	(1, 'App\\Models\\User', 3);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;

-- Dumping structure for table clinic_management.notifications
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.notifications: 0 rows
DELETE FROM `notifications`;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;

-- Dumping structure for table clinic_management.password_reset_tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.password_reset_tokens: 0 rows
DELETE FROM `password_reset_tokens`;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;

-- Dumping structure for table clinic_management.patients
DROP TABLE IF EXISTS `patients`;
CREATE TABLE IF NOT EXISTS `patients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `suffix` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date NOT NULL,
  `civil_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Single',
  `blood_type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(35) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `emergency_contact_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_relationship` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(35) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `allergies` text COLLATE utf8mb4_unicode_ci,
  `medical_history` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `college_id` bigint unsigned DEFAULT NULL,
  `program_id` bigint unsigned DEFAULT NULL,
  `academic_year_id` bigint unsigned DEFAULT NULL,
  `year_level` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `section` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enrollment_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `office_department` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employment_classification` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employment_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patients_patient_number_unique` (`patient_number`),
  KEY `patients_college_id_foreign` (`college_id`),
  KEY `patients_program_id_foreign` (`program_id`),
  KEY `patients_academic_year_id_foreign` (`academic_year_id`),
  KEY `patients_school_id_number_index` (`school_id_number`),
  KEY `patients_type_index` (`type`),
  KEY `patients_email_index` (`email`),
  KEY `patients_phone_index` (`phone`),
  KEY `patients_status_index` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.patients: 4 rows
DELETE FROM `patients`;
/*!40000 ALTER TABLE `patients` DISABLE KEYS */;
INSERT INTO `patients` (`id`, `patient_number`, `school_id_number`, `type`, `first_name`, `middle_name`, `last_name`, `suffix`, `gender`, `date_of_birth`, `civil_status`, `blood_type`, `email`, `phone`, `address`, `emergency_contact_name`, `emergency_contact_relationship`, `emergency_contact_phone`, `allergies`, `medical_history`, `status`, `college_id`, `program_id`, `academic_year_id`, `year_level`, `section`, `enrollment_status`, `office_department`, `employment_classification`, `category`, `position`, `employment_status`, `created_at`, `updated_at`) VALUES
	(1, 'PAT-2026-00001', '2023-01045', 'student', 'Krystal', 'Mae', 'Bernardino', NULL, 'Female', '2004-05-14', 'Single', 'O+', 'krystal.bernardino@snsu.edu.ph', '+63 912 345 6789', 'Brgy. Washington, Surigao City', 'Elena Bernardino', 'Mother', '+63 918 111 2233', 'Penicillin, Shellfish', 'Mild Asthma (controlled)', 'active', 1, 1, 1, '2nd Year', 'BSIT 2-A', 'enrolled', NULL, NULL, NULL, NULL, NULL, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(2, 'PAT-2026-00002', '2022-00812', 'student', 'Joshua', 'Paul', 'Alcantara', 'Jr.', 'Male', '2003-11-20', 'Single', 'A+', 'joshua.alcantara@snsu.edu.ph', '+63 920 456 7890', 'Brgy. San Juan, Surigao City', 'Joshua Alcantara Sr.', 'Father', '+63 921 222 3344', 'None', 'No prior chronic conditions', 'active', 3, 5, 1, '3rd Year', 'BSN 3-B', 'enrolled', NULL, NULL, NULL, NULL, NULL, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(3, 'PAT-2026-00003', 'EMP-2016-0089', 'employee', 'Ramon', 'Carlos', 'Villanueva', NULL, 'Male', '1985-03-25', 'Married', 'B+', 'ramon.villanueva@snsu.edu.ph', '+63 917 555 4321', 'Poblacion, Surigao City', 'Carla Villanueva', 'Spouse', '+63 917 888 9900', 'Ibuprofen', 'Hypertension Stage 1 (on Maintenance)', 'active', 1, NULL, NULL, NULL, NULL, NULL, 'Department of Computer Science', 'Permanent', 'Faculty', 'Assistant Professor IV', 'active', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(4, 'PAT-2026-00004', 'EMP-2020-0145', 'employee', 'Maria', 'Corazon', 'Dela Torre', NULL, 'Female', '1990-09-12', 'Single', 'AB+', 'maria.delatorre@snsu.edu.ph', '+63 928 333 4455', 'Brgy. Taft, Surigao City', 'Teresa Dela Torre', 'Sister', '+63 929 444 5566', 'Dust mites', 'Allergic rhinitis', 'active', NULL, NULL, NULL, NULL, NULL, NULL, 'Office of the University Registrar', 'Permanent', 'Non-Teaching / Staff', 'Administrative Officer II', 'active', '2026-11-04 09:37:50', '2026-11-04 09:37:50');
/*!40000 ALTER TABLE `patients` ENABLE KEYS */;

-- Dumping structure for table clinic_management.permissions
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=MyISAM AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.permissions: 73 rows
DELETE FROM `permissions`;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'dashboard.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(2, 'users.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(3, 'users.create', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(4, 'users.edit', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(5, 'users.delete', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(6, 'roles.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(7, 'roles.create', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(8, 'roles.edit', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(9, 'roles.delete', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(10, 'permissions.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(11, 'permissions.create', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(12, 'permissions.edit', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(13, 'permissions.delete', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(14, 'audit-logs.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(15, 'settings.manage', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(16, 'backups.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(17, 'backups.create', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(18, 'backups.download', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(19, 'backups.delete', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(20, 'academic-years.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(21, 'academic-years.create', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(22, 'academic-years.edit', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(23, 'academic-years.delete', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(24, 'colleges.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(25, 'colleges.create', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(26, 'colleges.edit', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(27, 'colleges.delete', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(28, 'programs.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(29, 'programs.create', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(30, 'programs.edit', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(31, 'programs.delete', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(32, 'clinicians.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(33, 'clinicians.create', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(34, 'clinicians.edit', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(35, 'clinicians.delete', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(36, 'patients.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(37, 'patients.create', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(38, 'patients.edit', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(39, 'patients.delete', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(40, 'patients.import', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(41, 'students.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(42, 'students.create', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(43, 'students.edit', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(44, 'students.delete', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(45, 'students.import', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(46, 'employees.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(47, 'employees.create', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(48, 'employees.edit', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(49, 'employees.delete', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(50, 'employees.import', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(51, 'physical-examinations.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(52, 'physical-examinations.create', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(53, 'physical-examinations.edit', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(54, 'physical-examinations.delete', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(55, 'physical-examinations.print', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(56, 'medical-profiles.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(57, 'medical-profiles.edit', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(58, 'medical-certificates.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(59, 'medical-certificates.create', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(60, 'medical-certificates.print', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(61, 'medicines.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(62, 'medicines.create', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(63, 'medicines.edit', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(64, 'medicines.delete', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(65, 'medicine-categories.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(66, 'medicine-categories.create', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(67, 'medicine-categories.edit', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(68, 'medicine-categories.delete', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(69, 'inventory.view', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(70, 'inventory.stock-in', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(71, 'inventory.stock-out', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(72, 'inventory.dispense', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(73, 'inventory.reports', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(74, 'clinic-visits.view', 'web', '2026-11-04 16:50:26', '2026-11-04 16:50:26'),
	(75, 'clinic-visits.create', 'web', '2026-11-04 16:50:26', '2026-11-04 16:50:26'),
	(76, 'clinic-visits.edit', 'web', '2026-11-04 16:50:26', '2026-11-04 16:50:26'),
	(77, 'clinic-visits.delete', 'web', '2026-11-04 16:50:26', '2026-11-04 16:50:26'),
	(78, 'medical-consultations.view', 'web', '2026-11-04 16:50:26', '2026-11-04 16:50:26'),
	(79, 'medical-consultations.create', 'web', '2026-11-04 16:50:26', '2026-11-04 16:50:26'),
	(80, 'medical-consultations.edit', 'web', '2026-11-04 16:50:27', '2026-11-04 16:50:27'),
	(81, 'medical-consultations.delete', 'web', '2026-11-04 16:50:27', '2026-11-04 16:50:27'),
	(82, 'dental-consultations.view', 'web', '2026-11-04 16:50:27', '2026-11-04 16:50:27'),
	(83, 'dental-consultations.create', 'web', '2026-11-04 16:50:27', '2026-11-04 16:50:27'),
	(84, 'dental-consultations.edit', 'web', '2026-11-04 16:50:27', '2026-11-04 16:50:27'),
	(85, 'dental-consultations.delete', 'web', '2026-11-04 16:50:27', '2026-11-04 16:50:27'),
	(86, 'emergency-records.view', 'web', '2026-11-04 16:50:27', '2026-11-04 16:50:27'),
	(87, 'emergency-records.create', 'web', '2026-11-04 16:50:27', '2026-11-04 16:50:27'),
	(88, 'emergency-records.edit', 'web', '2026-11-04 16:50:27', '2026-11-04 16:50:27'),
	(89, 'emergency-records.delete', 'web', '2026-11-04 16:50:27', '2026-11-04 16:50:27'),
	(90, 'first-aid-records.view', 'web', '2026-11-04 16:50:27', '2026-11-04 16:50:27'),
	(91, 'first-aid-records.create', 'web', '2026-11-04 16:50:28', '2026-11-04 16:50:28'),
	(92, 'first-aid-records.edit', 'web', '2026-11-04 16:50:28', '2026-11-04 16:50:28'),
	(93, 'first-aid-records.delete', 'web', '2026-11-04 16:50:28', '2026-11-04 16:50:28'),
	(94, 'physical-exam-schedules.view', 'web', '2026-11-04 16:50:28', '2026-11-04 16:50:28'),
	(95, 'physical-exam-schedules.create', 'web', '2026-11-04 16:50:28', '2026-11-04 16:50:28'),
	(96, 'physical-exam-schedules.edit', 'web', '2026-11-04 16:50:28', '2026-11-04 16:50:28'),
	(97, 'physical-exam-schedules.delete', 'web', '2026-11-04 16:50:28', '2026-11-04 16:50:28');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;

-- Dumping structure for table clinic_management.physical_examinations
DROP TABLE IF EXISTS `physical_examinations`;
CREATE TABLE IF NOT EXISTS `physical_examinations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pe_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patient_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned DEFAULT NULL,
  `examiner_id` bigint unsigned DEFAULT NULL,
  `clinician_id` bigint unsigned DEFAULT NULL,
  `examiner_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `examiner_license_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exam_date` date NOT NULL,
  `purpose` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Annual Physical Exam',
  `systolic` smallint unsigned DEFAULT NULL,
  `diastolic` smallint unsigned DEFAULT NULL,
  `pulse_rate` smallint unsigned DEFAULT NULL,
  `respiratory_rate` smallint unsigned DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `oxygen_saturation` smallint unsigned DEFAULT NULL,
  `height_cm` decimal(5,2) DEFAULT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `bmi` decimal(4,2) DEFAULT NULL,
  `bmi_category` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visual_acuity_right` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '20/20',
  `visual_acuity_left` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '20/20',
  `visual_acuity_is_corrected` tinyint(1) NOT NULL DEFAULT '0',
  `color_vision` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Normal',
  `hearing` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Normal',
  `general_appearance` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT 'Conscious, coherent, ambulatory, well-nourished',
  `skin` json DEFAULT NULL,
  `heent` json DEFAULT NULL,
  `neck` json DEFAULT NULL,
  `chest_lungs` json DEFAULT NULL,
  `heart` json DEFAULT NULL,
  `abdomen` json DEFAULT NULL,
  `musculoskeletal` json DEFAULT NULL,
  `neurological` json DEFAULT NULL,
  `dental` json DEFAULT NULL,
  `chest_xray_findings` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chest_xray_date` date DEFAULT NULL,
  `laboratory_notes` text COLLATE utf8mb4_unicode_ci,
  `clinical_findings` text COLLATE utf8mb4_unicode_ci,
  `medical_recommendations` text COLLATE utf8mb4_unicode_ci,
  `fitness_assessment` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'Class A',
  `fitness_activities` json DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `physical_examinations_pe_number_unique` (`pe_number`),
  KEY `physical_examinations_patient_id_foreign` (`patient_id`),
  KEY `physical_examinations_academic_year_id_foreign` (`academic_year_id`),
  KEY `physical_examinations_examiner_id_foreign` (`examiner_id`),
  KEY `physical_examinations_clinician_id_foreign` (`clinician_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.physical_examinations: 4 rows
DELETE FROM `physical_examinations`;
/*!40000 ALTER TABLE `physical_examinations` DISABLE KEYS */;
INSERT INTO `physical_examinations` (`id`, `pe_number`, `patient_id`, `academic_year_id`, `examiner_id`, `clinician_id`, `examiner_name`, `examiner_license_number`, `exam_date`, `purpose`, `systolic`, `diastolic`, `pulse_rate`, `respiratory_rate`, `temperature`, `oxygen_saturation`, `height_cm`, `weight_kg`, `bmi`, `bmi_category`, `visual_acuity_right`, `visual_acuity_left`, `visual_acuity_is_corrected`, `color_vision`, `hearing`, `general_appearance`, `skin`, `heent`, `neck`, `chest_lungs`, `heart`, `abdomen`, `musculoskeletal`, `neurological`, `dental`, `chest_xray_findings`, `chest_xray_date`, `laboratory_notes`, `clinical_findings`, `medical_recommendations`, `fitness_assessment`, `fitness_activities`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'PE-2026-00001', 1, 1, NULL, 1, 'DR. VICENTE S. QUIMPO, MD', '0052551', '2026-11-02', 'Annual Physical Exam', 120, 80, 74, 18, 36.6, 99, 165.00, 58.50, 21.49, 'Normal', '20/20', '20/20', 0, 'Normal', 'Normal', 'Conscious, coherent, ambulatory, well-nourished', '{"status": "normal", "remarks": "Good skin turgor, no jaundice, no pallor"}', '{"status": "normal", "remarks": "Anicteric sclerae, pink palpebral conjunctivae"}', '{"status": "normal", "remarks": "Supple, no palpable lymphadenopathy"}', '{"status": "normal", "remarks": "Clear breath sounds, symmetrical chest expansion"}', '{"status": "normal", "remarks": "Normal rate, regular rhythm, no murmurs"}', '{"status": "normal", "remarks": "Flat, soft, non-tender, no organomegaly"}', '{"status": "normal", "remarks": "Full range of motion, normal gait"}', '{"status": "normal", "remarks": "Grossly intact motor and sensory function"}', '{"status": "normal", "remarks": "Good oral hygiene"}', 'Normal chest radiograph, no active lung infiltrates', '2026-10-15', 'CBC, Urinalysis, and routine screening normal', 'Essentially normal physical examination findings', 'Maintain balanced diet, regular exercise, adequate hydration', 'Class A', '{"to_work": "fit", "to_travel": "fit", "work_immersion": "fit", "other_requirements": "fit", "on_the_job_training": "fit", "physical_activities": "fit"}', 'completed', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(2, 'PE-2026-00002', 2, 1, NULL, 1, 'DR. VICENTE S. QUIMPO, MD', '0052551', '2026-10-30', 'Annual Physical Exam', 120, 80, 74, 18, 36.6, 99, 165.00, 58.50, 21.49, 'Normal', '20/20', '20/20', 0, 'Normal', 'Normal', 'Conscious, coherent, ambulatory, well-nourished', '{"status": "normal", "remarks": "Good skin turgor, no jaundice, no pallor"}', '{"status": "normal", "remarks": "Anicteric sclerae, pink palpebral conjunctivae"}', '{"status": "normal", "remarks": "Supple, no palpable lymphadenopathy"}', '{"status": "normal", "remarks": "Clear breath sounds, symmetrical chest expansion"}', '{"status": "normal", "remarks": "Normal rate, regular rhythm, no murmurs"}', '{"status": "normal", "remarks": "Flat, soft, non-tender, no organomegaly"}', '{"status": "normal", "remarks": "Full range of motion, normal gait"}', '{"status": "normal", "remarks": "Grossly intact motor and sensory function"}', '{"status": "normal", "remarks": "Good oral hygiene"}', 'Normal chest radiograph, no active lung infiltrates', '2026-10-15', 'CBC, Urinalysis, and routine screening normal', 'Essentially normal physical examination findings', 'Maintain balanced diet, regular exercise, adequate hydration', 'Class A', '{"to_work": "fit", "to_travel": "fit", "work_immersion": "fit", "other_requirements": "fit", "on_the_job_training": "fit", "physical_activities": "fit"}', 'completed', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(3, 'PE-2026-00003', 3, 1, NULL, 1, 'DR. VICENTE S. QUIMPO, MD', '0052551', '2026-10-25', 'Annual Physical Exam', 120, 80, 74, 18, 36.6, 99, 165.00, 58.50, 21.49, 'Normal', '20/20', '20/20', 0, 'Normal', 'Normal', 'Conscious, coherent, ambulatory, well-nourished', '{"status": "normal", "remarks": "Good skin turgor, no jaundice, no pallor"}', '{"status": "normal", "remarks": "Anicteric sclerae, pink palpebral conjunctivae"}', '{"status": "normal", "remarks": "Supple, no palpable lymphadenopathy"}', '{"status": "normal", "remarks": "Clear breath sounds, symmetrical chest expansion"}', '{"status": "normal", "remarks": "Normal rate, regular rhythm, no murmurs"}', '{"status": "normal", "remarks": "Flat, soft, non-tender, no organomegaly"}', '{"status": "normal", "remarks": "Full range of motion, normal gait"}', '{"status": "normal", "remarks": "Grossly intact motor and sensory function"}', '{"status": "normal", "remarks": "Good oral hygiene"}', 'Normal chest radiograph, no active lung infiltrates', '2026-10-15', 'CBC, Urinalysis, and routine screening normal', 'Essentially normal physical examination findings', 'Maintain balanced diet, regular exercise, adequate hydration', 'Class A', '{"to_work": "fit", "to_travel": "fit", "work_immersion": "fit", "other_requirements": "fit", "on_the_job_training": "fit", "physical_activities": "fit"}', 'completed', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(4, 'PE-2026-00004', 4, 1, NULL, 1, 'DR. VICENTE S. QUIMPO, MD', '0052551', '2026-10-21', 'Annual Physical Exam', 120, 80, 74, 18, 36.6, 99, 165.00, 58.50, 21.49, 'Normal', '20/20', '20/20', 0, 'Normal', 'Normal', 'Conscious, coherent, ambulatory, well-nourished', '{"status": "normal", "remarks": "Good skin turgor, no jaundice, no pallor"}', '{"status": "normal", "remarks": "Anicteric sclerae, pink palpebral conjunctivae"}', '{"status": "normal", "remarks": "Supple, no palpable lymphadenopathy"}', '{"status": "normal", "remarks": "Clear breath sounds, symmetrical chest expansion"}', '{"status": "normal", "remarks": "Normal rate, regular rhythm, no murmurs"}', '{"status": "normal", "remarks": "Flat, soft, non-tender, no organomegaly"}', '{"status": "normal", "remarks": "Full range of motion, normal gait"}', '{"status": "normal", "remarks": "Grossly intact motor and sensory function"}', '{"status": "normal", "remarks": "Good oral hygiene"}', 'Normal chest radiograph, no active lung infiltrates', '2026-10-15', 'CBC, Urinalysis, and routine screening normal', 'Essentially normal physical examination findings', 'Maintain balanced diet, regular exercise, adequate hydration', 'Class A', '{"to_work": "fit", "to_travel": "fit", "work_immersion": "fit", "other_requirements": "fit", "on_the_job_training": "fit", "physical_activities": "fit"}', 'completed', '2026-11-04 09:37:50', '2026-11-04 09:37:50');
/*!40000 ALTER TABLE `physical_examinations` ENABLE KEYS */;

-- Dumping structure for table clinic_management.physical_exam_schedules
DROP TABLE IF EXISTS `physical_exam_schedules`;
CREATE TABLE IF NOT EXISTS `physical_exam_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `schedule_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Annual Physical Exam',
  `target_classification` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'student',
  `academic_year_id` bigint unsigned DEFAULT NULL,
  `college_id` bigint unsigned DEFAULT NULL,
  `program_id` bigint unsigned DEFAULT NULL,
  `year_level` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `office_department` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduled_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `venue` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacity` int unsigned DEFAULT NULL,
  `clinician_id` bigint unsigned DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `physical_exam_schedules_schedule_number_unique` (`schedule_number`),
  KEY `physical_exam_schedules_academic_year_id_foreign` (`academic_year_id`),
  KEY `physical_exam_schedules_college_id_foreign` (`college_id`),
  KEY `physical_exam_schedules_program_id_foreign` (`program_id`),
  KEY `physical_exam_schedules_clinician_id_foreign` (`clinician_id`),
  KEY `physical_exam_schedules_created_by_foreign` (`created_by`),
  KEY `physical_exam_schedules_target_classification_index` (`target_classification`),
  KEY `physical_exam_schedules_scheduled_date_index` (`scheduled_date`),
  KEY `physical_exam_schedules_status_index` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.physical_exam_schedules: 1 rows
DELETE FROM `physical_exam_schedules`;
/*!40000 ALTER TABLE `physical_exam_schedules` DISABLE KEYS */;
INSERT INTO `physical_exam_schedules` (`id`, `schedule_number`, `title`, `purpose`, `target_classification`, `academic_year_id`, `college_id`, `program_id`, `year_level`, `office_department`, `scheduled_date`, `start_time`, `end_time`, `venue`, `capacity`, `clinician_id`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
	(1, 'PES-2026-0001', 'College of Engineering & Information Technology Annual PE Screening', 'Annual Physical Exam', 'student', 1, 1, NULL, '1st Year', NULL, '2026-11-08', '08:00:00', '16:00:00', 'University Health Services Department - Examination Room 1', 60, 1, 'scheduled', 'Students are advised to fast for 8 hours prior if blood chemistry is included. Bring student ID.', 1, '2026-11-04 16:50:29', '2026-11-04 16:50:29');
/*!40000 ALTER TABLE `physical_exam_schedules` ENABLE KEYS */;

-- Dumping structure for table clinic_management.physical_exam_schedule_attendees
DROP TABLE IF EXISTS `physical_exam_schedule_attendees`;
CREATE TABLE IF NOT EXISTS `physical_exam_schedule_attendees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `physical_exam_schedule_id` bigint unsigned NOT NULL,
  `patient_id` bigint unsigned NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `physical_examination_id` bigint unsigned DEFAULT NULL,
  `clinic_visit_id` bigint unsigned DEFAULT NULL,
  `attended_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pes_att_unique` (`physical_exam_schedule_id`,`patient_id`),
  KEY `fk_pes_att_pat` (`patient_id`),
  KEY `fk_pes_att_pe` (`physical_examination_id`),
  KEY `fk_pes_att_vis` (`clinic_visit_id`),
  KEY `physical_exam_schedule_attendees_status_index` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.physical_exam_schedule_attendees: 4 rows
DELETE FROM `physical_exam_schedule_attendees`;
/*!40000 ALTER TABLE `physical_exam_schedule_attendees` DISABLE KEYS */;
INSERT INTO `physical_exam_schedule_attendees` (`id`, `physical_exam_schedule_id`, `patient_id`, `status`, `physical_examination_id`, `clinic_visit_id`, `attended_at`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 'attended', NULL, NULL, '2026-11-05 01:10:19', '2026-11-04 16:50:29', '2026-11-04 17:10:19'),
	(2, 1, 2, 'attended', NULL, NULL, '2026-11-05 02:47:08', '2026-11-04 16:50:29', '2026-11-04 18:47:08'),
	(3, 1, 3, 'attended', NULL, NULL, '2026-11-05 02:46:46', '2026-11-04 16:50:29', '2026-11-04 18:46:46'),
	(4, 1, 4, 'attended', NULL, NULL, '2026-11-05 02:46:48', '2026-11-04 16:50:29', '2026-11-04 18:46:48');
/*!40000 ALTER TABLE `physical_exam_schedule_attendees` ENABLE KEYS */;

-- Dumping structure for table clinic_management.programs
DROP TABLE IF EXISTS `programs`;
CREATE TABLE IF NOT EXISTS `programs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `college_id` bigint unsigned NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `degree_level` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Undergraduate',
  `years` tinyint unsigned NOT NULL DEFAULT '4',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `programs_code_unique` (`code`),
  KEY `programs_college_id_foreign` (`college_id`),
  KEY `programs_is_active_index` (`is_active`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.programs: 5 rows
DELETE FROM `programs`;
/*!40000 ALTER TABLE `programs` DISABLE KEYS */;
INSERT INTO `programs` (`id`, `college_id`, `code`, `name`, `degree_level`, `years`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 1, 'BSIT', 'Bachelor of Science in Information Technology', 'Undergraduate', 4, 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(2, 1, 'BSCS', 'Bachelor of Science in Computer Science', 'Undergraduate', 4, 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(3, 2, 'BSCE', 'Bachelor of Science in Civil Engineering', 'Undergraduate', 4, 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(4, 2, 'BSEE', 'Bachelor of Science in Electrical Engineering', 'Undergraduate', 4, 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(5, 3, 'BSN', 'Bachelor of Science in Nursing', 'Undergraduate', 4, 1, '2026-11-04 09:37:50', '2026-11-04 09:37:50');
/*!40000 ALTER TABLE `programs` ENABLE KEYS */;

-- Dumping structure for table clinic_management.roles
DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.roles: 5 rows
DELETE FROM `roles`;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'Super Admin', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(2, 'Administrator', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(3, 'Doctor', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(4, 'Nurse', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(5, 'Receptionist', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;

-- Dumping structure for table clinic_management.role_has_permissions
DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.role_has_permissions: 136 rows
DELETE FROM `role_has_permissions`;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
	(1, 2),
	(1, 3),
	(1, 4),
	(1, 5),
	(2, 2),
	(3, 2),
	(4, 2),
	(5, 2),
	(6, 2),
	(14, 2),
	(15, 2),
	(16, 2),
	(17, 2),
	(18, 2),
	(20, 2),
	(21, 2),
	(22, 2),
	(23, 2),
	(24, 2),
	(25, 2),
	(26, 2),
	(27, 2),
	(28, 2),
	(29, 2),
	(30, 2),
	(31, 2),
	(32, 2),
	(32, 3),
	(32, 4),
	(33, 2),
	(33, 3),
	(33, 4),
	(34, 2),
	(34, 3),
	(34, 4),
	(35, 2),
	(36, 2),
	(36, 3),
	(36, 4),
	(36, 5),
	(37, 2),
	(37, 3),
	(37, 4),
	(37, 5),
	(38, 2),
	(38, 3),
	(38, 4),
	(38, 5),
	(39, 2),
	(40, 2),
	(41, 2),
	(41, 3),
	(41, 4),
	(41, 5),
	(42, 2),
	(42, 3),
	(42, 4),
	(42, 5),
	(43, 2),
	(43, 3),
	(43, 4),
	(43, 5),
	(44, 2),
	(45, 2),
	(46, 2),
	(46, 3),
	(46, 4),
	(46, 5),
	(47, 2),
	(47, 3),
	(47, 4),
	(47, 5),
	(48, 2),
	(48, 3),
	(48, 4),
	(48, 5),
	(49, 2),
	(50, 2),
	(51, 2),
	(51, 3),
	(51, 4),
	(51, 5),
	(52, 2),
	(52, 3),
	(52, 4),
	(53, 2),
	(53, 3),
	(53, 4),
	(54, 2),
	(55, 2),
	(55, 3),
	(55, 4),
	(56, 2),
	(56, 3),
	(56, 4),
	(57, 2),
	(57, 3),
	(57, 4),
	(58, 2),
	(58, 3),
	(58, 4),
	(59, 2),
	(59, 3),
	(59, 4),
	(60, 2),
	(60, 3),
	(60, 4),
	(61, 2),
	(61, 3),
	(61, 4),
	(62, 2),
	(62, 3),
	(62, 4),
	(63, 2),
	(63, 3),
	(63, 4),
	(64, 2),
	(65, 2),
	(66, 2),
	(67, 2),
	(68, 2),
	(69, 2),
	(69, 3),
	(69, 4),
	(70, 2),
	(70, 3),
	(70, 4),
	(71, 2),
	(71, 3),
	(71, 4),
	(72, 2),
	(72, 3),
	(72, 4),
	(73, 2),
	(73, 3),
	(73, 4),
	(74, 2),
	(74, 3),
	(74, 4),
	(74, 5),
	(75, 2),
	(75, 3),
	(75, 4),
	(75, 5),
	(76, 2),
	(76, 3),
	(76, 4),
	(77, 2),
	(78, 2),
	(78, 3),
	(78, 4),
	(79, 2),
	(79, 3),
	(79, 4),
	(80, 2),
	(80, 3),
	(80, 4),
	(81, 2),
	(82, 2),
	(82, 3),
	(82, 4),
	(83, 2),
	(83, 3),
	(83, 4),
	(84, 2),
	(84, 3),
	(84, 4),
	(85, 2),
	(86, 2),
	(86, 3),
	(86, 4),
	(87, 2),
	(87, 3),
	(87, 4),
	(88, 2),
	(88, 3),
	(88, 4),
	(89, 2),
	(90, 2),
	(90, 3),
	(90, 4),
	(91, 2),
	(91, 3),
	(91, 4),
	(92, 2),
	(92, 3),
	(92, 4),
	(93, 2),
	(94, 2),
	(94, 3),
	(94, 4),
	(94, 5),
	(95, 2),
	(95, 3),
	(95, 4),
	(96, 2),
	(96, 3),
	(96, 4),
	(97, 2);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;

-- Dumping structure for table clinic_management.sessions
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.sessions: 1 rows
DELETE FROM `sessions`;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('uNYSRNxIFsEEQkKjyY9d6ednzZw5zxgKTEF673aV', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36 Edg/156.0.0.0', 'eyJfdG9rZW4iOiJETDFPWmZodnRnaG04SFJVTk5ETzBRbmYyNzZqTmRWR2dtOHBGbjlPIiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjIsIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9jbGluaWMuZGV2LmNvbVwvcGh5c2ljYWwtZXhhbWluYXRpb25zXC9jcmVhdGUiLCJyb3V0ZSI6InBoeXNpY2FsLWV4YW1pbmF0aW9ucy5jcmVhdGUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1793873279);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;

-- Dumping structure for table clinic_management.settings
DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`),
  KEY `settings_group_index` (`group`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.settings: 17 rows
DELETE FROM `settings`;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` (`id`, `group`, `key`, `value`, `created_at`, `updated_at`) VALUES
	(1, 'university', 'university_name', 'NORTHWESTERN VISAYAN COLLEGES INC.', '2026-11-04 09:37:50', '2026-11-05 02:00:45'),
	(2, 'university', 'university_short_name', 'NVC', '2026-11-04 09:37:50', '2026-11-05 01:39:01'),
	(3, 'university', 'university_campus', 'Kalibo Aklan', '2026-11-04 09:37:50', '2026-11-05 01:39:01'),
	(4, 'university', 'university_address', 'Kalibo Aklan', '2026-11-04 09:37:50', '2026-11-05 01:39:20'),
	(5, 'university', 'university_president', 'DR. JO E. CAPISTRANO', '2026-11-04 09:37:50', '2026-11-04 18:09:31'),
	(6, 'university', 'university_website', 'https://adssu.edu.ph', '2026-11-04 09:37:50', '2026-11-04 18:09:31'),
	(7, 'general', 'clinic_name', 'ClinicAssist', '2026-11-04 09:37:50', '2026-11-05 01:39:52'),
	(8, 'general', 'clinic_head', 'Dr. Maria Elena Santos, MD', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(9, 'general', 'clinic_license_no', 'DOH-R13-CLN-2026-088', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(10, 'general', 'clinic_email', 'clinic@snsu.edu.ph', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(11, 'general', 'clinic_phone', '+63 (86) 826-0135', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(12, 'general', 'clinic_address', 'Ground Floor, Student Services Center, SNSU Main Campus', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(13, 'general', 'date_format', 'M d, Y', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(14, 'general', 'table_page_length', '10', '2026-11-04 09:37:50', '2026-11-04 17:47:33'),
	(15, 'backup', 'backup_enabled', '1', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(16, 'backup', 'backup_retention_days', '30', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(17, 'security', 'audit_log_retention_days', '365', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(18, 'branding', 'logo_print_enabled', '1', '2026-11-04 18:08:00', '2026-11-04 18:08:00'),
	(19, 'branding', 'logo_print_variant', 'seal', '2026-11-04 18:08:00', '2026-11-04 18:08:00'),
	(20, 'branding', 'logo_print_size', 'medium', '2026-11-04 18:08:00', '2026-11-04 18:08:00'),
	(21, 'branding', 'logo_print_position', 'left', '2026-11-04 18:08:00', '2026-11-04 18:08:00'),
	(22, 'branding', 'logo_original', 'logos/original_doXxyw8XMW9a.png', '2026-11-04 18:08:00', '2026-11-05 01:37:57'),
	(23, 'branding', 'logo_large', 'logos/large_doXxyw8XMW9a.png', '2026-11-04 18:08:00', '2026-11-05 01:37:57'),
	(24, 'branding', 'logo_primary', 'logos/large_doXxyw8XMW9a.png', '2026-11-04 18:08:00', '2026-11-05 01:37:57'),
	(25, 'branding', 'logo_medium', 'logos/medium_doXxyw8XMW9a.png', '2026-11-04 18:08:00', '2026-11-05 01:37:57'),
	(26, 'branding', 'logo_small', 'logos/small_doXxyw8XMW9a.png', '2026-11-04 18:08:00', '2026-11-05 01:37:57'),
	(27, 'branding', 'logo_compact', 'logos/small_doXxyw8XMW9a.png', '2026-11-04 18:08:00', '2026-11-05 01:37:57'),
	(28, 'branding', 'logo_favicon', 'logos/favicon_doXxyw8XMW9a.png', '2026-11-04 18:08:00', '2026-11-05 01:37:57'),
	(29, 'branding', 'logo_transparent', 'logos/transparent_doXxyw8XMW9a.png', '2026-11-04 18:08:00', '2026-11-05 01:37:57'),
	(30, 'branding', 'logo_seal', 'logos/seal_doXxyw8XMW9a.png', '2026-11-04 18:08:00', '2026-11-05 01:37:57'),
	(31, 'branding', 'logo_grayscale', 'logos/grayscale_doXxyw8XMW9a.png', '2026-11-04 18:08:00', '2026-11-05 01:37:57'),
	(32, 'branding', 'logo_watermark', 'logos/watermark_doXxyw8XMW9a.png', '2026-11-04 18:08:00', '2026-11-05 01:37:57');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;

-- Dumping structure for table clinic_management.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_google_id_unique` (`google_id`),
  KEY `users_is_active_name_index` (`is_active`,`name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.users: 3 rows
DELETE FROM `users`;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `google_id`, `avatar`, `is_active`, `last_login_at`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'System Administrator', 'admin@clinic.test', NULL, '2026-11-04 09:37:50', '$2y$12$3kNvZ50Hb3gEXhQFezvvg.18.Xnuq8cujyHpakRwoQjr.0DvP9vRO', NULL, NULL, 1, '2026-11-04 17:09:54', NULL, '2026-11-04 09:37:51', '2026-11-04 17:09:54'),
	(2, 'Super Admin (SNSU)', 'apiloton1@snsu.edu.ph', NULL, '2026-11-04 09:37:51', '$2y$12$F3aSuyASP9cgev25AmotZO2pzfzbawteSFw.eZZVwwmYoFEPZndJu', '117952079880321133596', 'https://lh3.googleusercontent.com/a/ACg8ocICxZuvbmnED8B5vH9nAzZ75oj4EyyJOq04YOMO7jxaSaNGZqsv=s96-c', 1, '2026-11-04 23:48:33', 'RDpFW0dPbUJzB6LVp1ibgwNyWEDq5sCE8nYf5foIl1LzyrZhVbhQnibQfxCJ', '2026-11-04 09:37:51', '2026-11-04 23:48:33'),
	(3, 'Super Admin (ADSSU)', 'alpiloton@adssu.edu.ph', NULL, '2026-11-04 09:37:51', '$2y$12$lmJ6Ijt3oWxCCkACwriLz.u494dSubSxPQW44hygPRPnPyg3C8KaG', NULL, NULL, 1, NULL, NULL, '2026-11-04 09:37:51', '2026-11-04 09:37:51');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
