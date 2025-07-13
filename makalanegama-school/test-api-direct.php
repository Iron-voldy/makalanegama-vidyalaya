<?php
// Direct test of the teachers API
echo "<h2>Direct API Test</h2>";

try {
    // Include required files manually
    require_once 'admin/config.php';
    require_once 'admin/database.php';
    
    echo "<p>✅ Files included successfully</p>";
    
    // Create database connection
    $db = new Database();
    echo "<p>✅ Database connected</p>";
    
    // Get teachers directly
    $teachers = $db->getTeachers(10);
    echo "<p>✅ Found " . count($teachers) . " teachers</p>";
    
    // Return as JSON like the API
    header('Content-Type: application/json');
    echo json_encode($teachers, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>