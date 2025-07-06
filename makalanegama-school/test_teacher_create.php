<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'admin/config.php';
require_once 'admin/database.php';

echo "=== DIRECT TEACHER CREATION TEST ===\n";

try {
    $db = new Database();
    
    // Simple test data
    $data = [
        'name' => 'Test Teacher Direct',
        'qualification' => 'Bachelor of Science',
        'subject' => 'Mathematics',
        'department' => 'Science & Mathematics',
        'bio' => 'Test bio',
        'experience_years' => 5,
        'email' => 'test@direct.com',
        'phone' => '0771234567',
        'photo_url' => null,
        'specializations' => null,
        'is_active' => 1
    ];
    
    echo "Test data:\n";
    print_r($data);
    echo "\n";
    
    // Try direct creation
    echo "Attempting direct teacher creation...\n";
    $result = $db->createTeacher($data);
    
    echo "Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
    
    if (!$result) {
        // Get PDO error info
        $pdo = $db->getPDO();
        $errorInfo = $pdo->errorInfo();
        echo "PDO Error Info: ";
        print_r($errorInfo);
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>