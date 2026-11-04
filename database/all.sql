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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.audit_logs: 3 rows
DELETE FROM `audit_logs`;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` (`id`, `user_id`, `event`, `description`, `subject_type`, `subject_id`, `properties`, `ip_address`, `user_agent`, `created_at`) VALUES
	(1, 2, 'login', 'Super Admin (SNSU) logged in', 'App\\Models\\User', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 09:38:14'),
	(2, 2, 'login', 'Super Admin (SNSU) signed in via Google', 'App\\Models\\User', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 09:38:14'),
	(3, 2, 'updated', 'Updated healthcare provider DR. CARMINA V. DELOS REYES, MD', 'App\\Models\\Clinician', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 09:41:15'),
	(4, 2, 'stock_out', 'Deducted 50 tablets from Cetirizine Dihydrochloride (Alnix) 10mg Tablet (gbbfg)', 'App\\Models\\MedicineBatch', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-04 09:50:05');
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
	('nclinicassist-cache-spatie.permission.cache', 'a:3:{s:5:"alias";a:4:{s:1:"a";s:2:"id";s:1:"b";s:4:"name";s:1:"c";s:10:"guard_name";s:1:"r";s:5:"roles";}s:11:"permissions";a:73:{i:0;a:4:{s:1:"a";i:1;s:1:"b";s:14:"dashboard.view";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:1;a:4:{s:1:"a";i:2;s:1:"b";s:10:"users.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:2;a:4:{s:1:"a";i:3;s:1:"b";s:12:"users.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:3;a:4:{s:1:"a";i:4;s:1:"b";s:10:"users.edit";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:4;a:4:{s:1:"a";i:5;s:1:"b";s:12:"users.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:5;a:4:{s:1:"a";i:6;s:1:"b";s:10:"roles.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:6;a:3:{s:1:"a";i:7;s:1:"b";s:12:"roles.create";s:1:"c";s:3:"web";}i:7;a:3:{s:1:"a";i:8;s:1:"b";s:10:"roles.edit";s:1:"c";s:3:"web";}i:8;a:3:{s:1:"a";i:9;s:1:"b";s:12:"roles.delete";s:1:"c";s:3:"web";}i:9;a:3:{s:1:"a";i:10;s:1:"b";s:16:"permissions.view";s:1:"c";s:3:"web";}i:10;a:3:{s:1:"a";i:11;s:1:"b";s:18:"permissions.create";s:1:"c";s:3:"web";}i:11;a:3:{s:1:"a";i:12;s:1:"b";s:16:"permissions.edit";s:1:"c";s:3:"web";}i:12;a:3:{s:1:"a";i:13;s:1:"b";s:18:"permissions.delete";s:1:"c";s:3:"web";}i:13;a:4:{s:1:"a";i:14;s:1:"b";s:15:"audit-logs.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:14;a:4:{s:1:"a";i:15;s:1:"b";s:15:"settings.manage";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:15;a:4:{s:1:"a";i:16;s:1:"b";s:12:"backups.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:16;a:4:{s:1:"a";i:17;s:1:"b";s:14:"backups.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:17;a:4:{s:1:"a";i:18;s:1:"b";s:16:"backups.download";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:18;a:3:{s:1:"a";i:19;s:1:"b";s:14:"backups.delete";s:1:"c";s:3:"web";}i:19;a:4:{s:1:"a";i:20;s:1:"b";s:19:"academic-years.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:20;a:4:{s:1:"a";i:21;s:1:"b";s:21:"academic-years.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:21;a:4:{s:1:"a";i:22;s:1:"b";s:19:"academic-years.edit";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:22;a:4:{s:1:"a";i:23;s:1:"b";s:21:"academic-years.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:23;a:4:{s:1:"a";i:24;s:1:"b";s:13:"colleges.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:24;a:4:{s:1:"a";i:25;s:1:"b";s:15:"colleges.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:25;a:4:{s:1:"a";i:26;s:1:"b";s:13:"colleges.edit";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:26;a:4:{s:1:"a";i:27;s:1:"b";s:15:"colleges.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:27;a:4:{s:1:"a";i:28;s:1:"b";s:13:"programs.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:28;a:4:{s:1:"a";i:29;s:1:"b";s:15:"programs.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:29;a:4:{s:1:"a";i:30;s:1:"b";s:13:"programs.edit";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:30;a:4:{s:1:"a";i:31;s:1:"b";s:15:"programs.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:31;a:4:{s:1:"a";i:32;s:1:"b";s:15:"clinicians.view";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:32;a:4:{s:1:"a";i:33;s:1:"b";s:17:"clinicians.create";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:33;a:4:{s:1:"a";i:34;s:1:"b";s:15:"clinicians.edit";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:34;a:4:{s:1:"a";i:35;s:1:"b";s:17:"clinicians.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:35;a:4:{s:1:"a";i:36;s:1:"b";s:13:"patients.view";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:36;a:4:{s:1:"a";i:37;s:1:"b";s:15:"patients.create";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:37;a:4:{s:1:"a";i:38;s:1:"b";s:13:"patients.edit";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:38;a:4:{s:1:"a";i:39;s:1:"b";s:15:"patients.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:39;a:4:{s:1:"a";i:40;s:1:"b";s:15:"patients.import";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:40;a:4:{s:1:"a";i:41;s:1:"b";s:13:"students.view";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:41;a:4:{s:1:"a";i:42;s:1:"b";s:15:"students.create";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:42;a:4:{s:1:"a";i:43;s:1:"b";s:13:"students.edit";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:43;a:4:{s:1:"a";i:44;s:1:"b";s:15:"students.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:44;a:4:{s:1:"a";i:45;s:1:"b";s:15:"students.import";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:45;a:4:{s:1:"a";i:46;s:1:"b";s:14:"employees.view";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:46;a:4:{s:1:"a";i:47;s:1:"b";s:16:"employees.create";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:47;a:4:{s:1:"a";i:48;s:1:"b";s:14:"employees.edit";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:48;a:4:{s:1:"a";i:49;s:1:"b";s:16:"employees.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:49;a:4:{s:1:"a";i:50;s:1:"b";s:16:"employees.import";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:50;a:4:{s:1:"a";i:51;s:1:"b";s:26:"physical-examinations.view";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;}}i:51;a:4:{s:1:"a";i:52;s:1:"b";s:28:"physical-examinations.create";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:52;a:4:{s:1:"a";i:53;s:1:"b";s:26:"physical-examinations.edit";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:53;a:4:{s:1:"a";i:54;s:1:"b";s:28:"physical-examinations.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:54;a:4:{s:1:"a";i:55;s:1:"b";s:27:"physical-examinations.print";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:55;a:4:{s:1:"a";i:56;s:1:"b";s:21:"medical-profiles.view";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:56;a:4:{s:1:"a";i:57;s:1:"b";s:21:"medical-profiles.edit";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:57;a:4:{s:1:"a";i:58;s:1:"b";s:25:"medical-certificates.view";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:58;a:4:{s:1:"a";i:59;s:1:"b";s:27:"medical-certificates.create";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:59;a:4:{s:1:"a";i:60;s:1:"b";s:26:"medical-certificates.print";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:60;a:4:{s:1:"a";i:61;s:1:"b";s:14:"medicines.view";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:61;a:4:{s:1:"a";i:62;s:1:"b";s:16:"medicines.create";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:62;a:4:{s:1:"a";i:63;s:1:"b";s:14:"medicines.edit";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:63;a:4:{s:1:"a";i:64;s:1:"b";s:16:"medicines.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:64;a:4:{s:1:"a";i:65;s:1:"b";s:24:"medicine-categories.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:65;a:4:{s:1:"a";i:66;s:1:"b";s:26:"medicine-categories.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:66;a:4:{s:1:"a";i:67;s:1:"b";s:24:"medicine-categories.edit";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:67;a:4:{s:1:"a";i:68;s:1:"b";s:26:"medicine-categories.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:2;}}i:68;a:4:{s:1:"a";i:69;s:1:"b";s:14:"inventory.view";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:69;a:4:{s:1:"a";i:70;s:1:"b";s:18:"inventory.stock-in";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:70;a:4:{s:1:"a";i:71;s:1:"b";s:19:"inventory.stock-out";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:71;a:4:{s:1:"a";i:72;s:1:"b";s:18:"inventory.dispense";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}i:72;a:4:{s:1:"a";i:73;s:1:"b";s:17:"inventory.reports";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:2;i:1;i:3;i:2;i:4;}}}s:5:"roles";a:4:{i:0;a:3:{s:1:"a";i:2;s:1:"b";s:13:"Administrator";s:1:"c";s:3:"web";}i:1;a:3:{s:1:"a";i:3;s:1:"b";s:6:"Doctor";s:1:"c";s:3:"web";}i:2;a:3:{s:1:"a";i:4;s:1:"b";s:5:"Nurse";s:1:"c";s:3:"web";}i:3;a:3:{s:1:"a";i:5;s:1:"b";s:12:"Receptionist";s:1:"c";s:3:"web";}}}', 1793900622),
	('nclinicassist-cache-app.settings', 'a:17:{s:15:"university_name";s:34:"Surigao del Norte State University";s:21:"university_short_name";s:4:"SNSU";s:17:"university_campus";s:25:"Main Campus, Surigao City";s:18:"university_address";s:60:"Narciso Street, Surigao City, Surigao del Norte, Philippines";s:20:"university_president";s:26:"Dr. Gregorio Z. Gamboa Jr.";s:18:"university_website";s:19:"https://snsu.edu.ph";s:11:"clinic_name";s:22:"SNSU University Clinic";s:11:"clinic_head";s:26:"Dr. Maria Elena Santos, MD";s:17:"clinic_license_no";s:20:"DOH-R13-CLN-2026-088";s:12:"clinic_email";s:18:"clinic@snsu.edu.ph";s:12:"clinic_phone";s:17:"+63 (86) 826-0135";s:14:"clinic_address";s:55:"Ground Floor, Student Services Center, SNSU Main Campus";s:11:"date_format";s:6:"M d, Y";s:17:"table_page_length";s:2:"25";s:14:"backup_enabled";s:1:"1";s:21:"backup_retention_days";s:2:"30";s:24:"audit_log_retention_days";s:3:"365";}', 2109174222);
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
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
	(16, '2026_11_04_170000_create_medicines_and_inventory_tables', 1);
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
) ENGINE=MyISAM AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
	(73, 'inventory.reports', 'web', '2026-11-04 09:37:50', '2026-11-04 09:37:50');
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
	(73, 4);
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
	('zjHeLuNh8KcoyIf3gtN07s9X06blguUeDtSknRgg', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI1N1dkZGx3ZndCeTRFTkRoMmd4ZHRPNnJudEJvY1hjVzVFTVNiTk91IiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2NsaW5pYy5kZXYuY29tXC9wYXRpZW50cyIsInJvdXRlIjoicGF0aWVudHMuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MiwicGFzc3dvcmRfaGFzaF93ZWIiOiI0N2MwZDEzNjMwODhlNjY3ZWViNWI1OThkMmJjYWE2ODc3NjIyNjA5ZjI3M2UxZjg1ZTliY2ZjYTMzNDlmM2ZlIn0=', 1793814740);
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
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table clinic_management.settings: 17 rows
DELETE FROM `settings`;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` (`id`, `group`, `key`, `value`, `created_at`, `updated_at`) VALUES
	(1, 'university', 'university_name', 'Surigao del Norte State University', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(2, 'university', 'university_short_name', 'SNSU', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(3, 'university', 'university_campus', 'Main Campus, Surigao City', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(4, 'university', 'university_address', 'Narciso Street, Surigao City, Surigao del Norte, Philippines', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(5, 'university', 'university_president', 'Dr. Gregorio Z. Gamboa Jr.', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(6, 'university', 'university_website', 'https://snsu.edu.ph', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(7, 'general', 'clinic_name', 'SNSU University Clinic', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(8, 'general', 'clinic_head', 'Dr. Maria Elena Santos, MD', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(9, 'general', 'clinic_license_no', 'DOH-R13-CLN-2026-088', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(10, 'general', 'clinic_email', 'clinic@snsu.edu.ph', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(11, 'general', 'clinic_phone', '+63 (86) 826-0135', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(12, 'general', 'clinic_address', 'Ground Floor, Student Services Center, SNSU Main Campus', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(13, 'general', 'date_format', 'M d, Y', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(14, 'general', 'table_page_length', '25', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(15, 'backup', 'backup_enabled', '1', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(16, 'backup', 'backup_retention_days', '30', '2026-11-04 09:37:50', '2026-11-04 09:37:50'),
	(17, 'security', 'audit_log_retention_days', '365', '2026-11-04 09:37:50', '2026-11-04 09:37:50');
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
	(1, 'System Administrator', 'admin@clinic.test', NULL, '2026-11-04 09:37:50', '$2y$12$3kNvZ50Hb3gEXhQFezvvg.18.Xnuq8cujyHpakRwoQjr.0DvP9vRO', NULL, NULL, 1, NULL, NULL, '2026-11-04 09:37:51', '2026-11-04 09:37:51'),
	(2, 'Super Admin (SNSU)', 'apiloton1@snsu.edu.ph', NULL, '2026-11-04 09:37:51', '$2y$12$F3aSuyASP9cgev25AmotZO2pzfzbawteSFw.eZZVwwmYoFEPZndJu', '117952079880321133596', 'https://lh3.googleusercontent.com/a/ACg8ocICxZuvbmnED8B5vH9nAzZ75oj4EyyJOq04YOMO7jxaSaNGZqsv=s96-c', 1, '2026-11-04 09:38:14', 'RDpFW0dPbUJzB6LVp1ibgwNyWEDq5sCE8nYf5foIl1LzyrZhVbhQnibQfxCJ', '2026-11-04 09:37:51', '2026-11-04 09:38:14'),
	(3, 'Super Admin (ADSSU)', 'alpiloton@adssu.edu.ph', NULL, '2026-11-04 09:37:51', '$2y$12$lmJ6Ijt3oWxCCkACwriLz.u494dSubSxPQW44hygPRPnPyg3C8KaG', NULL, NULL, 1, NULL, NULL, '2026-11-04 09:37:51', '2026-11-04 09:37:51');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
