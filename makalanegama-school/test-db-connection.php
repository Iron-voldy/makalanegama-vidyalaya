<?php
/**
 * Database Connection Test for InfinityFree
 * Upload this file to test if database connection works
 * Delete this file after successful testing for security
 */

// Database configuration for InfinityFree
$host = 'sql201.infinityfree.com';
$dbname = 'if0_39408289_makalanegama_school'; // Replace with your actual database name
$username = 'if0_39408289';
$password = 'Hasindu2002';
$port = 3306;

echo "<h1>Database Connection Test</h1>";
echo "<p><strong>Testing connection to InfinityFree database...</strong></p>";

try {
    // Create PDO connection
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    echo "<p style='color: green;'>✅ <strong>Database connection successful!</strong></p>";
    echo "<p>Host: $host</p>";
    echo "<p>Database: $dbname</p>";
    echo "<p>Username: $username</p>";
    
    // Test if we can query the database
    $stmt = $pdo->query("SELECT DATABASE() as current_db");
    $result = $stmt->fetch();
    echo "<p>Connected to database: <strong>" . $result['current_db'] . "</strong></p>";
    
    // Check if tables exist
    echo "<h3>Checking Tables:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p style='color: orange;'>⚠️ No tables found. You need to import the database schema.</p>";
        echo "<p>Import the <code>database/school.sql</code> file through phpMyAdmin.</p>";
    } else {
        echo "<p style='color: green;'>✅ Found " . count($tables) . " tables:</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
        // Test teachers table specifically
        if (in_array('teachers', $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM teachers");
            $result = $stmt->fetch();
            echo "<p>Teachers table has <strong>" . $result['count'] . "</strong> records.</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ <strong>Database connection failed!</strong></p>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Verify the database name is exactly: <code>if0_39408289_makalanegama_school</code></li>";
    echo "<li>Check if the database exists in your InfinityFree control panel</li>";
    echo "<li>Verify hostname: <code>sql201.infinityfree.com</code></li>";
    echo "<li>Check username: <code>if0_39408289</code></li>";
    echo "<li>Verify password is correct</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><strong>Important:</strong> Delete this file after testing for security!</p>";
echo "<p>File location: <code>" . __FILE__ . "</code></p>";
?>