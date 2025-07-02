<?php
/**
 * Configuration file for Makalanegama School Website
 * Contains all necessary settings and constants
 */

// Prevent direct access
if (!defined('INCLUDED')) {
    define('INCLUDED', true);
}

// Environment settings
define('ENVIRONMENT', 'development'); // Change to 'production' for live site

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'makalanegama_school');
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');
define('DB_CHARSET', 'utf8mb4');

// Telegram Bot configuration
define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE');
define('TELEGRAM_WEBHOOK_SECRET', 'YOUR_WEBHOOK_SECRET_HERE');

// Authorized Telegram users (add user IDs of authorized personnel)
define('TELEGRAM_AUTHORIZED_USERS', [
    123456789,  // Replace with actual Telegram user IDs
    987654321,  // Add more user IDs as needed
    // Add Principal, Vice Principal, IT Coordinator user IDs
]);

// File upload settings
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'webp']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx']);

// Image optimization settings
define('MAX_IMAGE_WIDTH', 1200);
define('MAX_IMAGE_HEIGHT', 800);
define('JPEG_QUALITY', 85);
define('PNG_COMPRESSION', 8);
define('WEBP_QUALITY', 85);

// School information
define('SCHOOL_NAME', 'Makalanegama School');
define('SCHOOL_NAME_SINHALA', 'මාකලනේගම විද්‍යාලය');
define('SCHOOL_ADDRESS', 'X8X5+VGH, Galgamuwa-Nikawewa Rd, Galgamuwa, Sri Lanka');
define('SCHOOL_PHONE', '+94 37 205 0000');
define('SCHOOL_EMAIL', 'info@makalanegamaschool.lk');
define('SCHOOL_FACEBOOK', 'https://web.facebook.com/people/Makalanegama-kv/100063734032649/');

// Website settings
define('SITE_URL', 'https://makalanegamaschool.lk');
define('SITE_TITLE', 'Makalanegama School - Excellence in Education');
define('SITE_DESCRIPTION', 'Leading educational institution in Galgamuwa, Sri Lanka offering quality education from Grade 1-11');
define('SITE_KEYWORDS', 'Makalanegama School, Galgamuwa, Sri Lanka, education, school, provincial type 2');

// Email configuration (for contact forms)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password');
define('SMTP_FROM_EMAIL', 'noreply@makalanegamaschool.lk');
define('SMTP_FROM_NAME', 'Makalanegama School');

// Content moderation settings
define('AUTO_APPROVE_CONTENT', false); // Set to true to auto-approve all content
define('REQUIRE_APPROVAL', true); // Require manual approval for content
define('NOTIFICATION_EMAIL', 'admin@makalanegamaschool.lk'); // Email for notifications

// Cache settings
define('ENABLE_CACHE', true);
define('CACHE_DURATION', 300); // 5 minutes in seconds

// Security settings
define('ENABLE_RATE_LIMITING', true);
define('MAX_REQUESTS_PER_MINUTE', 60);
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour

// Pagination settings
define('ACHIEVEMENTS_PER_PAGE', 6);
define('NEWS_PER_PAGE', 6);
define('EVENTS_PER_PAGE', 10);
define('TEACHERS_PER_PAGE', 12);
define('GALLERY_PER_PAGE', 20);

// Social media settings
define('FACEBOOK_PAGE_ID', 'YOUR_FACEBOOK_PAGE_ID');
define('TWITTER_HANDLE', '@MakalanegamaSchool');
define('INSTAGRAM_HANDLE', '@makalanegama_school');
define('YOUTUBE_CHANNEL', 'YOUR_YOUTUBE_CHANNEL_ID');

// Google services configuration
define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY');
define('GOOGLE_ANALYTICS_ID', 'YOUR_GOOGLE_ANALYTICS_ID');
define('GOOGLE_RECAPTCHA_SITE_KEY', 'YOUR_RECAPTCHA_SITE_KEY');
define('GOOGLE_RECAPTCHA_SECRET_KEY', 'YOUR_RECAPTCHA_SECRET_KEY');

// Content categories
define('ACHIEVEMENT_CATEGORIES', [
    'Academic',
    'Sports',
    'Cultural',
    'Environmental',
    'Technology',
    'Community Service',
    'Arts',
    'Science'
]);

define('NEWS_CATEGORIES', [
    'General',
    'Academic',
    'Sports',
    'Events',
    'Facilities',
    'Announcements',
    'Achievements',
    'Admissions'
]);

define('EVENT_CATEGORIES', [
    'Academic',
    'Sports',
    'Cultural',
    'Parent Meeting',
    'Examination',
    'Holiday',
    'Workshop',
    'Competition'
]);

define('TEACHER_DEPARTMENTS', [
    'Science & Mathematics',
    'Languages',
    'Social Sciences',
    'Arts',
    'Physical Education',
    'Technology',
    'Special Education'
]);

// File paths
define('UPLOAD_PATH', '../assets/uploads/');
define('IMAGES_PATH', '../assets/images/');
define('LOGS_PATH', '../logs/');
define('BACKUP_PATH', '../backups/');

// Create necessary directories if they don't exist
$directories = [
    UPLOAD_PATH,
    UPLOAD_PATH . date('Y'),
    UPLOAD_PATH . date('Y/m'),
    LOGS_PATH,
    BACKUP_PATH
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Logging configuration
define('LOG_LEVEL', ENVIRONMENT === 'development' ? 'DEBUG' : 'ERROR');
define('LOG_FILE', LOGS_PATH . 'application.log');
define('ERROR_LOG_FILE', LOGS_PATH . 'errors.log');
define('TELEGRAM_LOG_FILE', LOGS_PATH . 'telegram.log');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', ENVIRONMENT === 'production' ? 1 : 0);
ini_set('session.use_strict_mode', 1);

// Error reporting based on environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ERROR_LOG_FILE);
}

// Timezone setting
date_default_timezone_set('Asia/Colombo');

// Helper functions
function logMessage($level, $message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logEntry = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
    
    if ($level === 'ERROR' || LOG_LEVEL === 'DEBUG') {
        file_put_contents(LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    if ($level === 'ERROR') {
        file_put_contents(ERROR_LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token']) || 
        !isset($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_EXPIRY) {
        
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && 
           isset($_SESSION['csrf_token_time']) &&
           (time() - $_SESSION['csrf_token_time']) <= CSRF_TOKEN_EXPIRY &&
           hash_equals($_SESSION['csrf_token'], $token);
}

function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePhone($phone) {
    // Simple phone validation for Sri Lankan numbers
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return preg_match('/^(\+94|0)?[0-9]{9}$/', $phone);
}

function generateSlug($text) {
    // Convert to lowercase
    $text = strtolower($text);
    
    // Replace non-alphanumeric characters with hyphens
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    
    // Remove leading/trailing hyphens
    $text = trim($text, '-');
    
    return $text;
}

function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

function isImageFile($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, ALLOWED_IMAGE_TYPES);
}

function isDocumentFile($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, ALLOWED_DOCUMENT_TYPES);
}

// Rate limiting function
function checkRateLimit($identifier) {
    if (!ENABLE_RATE_LIMITING) {
        return true;
    }
    
    $file = LOGS_PATH . 'rate_limit_' . md5($identifier) . '.json';
    $current_time = time();
    $window_start = $current_time - 60; // 1 minute window
    
    $requests = [];
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if ($data) {
            $requests = array_filter($data, function($timestamp) use ($window_start) {
                return $timestamp > $window_start;
            });
        }
    }
    
    if (count($requests) >= MAX_REQUESTS_PER_MINUTE) {
        return false;
    }
    
    $requests[] = $current_time;
    file_put_contents($file, json_encode($requests), LOCK_EX);
    
    return true;
}

// Content approval workflow
function requiresApproval($content_type, $user_id) {
    if (!REQUIRE_APPROVAL) {
        return false;
    }
    
    // Define which user roles can auto-approve
    $auto_approve_users = [
        // Add user IDs that can auto-approve (Principal, Vice Principal, etc.)
    ];
    
    return !in_array($user_id, $auto_approve_users);
}

// Load environment-specific configuration
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

// Verify critical configuration
$required_constants = [
    'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS',
    'TELEGRAM_BOT_TOKEN'
];

foreach ($required_constants as $constant) {
    if (!defined($constant) || empty(constant($constant))) {
        if (ENVIRONMENT === 'development') {
            die("Error: Required configuration constant '{$constant}' is not defined or empty.");
        } else {
            logMessage('ERROR', "Missing required configuration: {$constant}");
        }
    }
}

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>