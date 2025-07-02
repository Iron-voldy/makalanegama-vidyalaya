-- Makalanegama School Website Database Schema
-- Created for modern school website with Telegram integration

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+05:30";

-- Create database
CREATE DATABASE IF NOT EXISTS `makalanegama_school` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `makalanegama_school`;

-- --------------------------------------------------------

-- Table structure for table `achievements`
CREATE TABLE `achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `category` enum('Academic','Sports','Cultural','Environmental','Technology','Community Service','Arts','Science') DEFAULT 'Academic',
  `telegram_message_id` bigint(20) DEFAULT NULL,
  `telegram_user_id` bigint(20) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `approved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_approved` (`is_approved`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_telegram_message` (`telegram_message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

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
  `telegram_message_id` bigint(20) DEFAULT NULL,
  `telegram_user_id` bigint(20) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `approved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_event_date` (`event_date`),
  KEY `idx_category` (`category`),
  KEY `idx_approved` (`is_approved`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_telegram_message` (`telegram_message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `news`
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `category` enum('General','Academic','Sports','Events','Facilities','Announcements','Achievements','Admissions') DEFAULT 'General',
  `author` varchar(100) DEFAULT 'Administration',
  `telegram_message_id` bigint(20) DEFAULT NULL,
  `telegram_user_id` bigint(20) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `approved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_approved` (`is_approved`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_telegram_message` (`telegram_message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

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
  `telegram_message_id` bigint(20) DEFAULT NULL,
  `telegram_user_id` bigint(20) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `approved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_department` (`department`),
  KEY `idx_approved` (`is_approved`),
  KEY `idx_active` (`is_active`),
  KEY `idx_telegram_message` (`telegram_message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `gallery`
CREATE TABLE `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `category` enum('Academic','Sports','Cultural','Events','Facilities','Environment','Technology','General') DEFAULT 'General',
  `alt_text` varchar(255) DEFAULT NULL,
  `telegram_message_id` bigint(20) DEFAULT NULL,
  `telegram_user_id` bigint(20) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `approved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_approved` (`is_approved`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_telegram_message` (`telegram_message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `contact_submissions`
CREATE TABLE `contact_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('new','read','replied','archived') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `settings`
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` enum('admin','moderator','teacher','staff') DEFAULT 'staff',
  `telegram_user_id` bigint(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_active` (`is_active`),
  KEY `idx_telegram_user` (`telegram_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `audit_log`
CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_table_name` (`table_name`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('site_title', 'Makalanegama School - Excellence in Education', 'text', 'Main site title'),
('site_description', 'Leading educational institution in Galgamuwa, Sri Lanka offering quality education from Grade 1-11', 'text', 'Site meta description'),
('contact_email', 'info@makalanegamaschool.lk', 'text', 'Primary contact email'),
('contact_phone', '+94 37 205 0000', 'text', 'Primary contact phone'),
('school_address', 'X8X5+VGH, Galgamuwa-Nikawewa Rd, Galgamuwa, Sri Lanka', 'text', 'School physical address'),
('facebook_url', 'https://web.facebook.com/people/Makalanegama-kv/100063734032649/', 'text', 'Facebook page URL'),
('auto_approve_content', '0', 'boolean', 'Automatically approve content from Telegram'),
('maintenance_mode', '0', 'boolean', 'Enable maintenance mode'),
('google_analytics_id', '', 'text', 'Google Analytics tracking ID'),
('enable_cache', '1', 'boolean', 'Enable content caching'),
('cache_duration', '300', 'number', 'Cache duration in seconds'),
('max_file_size', '10485760', 'number', 'Maximum file upload size in bytes');

-- --------------------------------------------------------

-- Insert sample data for testing

-- Sample achievements
INSERT INTO `achievements` (`title`, `description`, `category`, `is_approved`, `is_featured`, `created_at`) VALUES
('Provincial Mathematics Excellence', 'Our Grade 10 students achieved outstanding results in the provincial mathematics competition, securing first place among 50 participating schools.', 'Academic', 1, 1, '2024-02-15 10:00:00'),
('Inter-School Cricket Championship', 'Our cricket team won the zonal championship after a thrilling final match against St. Joseph\'s College.', 'Sports', 1, 0, '2024-01-20 14:30:00'),
('Environmental Conservation Award', 'Recognition for our school\'s outstanding contribution to environmental conservation through our gardening and sustainability projects.', 'Environmental', 1, 0, '2024-01-10 09:15:00'),
('Computer Lab Inauguration', 'Successfully launched our new computer laboratory with support from Wire Academy & Technology for Village.', 'Technology', 1, 1, '2023-12-15 11:00:00');

-- Sample events
INSERT INTO `events` (`title`, `description`, `event_date`, `event_time`, `location`, `category`, `is_approved`, `created_at`) VALUES
('Annual Sports Day', 'Join us for our annual sports day featuring various athletic competitions and cultural performances.', '2024-02-25', '08:00:00', 'School Grounds', 'Sports', 1, '2024-02-01 09:00:00'),
('Parent-Teacher Meeting', 'Meet with teachers to discuss student progress and academic performance for the first term.', '2024-03-15', '14:00:00', 'School Hall', 'Parent Meeting', 1, '2024-02-01 10:00:00'),
('Science Fair 2024', 'Students will showcase their innovative science projects and experiments focusing on technology and environment.', '2024-04-10', '09:00:00', 'Computer Lab', 'Academic', 1, '2024-02-01 11:00:00'),
('Cultural Festival', 'Celebrate Sri Lankan heritage with traditional music, dance, and drama performances.', '2024-04-20', '18:00:00', 'School Auditorium', 'Cultural', 1, '2024-02-01 12:00:00');

-- Sample news
INSERT INTO `news` (`title`, `content`, `category`, `author`, `is_approved`, `is_featured`, `created_at`) VALUES
('New Computer Lab Officially Opens', 'We are proud to announce the official opening of our state-of-the-art computer laboratory, made possible through the generous support of Wire Academy & Technology for Village. The lab features 25 modern computers with high-speed internet connectivity, enabling our students to develop essential digital literacy skills for the 21st century.', 'Facilities', 'Principal', 1, 1, '2024-02-10 08:00:00'),
('2024 Admissions Now Open', 'Applications for Grade 1 admissions for the 2024 academic year are now being accepted. Parents are encouraged to visit the school office between 8:00 AM and 2:00 PM on weekdays to collect application forms and learn about admission requirements. Early application is recommended as places are limited.', 'Admissions', 'Admissions Office', 1, 0, '2024-02-05 09:00:00'),
('Teacher Professional Development Workshop', 'Our dedicated teaching staff participated in a comprehensive professional development workshop focusing on modern teaching methodologies and digital integration in education. The workshop was conducted by education experts from the National Institute of Education and covered innovative approaches to student engagement.', 'Academic', 'Academic Coordinator', 1, 0, '2024-01-28 10:00:00'),
('Environmental Conservation Initiative Launched', 'Makalanegama School has launched a comprehensive environmental conservation initiative including tree planting, waste management, and renewable energy projects. Students are actively participating in creating a sustainable school environment and learning about environmental responsibility.', 'Announcements', 'Environment Club', 1, 0, '2024-01-20 11:00:00');

-- Sample teachers
INSERT INTO `teachers` (`name`, `qualification`, `subject`, `department`, `bio`, `experience_years`, `email`, `is_approved`, `created_at`) VALUES
('Mr. Sunil Perera', 'B.Ed (Mathematics), Dip. in Education', 'Mathematics', 'Science & Mathematics', 'Experienced mathematics teacher specializing in advanced mathematics and statistics. Passionate about making complex mathematical concepts accessible to all students.', 15, 'sperera@makalanegamaschool.lk', 1, '2024-01-01 08:00:00'),
('Mrs. Kamala Wijesinghe', 'B.A (Sinhala), PGDE', 'Sinhala Language & Literature', 'Languages', 'Passionate about promoting Sinhala literature and language skills among students. Dedicated to preserving and sharing Sri Lankan cultural heritage through education.', 12, 'kwijesinghe@makalanegamaschool.lk', 1, '2024-01-01 08:00:00'),
('Mr. Rohan Fernando', 'B.Sc (Physics), Dip. in Education', 'Science', 'Science & Mathematics', 'Dedicated science teacher focusing on practical experiments and scientific inquiry. Believes in hands-on learning to make science engaging and understandable.', 10, 'rfernando@makalanegamaschool.lk', 1, '2024-01-01 08:00:00'),
('Mrs. Priyanka Silva', 'B.A (English), TESL Certificate', 'English Language', 'Languages', 'English language specialist with expertise in modern teaching methodologies. Focuses on developing communication skills and confidence in English language usage.', 8, 'psilva@makalanegamaschool.lk', 1, '2024-01-01 08:00:00'),
('Mr. Asanka Rathnayake', 'B.A (History), Dip. in Education', 'History & Social Studies', 'Social Sciences', 'History teacher with special interest in Sri Lankan heritage and culture. Passionate about connecting historical events to contemporary understanding.', 14, 'arathnayake@makalanegamaschool.lk', 1, '2024-01-01 08:00:00'),
('Mrs. Sandya Mendis', 'B.Sc (Geography), PGDE', 'Geography', 'Social Sciences', 'Geography teacher promoting environmental awareness and sustainability. Integrates field studies and practical geography into classroom learning.', 9, 'smendis@makalanegamaschool.lk', 1, '2024-01-01 08:00:00');

-- Sample gallery images
INSERT INTO `gallery` (`title`, `description`, `category`, `is_approved`, `created_at`) VALUES
('Interactive Learning Session', 'Students engaged in interactive learning with modern teaching methods', 'Academic', 1, '2024-02-01 10:00:00'),
('Science Laboratory Experiment', 'Hands-on science experiments in our well-equipped laboratory', 'Academic', 1, '2024-01-25 11:00:00'),
('Annual Sports Day', 'Athletic competitions and team spirit on display', 'Sports', 1, '2024-01-20 12:00:00'),
('Cultural Dance Performance', 'Traditional Sri Lankan dance performances by our students', 'Cultural', 1, '2024-01-15 13:00:00'),
('Environmental Project', 'Students working on gardening and sustainability initiatives', 'Environment', 1, '2024-01-10 14:00:00'),
('Computer Class Session', 'Students learning digital literacy in our new computer lab', 'Technology', 1, '2024-01-05 15:00:00');

-- Sample contact submissions
INSERT INTO `contact_submissions` (`name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`) VALUES
('Nimal Perera', 'nimal.perera@email.com', '+94771234567', 'Grade 1 Admission Inquiry', 'I would like to inquire about the admission process for my child for Grade 1 in 2024. Could you please provide me with the necessary information and application forms?', 'new', '2024-02-15 09:30:00'),
('Kumari Silva', 'kumari.silva@email.com', '+94712345678', 'School Transport', 'Does the school provide transportation services for students living in the Kurunegala area? If so, what are the routes and fees?', 'read', '2024-02-14 14:20:00'),
('Roshan Fernando', 'roshan.fernando@email.com', '+94723456789', 'Extracurricular Activities', 'I would like to know more about the extracurricular activities available for students, particularly sports and cultural programs.', 'replied', '2024-02-13 16:45:00');

-- Create indexes for better performance
CREATE INDEX idx_achievements_category_approved ON achievements(category, is_approved);
CREATE INDEX idx_events_date_approved ON events(event_date, is_approved);
CREATE INDEX idx_news_category_approved ON news(category, is_approved);
CREATE INDEX idx_teachers_department_approved ON teachers(department, is_approved);
CREATE INDEX idx_gallery_category_approved ON gallery(category, is_approved);

-- Create full-text search indexes
ALTER TABLE achievements ADD FULLTEXT(title, description);
ALTER TABLE events ADD FULLTEXT(title, description);
ALTER TABLE news ADD FULLTEXT(title, content);
ALTER TABLE teachers ADD FULLTEXT(name, qualification, subject, bio);

-- Create triggers for audit logging
DELIMITER $

CREATE TRIGGER achievements_audit_insert
AFTER INSERT ON achievements
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (action, table_name, record_id, new_values, created_at)
    VALUES ('INSERT', 'achievements', NEW.id, JSON_OBJECT(
        'title', NEW.title,
        'description', NEW.description,
        'category', NEW.category,
        'is_approved', NEW.is_approved
    ), NOW());
END$

CREATE TRIGGER achievements_audit_update
AFTER UPDATE ON achievements
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (action, table_name, record_id, old_values, new_values, created_at)
    VALUES ('UPDATE', 'achievements', NEW.id, JSON_OBJECT(
        'title', OLD.title,
        'description', OLD.description,
        'category', OLD.category,
        'is_approved', OLD.is_approved
    ), JSON_OBJECT(
        'title', NEW.title,
        'description', NEW.description,
        'category', NEW.category,
        'is_approved', NEW.is_approved
    ), NOW());
END$

CREATE TRIGGER events_audit_insert
AFTER INSERT ON events
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (action, table_name, record_id, new_values, created_at)
    VALUES ('INSERT', 'events', NEW.id, JSON_OBJECT(
        'title', NEW.title,
        'description', NEW.description,
        'event_date', NEW.event_date,
        'category', NEW.category,
        'is_approved', NEW.is_approved
    ), NOW());
END$

CREATE TRIGGER news_audit_insert
AFTER INSERT ON news
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (action, table_name, record_id, new_values, created_at)
    VALUES ('INSERT', 'news', NEW.id, JSON_OBJECT(
        'title', NEW.title,
        'content', NEW.content,
        'category', NEW.category,
        'is_approved', NEW.is_approved
    ), NOW());
END$

DELIMITER ;

-- Create views for commonly used queries
CREATE VIEW approved_achievements AS
SELECT * FROM achievements WHERE is_approved = 1 ORDER BY created_at DESC;

CREATE VIEW upcoming_events AS
SELECT * FROM events WHERE is_approved = 1 AND event_date >= CURDATE() ORDER BY event_date ASC;

CREATE VIEW recent_news AS
SELECT * FROM news WHERE is_approved = 1 ORDER BY created_at DESC;

CREATE VIEW active_teachers AS
SELECT * FROM teachers WHERE is_approved = 1 AND is_active = 1 ORDER BY name ASC;

CREATE VIEW featured_content AS
SELECT 'achievement' as type, id, title, description, image_url, created_at FROM achievements WHERE is_approved = 1 AND is_featured = 1
UNION ALL
SELECT 'event' as type, id, title, description, image_url, created_at FROM events WHERE is_approved = 1 AND is_featured = 1
UNION ALL
SELECT 'news' as type, id, title, content as description, image_url, created_at FROM news WHERE is_approved = 1 AND is_featured = 1
ORDER BY created_at DESC;

-- Create stored procedures for common operations
DELIMITER $

CREATE PROCEDURE GetContentStats()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM achievements WHERE is_approved = 1) as total_achievements,
        (SELECT COUNT(*) FROM events WHERE is_approved = 1) as total_events,
        (SELECT COUNT(*) FROM news WHERE is_approved = 1) as total_news,
        (SELECT COUNT(*) FROM teachers WHERE is_approved = 1 AND is_active = 1) as total_teachers,
        (SELECT COUNT(*) FROM gallery WHERE is_approved = 1) as total_gallery,
        (SELECT COUNT(*) FROM contact_submissions WHERE status = 'new') as pending_contacts;
END$

CREATE PROCEDURE ApproveContent(IN content_type VARCHAR(50), IN content_id INT)
BEGIN
    CASE content_type
        WHEN 'achievements' THEN
            UPDATE achievements SET is_approved = 1, approved_at = NOW() WHERE id = content_id;
        WHEN 'events' THEN
            UPDATE events SET is_approved = 1, approved_at = NOW() WHERE id = content_id;
        WHEN 'news' THEN
            UPDATE news SET is_approved = 1, approved_at = NOW() WHERE id = content_id;
        WHEN 'teachers' THEN
            UPDATE teachers SET is_approved = 1, approved_at = NOW() WHERE id = content_id;
        WHEN 'gallery' THEN
            UPDATE gallery SET is_approved = 1, approved_at = NOW() WHERE id = content_id;
    END CASE;
END$

CREATE PROCEDURE GetPendingApprovals()
BEGIN
    SELECT 'achievement' as type, id, title, description, created_at FROM achievements WHERE is_approved = 0
    UNION ALL
    SELECT 'event' as type, id, title, description, created_at FROM events WHERE is_approved = 0
    UNION ALL
    SELECT 'news' as type, id, title, content as description, created_at FROM news WHERE is_approved = 0
    UNION ALL
    SELECT 'teacher' as type, id, name as title, bio as description, created_at FROM teachers WHERE is_approved = 0
    UNION ALL
    SELECT 'gallery' as type, id, title, description, created_at FROM gallery WHERE is_approved = 0
    ORDER BY created_at DESC;
END$

DELIMITER ;

-- Insert default admin user (password: admin123 - change this!)
INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `role`, `is_active`, `created_at`) VALUES
('admin', 'admin@makalanegamaschool.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'School Administrator', 'admin', 1, NOW());

-- Final optimizations
OPTIMIZE TABLE achievements;
OPTIMIZE TABLE events;
OPTIMIZE TABLE news;
OPTIMIZE TABLE teachers;
OPTIMIZE TABLE gallery;
OPTIMIZE TABLE contact_submissions;
OPTIMIZE TABLE users;
OPTIMIZE TABLE audit_log;

COMMIT;

-- End of schema