<?php
/**
 * Database Setup Script for Makalanegama School
 * This will create the database and import the schema with sample data
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🚀 Makalanegama School Database Setup</h2>";

try {
    // First, connect to MySQL without specifying a database
    $host = 'localhost';
    $username = 'root';
    $password = '2009928';
    $port = 3306;
    
    echo "<p>📡 Connecting to MySQL server...</p>";
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p>✅ Connected to MySQL server successfully!</p>";
    
    // Read the SQL file
    $sqlFile = __DIR__ . '/database/school.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    echo "<p>📂 Reading SQL file: " . htmlspecialchars($sqlFile) . "</p>";
    $sql = file_get_contents($sqlFile);
    
    if (!$sql) {
        throw new Exception("Failed to read SQL file or file is empty");
    }
    
    echo "<p>📝 SQL file read successfully (" . strlen($sql) . " characters)</p>";
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt) && !preg_match('/^\s*SET/', $stmt) && $stmt !== 'START TRANSACTION' && $stmt !== 'COMMIT';
        }
    );
    
    echo "<p>🔢 Found " . count($statements) . " SQL statements to execute</p>";
    
    $executed = 0;
    $errors = [];
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        try {
            $pdo->exec($statement);
            $executed++;
            
            // Show progress for important operations
            if (stripos($statement, 'CREATE DATABASE') !== false) {
                echo "<p>✅ Database created</p>";
            } elseif (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE `?(\w+)`?/i', $statement, $matches);
                $tableName = $matches[1] ?? 'unknown';
                echo "<p>✅ Table created: $tableName</p>";
            } elseif (stripos($statement, 'INSERT INTO') !== false) {
                preg_match('/INSERT INTO `?(\w+)`?/i', $statement, $matches);
                $tableName = $matches[1] ?? 'unknown';
                
                // Count how many rows were inserted
                $rowCount = substr_count($statement, 'VALUES') + substr_count($statement, 'values');
                if ($rowCount <= 0) $rowCount = 1;
                
                echo "<p>✅ Sample data inserted into $tableName ($rowCount rows)</p>";
            }
            
        } catch (PDOException $e) {
            $errors[] = "Error executing statement: " . $e->getMessage() . "\nStatement: " . substr($statement, 0, 100) . "...";
            echo "<p>⚠️ Warning: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    echo "<h3>📊 Setup Summary</h3>";
    echo "<p>✅ Executed: $executed statements</p>";
    echo "<p>⚠️ Errors: " . count($errors) . "</p>";
    
    if (count($errors) > 0) {
        echo "<h4>Error Details:</h4>";
        foreach ($errors as $error) {
            echo "<p style='color: red; font-size: 0.9em;'>" . htmlspecialchars($error) . "</p>";
        }
    }
    
    // Test the final connection with the new database
    echo "<h3>🔗 Testing Database Connection</h3>";
    
    $testPdo = new PDO("mysql:host=$host;port=$port;dbname=makalanegama_school;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p>✅ Connected to makalanegama_school database successfully!</p>";
    
    // Check tables
    $stmt = $testPdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>📋 Tables created:</p><ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Check teachers data
    $stmt = $testPdo->query("SELECT COUNT(*) as count FROM teachers");
    $teacherCount = $stmt->fetch()['count'];
    
    echo "<p>👥 Teachers in database: $teacherCount</p>";
    
    if ($teacherCount > 0) {
        $stmt = $testPdo->query("SELECT name, subject FROM teachers LIMIT 3");
        $teachers = $stmt->fetchAll();
        
        echo "<p>Sample teachers:</p><ul>";
        foreach ($teachers as $teacher) {
            echo "<li>" . htmlspecialchars($teacher['name']) . " - " . htmlspecialchars($teacher['subject']) . "</li>";
        }
        echo "</ul>";
    }
    
    // Check admin user
    $stmt = $testPdo->query("SELECT COUNT(*) as count FROM admin_users");
    $adminCount = $stmt->fetch()['count'];
    
    echo "<p>👤 Admin users in database: $adminCount</p>";
    
    if ($adminCount > 0) {
        $stmt = $testPdo->query("SELECT username, full_name FROM admin_users LIMIT 1");
        $admin = $stmt->fetch();
        echo "<p>Default admin: " . htmlspecialchars($admin['username']) . " (" . htmlspecialchars($admin['full_name']) . ")</p>";
        echo "<p>🔑 <strong>Default admin login:</strong> username: <code>admin</code>, password: <code>admin123</code></p>";
    }
    
    echo "<h3>🎉 Setup Complete!</h3>";
    echo "<p>✅ Database setup completed successfully!</p>";
    echo "<p>🌐 You can now visit <a href='teachers.html'>teachers.html</a> to see the teachers with real database data</p>";
    echo "<p>🏢 Admin panel: <a href='admin/'>admin/</a> (login with admin/admin123)</p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Setup Failed</h3>";
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please make sure:</p>";
    echo "<ul>";
    echo "<li>XAMPP is running</li>";
    echo "<li>MySQL service is started</li>";
    echo "<li>MySQL credentials are correct (root/2009928)</li>";
    echo "<li>You have permission to create databases</li>";
    echo "</ul>";
}
?>