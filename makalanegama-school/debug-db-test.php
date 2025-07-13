<?php
// Simple test file to check database connection and teachers data
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

try {
    require_once 'admin/config.php';
    echo "<p>✅ Config loaded successfully</p>";
    
    require_once 'admin/database.php';
    echo "<p>✅ Database class loaded successfully</p>";
    
    $db = new Database();
    echo "<p>✅ Database connection successful</p>";
    
    // Test simple query
    $pdo = $db->getPDO();
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "<p>✅ Basic query test successful: " . $result['test'] . "</p>";
    
    // Check teachers table
    $stmt = $pdo->query("SHOW TABLES LIKE 'teachers'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Teachers table exists</p>";
        
        // Count teachers
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM teachers");
        $count = $stmt->fetch()['count'];
        echo "<p>📊 Total teachers in database: " . $count . "</p>";
        
        if ($count > 0) {
            // Show first few teachers
            $stmt = $pdo->query("SELECT name, subject, department FROM teachers LIMIT 3");
            $teachers = $stmt->fetchAll();
            echo "<p>👥 Sample teachers:</p><ul>";
            foreach ($teachers as $teacher) {
                echo "<li>" . htmlspecialchars($teacher['name']) . " - " . htmlspecialchars($teacher['subject']) . " (" . htmlspecialchars($teacher['department']) . ")</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>⚠️ No teachers found in database</p>";
        }
    } else {
        echo "<p>❌ Teachers table does not exist</p>";
    }
    
    // Test the API endpoint
    echo "<h3>Testing API Endpoint</h3>";
    $apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/api/teachers.php?limit=5';
    echo "<p>API URL: " . htmlspecialchars($apiUrl) . "</p>";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10
        ]
    ]);
    
    $apiResponse = file_get_contents($apiUrl, false, $context);
    if ($apiResponse !== false) {
        $apiData = json_decode($apiResponse, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<p>✅ API responded successfully</p>";
            echo "<pre>" . htmlspecialchars(json_encode($apiData, JSON_PRETTY_PRINT)) . "</pre>";
        } else {
            echo "<p>❌ API returned invalid JSON</p>";
            echo "<pre>" . htmlspecialchars($apiResponse) . "</pre>";
        }
    } else {
        echo "<p>❌ Failed to call API</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Stack trace:</p><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>