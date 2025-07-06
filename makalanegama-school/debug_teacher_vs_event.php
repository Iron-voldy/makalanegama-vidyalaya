<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'admin/config.php';
require_once 'admin/database.php';

echo "Comparing Teacher vs Event creation methods...\n\n";

try {
    $db = new Database();
    
    // Test Event Creation (which works)
    echo "=== TESTING EVENT CREATION (WORKING) ===\n";
    $eventData = [
        'title' => 'Test Event',
        'description' => 'Test Description',
        'event_date' => '2025-08-01',
        'event_time' => '10:00:00',
        'location' => 'School',
        'category' => 'Academic',
        'is_featured' => false
    ];
    
    echo "Event data:\n";
    print_r($eventData);
    
    $eventResult = $db->createEvent($eventData);
    echo "Event creation result: " . ($eventResult ? 'SUCCESS' : 'FAILED') . "\n\n";
    
    // Test Teacher Creation (which fails)
    echo "=== TESTING TEACHER CREATION (FAILING) ===\n";
    $teacherData = [
        'name' => 'Test Teacher Debug',
        'qualification' => 'Bachelor of Science',
        'subject' => 'Mathematics',
        'department' => 'Science & Mathematics',
        'bio' => 'Test bio',
        'experience_years' => 5,
        'email' => 'debug@test.com',
        'phone' => '0771234567',
        'photo_url' => null,
        'specializations' => null,
        'is_active' => 1
    ];
    
    echo "Teacher data:\n";
    print_r($teacherData);
    
    $teacherResult = $db->createTeacher($teacherData);
    echo "Teacher creation result: " . ($teacherResult ? 'SUCCESS' : 'FAILED') . "\n\n";
    
    // Check the actual SQL execution for teacher creation
    echo "=== TESTING DIRECT TEACHER SQL ===\n";
    $sql = "INSERT INTO teachers (name, qualification, subject, department, bio, experience_years, email, phone, photo_url, specializations, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->getPDO()->prepare($sql);
    
    $directResult = $stmt->execute([
        $teacherData['name'],
        $teacherData['qualification'],
        $teacherData['subject'],
        $teacherData['department'],
        $teacherData['bio'],
        $teacherData['experience_years'],
        $teacherData['email'],
        $teacherData['phone'],
        $teacherData['photo_url'],
        $teacherData['specializations'],
        $teacherData['is_active']
    ]);
    
    echo "Direct SQL result: " . ($directResult ? 'SUCCESS' : 'FAILED') . "\n";
    if (!$directResult) {
        echo "SQL Error: " . print_r($stmt->errorInfo(), true) . "\n";
    }
    
    // Check if there are constraints or triggers causing issues
    echo "\n=== CHECKING TABLE CONSTRAINTS ===\n";
    $stmt = $db->getPDO()->query("SHOW CREATE TABLE teachers");
    $tableInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Teachers table structure:\n";
    echo $tableInfo['Create Table'] . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>