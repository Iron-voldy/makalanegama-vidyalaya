-- Updated Makalanegama School Database Schema
-- Removed Telegram fields and simplified for admin management

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+05:30";

-- Create database
CREATE DATABASE IF NOT EXISTS `makalanegama_school` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `makalanegama_school`;

-- --------------------------------------------------------

-- Table structure for table `admin_users`
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` enum('admin','moderator') DEFAULT 'moderator',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `achievements`
CREATE TABLE `achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `category` enum('Academic','Sports','Cultural','Environmental','Technology','Community Service','Arts','Science') DEFAULT 'Academic',
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `events`
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT 'School',
  `image_url` varchar(500) DEFAULT NULL,
  `category` enum('Academic','Sports','Cultural','Parent Meeting','Examination','Holiday','Workshop','Competition') DEFAULT 'Academic',
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_date` (`event_date`),
  KEY `idx_category` (`category`),
  KEY `idx_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `news`
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `category` enum('General','Academic','Sports','Events','Facilities','Announcements','Achievements','Admissions') DEFAULT 'General',
  `author` varchar(100) DEFAULT 'Administration',
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `teachers`
CREATE TABLE `teachers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `qualification` varchar(500) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `department` enum('Science & Mathematics','Languages','Social Sciences','Arts','Physical Education','Technology','Special Education') DEFAULT 'Science & Mathematics',
  `bio` text DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `photo_url` varchar(500) DEFAULT NULL,
  `specializations` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_department` (`department`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `contact_submissions`
CREATE TABLE `contact_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('new','read','replied','archived') DEFAULT 'new',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO `admin_users` (`username`, `email`, `password_hash`, `full_name`, `role`, `is_active`) VALUES
('admin', 'admin@makalanegamaschool.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'School Administrator', 'admin', 1);

-- Insert sample data
INSERT INTO `achievements` (`title`, `description`, `category`, `is_featured`, `created_at`) VALUES
('Provincial Mathematics Excellence', 'Our Grade 10 students achieved outstanding results in the provincial mathematics competition.', 'Academic', 1, '2024-02-15 10:00:00'),
('Inter-School Cricket Championship', 'Our cricket team won the zonal championship after a thrilling final match.', 'Sports', 0, '2024-01-20 14:30:00'),
('Environmental Conservation Award', 'Recognition for our school\'s outstanding contribution to environmental conservation.', 'Environmental', 0, '2024-01-10 09:15:00');

INSERT INTO `events` (`title`, `description`, `event_date`, `event_time`, `location`, `category`, `created_at`) VALUES
('Annual Sports Day', 'Join us for our annual sports day featuring various athletic competitions.', '2024-02-25', '08:00:00', 'School Grounds', 'Sports', '2024-02-01 09:00:00'),
('Parent-Teacher Meeting', 'Meet with teachers to discuss student progress and academic performance.', '2024-03-15', '14:00:00', 'School Hall', 'Parent Meeting', '2024-02-01 10:00:00'),
('Science Fair 2024', 'Students will showcase their innovative science projects and experiments.', '2024-04-10', '09:00:00', 'Computer Lab', 'Academic', '2024-02-01 11:00:00');

INSERT INTO `news` (`title`, `content`, `category`, `author`, `is_featured`, `created_at`) VALUES
('New Computer Lab Officially Opens', 'We are proud to announce the official opening of our state-of-the-art computer laboratory.', 'Facilities', 'Principal', 1, '2024-02-10 08:00:00'),
('2024 Admissions Now Open', 'Applications for Grade 1 admissions for the 2024 academic year are now being accepted.', 'Admissions', 'Admissions Office', 0, '2024-02-05 09:00:00'),
('Teacher Professional Development Workshop', 'Our dedicated teaching staff participated in a comprehensive professional development workshop.', 'Academic', 'Academic Coordinator', 0, '2024-01-28 10:00:00');

INSERT INTO `teachers` (`name`, `qualification`, `subject`, `department`, `bio`, `experience_years`, `email`, `created_at`) VALUES
('Mr. Sunil Perera', 'B.Ed (Mathematics), Dip. in Education', 'Mathematics', 'Science & Mathematics', 'Experienced mathematics teacher specializing in advanced mathematics and statistics.', 15, 'sperera@makalanegamaschool.lk', '2024-01-01 08:00:00'),
('Mrs. Kamala Wijesinghe', 'B.A (Sinhala), PGDE', 'Sinhala Language & Literature', 'Languages', 'Passionate about promoting Sinhala literature and language skills among students.', 12, 'kwijesinghe@makalanegamaschool.lk', '2024-01-01 08:00:00'),
('Mr. Rohan Fernando', 'B.Sc (Physics), Dip. in Education', 'Science', 'Science & Mathematics', 'Dedicated science teacher focusing on practical experiments and scientific inquiry.', 10, 'rfernando@makalanegamaschool.lk', '2024-01-01 08:00:00');

COMMIT;