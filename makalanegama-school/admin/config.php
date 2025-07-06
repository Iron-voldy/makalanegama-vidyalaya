<?php
/**
 * Admin Configuration file for Makalanegama School Website
 */

// Prevent direct access
if (!defined('INCLUDED')) {
    define('INCLUDED', true);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Environment settings
define('ENVIRONMENT', 'development'); // Change to 'production' for live site

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'makalanegama_school');
define('DB_USER', 'root');
define('DB_PASS', '2009928');

// File upload settings
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'webp']);
define('UPLOAD_PATH', '../assets/uploads/');
define('IMAGES_PATH', '../assets/images/');

// Admin settings
define('ADMIN_SESSION_TIMEOUT', 3600); // 1 hour
define('ITEMS_PER_PAGE', 10);

// Create necessary directories if they don't exist
$directories = [
    UPLOAD_PATH,
    UPLOAD_PATH . date('Y'),
    UPLOAD_PATH . date('Y/m'),
    IMAGES_PATH
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Error reporting based on environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone setting
date_default_timezone_set('Asia/Colombo');

// Helper functions
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && 
           isset($_SESSION['admin_username']) && 
           (time() - $_SESSION['last_activity']) < ADMIN_SESSION_TIMEOUT;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

function uploadImage($file) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        error_log("Upload failed: No temp file");
        return false;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("Upload failed: Error code " . $file['error']);
        return false;
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, ALLOWED_IMAGE_TYPES)) {
        error_log("Upload failed: Invalid file type: " . $extension);
        return false;
    }
    
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        error_log("Upload failed: File too large: " . $file['size'] . " bytes");
        return false;
    }
    
    $uploadDir = UPLOAD_PATH . date('Y/m/');
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("Upload failed: Cannot create directory: " . $uploadDir);
            return false;
        }
    }
    
    $filename = uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $relativePath = str_replace('../', '', $uploadPath);
        error_log("Upload successful: " . $relativePath);
        return $relativePath;
    } else {
        error_log("Upload failed: Cannot move file to " . $uploadPath);
        return false;
    }
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M j, Y - g:i A', strtotime($datetime));
}
?>