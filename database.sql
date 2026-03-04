-- ================================================
-- Clearance System Database Schema
-- Database: clearance_system
-- Run this in phpMyAdmin: SQL tab
-- ================================================

CREATE DATABASE IF NOT EXISTS `clearance_system`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `clearance_system`;

-- ------------------------------------------------
-- Table: admins
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `full_name`  VARCHAR(150) NOT NULL,
    `email`      VARCHAR(150) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: advisers
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `advisers` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `full_name`  VARCHAR(150) NOT NULL,
    `email`      VARCHAR(150) NOT NULL UNIQUE,
    `department` VARCHAR(150) NOT NULL,
    `password`   VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: signatories
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `signatories` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `full_name`  VARCHAR(150) NOT NULL,
    `email`      VARCHAR(150) NOT NULL UNIQUE,
    `office`     VARCHAR(150) NOT NULL,
    `password`   VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: students
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `student_id`  VARCHAR(50)  NOT NULL UNIQUE,
    `full_name`   VARCHAR(150) NOT NULL,
    `email`       VARCHAR(150) NOT NULL UNIQUE,
    `course`      VARCHAR(100) NOT NULL,
    `year_level`  TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `section`     VARCHAR(20)  NOT NULL,
    `adviser_id`  INT UNSIGNED,
    `password`    VARCHAR(255) NOT NULL,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_student_adviser`
        FOREIGN KEY (`adviser_id`) REFERENCES `advisers`(`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Table: clearance_status
-- (one row per student-signatory pair)
-- ------------------------------------------------
CREATE TABLE IF NOT EXISTS `clearance_status` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `student_id`      INT UNSIGNED NOT NULL,
    `signatory_id`    INT UNSIGNED NOT NULL,
    `status`          ENUM('pending','signed') NOT NULL DEFAULT 'pending',
    `signed_at`       DATETIME DEFAULT NULL,
    `adviser_status`  ENUM('pending','cleared') NOT NULL DEFAULT 'pending',
    `cleared_at`      DATETIME DEFAULT NULL,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_student_signatory` (`student_id`, `signatory_id`),
    CONSTRAINT `fk_cs_student`
        FOREIGN KEY (`student_id`)   REFERENCES `students`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_cs_signatory`
        FOREIGN KEY (`signatory_id`) REFERENCES `signatories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------
-- Default Admin Account
-- Password: admin123 (hashed with password_hash)
-- ------------------------------------------------
INSERT INTO `admins` (`full_name`, `email`, `password`) VALUES
('System Admin', 'admin@school.edu', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- NOTE: The hash above is for "password". 
-- To use a real password, run in PHP:
--   echo password_hash('your_password', PASSWORD_DEFAULT);
-- and paste the result here, or change it via phpMyAdmin.
