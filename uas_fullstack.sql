-- ============================================================
-- Smart-Hub Management System - Database Schema
-- Database: uas_fullstack
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- --------------------------------------------------------
-- Buat dan gunakan database
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `uas_fullstack`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `uas_fullstack`;

-- ============================================================
-- TABEL BAWAAN LARAVEL
-- ============================================================

-- Tabel users (autentikasi)
CREATE TABLE IF NOT EXISTS `users` (
  `id`                bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`              varchar(255) NOT NULL,
  `email`             varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password`          varchar(255) NOT NULL,
  `role`              varchar(50) DEFAULT 'admin',
  `remember_token`    varchar(100) DEFAULT NULL,
  `created_at`        timestamp NULL DEFAULT NULL,
  `updated_at`        timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key`        varchar(255) NOT NULL,
  `value`      mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key`        varchar(255) NOT NULL,
  `owner`      varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id`            varchar(255) NOT NULL,
  `user_id`       bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address`    varchar(45) DEFAULT NULL,
  `user_agent`    text DEFAULT NULL,
  `payload`       longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel jobs (queue)
CREATE TABLE IF NOT EXISTS `jobs` (
  `id`           bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue`        varchar(255) NOT NULL,
  `payload`      longtext NOT NULL,
  `attempts`     tinyint(3) UNSIGNED NOT NULL,
  `reserved_at`  int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at`   int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id`             varchar(255) NOT NULL,
  `name`           varchar(255) NOT NULL,
  `total_jobs`     int(11) NOT NULL,
  `pending_jobs`   int(11) NOT NULL,
  `failed_jobs`    int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options`        mediumtext DEFAULT NULL,
  `cancelled_at`   int(11) DEFAULT NULL,
  `created_at`     int(11) NOT NULL,
  `finished_at`    int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id`         bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`       varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue`      text NOT NULL,
  `payload`    longtext NOT NULL,
  `exception`  longtext NOT NULL,
  `failed_at`  timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL APLIKASI SMART-HUB
-- ============================================================

-- Tabel kategori peralatan
CREATE TABLE IF NOT EXISTS `categories` (
  `id`          bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at`  timestamp NULL DEFAULT NULL,
  `updated_at`  timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel peralatan (items)
CREATE TABLE IF NOT EXISTS `items` (
  `id`          bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `code`        varchar(50) NOT NULL,
  `name`        varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `stock`       int(11) NOT NULL DEFAULT 0,
  `status`      varchar(50) DEFAULT 'available',
  `created_at`  timestamp NULL DEFAULT NULL,
  `updated_at`  timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `items_code_unique` (`code`),
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel transaksi peminjaman
CREATE TABLE IF NOT EXISTS `transactions` (
  `id`              bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id`         bigint(20) UNSIGNED NOT NULL,
  `borrower_name`   varchar(255) NOT NULL,
  `borrower_nim`    varchar(50) NOT NULL,
  `borrow_date`     date NOT NULL,
  `return_date`     date NOT NULL,
  `status`          enum('checkin','checkout','pending') NOT NULL DEFAULT 'checkin',
  `notes`           text DEFAULT NULL,
  `checkout_notes`  text DEFAULT NULL,
  `created_at`      timestamp NULL DEFAULT NULL,
  `updated_at`      timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATA AWAL (SEED)
-- ============================================================

-- Akun Administrator
-- Password: password (di-hash dengan bcrypt)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `created_at`, `updated_at`)
VALUES ('Administrator', 'admin@smarthub.com', '$2y$12$8g7v3ZC7V4E7l7j6nqTFr.rMRBP7/mWjvXDt0GLCD1d0FalXrOCy6', 'admin', NOW(), NOW());

-- Data Kategori Awal
INSERT INTO `categories` (`name`, `description`, `created_at`, `updated_at`) VALUES
('Elektronik',   'Peralatan elektronik dan komputer',       NOW(), NOW()),
('Jaringan',     'Peralatan jaringan dan konektivitas',     NOW(), NOW()),
('Alat Ukur',   'Instrumen pengukuran dan kalibrasi',      NOW(), NOW()),
('Perlengkapan', 'Perlengkapan dan aksesori pendukung',    NOW(), NOW());

-- Data Peralatan Awal
INSERT INTO `items` (`category_id`, `code`, `name`, `description`, `stock`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ELK-001', 'Laptop Lenovo ThinkPad', 'Laptop untuk kebutuhan komputasi',        5, 'available', NOW(), NOW()),
(1, 'ELK-002', 'Proyektor Epson EB-X41', 'Proyektor untuk presentasi',              3, 'available', NOW(), NOW()),
(2, 'NET-001', 'Switch 24-Port D-Link',  'Switch jaringan 24 port',                 2, 'available', NOW(), NOW()),
(2, 'NET-002', 'Kabel UTP Cat6 (50m)',   'Kabel jaringan kategori 6',               10, 'available', NOW(), NOW()),
(3, 'UKR-001', 'Multimeter Digital',     'Alat ukur tegangan dan arus listrik',     4, 'available', NOW(), NOW()),
(4, 'PKL-001', 'Extension Cord 5m',      'Kabel ekstensi daya listrik 5 meter',     8, 'available', NOW(), NOW());

COMMIT;
